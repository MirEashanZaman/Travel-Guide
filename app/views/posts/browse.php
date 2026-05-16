<?php 
$user = $_SESSION['user']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Places &mdash; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/posts.css">
</head>
<body class="app-body">

<?php require 'app/views/layout/navbar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Explore Visiting Places</h1>
            <p class="page-sub">Discover the best destinations worldwide, filtered by your preferences</p>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php 
            $messages = ['added' => 'Comment added successfully!', 'deleted' => 'Comment removed.'];
            $msg = $messages[$_GET['msg']] ?? null; 
        ?>
        <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php endif; ?>

    <div class="card filter-card">
        <div class="filter-bar">
            <!-- Search -->
            <div class="field filter-field-grow">
                <label>Search Destination</label>
                <div class="search-wrap filter-search-wrap">
                    <span class="search-icon">&#128269;</span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Name or country...">
                </div>
            </div>

            <!-- Cost Filter -->
            <div class="field">
                <label>Budget</label>
                <div class="budget-filter-wrap">
                    <label class="budget-label"><input type="radio" name="cost" value="" checked> All</label>
                    <label class="budget-label"><input type="radio" name="cost" value="low"> $ (Low - $500)</label>
                    <label class="budget-label"><input type="radio" name="cost" value="medium"> $$ (Med - $1500)</label>
                    <label class="budget-label"><input type="radio" name="cost" value="high"> $$$ (High - $3000)</label>
                </div>
            </div>
        </div>

        <!-- Genre Filter -->
        <div class="genre-filter-wrap">
            <span class="genre-filter-title">Genres:</span>
            <?php 
                $genres = ['Nature', 'Historical', 'City', 'Beach', 'Mountain', 'Adventure'];
                foreach ($genres as $g): 
            ?>
                <label class="genre-label">
                    <input type="checkbox" name="genre" value="<?= strtolower($g) ?>"> <?= $g ?>
                </label>
            <?php endforeach; ?>
        </div>
        
        <div class="filter-footer">
            <span class="badge" id="resultCount"><?= count($posts) ?> places found</span>
            <button class="btn btn-ghost btn-reset" onclick="resetFilters()">Reset All</button>
        </div>
    </div>

    <div id="postGrid" class="post-grid">
        <?php if (empty($posts)): ?>
            <div class="card"><p class="empty">No approved places found.</p></div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                    <div class="post-meta">
                        <span><?= htmlspecialchars($post['country']) ?> &middot; <?= ucfirst($post['genre']) ?></span>
                        <?php 
                            $valMap = ['low' => '$500', 'medium' => '$1500', 'high' => '$3000'];
                            $val = $valMap[strtolower($post['cost_level'])] ?? '';
                        ?>
                        <span class="cost-badge <?= strtolower($post['cost_level']) ?>"><?= ucfirst($post['cost_level']) ?> (<?= $val ?>)</span>
                    </div>
                    <div class="post-snippet">
                        <?= htmlspecialchars(mb_strimwidth($post['short_history'], 0, 100, "...")) ?>
                    </div>
                    <div class="post-footer">
                        <a class="btn btn-primary btn-full-width" href="index.php?page=user&action=detail&id=<?= $post['id'] ?>">Read More</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>



<script>
(function () {
    var searchInput   = document.getElementById('searchInput');
    var costRadios    = document.getElementsByName('cost');
    var genreChecks   = document.getElementsByName('genre');
    var grid          = document.getElementById('postGrid');
    var counter       = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function trim(s, len) {
        if (!s) return '';
        return s.length > len ? s.substring(0, len) + "..." : s;
    }

    function render(rows) {
        if (!rows.length) {
            grid.innerHTML = '<div class="card text-center-empty"><p class="empty">No destinations match your filters. Try adjusting them!</p></div>';
            counter.textContent = '0 results';
            return;
        }
        var html = '';
        rows.forEach(function (r) {
            var valMap = {'low': '$500', 'medium': '$1500', 'high': '$3000'};
            var val = valMap[(r.cost_level || 'medium').toLowerCase()] || '';
            html +=
                '<div class="post-card">' +
                    '<h3>' + esc(r.title) + '</h3>' +
                    '<div class="post-meta">' +
                        '<span>' + esc(r.country) + ' &middot; ' + (r.genre ? r.genre.charAt(0).toUpperCase() + r.genre.slice(1) : 'Travel') + '</span>' +
                        '<span class="cost-badge ' + (r.cost_level || 'medium').toLowerCase() + '">' + 
                            (r.cost_level ? (r.cost_level.charAt(0).toUpperCase() + r.cost_level.slice(1)) : 'Medium') + ' (' + val + ')' +
                        '</span>' +
                    '</div>' +
                    '<div class="post-snippet">' + esc(trim(r.short_history, 100)) + '</div>' +
                    '<div class="post-footer">' +
                        '<a class="btn btn-primary btn-full-width" href="index.php?page=user&action=detail&id=' + r.id + '">Read More</a>' +
                    '</div>' +
                '</div>';
        });
        grid.innerHTML = html;
        counter.textContent = rows.length + ' results found';
    }

    window.resetFilters = function() {
        searchInput.value = "";
        costRadios[0].checked = true;
        for (var i = 0; i < genreChecks.length; i++) genreChecks[i].checked = false;
        //Call applyFilters instantly for immediate reset feedback
        applyFilters(true);
    };

    function applyFilters(instant) {
        clearTimeout(timer);
        var run = function () {
            var q = searchInput.value.trim();
            var cost = "";
            for (var i = 0; i < costRadios.length; i++) {
                if (costRadios[i].checked) { cost = costRadios[i].value; break; }
            }
            var genres = [];
            for (var i = 0; i < genreChecks.length; i++) {
                if (genreChecks[i].checked) genres.push(genreChecks[i].value);
            }

            var url = 'index.php?page=ajax&type=filter&q=' + encodeURIComponent(q) + 
                      '&country=' + 
                      '&cost=' + encodeURIComponent(cost) + 
                      '&genre=' + encodeURIComponent(genres.join(','));

            fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        };

        if (instant === true) run();
        else timer = setTimeout(run, 300);
    }

    searchInput.addEventListener('input', applyFilters);
    for (var i = 0; i < costRadios.length; i++) costRadios[i].addEventListener('change', applyFilters);
    for (var i = 0; i < genreChecks.length; i++) genreChecks[i].addEventListener('change', applyFilters);
})();
</script>

</body>
</html>


