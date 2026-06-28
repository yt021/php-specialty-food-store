<?php

if (!function_exists('snappay_product_feed_public_base_url')) {
    function snappay_product_feed_public_base_url()
    {
        return 'https://abanfruit.com';
    }
}

if (!function_exists('snappay_product_feed_output_path')) {
    function snappay_product_feed_output_path()
    {
        return dirname(__DIR__, 2) . '/snappay-products.json';
    }
}

if (!function_exists('snappay_product_feed_public_url')) {
    function snappay_product_feed_public_url()
    {
        return snappay_product_feed_public_base_url() . '/snappay-products.json';
    }
}

if (!function_exists('snappay_product_feed_to_irr')) {
    function snappay_product_feed_to_irr($amount_toman)
    {
        $amount_toman = (int)round((float)$amount_toman);
        if (defined('SNAPPAY_AMOUNT_MULTIPLIER')) {
            $mult = (int)SNAPPAY_AMOUNT_MULTIPLIER;
            if ($mult > 0) return $amount_toman * $mult;
        }
        return $amount_toman * 10;
    }
}

if (!function_exists('snappay_product_feed_parse_csv_list')) {
    function snappay_product_feed_parse_csv_list($value)
    {
        if (function_exists('get_str_index')) {
            $parsed = get_str_index((string)$value, ',');
            return isset($parsed[1]) && is_array($parsed[1]) ? $parsed[1] : array();
        }

        $parts = explode(',', (string)$value);
        $out = array();
        foreach ($parts as $part) {
            $part = trim((string)$part);
            if ($part !== '') $out[] = $part;
        }
        return $out;
    }
}

if (!function_exists('snappay_product_feed_clean_text')) {
    function snappay_product_feed_clean_text($value)
    {
        $value = (string)$value;
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', $value);
        return trim((string)$value);
    }
}

if (!function_exists('snappay_product_feed_image_urls')) {
    function snappay_product_feed_image_urls(mysqli $mysqli, array $product)
    {
        $ids = array();
        if (!empty($product['first_img_id'])) {
            $ids[] = (int)$product['first_img_id'];
        }
        if (!empty($product['img_str'])) {
            $img_ids = snappay_product_feed_parse_csv_list($product['img_str']);
            foreach ($img_ids as $img_id) {
                $img_id = (int)$img_id;
                if ($img_id > 0) $ids[] = $img_id;
            }
        }

        $ids = array_values(array_unique($ids));
        if (count($ids) === 0) return array();

        $urls = array();
        $st = $mysqli->prepare("SELECT file_name FROM content WHERE id = ? AND del_flag = 0 AND type = 'Image' LIMIT 1");
        if (!$st) return array();

        foreach ($ids as $id) {
            $id_sql = (string)$id;
            $st->bind_param('s', $id_sql);
            if (!$st->execute()) continue;
            $res = $st->get_result();
            $row = $res ? $res->fetch_assoc() : null;
            if (!$row || empty($row['file_name'])) continue;
            $urls[] = snappay_product_feed_public_base_url() . '/content/' . ltrim((string)$row['file_name'], '/');
        }
        $st->close();

        return array_values(array_unique($urls));
    }
}

if (!function_exists('snappay_product_feed_category_path')) {
    function snappay_product_feed_category_path($category)
    {
        $category = trim((string)$category);
        if ($category === '') return 'میوه خشک';
        return $category;
    }
}

if (!function_exists('snappay_product_feed_variant_id')) {
    function snappay_product_feed_variant_id($product_id, $weight)
    {
        $pid = str_pad((string)((int)$product_id), 3, '0', STR_PAD_LEFT);
        $weight_int = (int)$weight;
        if ($weight_int > 0) {
            return $pid . str_pad((string)$weight_int, 4, '0', STR_PAD_LEFT);
        }
        return $pid;
    }
}

if (!function_exists('snappay_product_feed_lightest_index')) {
    function snappay_product_feed_lightest_index($weights, $prices)
    {
        if (!is_array($weights) || !is_array($prices)) return null;

        $best_index = null;
        $best_weight = null;
        foreach ($weights as $i => $weight) {
            if (!isset($prices[$i]) || (int)round((float)$prices[$i]) <= 0) continue;

            $weight_int = (int)$weight;
            if ($best_index === null) {
                $best_index = $i;
                $best_weight = $weight_int;
                continue;
            }

            if ($weight_int > 0 && ($best_weight <= 0 || $weight_int < $best_weight)) {
                $best_index = $i;
                $best_weight = $weight_int;
            }
        }

        return $best_index;
    }
}

