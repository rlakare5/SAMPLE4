<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'farmer') {
    header('Location: index.php?page=login');
    exit;
}

$userId = $_SESSION['user_id'];
$farms = $pdo->prepare("SELECT * FROM farms WHERE user_id = ? AND latitude IS NOT NULL AND longitude IS NOT NULL");
$farms->execute([$userId]);
$farms = $farms->fetchAll();

$selectedFarm = null;
$weatherData = null;

if (isset($_GET['farm_id'])) {
    $farmId = intval($_GET['farm_id']);
    $selectedFarm = $pdo->prepare("SELECT * FROM farms WHERE id = ? AND user_id = ?");
    $selectedFarm->execute([$farmId, $userId]);
    $selectedFarm = $selectedFarm->fetch();
    
    if ($selectedFarm) {
        $weatherData = getWeatherForecast($selectedFarm['latitude'], $selectedFarm['longitude']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Forecast - AgriIntel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel</div>
            <ul class="nav-links">
                <li><a href="index.php?page=farmer">Dashboard</a></li>
                <li><a href="index.php?page=weather">Weather</a></li>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                <li><a href="index.php?page=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>Weather Forecast (6 Months)</h1>

        <div class="card">
            <h2>Select Farm</h2>
            <form method="GET">
                <input type="hidden" name="page" value="weather">
                <div class="form-group">
                    <select name="farm_id" onchange="this.form.submit()">
                        <option value="">Choose a farm</option>
                        <?php foreach ($farms as $farm): ?>
                            <option value="<?= $farm['id'] ?>" <?= isset($_GET['farm_id']) && $_GET['farm_id'] == $farm['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($farm['name']) ?> - <?= htmlspecialchars($farm['location_address']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selectedFarm && $weatherData): ?>
            <div class="card">
                <h2>Weather Forecast for <?= htmlspecialchars($selectedFarm['name']) ?></h2>
                <p><strong>Location:</strong> <?= htmlspecialchars($selectedFarm['location_address']) ?></p>
                <p><strong>Coordinates:</strong> <?= $selectedFarm['latitude'] ?>, <?= $selectedFarm['longitude'] ?></p>
                
                <div class="weather-grid">
                    <?php foreach ($weatherData['forecast'] as $index => $forecast): ?>
                        <div class="weather-card">
                            <h3>Month <?= $index + 1 ?></h3>
                            <div class="weather-icon">☀️</div>
                            <p><strong>Condition:</strong> <?= $forecast['condition'] ?></p>
                            <p><strong>Temperature:</strong> <?= $forecast['temperature'] ?>°C</p>
                            <p><strong>Rainfall:</strong> <?= $forecast['rainfall'] ?> mm</p>
                            <p><strong>Humidity:</strong> <?= $forecast['humidity'] ?>%</p>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-success" style="margin-top: 2rem;">
                    <strong>🌟 AI Recommendation:</strong> Based on the forecast, this is a good time for planting. 
                    Consider crops that thrive in moderate rainfall and warm temperatures.
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .weather-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .weather-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
        }
        
        .weather-card h3 {
            margin-bottom: 1rem;
        }
        
        .weather-icon {
            font-size: 3rem;
            margin: 1rem 0;
        }
        
        .weather-card p {
            margin: 0.5rem 0;
        }
    </style>
</body>
</html>
