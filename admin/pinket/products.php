<?php
require_once __DIR__ . '/pinket-config.php';
require_once 'helpers.php';

function prepareProductsData(mysqli $mysqli): array {
    $output = [ "branchKey" => "sunmiveh", "items" => [] ];
    $query = "SELECT * FROM products WHERE del_flag = 0";
    $res = $mysqli->query($query);
    if ($res) {
        while ($product = $res->fetch_assoc()) {
            $productId = (int)$product['id'];
            $productIdPadded = str_pad($productId, 3, '0', STR_PAD_LEFT);
            $isUnavailable = isset($product['state']) && $product['state'] == 1;

            $stmt = $mysqli->prepare("SELECT * FROM products_price WHERE pid = ? ORDER BY id DESC LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $priceRes = $stmt->get_result();

                if ($priceRow = $priceRes->fetch_assoc()) {
                    $weights = array_map('trim', preg_split('/[\s,]+/', $priceRow['weight']));
                    $prices = array_map('trim', preg_split('/[\s,]+/', $priceRow['price']));

                    $imgPath = "";
                    if (!empty($product['first_img_id'])) {
                        $fileName = getSingleValue($mysqli, "content", "file_name", "id = ?", [$product['first_img_id']], "i");
                        $imgPath = buildImageUrl($fileName);
                    }

                    $categoryName = $product['category'];
                    $catId = getSingleValue($mysqli, "categories", "id", "name = ? AND del_flag = 0", [$categoryName], "s");

                    // اگر فقط یک وزن و قیمت داریم
                    if (count($weights) === 1 && count($prices) === 1) {
                        $weightInt = (int)$weights[0];
                        $priceInt = intval($prices[0]);
                        $variantName = $weightInt > 0 ? $weights[0] . " گرمی" : null;

                        $output['items'][] = [
                            "itemId" => (string)$productIdPadded,
                            "name" => $product['name'],
                            "price" => $priceInt,
                            "quantity" => $isUnavailable ? 0 : 100,
                            "imageUrls" => $imgPath ? [$imgPath] : [],
                            "categoryId" => $catId,
                            // اگر وزن معتبر بود، می‌تونیم به عنوان unitInformation اضافه کنیم
                            "unitInformation" => $variantName
                        ];
                    } else {
                        // کالا اصلی (parent)
                        $output['items'][] = [
                            "itemId" => (string)$productIdPadded,
                            "name" => $product['name'],
                            "price" => intval($prices[0] ?? 0),
                            "quantity" => $isUnavailable ? 0 : 100,
                            "imageUrls" => $imgPath ? [$imgPath] : [],
                            "categoryId" => $catId
                        ];

                        // تنوع‌ها (variants)
                        for ($i = 0; $i < count($weights); $i++) {
                            $weightInt = (int)$weights[$i];
                            $variantIdPadded = str_pad($weightInt, 4, '0', STR_PAD_LEFT);
                            $variantItemId = $productIdPadded . $variantIdPadded;

                            $variantPrice = intval($prices[$i] ?? 0);
                            if ($variantPrice > 0) {
                                $variantName = $weights[$i] . " گرمی";

                                $output['items'][] = [
                                    "itemId" => (string)$variantItemId,
                                    "parentId" => (string)$productIdPadded,
                                    "name" => $product['name'],
                                    "variant" => ["وزن" => $variantName],
                                    "price" => $variantPrice,
                                    "quantity" => $isUnavailable ? 0 : 100,
                                    "imageUrls" => $imgPath ? [$imgPath] : [],
                                    "categoryId" => $catId,
                                    "unitInformation" => $variantName
                                ];
                            }
                        }
                    }
                }
                $stmt->close();
            }
        }
    }

    return $output;
}

function sendProductsToPinket(mysqli $mysqli) {
    $config = getPinketConfig();
    $baseUrl = $config['inventoryUrl'];
    $branchKey = $config['storeKey'];
    $token = $config['prodCatToken'];
    $url = rtrim($baseUrl, '/') . '/api/agent/v1/update-items';
    $data = prepareProductsData($mysqli);
    $data['branchKey'] = $branchKey;

    // Add discount:0 to each item if not present
    if (isset($data['items']) && is_array($data['items'])) {
        foreach ($data['items'] as &$item) {
            if (!isset($item['discount'])) {
                $item['discount'] = 0;
            }
        }
        unset($item);
    }

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

    return [
        'response' => $response,
        'error' => $error,
        'httpCode' => $httpCode
    ];
}