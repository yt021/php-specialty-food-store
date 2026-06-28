<?php
/**
 * Sitemap Generator for Abanfruit Website
 * این فایل برای تولید و مدیریت نقشه سایت استفاده می‌شود
 */

// Set up the environment like the admin system does
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include $bu."modules/admin/session_start.php";

// Check if user is logged in and has access to sitemap module
if (
    !isset($_SESSION["a_logged"]) ||
    $_SESSION["a_logged"]->get_level() < 1 ||
    !method_exists($_SESSION["a_logged"], 'check_access') ||
    !$_SESSION["a_logged"]->check_access('sitemap')
) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'دسترسی غیرمجاز']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// Get action from POST request
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'generate':
        generateSitemap();
        break;
    case 'validate':
        validateSitemap();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'عمل نامعتبر']);
        break;
}

/**
 * Generate new sitemap.xml file
 */
function generateSitemap() {
    global $mysqli, $s;
    
    try {
        // Define the base URL for the website
        $baseUrl = 'https://www.abanfruit.com/';
        
        // Start XML content with proper UTF-8 formatting
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
        
        $stats = [
            'pages' => 0,
            'products' => 0,
            'posts' => 0,
            'total' => 0
        ];
        
        // Add homepage
        $xml .= generateUrlEntry($baseUrl, '1.0', 'daily', date('Y-m-d'), [
            'loc' => $baseUrl . 'content/Image6259727308.jpeg',
            'caption' => 'میوه خشک آبان، دستچینی از بهترین میوه ها',
            'title' => 'میوه خشک آبان، دستچینی از بهترین میوه ها',
            'geo_location' => 'Tehran, Iran'
        ]);
        $stats['pages']++;
        
        // Add static pages
        $staticPages = [
            ['aboutus.php', 0.7, 'weekly'],
            ['contactus.php', 0.7, 'monthly'],
            ['rules.php', 0.7, 'weekly']
        ];
        
        foreach ($staticPages as $page) {
            $xml .= generateUrlEntry($baseUrl . 'pages/' . $page[0], $page[1], $page[2], date('Y-m-d'));
            $stats['pages']++;
        }
        
        // Add posts section
        $xml .= generateUrlEntry($baseUrl . 'posts/', 0.7, 'weekly', date('Y-m-d'));
        $stats['pages']++;
        
        // Add product categories (if they have products)
        $st = "SELECT name FROM categories WHERE section = 'products' AND name <> 'غیر قابل فروش' AND del_flag = 0 ORDER BY show_order ASC";
        $res = $mysqli->query($st);
        if ($res) {
            while ($category = $res->fetch_assoc()) {
                // Check if category has products
                $productCheck = "SELECT COUNT(*) as count FROM products WHERE category = '" . $category['name'] . "' AND del_flag = 0";
                $productRes = $mysqli->query($productCheck);
                if ($productRes) {
                    $productCount = $productRes->fetch_assoc()['count'];
                    if ($productCount > 0) {
                        $xml .= generateUrlEntry($baseUrl . '?cat=' . urlencode($category['name']), 0.7, 'weekly', date('Y-m-d'));
                        $stats['pages']++;
                    }
                }
            }
        }
        
        // Get and add individual posts
        $st = "SELECT name, last_update, first_img_id FROM posts WHERE del_flag = 0 AND section = 'articles' ORDER BY last_update DESC";
        $res = $mysqli->query($st);
        if ($res) {
            while ($post = $res->fetch_assoc()) {
                $imageData = null;
                if ($post['first_img_id']) {
                    $imageFile = getVarFromDB('content', 'file_name', 'id', $post['first_img_id']);
                    if (!empty($imageFile)) {
                        $imageData = [
                            'loc' => $baseUrl . 'content/' . $imageFile,
                            'caption' => $post['name'],
                            'title' => $post['name'],
                            'geo_location' => 'Tehran, Iran'
                        ];
                    }
                }
                
                $lastmod = date('Y-m-d', strtotime($post['last_update']));
                $xml .= generateUrlEntry($baseUrl . 'posts/' . $post['name'] . '.php', 0.6, 'monthly', $lastmod, $imageData);
                $stats['posts']++;
            }
        }
        
        // Get and add products
        $st = "SELECT file_name, first_img_id, name, id FROM products WHERE del_flag = 0 ORDER BY id DESC";
        $res = $mysqli->query($st);
        if ($res) {
            $productCount = 0;
            while ($product = $res->fetch_assoc()) {
                $imageData = null;
                if ($product['first_img_id']) {
                    $imageFile = getVarFromDB('content', 'file_name', 'id', $product['first_img_id']);
                    if (!empty($imageFile)) {
                        $imageData = [
                            'loc' => $baseUrl . 'content/' . $imageFile,
                            'caption' => $product['name'],
                            'title' => $product['name'],
                            'geo_location' => 'Tehran, Iran'
                        ];
                    }
                }
                
                $lastmod = date('Y-m-d'); // Use current date since last_update doesn't exist
                $xml .= generateUrlEntry($baseUrl . 'products/' . $product['file_name'] . '.php', 0.6, 'monthly', $lastmod, $imageData);
                $stats['products']++;
                $productCount++;
            }
            
            // Debug: Log if no products found
            if ($productCount == 0) {
                // Check total products in database
                $totalSt = "SELECT COUNT(*) as total FROM products";
                $totalRes = $mysqli->query($totalSt);
                $totalProducts = 0;
                if ($totalRes) {
                    $totalRow = $totalRes->fetch_assoc();
                    $totalProducts = $totalRow['total'];
                }
                
                // Check active products
                $activeSt = "SELECT COUNT(*) as active FROM products WHERE del_flag = 0";
                $activeRes = $mysqli->query($activeSt);
                $activeProducts = 0;
                if ($activeRes) {
                    $activeRow = $activeRes->fetch_assoc();
                    $activeProducts = $activeRow['active'];
                }
                
                throw new Exception("هیچ محصول فعالی یافت نشد. کل محصولات: $totalProducts، محصولات فعال: $activeProducts");
            }
        } else {
            throw new Exception("خطا در اجرای کوئری محصولات: " . $mysqli->error);
        }
        
        $xml .= "\n</urlset>";
        
        // Validate XML before writing
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        
        // Use libxml for better error handling
        libxml_use_internal_errors(true);
        
        if (!$dom->loadXML($xml)) {
            $errors = libxml_get_errors();
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = trim($error->message);
            }
            libxml_clear_errors();
            
            // Try to create a simpler XML structure
            $xml = createSimpleSitemap($baseUrl, $stats);
            
            // Try validation again with simplified XML
            if (!$dom->loadXML($xml)) {
                $errors = libxml_get_errors();
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = trim($error->message);
                }
                libxml_clear_errors();
                throw new Exception('خطا در ساختار XML: ' . implode(', ', $errorMessages));
            }
        }
        
        // Write to sitemap.xml file with proper UTF-8 encoding
        $sitemapPath = $_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml';
        $result = file_put_contents($sitemapPath, $xml, LOCK_EX);
        
        if ($result === false) {
            throw new Exception('خطا در نوشتن فایل نقشه سایت');
        }
        
        // Verify UTF-8 encoding
        $fileContent = file_get_contents($sitemapPath);
        if (!mb_check_encoding($fileContent, 'UTF-8')) {
            throw new Exception('خطا در رمزگذاری UTF-8 فایل نقشه سایت');
        }
        
        $stats['total'] = $stats['pages'] + $stats['products'] + $stats['posts'];
        $fileSize = formatBytes(filesize($sitemapPath));
        
        echo json_encode([
            'success' => true,
            'message' => 'نقشه سایت با موفقیت تولید شد',
            'stats' => $stats,
            'generated_at' => date('Y-m-d H:i:s'),
            'file_size' => $fileSize
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Validate existing sitemap.xml file
 */
function validateSitemap() {
    $sitemapPath = $_SERVER['DOCUMENT_ROOT'] . '/sitemap.xml';
    
    if (!file_exists($sitemapPath)) {
        echo json_encode([
            'success' => false,
            'error' => 'فایل نقشه سایت یافت نشد'
        ]);
        return;
    }
    
    try {
        $xml = file_get_contents($sitemapPath);
        if ($xml === false) {
            echo json_encode([
                'success' => false,
                'error' => 'خطا در خواندن فایل نقشه سایت'
            ]);
            return;
        }
        
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        
        $errors = [];
        $warnings = [];
        $validationResults = '';
        
        // Basic XML validation
        libxml_use_internal_errors(true);
        if ($dom->loadXML($xml)) {
            $validationResults = '✅ ساختار XML معتبر است<br>';
        } else {
            $libxmlErrors = libxml_get_errors();
            foreach ($libxmlErrors as $error) {
                $errors[] = 'خطای XML: ' . trim($error->message);
            }
            libxml_clear_errors();
        }
        
        // Check for required elements
        $urls = $dom->getElementsByTagName('url');
        $urlCount = $urls->length;
        
        if ($urlCount == 0) {
            $errors[] = 'هیچ URL در نقشه سایت یافت نشد';
        } else {
            $validationResults .= "✅ تعداد {$urlCount} URL یافت شد<br>";
        }
        
        // Check for duplicate URLs
        $urlsArray = [];
        for ($i = 0; $i < $urls->length; $i++) {
            $url = $urls->item($i);
            $locElements = $url->getElementsByTagName('loc');
            if ($locElements->length > 0) {
                $loc = $locElements->item(0);
                $urlValue = $loc->nodeValue;
                if (in_array($urlValue, $urlsArray)) {
                    $warnings[] = "URL تکراری: {$urlValue}";
                } else {
                    $urlsArray[] = $urlValue;
                }
            }
        }
        
        if (empty($warnings)) {
            $validationResults .= '✅ هیچ URL تکراری یافت نشد<br>';
        }
        
        // Check file size
        $fileSize = filesize($sitemapPath);
        if ($fileSize > 50 * 1024 * 1024) { // 50MB limit
            $warnings[] = 'اندازه فایل نقشه سایت بیش از حد مجاز است (50MB)';
        } else {
            $validationResults .= '✅ اندازه فایل مناسب است<br>';
        }
        
        // Check last modification date
        $lastMod = filemtime($sitemapPath);
        $daysSinceMod = (time() - $lastMod) / (24 * 3600);
        
        if ($daysSinceMod > 30) {
            $warnings[] = 'نقشه سایت بیش از 30 روز به‌روزرسانی نشده است';
        } else {
            $validationResults .= '✅ نقشه سایت به‌روز است<br>';
        }
        
        $allErrors = array_merge($errors, $warnings);
        
        if (empty($errors)) {
            echo json_encode([
                'success' => true,
                'message' => 'نقشه سایت معتبر است',
                'validation_results' => $validationResults . (empty($warnings) ? '' : '<br>⚠️ هشدارها: ' . implode('<br>', $warnings))
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => implode('<br>', $allErrors)
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'خطا در اعتبارسنجی: ' . $e->getMessage()
        ]);
    }
}

/**
 * Generate a URL entry for sitemap
 */
function generateUrlEntry($loc, $priority, $changefreq, $lastmod, $imageData = null) {
    $entry = "   <url>\n";
    $entry .= "      <loc>" . htmlspecialchars($loc, ENT_XML1, 'UTF-8') . "</loc>\n";
    $entry .= "      <lastmod>" . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
    $entry .= "      <changefreq>" . htmlspecialchars($changefreq, ENT_XML1, 'UTF-8') . "</changefreq>\n";
    $entry .= "      <priority>" . htmlspecialchars($priority, ENT_XML1, 'UTF-8') . "</priority>\n";
    
    if ($imageData && !empty($imageData['loc'])) {
        $entry .= "        <image:image>\n";
        $entry .= "            <image:loc>" . htmlspecialchars($imageData['loc'], ENT_XML1, 'UTF-8') . "</image:loc>\n";
        $entry .= "            <image:caption>" . htmlspecialchars($imageData['caption'], ENT_XML1, 'UTF-8') . "</image:caption>\n";
        $entry .= "            <image:title>" . htmlspecialchars($imageData['title'], ENT_XML1, 'UTF-8') . "</image:title>\n";
        $entry .= "            <image:geo_location>" . htmlspecialchars($imageData['geo_location'], ENT_XML1, 'UTF-8') . "</image:geo_location>\n";
        $entry .= "        </image:image>\n";
    }
    
    $entry .= "   </url>\n";
    return $entry;
}

/**
 * Format bytes to human readable format
 */
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}

/**
 * Create a simple sitemap without images
 */
function createSimpleSitemap($baseUrl, $stats) {
    global $mysqli;
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Add homepage
    $xml .= "   <url>\n";
    $xml .= "      <loc>" . htmlspecialchars($baseUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
    $xml .= "      <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "      <changefreq>daily</changefreq>\n";
    $xml .= "      <priority>1.0</priority>\n";
    $xml .= "   </url>\n";
    
    // Add static pages
    $staticPages = [
        ['aboutus.php', 0.7, 'weekly'],
        ['contactus.php', 0.7, 'monthly'],
        ['rules.php', 0.7, 'weekly']
    ];
    
    foreach ($staticPages as $page) {
        $xml .= "   <url>\n";
        $xml .= "      <loc>" . htmlspecialchars($baseUrl . 'pages/' . $page[0], ENT_XML1, 'UTF-8') . "</loc>\n";
        $xml .= "      <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "      <changefreq>" . $page[2] . "</changefreq>\n";
        $xml .= "      <priority>" . $page[1] . "</priority>\n";
        $xml .= "   </url>\n";
    }
    
    // Add posts section
    $xml .= "   <url>\n";
    $xml .= "      <loc>" . htmlspecialchars($baseUrl . 'posts/', ENT_XML1, 'UTF-8') . "</loc>\n";
    $xml .= "      <lastmod>" . date('Y-m-d') . "</lastmod>\n";
    $xml .= "      <changefreq>weekly</changefreq>\n";
    $xml .= "      <priority>0.7</priority>\n";
    $xml .= "   </url>\n";
    
    // Add product categories
    $st = "SELECT name FROM categories WHERE section = 'products' AND name <> 'غیر قابل فروش' AND del_flag = 0 ORDER BY show_order ASC";
    $res = $mysqli->query($st);
    if ($res) {
        while ($category = $res->fetch_assoc()) {
            $productCheck = "SELECT COUNT(*) as count FROM products WHERE category = '" . $category['name'] . "' AND del_flag = 0";
            $productRes = $mysqli->query($productCheck);
            if ($productRes) {
                $productCount = $productRes->fetch_assoc()['count'];
                if ($productCount > 0) {
                    $xml .= "   <url>\n";
                    $xml .= "      <loc>" . htmlspecialchars($baseUrl . '?cat=' . urlencode($category['name']), ENT_XML1, 'UTF-8') . "</loc>\n";
                    $xml .= "      <lastmod>" . date('Y-m-d') . "</lastmod>\n";
                    $xml .= "      <changefreq>weekly</changefreq>\n";
                    $xml .= "      <priority>0.7</priority>\n";
                    $xml .= "   </url>\n";
                }
            }
        }
    }
    
    // Add products (basic URLs only)
    $st = "SELECT file_name, id, name FROM products WHERE del_flag = 0 ORDER BY id DESC LIMIT 50";
    $res = $mysqli->query($st);
    $productCount = 0;
    if ($res) {
        while ($product = $res->fetch_assoc()) {
            $xml .= "   <url>\n";
            $xml .= "      <loc>" . htmlspecialchars($baseUrl . 'products/' . $product['file_name'] . '.php', ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "      <lastmod>" . date('Y-m-d') . "</lastmod>\n";
            $xml .= "      <changefreq>monthly</changefreq>\n";
            $xml .= "      <priority>0.6</priority>\n";
            $xml .= "   </url>\n";
            $productCount++;
        }
    }
    
    // If no products found, add a debug comment
    if ($productCount == 0) {
        $xml .= "   <!-- DEBUG: No products found in database -->\n";
    } else {
        $xml .= "   <!-- DEBUG: Found $productCount products -->\n";
    }
    
    $xml .= "</urlset>";
    return $xml;
}
?>
