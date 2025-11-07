<?php

function getWeatherForecast($latitude, $longitude) {
    $apiKey = "d0734fba50b82f4803ad22aad196f11a";
    
    $forecast = [];
    $currentMonth = new DateTime();
    
    for ($i = 0; $i < 6; $i++) {
        $month = clone $currentMonth;
        $month->modify("+{$i} months");
        
        $monthNum = (int)$month->format('n');
        $year = (int)$month->format('Y');
        
        $seasonalTemp = [
            1 => 15, 2 => 18, 3 => 25, 4 => 30, 5 => 35, 6 => 32,
            7 => 28, 8 => 27, 9 => 28, 10 => 25, 11 => 20, 12 => 16
        ];
        
        $seasonalRainfall = [
            1 => 20, 2 => 25, 3 => 30, 4 => 40, 5 => 80, 6 => 200,
            7 => 250, 8 => 220, 9 => 180, 10 => 100, 11 => 40, 12 => 15
        ];
        
        $tempVariation = [
            1 => 0, 2 => 1, 3 => 2, 4 => 1, 5 => -1, 6 => -2,
            7 => 0, 8 => 1, 9 => -1, 10 => 0, 11 => 1, 12 => -1
        ];
        
        $rainfallVariation = [
            1 => 5, 2 => -5, 3 => 10, 4 => -10, 5 => 15, 6 => -15,
            7 => 10, 8 => -10, 9 => 5, 10 => -5, 11 => 0, 12 => 0
        ];
        
        $baseTemp = $seasonalTemp[$monthNum];
        $baseRainfall = $seasonalRainfall[$monthNum];
        
        $temp = $baseTemp + $tempVariation[$monthNum];
        $rainfall = max(0, $baseRainfall + $rainfallVariation[$monthNum]);
        $humidity = min(100, max(40, 60 + ($rainfall / 5)));
        
        $condition = 'Sunny';
        if ($rainfall > 150) {
            $condition = 'Heavy Rainfall';
        } elseif ($rainfall > 80) {
            $condition = 'Rainy';
        } elseif ($rainfall > 40) {
            $condition = 'Partly Cloudy';
        } elseif ($temp > 32) {
            $condition = 'Hot & Sunny';
        }
        
        $forecast[] = [
            'month' => $month->format('F Y'),
            'temperature' => $temp,
            'rainfall' => $rainfall,
            'humidity' => round($humidity),
            'condition' => $condition
        ];
    }
    
    return ['forecast' => $forecast];
}

function calculateSeeds($cropType, $area) {
    $seedsPerHectare = [
        'wheat' => 120,
        'rice' => 40,
        'corn' => 20,
        'cotton' => 15,
        'soybean' => 80,
        'potato' => 2500,
        'tomato' => 300,
        'onion' => 10
    ];
    
    $seeds = isset($seedsPerHectare[$cropType]) ? $seedsPerHectare[$cropType] : 100;
    return $seeds * $area;
}

function calculateYield($cropType, $area) {
    $yieldPerHectare = [
        'wheat' => 3000,
        'rice' => 4000,
        'corn' => 5000,
        'cotton' => 1500,
        'soybean' => 2500,
        'potato' => 20000,
        'tomato' => 35000,
        'onion' => 25000
    ];
    
    $yield = isset($yieldPerHectare[$cropType]) ? $yieldPerHectare[$cropType] : 3000;
    return $yield * $area;
}

function getMarketPrice($cropType) {
    $prices = [
        'wheat' => 25,
        'rice' => 30,
        'corn' => 20,
        'cotton' => 60,
        'soybean' => 40,
        'potato' => 15,
        'tomato' => 25,
        'onion' => 20
    ];
    
    return isset($prices[$cropType]) ? $prices[$cropType] : 25;
}

function calculateProfit($cropType, $area, $yield) {
    $marketPrice = getMarketPrice($cropType);
    $revenue = $yield * $marketPrice;
    
    $costPerHectare = [
        'wheat' => 15000,
        'rice' => 20000,
        'corn' => 18000,
        'cotton' => 25000,
        'soybean' => 16000,
        'potato' => 30000,
        'tomato' => 40000,
        'onion' => 22000
    ];
    
    $cost = isset($costPerHectare[$cropType]) ? $costPerHectare[$cropType] * $area : 15000 * $area;
    $profit = $revenue - $cost;
    
    return [
        'revenue' => $revenue,
        'cost' => $cost,
        'profit' => $profit
    ];
}

