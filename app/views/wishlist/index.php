<?php 
$user = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/posts.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div class="header-flex">
            <div>
                <h1 class="page-title">My Wishlist</h1>
                <p class="page-sub">Your curated list of dream destinations to visit</p>
            </div>
            <a href="index.php?page=user" class="btn btn-ghost">&larr; Back to Explore</a>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = ['updated' => 'Wishlist updated successfully!'];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-toolbar">
            <h3 class="card-title">Saved Destinations</h3>
            <span class="badge"><?= count($wishlistItems) ?> saved places</span>
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Destination</th>
                        <th>Country</th>
                        <th>Cost Level</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="wishlistBody">
                    <?php if (empty($wishlistItems)): ?>
                        <tr><td colspan="5" class="empty">Your wishlist is empty. Start exploring!</td></tr>
                    <?php else: ?>
                        <?php foreach ($wishlistItems as $i => $item): ?>
                            <tr id="row-<?= $item['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                <td><?= htmlspecialchars($item['country']) ?></td>
                                <td>
                                    <span class="cost-badge <?= strtolower($item['cost_level']) ?>">
                                        <?= ucfirst($item['cost_level']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a class="btn-sm btn-delete" 
                                       href="javascript:void(0)" 
                                       onclick="removeWishlistItem(<?= $item['id'] ?>)"
                                       >Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>



<script>
function removeWishlistItem(postId) {
    if (!confirm('Remove this place from your wishlist?')) return;

    //Use FormData to send a POST request, just like the demo's AJAX actions
    var formData = new FormData();
    formData.append('post_id', postId);

    fetch('index.php?page=wishlist&action=remove', {
        method: 'POST',
        body: formData
    })
    .then(function (response) { return response.json(); })
    .then(function (data) {
        if (data.success) {
            //Remove the row from the table without reloading the page
            var row = document.getElementById('row-' + postId);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(function() {
                    row.remove();
                }, 300);
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(function (error) {
        console.error('Error:', error);
        alert('Something went wrong. Please try again.');
    });
}
</script>

</body>
</html>


