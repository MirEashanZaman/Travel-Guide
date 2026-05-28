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
    <!-- Leaflet.js (Free Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
                <label>Budget Tier</label>
                <div class="budget-filter-wrap">
                    <label class="budget-label"><input type="radio" name="cost" value="" checked> All</label>
                    <label class="budget-label"><input type="radio" name="cost" value="low"> Budget (Under $1,000)</label>
                    <label class="budget-label"><input type="radio" name="cost" value="medium"> Mid-Range ($1,000 - $2,500)</label>
                    <label class="budget-label"><input type="radio" name="cost" value="high"> Luxury (Over $2,500)</label>
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

    <!-- Map Controls -->
    <div class="card map-controls-bar">
        <div class="map-controls-bar-inner">
            <div class="map-controls-title-group">
                <span class="map-controls-title-icon">🗺️</span>
                <h3 class="map-controls-title-text">Interactive Destination Map</h3>
                <span class="badge map-controls-badge-count" id="mapCount">0 markers shown</span>
            </div>
            <div class="map-controls-action-group">
                <div class="map-controls-near-me-wrap">
                    <label class="map-controls-near-me-label">
                        <input type="checkbox" id="nearMeCheck" onchange="var rad = document.getElementById('nearMeRadius'); if (rad) rad.disabled = !this.checked; if (window.handleNearMeToggle) window.handleNearMeToggle(this);"> 📍 Near Me
                    </label>
                    <select id="nearMeRadius" class="calc-input map-controls-radius-select" onchange="if (window.handleNearMeRadiusChange) window.handleNearMeRadiusChange(this);" disabled>
                        <option value="500">within 500 km</option>
                        <option value="1500">within 1,500 km</option>
                        <option value="5000">within 5,000 km</option>
                        <option value="10000" selected>within 10,000 km</option>
                    </select>
                </div>
                <button id="toggleMapBtn" class="btn btn-ghost map-controls-toggle-btn" onclick="toggleMapDrawer()">Toggle Map View</button>
            </div>
        </div>
    </div>
    <!-- Map Container (separate stacking context) -->
    <div id="mapWrapper">
        <div id="leafletMap"></div>
    </div>

    <div id="postGrid" class="post-grid">
        <?php if (empty($posts)): ?>
            <div class="card"><p class="empty">No approved places found.</p></div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="post-card-header">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user' && $_SESSION['user']['is_verified'] == 1): ?>
                            <?php $inWishlist = in_array(intval($post['id']), $wishlistIds); ?>
                            <button class="wishlist-card-btn <?= $inWishlist ? 'wishlisted' : '' ?>" 
                                    onclick="toggleCardWishlist(this, <?= $post['id'] ?>)">
                                <span class="heart-icon"></span>
                                <span class="btn-text"><?= $inWishlist ? 'Wishlisted' : 'Add to Wishlist' ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="post-meta">
                        <span><?= htmlspecialchars($post['country']) ?> &middot; <?= ucfirst($post['genre']) ?></span>
                        <?php 
                            $baseCost = $post['base_cost'] ?? null;
                            if ($baseCost !== null) {
                                $tierText = '$' . number_format(floatval($baseCost));
                            } else {
                                $tierMap = [
                                    'low' => 'Budget',
                                    'medium' => 'Mid-Range',
                                    'high' => 'Luxury'
                                ];
                                $tierText = $tierMap[strtolower($post['cost_level'])] ?? 'Mid-Range';
                            }
                        ?>
                        <span class="cost-badge <?= strtolower($post['cost_level']) ?>"><?= $tierText ?></span>
                    </div>
                    <div class="post-snippet">
                        <?= htmlspecialchars(mb_strimwidth($post['short_history'], 0, 100, "...")) ?>
                    </div>
                    <div class="post-footer" style="display: flex; gap: 10px; width: 100%;">
                        <a class="btn btn-primary" style="flex: 1; text-align: center;" href="index.php?page=user&action=detail&id=<?= $post['id'] ?>">Read More</a>
                        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'scout' && $_SESSION['user']['id'] == $post['scout_id']): ?>
                            <a class="btn btn-ghost" style="padding: 0 15px; display: flex; align-items: center; justify-content: center; font-size: 14px;" href="index.php?page=scout&action=request_change&id=<?= $post['id'] ?>" title="Request Edit">✏️ Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>