function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function getAIFarmingInsights($cropType, $weatherData, $area) {
    $apiKey = getenv('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');
    
    if (empty($apiKey)) {
        return "AI insights are not available. Please configure your OpenAI API key.";
    }
    
    $weatherSummary = "";
    if ($weatherData && isset($weatherData['forecast'])) {
        $avgTemp = 0;
        $avgRainfall = 0;
        $count = count($weatherData['forecast']);
        
        foreach ($weatherData['forecast'] as $forecast) {
            $avgTemp += $forecast['temperature'];
            $avgRainfall += $forecast['rainfall'];
        }
        
        $avgTemp = round($avgTemp / $count, 1);
        $avgRainfall = round($avgRainfall / $count, 1);
        
        $weatherSummary = "Average temperature: {$avgTemp}°C, Average rainfall: {$avgRainfall}mm over next 6 months";
    }
    
    $prompt = "You are an expert agricultural advisor for Indian farmers. Provide practical, actionable farming tips in 3-4 short bullet points.

Crop: {$cropType}
Farm area: {$area} hectares
Weather forecast: {$weatherSummary}

Give specific advice on:
- Best practices for this crop in these conditions
- Irrigation and water management tips
- Potential challenges to watch for
- Harvest timing recommendations

Keep it concise, practical, and easy to understand for farmers.";

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful agricultural expert advisor for Indian farmers.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 300,
        'temperature' => 0.7
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return "Network error: Unable to connect to AI service. Please check your internet connection and try again.";
    }
    
    if ($httpCode !== 200) {
        return "AI insights are temporarily unavailable. Please try again later.";
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    }
    
    return "Unable to generate AI insights at this time.";
}

function getAICropRecommendation($weatherData, $area, $location) {
    $apiKey = getenv('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');
    
    if (empty($apiKey)) {
        return [
            'recommendation' => 'AI recommendations not available',
            'reason' => 'OpenAI API key not configured'
        ];
    }
    
    $weatherSummary = "";
    if ($weatherData && isset($weatherData['forecast'])) {
        $avgTemp = 0;
        $avgRainfall = 0;
        $count = count($weatherData['forecast']);
        
        foreach ($weatherData['forecast'] as $forecast) {
            $avgTemp += $forecast['temperature'];
            $avgRainfall += $forecast['rainfall'];
        }
        
        $avgTemp = round($avgTemp / $count, 1);
        $avgRainfall = round($avgRainfall / $count, 1);
        
        $weatherSummary = "Avg temp: {$avgTemp}°C, Avg rainfall: {$avgRainfall}mm";
    }
    
    $prompt = "Based on this farm data, recommend the top 3 most suitable crops for Indian farmers:

Location: {$location}
Farm area: {$area} hectares
Weather: {$weatherSummary}

Format your response as:
1. [Crop name] - [Brief reason why it's suitable]
2. [Crop name] - [Brief reason why it's suitable]
3. [Crop name] - [Brief reason why it's suitable]

Keep it concise and practical.";

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are an agricultural expert helping Indian farmers choose the best crops.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 200,
        'temperature' => 0.7
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return [
            'recommendation' => 'Network error: Unable to connect to AI service',
            'reason' => 'Please check your internet connection and try again'
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'recommendation' => 'AI recommendations temporarily unavailable',
            'reason' => 'Please try again later'
        ];
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return [
            'recommendation' => $result['choices'][0]['message']['content'],
            'reason' => 'Based on weather patterns and location'
        ];
    }
    
    return [
        'recommendation' => 'Unable to generate recommendations',
        'reason' => 'Service unavailable'
    ];
}

function getAIMarketInsights($cropType) {
    $apiKey = getenv('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');
    
    if (empty($apiKey)) {
        return "Market insights are not available at this time.";
    }
    
    $prompt = "Provide a brief market outlook for {$cropType} in India. Include:
- Current demand trends
- Price forecast for next 3 months
- Best time to sell

Keep it under 100 words and practical for farmers.";

    $data = [
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a market analyst for agricultural commodities in India.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => 150,
        'temperature' => 0.7
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    
    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return "Network error: Unable to connect to AI service. Please check your internet connection.";
    }
    
    if ($httpCode !== 200) {
        return "Market insights temporarily unavailable.";
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    }
    
    return "Unable to fetch market insights.";
}
