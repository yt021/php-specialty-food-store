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
    $token = $config['prodCatToken'];
    $url = rtrim($baseUrl, '/') . '/api/agent/v1/categories';
    $categories = prepareCategoriesData();
    $data = [ 'categories' => $categories ];
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    // Set Authorization header as 'Basic <token>'
    $authHeader = 'Authorization: Basic ' . $token;
    $host = parse_url($url, PHP_URL_HOST);
    $path = parse_url($url, PHP_URL_PATH);
    $acceptHeader = 'Accept: */*';
    $contentTypeHeader = 'Content-Type: application/json';
    $contentLengthHeader = 'Content-Length: ' . strlen($jsonData);
    $headers = [
        $authHeader,
        $contentTypeHeader,
        $acceptHeader,
        $contentLengthHeader
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // --- LOG THE OUTGOING REQUEST ---
    $fullRequest = "POST $path HTTP/1.1\n" .
        "Host: $host\n" .
        "$authHeader\n" .
        "$contentTypeHeader\n" .
        "$acceptHeader\n" .
        "$contentLengthHeader\n\n" .
        $jsonData . "\n";
    file_put_contents(__DIR__ . '/last_pinket_request.log', $fullRequest, FILE_APPEND);
    // --- END LOG ---

    // Enable cURL verbose logging
    $verbose = fopen(__DIR__ . '/curl_verbose.log', 'a+');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    rewind($verbose);
    fclose($verbose);
    return [
        'response' => $response,
        'error' => $error,
        'httpCode' => $httpCode
    ];
}