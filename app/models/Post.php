<?php
 //Fetch all posts that have been approved by the admin
function getApprovedPosts($conn) {
    $r = mysqli_query($conn, "SELECT posts.*, cost_estimates.base_cost FROM posts LEFT JOIN cost_estimates ON posts.id = cost_estimates.post_id WHERE status = 'approved' ORDER BY created_at DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

//Fetch all approved posts submitted by a specific scout
function getPostsByScout($conn, $scoutId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE scout_id = ? AND status = 'approved' ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'i', $scoutId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Fetch a single post by its ID for the detail page
function getPost($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ? AND status = 'approved'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

//AJAX Search: Find approved posts by title or country
function searchPosts($conn, $term) {
    $like = '%' . $term . '%';
    $stmt = mysqli_prepare($conn, 
        "SELECT posts.*, cost_estimates.base_cost FROM posts 
         LEFT JOIN cost_estimates ON posts.id = cost_estimates.post_id
         WHERE (title LIKE ? OR country LIKE ?) 
         AND status = 'approved' 
         ORDER BY posts.id DESC"
    );
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Add a new comment to a post
function addComment($conn, $postId, $userId, $content, $rating = 5) {
    $rating = max(1, min(5, intval($rating)));
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, user_id, content, rating) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iisi', $postId, $userId, $content, $rating);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Fetch all comments for a post, including the user name
function getComments($conn, $postId) {
    $stmt = mysqli_prepare($conn, 
        "SELECT comments.*, users.name as user_name 
         FROM comments 
         JOIN users ON comments.user_id = users.id 
         WHERE comments.post_id = ? 
         ORDER BY comments.created_at ASC"
    );
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

//Delete a comment (only if it belongs to the logged-in user)
function deleteComment($conn, $commentId, $userId) {
    $stmt = mysqli_prepare($conn, "DELETE FROM comments WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $commentId, $userId);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

//Fetch cost estimate for a specific post
function getCostEstimate($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM cost_estimates WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}
function filterPosts($conn, $params) {
    $sql = "SELECT posts.*, cost_estimates.base_cost FROM posts LEFT JOIN cost_estimates ON posts.id = cost_estimates.post_id WHERE status = 'approved'";
    $types = "";
    $args = [];

    if (!empty($params['q'])) {
        $sql .= " AND (title LIKE ? OR country LIKE ?)";
        $q = "%" . $params['q'] . "%";
        $args[] = $q; $args[] = $q;
        $types .= "ss";
    }

    if (!empty($params['country'])) {
        $sql .= " AND country = ?";
        $args[] = $params['country'];
        $types .= "s";
    }

    if (!empty($params['cost_level'])) {
        $sql .= " AND cost_level = ?";
        $args[] = $params['cost_level'];
        $types .= "s";
    }

    if (!empty($params['genres'])) {
        $genreList = $params['genres'];
        $placeholders = implode(',', array_fill(0, count($genreList), '?'));
        $sql .= " AND genre IN ($placeholders)";
        foreach ($genreList as $g) {
            $args[] = $g;
            $types .= "s";
        }
    }

    $sql .= " ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($args)) {
        mysqli_stmt_bind_param($stmt, $types, ...$args);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

function getAllCountries($conn) {
    $r = mysqli_query($conn, "SELECT DISTINCT country FROM posts WHERE status = 'approved' ORDER BY country ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getLocalPhrases($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM local_phrases WHERE post_id = ? ORDER BY phrase_no ASC");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function seedDefaultLocalPhrases($conn, $postId) {
    $post = getPost($conn, $postId);
    if (!$post) return false;
    
    $country = strtolower($post['country']);
    
    $phrases = [];
    if (strpos($country, 'france') !== false || strpos($country, 'paris') !== false) {
        $phrases = [
            ['no' => 1, 'orig' => 'Bonjour', 'trans' => 'Hello / Good morning', 'phon' => 'bohn-zhoor'],
            ['no' => 2, 'orig' => 'Merci beaucoup', 'trans' => 'Thank you very much', 'phon' => 'mair-see boh-koo'],
            ['no' => 3, 'orig' => 'S\'il vous plaît', 'trans' => 'Please', 'phon' => 'seel voo pleh'],
            ['no' => 4, 'orig' => 'Excusez-moi, pourriez-vous m\'aider?', 'trans' => 'Excuse me, could you help me?', 'phon' => 'ek-skew-zay mwah, poo-ree-ay voo may-day'],
            ['no' => 5, 'orig' => 'L\'addition, s\'il vous plaît', 'trans' => 'The bill, please', 'phon' => 'lah-dee-syohn seel voo pleh']
        ];
    } elseif (strpos($country, 'germany') !== false || strpos($country, 'berlin') !== false) {
        $phrases = [
            ['no' => 1, 'orig' => 'Guten Tag', 'trans' => 'Hello / Good day', 'phon' => 'goo-ten tahk'],
            ['no' => 2, 'orig' => 'Danke schön', 'trans' => 'Thank you very much', 'phon' => 'dahn-kuh shuhn'],
            ['no' => 3, 'orig' => 'Bitte', 'trans' => 'Please / You\'re welcome', 'phon' => 'bit-tuh'],
            ['no' => 4, 'orig' => 'Entschuldigung, könnten Sie mir helfen?', 'trans' => 'Pardon me, could you help me?', 'phon' => 'ent-shool-dee-goong, kuhn-ten zee meer hel-fen'],
            ['no' => 5, 'orig' => 'Die Rechnung, bitte', 'trans' => 'The bill, please', 'phon' => 'dee rekh-noong bit-tuh']
        ];
    } elseif (strpos($country, 'bangladesh') !== false || strpos($country, 'dhaka') !== false || strpos($country, 'sylhet') !== false) {
        $phrases = [
            ['no' => 1, 'orig' => 'Assalamualaikum / Shagotom', 'trans' => 'Peace be upon you / Welcome', 'phon' => 'ash-salaam-mu-alaikum / sha-go-tom'],
            ['no' => 2, 'orig' => 'Dhonnobad', 'trans' => 'Thank you', 'phon' => 'dhon-no-bad'],
            ['no' => 3, 'orig' => 'Doya kore', 'trans' => 'Please', 'phon' => 'do-ya ko-re'],
            ['no' => 4, 'orig' => 'Washroom kothay?', 'trans' => 'Where is the washroom?', 'phon' => 'washroom ko-thay'],
            ['no' => 5, 'orig' => 'Etar dam koto?', 'trans' => 'How much is this?', 'phon' => 'e-tar dam ko-to']
        ];
    } else {
        $phrases = [
            ['no' => 1, 'orig' => 'Hello', 'trans' => 'Welcome / Greetings', 'phon' => 'heh-low'],
            ['no' => 2, 'orig' => 'Thank you', 'trans' => 'Expression of gratitude', 'phon' => 'thangk yoo'],
            ['no' => 3, 'orig' => 'Please', 'trans' => 'Polite request', 'phon' => 'pleez'],
            ['no' => 4, 'orig' => 'Where is the restroom?', 'trans' => 'Asking for toilet', 'phon' => 'wair iz thee rest-room'],
            ['no' => 5, 'orig' => 'How much is this?', 'trans' => 'Asking for price', 'phon' => 'how muhch iz this']
        ];
    }
    
    $ins = mysqli_prepare($conn, "INSERT INTO local_phrases (post_id, phrase_no, original_phrase, translation, phonetic) VALUES (?, ?, ?, ?, ?)");
    foreach ($phrases as $p) {
        mysqli_stmt_bind_param($ins, 'iisss', $postId, $p['no'], $p['orig'], $p['trans'], $p['phon']);
        mysqli_stmt_execute($ins);
    }
    mysqli_stmt_close($ins);
    return true;
}

function getItinerary($conn, $postId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM itinerary_items WHERE post_id = ? ORDER BY day_number ASC, FIELD(time_of_day, 'morning', 'afternoon', 'evening') ASC");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function seedDefaultItinerary($conn, $postId) {
    $post = getPost($conn, $postId);
    if (!$post) return false;
    
    $title = htmlspecialchars($post['title']);
    $country = htmlspecialchars($post['country']);
    
    $items = [
        [
            'day_number' => 1,
            'time_of_day' => 'morning',
            'activity_title' => 'Sunrise Discovery Tour',
            'activity_description' => "Kick off your journey in $title with an early morning walk to capture the iconic sunrise. Marvel at the waking city/landscape and enjoy fresh local breakfast options nearby.",
            'estimated_cost' => 15.00
        ],
        [
            'day_number' => 1,
            'time_of_day' => 'afternoon',
            'activity_title' => 'Historical Core & Heritage Exploration',
            'activity_description' => "Dive deep into the rich cultural history of $country by touring local heritage landmarks, museums, and notable architectural marvels accompanied by local storytelling.",
            'estimated_cost' => 25.00
        ],
        [
            'day_number' => 1,
            'time_of_day' => 'evening',
            'activity_title' => 'Culinary Tasting & Night Walk',
            'activity_description' => "Taste authentic local cuisines and street food delicacies. Experience the lively nightlife, stroll through bustling local markets, and relax by beautifully lit monuments.",
            'estimated_cost' => 30.00
        ],
        [
            'day_number' => 2,
            'time_of_day' => 'morning',
            'activity_title' => 'Scenic Adventure & Outdoor Trek',
            'activity_description' => "Embark on an outdoor trek or nature walk. Enjoy gorgeous scenic views, photography spots, and pristine natural surroundings away from the crowd.",
            'estimated_cost' => 10.00
        ],
        [
            'day_number' => 2,
            'time_of_day' => 'afternoon',
            'activity_title' => 'Artisans Market & Shopping Stroll',
            'activity_description' => "Visit local handicraft centers and support neighborhood artisans. Find unique souvenirs, textiles, and handmade products representing the true essence of $country.",
            'estimated_cost' => 20.00
        ],
        [
            'day_number' => 2,
            'time_of_day' => 'evening',
            'activity_title' => 'Sunset Views & Scenic Dinner',
            'activity_description' => "Conclude the day at a prime sunset vantage point followed by a scenic dinner featuring signature local dishes and live ambient cultural performances.",
            'estimated_cost' => 45.00
        ],
        [
            'day_number' => 3,
            'time_of_day' => 'morning',
            'activity_title' => 'Hidden Gems & Local Off-beaten Paths',
            'activity_description' => "Explore the lesser-known, secret locations loved by residents. Uncover quiet parks, quaint cafes, and local neighborhood secrets that regular tourists miss.",
            'estimated_cost' => 12.00
        ],
        [
            'day_number' => 3,
            'time_of_day' => 'afternoon',
            'activity_title' => 'Relaxation Session & Cultural Workshop',
            'activity_description' => "Participate in an interactive cultural pottery, cooking, or craft workshop. Connect with local hosts to learn their traditions firsthand while enjoying afternoon tea.",
            'estimated_cost' => 35.00
        ],
        [
            'day_number' => 3,
            'time_of_day' => 'evening',
            'activity_title' => 'Farewell Night and Skyline Views',
            'activity_description' => "Raise a glass to an unforgettable trip to $title! Capture final panoramic photos from a rooftop viewing deck or scenic riverside promenade to conclude your journey.",
            'estimated_cost' => 50.00
        ]
    ];
    
    $stmt = mysqli_prepare($conn, "INSERT INTO itinerary_items (post_id, day_number, time_of_day, activity_title, activity_description, estimated_cost) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        mysqli_stmt_bind_param($stmt, 'iisssd', $postId, $item['day_number'], $item['time_of_day'], $item['activity_title'], $item['activity_description'], $item['estimated_cost']);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);
    return true;
}
?>
