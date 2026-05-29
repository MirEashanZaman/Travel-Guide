<?php

function scoutCtrl($conn) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'scout') {
        header('Location: index.php?page=login');
        exit;
    }

    $scoutId = $_SESSION['user']['id'];
    $action = $_GET['action'] ?? '';
    $error = '';
    $editing = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'add') {
            //Handle optional image upload
            $image_path = $_POST['old_image'] ?? '';
            if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['post_image'];
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($ext, $allowed)) {
                    $error = "Invalid image type. Only JPG, PNG, WEBP allowed.";
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $error = "Image too large. Max 2MB.";
                } else {
                    $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                    $target = 'public/uploads/posts/' . $filename;
                    if (!is_dir('public/uploads/posts/')) mkdir('public/uploads/posts/', 0777, true);
                    if (move_uploaded_file($file['tmp_name'], $target)) {
                        $image_path = $target;
                    }
                }
            }

            if (empty($error)) {
                $expected_cost = floatval($_POST['expected_cost'] ?? 1500);
                $derived_cost_level = ($expected_cost < 1000) ? 'low' : (($expected_cost <= 2500) ? 'medium' : 'high');

                // Parse itinerary items
                $itinerary_data = [];
                if (isset($_POST['itinerary']) && is_array($_POST['itinerary'])) {
                    foreach ($_POST['itinerary'] as $dayNum => $times) {
                        foreach ($times as $timeOfDay => $fields) {
                            $title = trim($fields['activity_title'] ?? '');
                            $desc = trim($fields['activity_description'] ?? '');
                            $cost = floatval($fields['estimated_cost'] ?? 0.00);
                            
                            if ($title !== '') {
                                $itinerary_data[] = [
                                    'day_number' => intval($dayNum),
                                    'time_of_day' => $timeOfDay,
                                    'activity_title' => $title,
                                    'activity_description' => $desc,
                                    'estimated_cost' => $cost
                                ];
                            }
                        }
                    }
                }

                $data = [
                    'title' => trim($_POST['title'] ?? ''),
                    'country' => trim($_POST['country'] ?? ''),
                    'short_history' => trim($_POST['short_history'] ?? ''),
                    'genre' => trim($_POST['genre'] ?? ''),
                    'expected_cost' => $expected_cost,
                    'cost_level' => $derived_cost_level,
                    'travel_medium_info' => trim($_POST['travel_medium_info'] ?? ''),
                    'image' => $image_path,
                    'itinerary' => $itinerary_data
                ];

                if (empty($data['title']) || empty($data['country'])) {
                    $error = "Title and Country are required.";
                } else {
                    $originalPostId = !empty($_POST['original_post_id']) ? intval($_POST['original_post_id']) : null;
                    if (createPostRequest($conn, $scoutId, json_encode($data), $originalPostId)) {
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                            echo json_encode(['success' => true, 'message' => 'Request submitted!']);
                            exit;
                        }
                        header('Location: index.php?page=scout&msg=added');
                        exit;
                    }
                    $error = "Database error.";
                }
            }
        } elseif ($action === 'update') {
            $reqId = intval($_POST['request_id'] ?? 0);
            $oldReq = getPostRequestById($conn, $reqId);
            
            if ($oldReq) {
                $oldData = json_decode($oldReq['post_data'], true);
                $image_path = $_POST['old_image'] ?? $oldData['image'] ?? '';
                
                if (isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['post_image'];
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    if (!in_array($ext, $allowed)) {
                        $error = "Invalid image type.";
                    } elseif ($file['size'] > 2 * 1024 * 1024) {
                        $error = "Image too large (Max 2MB).";
                    } else {
                        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
                        $target = 'public/uploads/posts/' . $filename;
                        if (!is_dir('public/uploads/posts/')) mkdir('public/uploads/posts/', 0777, true);
                        if (move_uploaded_file($file['tmp_name'], $target)) {
                            $image_path = $target;
                        }
                    }
                }

                if (empty($error)) {
                    $expected_cost = floatval($_POST['expected_cost'] ?? 1500);
                    $derived_cost_level = ($expected_cost < 1000) ? 'low' : (($expected_cost <= 2500) ? 'medium' : 'high');

                    //Parse itinerary items
                    $itinerary_data = [];
                    if (isset($_POST['itinerary']) && is_array($_POST['itinerary'])) {
                        foreach ($_POST['itinerary'] as $dayNum => $times) {
                            foreach ($times as $timeOfDay => $fields) {
                                $title = trim($fields['activity_title'] ?? '');
                                $desc = trim($fields['activity_description'] ?? '');
                                $cost = floatval($fields['estimated_cost'] ?? 0.00);
                                
                                if ($title !== '') {
                                    $itinerary_data[] = [
                                        'day_number' => intval($dayNum),
                                        'time_of_day' => $timeOfDay,
                                        'activity_title' => $title,
                                        'activity_description' => $desc,
                                        'estimated_cost' => $cost
                                    ];
                                }
                            }
                        }
                    }

                    $data = [
                        'title' => trim($_POST['title'] ?? ''),
                        'country' => trim($_POST['country'] ?? ''),
                        'short_history' => trim($_POST['short_history'] ?? ''),
                        'genre' => trim($_POST['genre'] ?? ''),
                        'expected_cost' => $expected_cost,
                        'cost_level' => $derived_cost_level,
                        'travel_medium_info' => trim($_POST['travel_medium_info'] ?? ''),
                        'image' => $image_path,
                        'itinerary' => $itinerary_data
                    ];

                    if (empty($data['title']) || empty($data['country'])) {
                        $error = "Title and Country required";
                    } else {
                        if (updatePostRequest($conn, $reqId, json_encode($data))) {
                            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                                echo json_encode(['success' => true, 'message' => 'Request updated']);
                                exit;
                            }
                            header('Location: index.php?page=scout&msg=updated');
                            exit;
                        }
                        $error = "Database error";
                    }
                }
            } else {
                $error = "Request not found";
            }
        }

        //AJAX error handler
        if (!empty($error) && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['success' => false, 'message' => $error]);
            exit;
        }
    } else {
        if ($action === 'delete') {
            $reqId = intval($_GET['id'] ?? 0);
            $ok = deletePostRequest($conn, $reqId, $scoutId);
            
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => $ok, 'message' => $ok ? 'Deleted.' : 'Failed to delete.']);
                exit;
            }
            
            header('Location: index.php?page=scout&msg=' . ($ok ? 'deleted' : 'error'));
            exit;
        } elseif ($action === 'edit') {
            $reqId = intval($_GET['id'] ?? 0);
            $req = getPostRequestById($conn, $reqId);
            if ($req && $req['scout_id'] == $scoutId) {
                if ($req['status'] === 'pending') {
                    $editing = json_decode($req['post_data'], true);
                    $editing['id'] = $req['id'];
                    $editing['original_post_id'] = $req['original_post_id'];
                } else {
                    header('Location: index.php?page=scout&msg=error');
                    exit;
                }
            } else {
                header('Location: index.php?page=scout');
                exit;
            }
        } elseif ($action === 'request_change') {
            $postId = intval($_GET['id'] ?? 0);
            $post = getPost($conn, $postId);
            if ($post && $post['scout_id'] == $scoutId) {
                $isEdit = true;
                $editing = $post;
                $editing['original_post_id'] = $postId;
                unset($editing['id']); 
                $costEst = getCostEstimate($conn, $postId);
                $editing['expected_cost'] = $costEst ? floatval($costEst['base_cost']) : 1500;
                
                //Fetch the existing itinerary items
                $editing['itinerary'] = getItinerary($conn, $postId);
            } else {
                header('Location: index.php?page=scout');
                exit;
            }
        }
    }

    $approvedPosts = getPostsByScout($conn, $scoutId);

    $rawRequests = getRequestsByScout($conn, $scoutId);
    $requests = [];
    foreach ($rawRequests as $r) {
        $r['data'] = json_decode($r['post_data'], true);
        
        //If the request was approved, verify if the post still exists in the database
        if ($r['status'] === 'approved') {
            $postExists = false;
            foreach ($approvedPosts as $key => $post) {
                if ($post['title'] === $r['data']['title']) {
                    $postExists = true;
                    unset($approvedPosts[$key]);
                    break;
                }
            }
            //If it was approved but the post is missing, it means it was deleted
            if (!$postExists) {
                $r['status'] = 'deleted';
            }
        }
        
        $requests[] = $r;
    }
    
    $approvedPosts = getPostsByScout($conn, $scoutId);

    require 'app/views/scout/dashboard.php';
}

?>
