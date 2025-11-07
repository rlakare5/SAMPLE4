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
$aiRecommendation = null;
$weatherData = null;

if (isset($_GET['farm_id'])) {
    $farmId = intval($_GET['farm_id']);
    $selectedFarm = $pdo->prepare("SELECT * FROM farms WHERE id = ? AND user_id = ?");
    $selectedFarm->execute([$farmId, $userId]);
    $selectedFarm = $selectedFarm->fetch();
    
    if ($selectedFarm) {
        $weatherData = getWeatherForecast($selectedFarm['latitude'], $selectedFarm['longitude']);
        $aiRecommendation = getAICropRecommendation($weatherData, $selectedFarm['area'], $selectedFarm['location_address']);
    }
}

$crops = $pdo->prepare("SELECT c.*, f.latitude, f.longitude FROM crops c LEFT JOIN farms f ON c.farm_id = f.id WHERE c.user_id = ? ORDER BY c.created_at DESC LIMIT 5");
$crops->execute([$userId]);
$crops = $crops->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Insights - AgriIntel</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel</div>
            <ul class="nav-links">
                <li><a href="index.php?page=farmer">Dashboard</a></li>
                <li><a href="index.php?page=weather">Weather</a></li>
                <li><a href="index.php?page=ai-insights">AI Insights</a></li>
                <li><span>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
                <li><a href="index.php?page=logout">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-container">
        <h1>🤖 AI-Powered Farming Insights</h1>

        <div class="card">
            <h2>Get Crop Recommendations</h2>
            <form method="GET">
                <input type="hidden" name="page" value="ai-insights">
                <div class="form-group">
                    <label>Select Your Farm</label>
                    <select name="farm_id" onchange="this.form.submit()">
                        <option value="">Choose a farm for AI recommendations</option>
                        <?php foreach ($farms as $farm): ?>
                            <option value="<?= $farm['id'] ?>" <?= isset($_GET['farm_id']) && $_GET['farm_id'] == $farm['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($farm['name']) ?> - <?= $farm['area'] ?> hectares
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <?php if ($selectedFarm && $aiRecommendation): ?>
            <div class="card ai-card">
                <h2>🌟 AI Crop Recommendations for <?= htmlspecialchars($selectedFarm['name']) ?></h2>
                <div class="ai-content">
                    <p><strong>Farm Details:</strong></p>
                    <ul>
                        <li>Area: <?= $selectedFarm['area'] ?> hectares</li>
                        <li>Location: <?= htmlspecialchars($selectedFarm['location_address']) ?></li>
                    </ul>
                    
                    <div class="ai-recommendation">
                        <h3>💡 Recommended Crops:</h3>
                        <div class="recommendation-text">
                            <?= nl2br(htmlspecialchars($aiRecommendation['recommendation'])) ?>
                        </div>
                        <p class="ai-note"><em>Based on: <?= htmlspecialchars($aiRecommendation['reason']) ?></em></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($crops) > 0): ?>
            <div class="card">
                <h2>📊 AI Insights for Your Recent Crops</h2>
                <div class="crops-insights-grid">
                    <?php foreach ($crops as $crop): 
                        $cropWeather = null;
                        if ($crop['latitude'] && $crop['longitude']) {
                            $cropWeather = getWeatherForecast($crop['latitude'], $crop['longitude']);
                        }
                        $aiInsights = getAIFarmingInsights($crop['crop_type'], $cropWeather, $crop['area']);
                        $marketInsights = getAIMarketInsights($crop['crop_type']);
                    ?>
                        <div class="crop-insight-card">
                            <h3><?= ucfirst($crop['crop_type']) ?></h3>
                            <p><strong>Area:</strong> <?= $crop['area'] ?> hectares</p>
                            <p><strong>Expected Harvest:</strong> <?= date('M d, Y', strtotime($crop['expected_harvest_date'])) ?></p>
                            
                            <div class="insight-section">
                                <h4>🌱 Farming Tips:</h4>
                                <div class="insight-text">
                                    <?= nl2br(htmlspecialchars($aiInsights)) ?>
                                </div>
                            </div>
                            
                            <div class="insight-section">
                                <h4>💰 Market Outlook:</h4>
                                <div class="insight-text">
                                    <?= nl2br(htmlspecialchars($marketInsights)) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .ai-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .ai-card h2, .ai-card h3 {
            color: white;
        }
        
        .ai-content {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .ai-recommendation {
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .ai-recommendation h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .recommendation-text {
            line-height: 1.8;
            white-space: pre-wrap;
        }
        
        .ai-note {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .crops-insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .crop-insight-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .crop-insight-card h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .insight-section {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }
        
        .insight-section h4 {
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .insight-text {
            background: white;
            padding: 1rem;
            border-radius: 6px;
            line-height: 1.6;
            white-space: pre-wrap;
        }
    </style>
</body>
</html>