if (!function_exists('snappay_product_feed_build')) {
    function snappay_product_feed_build(mysqli $mysqli)
    {
        $items = array();
        $generated_at = date('c');

        $sql = "SELECT p.id, p.name, p.file_name, p.category, p.content, p.first_img_id, p.img_str, p.state,
                       pp.weight, pp.price
                FROM products p
                INNER JOIN products_price pp ON p.id = pp.pid
                WHERE p.del_flag = 0
                  AND p.type = 'pack'
                  AND p.category <> 'غیر قابل فروش'
                  AND pp.start_time = (
                      SELECT MAX(pp2.start_time)
                      FROM products_price pp2
                      WHERE pp2.pid = p.id
                  )
                ORDER BY p.show_order DESC, p.id DESC";

        $st = $mysqli->prepare($sql);
        if (!$st || !$st->execute()) {
            return array(false, array(
                'error' => $st ? $st->error : $mysqli->error,
                'items_count' => 0,
                'generated_at' => $generated_at
            ));
        }

        $res = $st->get_result();
        while ($product = $res->fetch_assoc()) {
            $pid = (int)$product['id'];
            if ($pid < 1) continue;
            if ($pid === 73) continue;

            $weights = snappay_product_feed_parse_csv_list($product['weight']);
            $base_prices = snappay_product_feed_parse_csv_list($product['price']);
            if (count($weights) === 0 || count($base_prices) === 0) continue;

            $sale_prices = $base_prices;
            $old_prices = array();
            $has_discount = false;
            if (function_exists('product_discount_get_active') && function_exists('product_discount_prepare_price_lists')) {
                $discount = product_discount_get_active($pid);
                if ($discount !== false) {
                    $prepared = product_discount_prepare_price_lists($base_prices, $discount, $weights);
                    if (isset($prepared['price']) && is_array($prepared['price'])) {
                        $sale_prices = $prepared['price'];
                    }
                    if (isset($prepared['old_price']) && is_array($prepared['old_price'])) {
                        $old_prices = $prepared['old_price'];
                    }
                    $has_discount = !empty($prepared['has_discount']);
                }
            }

            $name = trim((string)$product['name']);
            if ($name === '') continue;

            $link = snappay_product_feed_public_base_url() . '/products/' . rawurlencode((string)$product['file_name']) . '.php';
            $image_urls = snappay_product_feed_image_urls($mysqli, $product);
            if (count($image_urls) > 1) {
                $image_urls = array_slice($image_urls, 0, 1);
            }
            $availability = ((int)$product['state'] === 0) ? 'in stock' : 'out of stock';
            $category = snappay_product_feed_category_path($product['category']);
            $content = snappay_product_feed_clean_text($product['content']);

            $i = snappay_product_feed_lightest_index($weights, $sale_prices);
            if ($i !== null && isset($weights[$i])) {
                $weight = isset($weights[$i]) ? trim((string)$weights[$i]) : '';
                $sale_price_toman = isset($sale_prices[$i]) ? (int)round((float)$sale_prices[$i]) : 0;
                if ($sale_price_toman <= 0) continue;

                $title = $name;

                $description = array(
                    'نام محصول' => $name,
                    'دسته‌بندی' => (string)$product['category'],
                );
                if ($weight !== '' && (int)$weight > 0) {
                    $description['وزن'] = $weight . ' گرم';
                }
                if ($content !== '') {
                    $description['توضیحات'] = $content;
                }

                $item = array(
                    'id' => str_pad((string)$pid, 3, '0', STR_PAD_LEFT),
                    'title' => $title,
                    'subtitle' => '',
                    'link' => $link,
                    'image_link' => $image_urls,
                    'availability' => $availability,
                    'sale_price' => snappay_product_feed_to_irr($sale_price_toman),
                    'category' => $category,
                    'description' => $description,
                    'brand' => 'آبان',
                    'GTIN' => '',
                    'size' => ((int)$weight > 0) ? ($weight . ' گرم') : '',
                );

                if ($has_discount && isset($old_prices[$i]) && (int)$old_prices[$i] > $sale_price_toman) {
                    $item['regular_price'] = snappay_product_feed_to_irr((int)$old_prices[$i]);
                } else {
                    $item['regular_price'] = snappay_product_feed_to_irr($sale_price_toman);
                }

                $items[] = $item;
            }
        }
        $st->close();

        return array(true, array(
            'generated_at' => $generated_at,
            'items_count' => count($items),
            'items' => $items
        ));
    }
}

if (!function_exists('snappay_product_feed_write')) {
    function snappay_product_feed_write(mysqli $mysqli, &$error = '')
    {
        list($ok, $feed) = snappay_product_feed_build($mysqli);
        if (!$ok) {
            $error = isset($feed['error']) ? (string)$feed['error'] : 'خطای نامشخص در ساخت خوراک محصول.';
            return false;
        }

        $json = json_encode($feed, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if (!is_string($json)) {
            $error = 'کدگذاری JSON ناموفق بود.';
            return false;
        }

        $path = snappay_product_feed_output_path();
        $tmp = $path . '.tmp';
        if (file_put_contents($tmp, $json, LOCK_EX) === false) {
            $error = 'نوشتن فایل موقت خوراک محصول ناموفق بود.';
            return false;
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            $error = 'جایگزینی فایل خوراک محصول ناموفق بود.';
            return false;
        }

        return array(
            'path' => $path,
            'url' => snappay_product_feed_public_url(),
            'items_count' => (int)$feed['items_count'],
            'generated_at' => (string)$feed['generated_at']
        );
    }
}

?>
