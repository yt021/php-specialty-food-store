<?php
require_once __DIR__ . '/pinket-config.php';

function prepareCategoriesData($baseUrl = 'https://abanfruit.com') {
    return [
        [
            'id' => '1',
            'name' => 'میوه خشک',
            'parentId' => null,
            'imageUrl' => $baseUrl . '/images/categories/1.jpg'
        ],
        [
            'id' => '12',
            'name' => 'آجیل',
            'parentId' => null,
            'imageUrl' => $baseUrl . '/images/categories/12.jpg'
        ],
        [
            'id' => '11',
            'name' => 'کرانج میوه و بستنی',
            'parentId' => null,
            'imageUrl' => $baseUrl . '/images/categories/11.jpg'
        ],
        [
            'id' => '8',
            'name' => 'بسته بندی کادویی',
            'parentId' => null,
            'imageUrl' => $baseUrl . '/images/categories/8.jpg'
        ],
        [
            'id' => '10',
            'name' => 'لواشک',
            'parentId' => null,
            'imageUrl' => $baseUrl . '/images/categories/10.jpg'
        ]
    ];
}

function sendCategoriesToPinket() {
    $config = getPinketConfig();
    $baseUrl = $config['inventoryUrl'];
    $username = $config['basicAuthUsername'];
    $password = $config['basicAuthPassword'];
    $url = rtrim($baseUrl, '/') . '/api/agent/v1/categories';
    $categories = prepareCategoriesData();
    // If discount is required for categories, add it (usually not needed, but for compatibility):
    foreach ($categories as &$cat) {
        if (!isset($cat['discount'])) {
            $cat['discount'] = 0;
        }
    }
    unset($cat);
    $data = [ 'categories' => $categories ];
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    // Basic Auth header
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    $headers = [
        'Content-Type: application/json',
        // 'Cookie: JSESSIONID=YOUR_SESSION_ID', // Uncomment and set if required by Pinket
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // --- LOG THE OUTGOING REQUEST ---
    $logData = [
        'datetime' => date('Y-m-d H:i:s'),
        'type' => 'categories',
        'url' => $url,
        'headers' => $headers,
        'body' => $jsonData
    ];
    file_put_contents(__DIR__ . '/last_pinket_request.log', print_r($logData, true), FILE_APPEND);
    // --- END LOG ---

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [
        'response' => $response,
        'error' => $error,
        'httpCode' => $httpCode
    ];
}