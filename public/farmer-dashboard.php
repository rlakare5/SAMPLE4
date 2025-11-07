<?php
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_farm'])) {
        $farmName = sanitizeInput($_POST['farm_name']);
        $area = floatval($_POST['area']);
        $latitude = floatval($_POST['latitude']);
        $longitude = floatval($_POST['longitude']);
        $address = sanitizeInput($_POST['address']);
        
        $stmt = $pdo->prepare("INSERT INTO farms (user_id, name, area, latitude, longitude, location_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $farmName, $area, $latitude, $longitude, $address]);
    }
    
    if (isset($_POST['add_crop'])) {
        $farmId = intval($_POST['farm_id']);
        $cropType = sanitizeInput($_POST['crop_type']);
        $area = floatval($_POST['crop_area']);
        $plantingDate = $_POST['planting_date'];
        $harvestDate = $_POST['harvest_date'];
        
        $seeds = calculateSeeds($cropType, $area);
        $yield = calculateYield($cropType, $area);
        $marketPrice = getMarketPrice($cropType);
        $profitData = calculateProfit($cropType, $area, $yield);
        
        $stmt = $pdo->prepare("INSERT INTO crops (farm_id, user_id, crop_type, area, planting_date, expected_harvest_date, seeds_required, estimated_yield, estimated_profit, market_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$farmId, $userId, $cropType, $area, $plantingDate, $harvestDate, $seeds, $yield, $profitData['profit'], $marketPrice]);
    }
}

$farms = $pdo->prepare("SELECT * FROM farms WHERE user_id = ? ORDER BY created_at DESC");
$farms->execute([$userId]);
$farms = $farms->fetchAll();

$crops = $pdo->prepare("SELECT c.*, f.name as farm_name FROM crops c LEFT JOIN farms f ON c.farm_id = f.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$crops->execute([$userId]);
$crops = $crops->fetchAll();

$totalCrops = count($crops);
$totalArea = array_sum(array_column($farms, 'area'));
$totalProfit = array_sum(array_column($crops, 'estimated_profit'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - AgriIntel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel</div>
            <ul class="nav-links">
                <li><a href="index.php?page=farmer">Dashboard</a></li>
                <li><a href="index.php?page=weather">Weather</a></li>
                <li><a href="index.php?page=ai-insights">🤖 AI Insights</a></li>
                <li><a href="index.php?page=chat">💬 Messages</a></li>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                <li><a href="index.php?page=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Farms</h3>
                <p class="stat-value"><?= count($farms) ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Area</h3>
                <p class="stat-value"><?= number_format($totalArea, 2) ?> hectares</p>
            </div>
            <div class="stat-card">
                <h3>Active Crops</h3>
                <p class="stat-value"><?= $totalCrops ?></p>
            </div>
            <div class="stat-card">
                <h3>Estimated Profit</h3>
                <p class="stat-value"><?= formatCurrency($totalProfit) ?></p>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <h2>Add New Farm</h2>
                <form method="POST" id="farmForm">
                    <input type="hidden" name="add_farm" value="1">
                    <div class="form-group">
                        <label>Farm Name</label>
                        <input type="text" name="farm_name" required>
                    </div>
                    <div class="form-group">
                        <label>Total Area (hectares)</label>
                        <input type="number" step="0.01" name="area" required>
                    </div>
                    <div class="form-group">
                        <label>Location Address</label>
                        <input type="text" name="address" id="address">
                    </div>
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" onclick="getLocation()">📍 Get My Location</button>
                    </div>
                    <div id="map" style="height: 250px; margin: 10px 0;"></div>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <button type="submit" class="btn btn-primary">Add Farm</button>
                </form>
            </div>

            <div class="card">
                <h2>My Farms</h2>
                <div class="table-container">
                    <?php if (count($farms) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Area</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($farms as $farm): ?>
                                <tr>
                                    <td><?= htmlspecialchars($farm['name']) ?></td>
                                    <td><?= $farm['area'] ?> ha</td>
                                    <td><?= htmlspecialchars($farm['location_address']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No farms added yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (count($farms) > 0): ?>
        <div class="card">
            <h2>Add New Crop</h2>
            <form method="POST" id="cropForm">
                <input type="hidden" name="add_crop" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Farm</label>
                        <select name="farm_id" required>
                            <?php foreach ($farms as $farm): ?>
                                <option value="<?= $farm['id'] ?>"><?= htmlspecialchars($farm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Crop Type</label>
                        <select name="crop_type" required>
                            <option value="wheat">Wheat</option>
                            <option value="rice">Rice</option>
                            <option value="corn">Corn</option>
                            <option value="cotton">Cotton</option>
                            <option value="soybean">Soybean</option>
                            <option value="potato">Potato</option>
                            <option value="tomato">Tomato</option>
                            <option value="onion">Onion</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Area (hectares)</label>
                        <input type="number" step="0.01" name="crop_area" required>
                    </div>
                    <div class="form-group">
                        <label>Planting Date</label>
                        <input type="date" name="planting_date" required>
                    </div>
                    <div class="form-group">
                        <label>Expected Harvest Date</label>
                        <input type="date" name="harvest_date" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Crop & Calculate</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>My Crops</h2>
            <div class="table-container">
                <?php if (count($crops) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Crop</th>
                                <th>Farm</th>
                                <th>Area</th>
                                <th>Seeds Required</th>
                                <th>Est. Yield (kg)</th>
                                <th>Market Price</th>
                                <th>Est. Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crops as $crop): ?>
                            <tr>
                                <td><?= ucfirst($crop['crop_type']) ?></td>
                                <td><?= htmlspecialchars($crop['farm_name']) ?></td>
                                <td><?= $crop['area'] ?> ha</td>
                                <td><?= number_format($crop['seeds_required']) ?> kg</td>
                                <td><?= number_format($crop['estimated_yield']) ?></td>
                                <td><?= formatCurrency($crop['market_price']) ?>/kg</td>
                                <td class="<?= $crop['estimated_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= formatCurrency($crop['estimated_profit']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No crops added yet. Add a farm first to get started.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let map;
        let marker;
        
        function initMap() {
            map = L.map('map').setView([20.5937, 78.9629], 5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            map.on('click', function(e) {
                if (marker) {
                    map.removeLayer(marker);
                }
                marker = L.marker(e.latlng).addTo(map);
                document.getElementById('latitude').value = e.latlng.lat;
                document.getElementById('longitude').value = e.latlng.lng;
                
                fetch(`https://nominatim.openstreetmap.org/reverse?lat=${e.latlng.lat}&lon=${e.latlng.lng}&format=json`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('address').value = data.display_name;
                    });
            });
        }
        
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 13);
                    if (marker) {
                        map.removeLayer(marker);
                    }
                    marker = L.marker([lat, lng]).addTo(map);
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('address').value = data.display_name;
                        });
                });
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }
        
        window.onload = initMap;
    </script>
</body>
</html>