<script>
(function () {
    var wishlistIds = <?= json_encode($wishlistIds) ?>;
    var isGeneralUser = <?= (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'user' && $_SESSION['user']['is_verified'] == 1) ? 'true' : 'false' ?>;
    var isScout = <?= (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'scout') ? 'true' : 'false' ?>;
    var currentUserId = <?= isset($_SESSION['user']) ? intval($_SESSION['user']['id']) : 0 ?>;

    var searchInput   = document.getElementById('searchInput');
    var costRadios    = document.getElementsByName('cost');
    var genreChecks   = document.getElementsByName('genre');
    var grid          = document.getElementById('postGrid');
    var counter       = document.getElementById('resultCount');
    var timer;

    // Haversine coordinates helper mappings
    var coordsMap = {
        "berlin": [52.5200, 13.4050],
        "paris": [48.8566, 2.3522],
        "sylhet": [24.8949, 91.8687],
        "dhaka": [23.8103, 90.4125],
        "chittagong": [22.3569, 91.7832],
        "cox's bazar": [21.4272, 92.0058],
        "germany": [51.1657, 10.4515],
        "france": [46.2276, 2.2137],
        "bangladesh": [23.6850, 90.3563]
    };

    var geocodeCache = {};
    var map;
    var markers = [];
    var userLocation = null;

    var nearMeCheck = document.getElementById('nearMeCheck');
    var nearMeRadius = document.getElementById('nearMeRadius');
    var mapWrapper = document.getElementById('mapWrapper');
    var mapCountBadge = document.getElementById('mapCount');

    // Haversine Distance Calculator
    function getHaversineDistance(lat1, lon1, lat2, lon2) {
        var R = 6371; // km
        var dLat = deg2rad(lat2 - lat1);
        var dLon = deg2rad(lon2 - lon1);
        var a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function deg2rad(deg) {
        return deg * (Math.PI / 180);
    }

    // Leaflet Map initializer
    function initMap() {
        if (typeof L === 'undefined') {
            console.warn("Leaflet library is not loaded. Map bypassed.");
            return;
        }
        if (map) return;
        var mapContainer = document.getElementById('leafletMap');
        if (!mapContainer) return;
        
        try {
            map = L.map(mapContainer, {
                center: [25, 45],
                zoom: 2,
                scrollWheelZoom: true
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(map);
        } catch (e) {
            console.error("Leaflet map initialization failed:", e);
        }
    }

    function clearMarkers() {
        if (!map) return;
        for (var i = 0; i < markers.length; i++) {
            try {
                map.removeLayer(markers[i]);
            } catch (e) {
                console.error("Error removing marker:", e);
            }
        }
        markers = [];
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function trim(s, len) {
        if (!s) return '';
        return s.length > len ? s.substring(0, len) + "..." : s;
    }

    // Render cards and plot map markers
    function render(rows) {
        // Step 1: Filter rows client-side if "Near Me" is active
        if (nearMeCheck && nearMeCheck.checked && userLocation) {
            var radiusLimit = parseFloat(nearMeRadius.value);
            rows = rows.filter(function(r) {
                var nameKey = (r.title || "").toLowerCase();
                var countryKey = (r.country || "").toLowerCase();
                var coords = coordsMap[nameKey] || coordsMap[countryKey] || geocodeCache[r.id];
                if (!coords) return true;
                var d = getHaversineDistance(userLocation.lat, userLocation.lng, coords[0], coords[1]);
                return d <= radiusLimit;
            });
        }

        // Step 2: Render grid cards
        if (!rows.length) {
            grid.innerHTML = '<div class="card text-center-empty"><p class="empty">No destinations match your filters. Try adjusting them!</p></div>';
            counter.textContent = '0 results';
            clearMarkers();
            if (mapCountBadge) mapCountBadge.textContent = '0 markers shown';
            return;
        }

        var html = '';
        rows.forEach(function (r) {
            var tierText;
            if (r.base_cost !== null && r.base_cost !== undefined && r.base_cost !== '') {
                tierText = '$' + Number(parseFloat(r.base_cost)).toLocaleString();
            } else {
                var tierMap = {
                    'low': 'Budget',
                    'medium': 'Mid-Range',
                    'high': 'Luxury'
                };
                tierText = tierMap[(r.cost_level || 'medium').toLowerCase()] || 'Mid-Range';
            }
            
            var wishlistBtnHtml = '';
            if (isGeneralUser) {
                var inWishlist = wishlistIds.includes(parseInt(r.id));
                wishlistBtnHtml = 
                    '<button class="wishlist-card-btn ' + (inWishlist ? 'wishlisted' : '') + '" onclick="toggleCardWishlist(this, ' + r.id + ')">' +
                        '<span class="heart-icon"></span>' +
                        '<span class="btn-text">' + (inWishlist ? 'Wishlisted' : 'Add to Wishlist') + '</span>' +
                    '</button>';
            }

            var editBtnHtml = '';
            if (isScout && parseInt(r.scout_id) === currentUserId) {
                editBtnHtml = '<a class="btn btn-ghost" style="padding: 0 15px; display: flex; align-items: center; justify-content: center; font-size: 14px;" href="index.php?page=scout&action=request_change&id=' + r.id + '" title="Request Edit">✏️ Edit</a>';
            }

            html +=
                '<div class="post-card">' +
                    '<div class="post-card-header">' +
                        '<h3>' + esc(r.title) + '</h3>' +
                        wishlistBtnHtml +
                    '</div>' +
                    '<div class="post-meta">' +
                        '<span>' + esc(r.country) + ' &middot; ' + (r.genre ? r.genre.charAt(0).toUpperCase() + r.genre.slice(1) : 'Travel') + '</span>' +
                        '<span class="cost-badge ' + (r.cost_level || 'medium').toLowerCase() + '">' + 
                            tierText +
                        '</span>' +
                    '</div>' +
                    '<div class="post-snippet">' + esc(trim(r.short_history, 100)) + '</div>' +
                    '<div class="post-footer" style="display: flex; gap: 10px; width: 100%;">' +
                        '<a class="btn btn-primary" style="flex: 1; text-align: center;" href="index.php?page=user&action=detail&id=' + r.id + '">Read More</a>' +
                        editBtnHtml +
                    '</div>' +
                '</div>';
        });
        grid.innerHTML = html;
        counter.textContent = rows.length + ' results found';

        // Step 3: Plot markers on map
        plotMarkers(rows);
    }

    function plotMarkers(rows) {
        if (typeof L === 'undefined' || !map) return;
        clearMarkers();
        var plottedCount = 0;

        rows.forEach(function(r) {
            var nameKey = (r.title || "").toLowerCase();
            var countryKey = (r.country || "").toLowerCase();
            var coords = coordsMap[nameKey] || coordsMap[countryKey] || geocodeCache[r.id];

            if (coords) {
                placeMarker(r, coords);
                plottedCount++;
            } else {
                var query = (r.title || "") + ", " + (r.country || "");
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query))
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.length > 0) {
                            var computed = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                            geocodeCache[r.id] = computed;
                            placeMarker(r, computed);
                            mapCountBadge.textContent = (parseInt(mapCountBadge.textContent) + 1) + ' markers shown';
                        } else {
                            var countryL = (r.country || "").toLowerCase();
                            var fallback = coordsMap[countryL] || [23.6850, 90.3563];
                            placeMarker(r, fallback);
                        }
                    })
                    .catch(function() {
                        var countryL = (r.country || "").toLowerCase();
                        var fallback = coordsMap[countryL] || [23.6850, 90.3563];
                        placeMarker(r, fallback);
                    });
            }
        });

        if (mapCountBadge) {
            mapCountBadge.textContent = plottedCount + ' markers shown';
        }
    }

    function placeMarker(r, coords) {
        if (typeof L === 'undefined' || !map) return;
        try {
            var marker = L.marker([coords[0], coords[1]]).addTo(map);

            var popupHtml = 
                '<div style="font-family: inherit; font-size: 13px; min-width: 180px;">' +
                    '<h4 style="margin: 0 0 5px 0; font-size: 14px; color: #1a237e; font-weight: bold;">' + esc(r.title) + '</h4>' +
                    '<p style="margin: 0 0 8px 0; color: #666;">' + esc(r.country) + ' &middot; ' + (r.genre ? r.genre.toUpperCase() : 'TRAVEL') + '</p>' +
                    '<div style="display: flex; gap: 8px; flex-wrap: wrap;">' +
                        '<a href="index.php?page=user&action=detail&id=' + r.id + '" style="padding: 4px 10px; font-size: 11px; text-decoration: none; color: white; background: #1a237e; border-radius: 4px; display: inline-block;">Details</a>' +
                        '<button onclick="getDirectionsTo(' + coords[0] + ', ' + coords[1] + ')" style="padding: 4px 10px; font-size: 11px; cursor: pointer; border: 1px solid #1a237e; color: #1a237e; background: white; border-radius: 4px;">Directions</button>' +
                    '</div>' +
                '</div>';

            marker.bindPopup(popupHtml);
            markers.push(marker);
        } catch (e) {
            console.error("Error placing marker:", e);
        }
    }

    window.getDirectionsTo = function(destLat, destLng) {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                var url = 'https://www.google.com/maps/dir/?api=1&origin=' + pos.coords.latitude + ',' + pos.coords.longitude + '&destination=' + destLat + ',' + destLng;
                window.open(url, '_blank');
            }, function() {
                var url = 'https://www.google.com/maps/dir/?api=1&destination=' + destLat + ',' + destLng;
                window.open(url, '_blank');
            });
        } else {
            var url = 'https://www.google.com/maps/dir/?api=1&destination=' + destLat + ',' + destLng;
            window.open(url, '_blank');
        }
    };

    window.toggleMapDrawer = function() {
        if (!mapWrapper) return;
        if (mapWrapper.style.display === 'none') {
            mapWrapper.style.display = 'block';
            setTimeout(function() {
                if (typeof L !== 'undefined' && map) map.invalidateSize();
            }, 150);
        } else {
            mapWrapper.style.display = 'none';
        }
    };

    window.toggleCardWishlist = function(btn, postId) {
        if (!isGeneralUser) return;
        var isAdding = !btn.classList.contains('wishlisted');
        var action = isAdding ? 'add' : 'remove';
        
        var fd = new FormData();
        fd.append('post_id', postId);

        fetch('index.php?page=wishlist&action=' + action, {
            method: 'POST',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                if (isAdding) {
                    btn.classList.add('wishlisted');
                    btn.querySelector('.btn-text').textContent = 'Wishlisted';
                    if (!wishlistIds.includes(postId)) {
                        wishlistIds.push(postId);
                    }
                } else {
                    btn.classList.remove('wishlisted');
                    btn.querySelector('.btn-text').textContent = 'Add to Wishlist';
                    var index = wishlistIds.indexOf(postId);
                    if (index > -1) {
                        wishlistIds.splice(index, 1);
                    }
                }
            } else {
                alert(data.message);
            }
        })
        .catch(function(e) { console.error(e); });
    };

    window.resetFilters = function() {
        searchInput.value = "";
        costRadios[0].checked = true;
        for (var i = 0; i < genreChecks.length; i++) genreChecks[i].checked = false;
        if (nearMeCheck) {
            nearMeCheck.checked = false;
            nearMeRadius.disabled = true;
        }
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

    // Near Me location handling
    function getUserLocation(callback) {
        // Set fallback immediately so it always works
        var fallback = { lat: 23.8103, lng: 90.4125 }; // Dhaka default
        var resolved = false;

        // Try real geolocation with a hard 3s timeout
        if (navigator.geolocation) {
            var geoTimeout = setTimeout(function() {
                if (!resolved) {
                    resolved = true;
                    callback(fallback);
                }
            }, 3000);

            try {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    if (!resolved) {
                        resolved = true;
                        clearTimeout(geoTimeout);
                        callback({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                    }
                }, function() {
                    if (!resolved) {
                        resolved = true;
                        clearTimeout(geoTimeout);
                        callback(fallback);
                    }
                }, { timeout: 3000, maximumAge: 300000 });
            } catch (err) {
                console.warn("Geolocation request failed synchronously:", err);
                if (!resolved) {
                    resolved = true;
                    clearTimeout(geoTimeout);
                    callback(fallback);
                }
            }
        } else {
            callback(fallback);
        }
    }

    // Expose toggle & select handlers globally to bypass event hook issues
    window.handleNearMeToggle = function(chk) {
        if (chk.checked) {
            if (!userLocation) {
                getUserLocation(function(loc) {
                    userLocation = loc;
                    applyFilters(true);
                });
            } else {
                applyFilters(true);
            }
        } else {
            applyFilters(true);
        }
    };

    window.handleNearMeRadiusChange = function(sel) {
        if (nearMeCheck && nearMeCheck.checked) {
            applyFilters(true);
        }
    };

    // Page Load Setup
    searchInput.addEventListener('input', applyFilters);
    for (var i = 0; i < costRadios.length; i++) costRadios[i].addEventListener('change', applyFilters);
    for (var i = 0; i < genreChecks.length; i++) genreChecks[i].addEventListener('change', applyFilters);

    // Initial Map Setup
    try {
        initMap();
    } catch (e) {
        console.error("Initial map setup failed:", e);
    }
    var initialRows = <?= json_encode($posts) ?>;
    try {
        render(initialRows);
    } catch (e) {
        console.error("Initial render failed:", e);
    }
})();
</script>

</body>
</html>


