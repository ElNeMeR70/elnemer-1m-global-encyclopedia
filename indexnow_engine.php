<?php
/**
 * ==============================================================================
 * INDEXNOW AUTONOMOUS PROTOCOL ENGINE (MICROSOFT BING & COPILOT & YANDEX)
 * ElNemer 1-Million-Book Global Scientific Encyclopedia
 * 
 * Author: Eng. Mahmoud Bek ElNemer (المهندس محمود النمر)
 * ==============================================================================
 */

define('INDEXNOW_KEY', '72806865ca4d56a0b03ad7e925ba6e7c');
define('INDEXNOW_HOST', 'million.elnemer-ai.com');
define('INDEXNOW_KEY_LOCATION', 'https://million.elnemer-ai.com/' . INDEXNOW_KEY . '.txt');

function send_indexnow_batch($url_list) {
    $payload = [
        'host' => INDEXNOW_HOST,
        'key' => INDEXNOW_KEY,
        'keyLocation' => INDEXNOW_KEY_LOCATION,
        'urlList' => array_values(array_unique($url_list))
    ];
    
    $json_data = json_encode($payload, JSON_UNESCAPED_SLASHES);
    
    $endpoints = [
        'https://api.indexnow.org/indexnow',
        'https://www.bing.com/indexnow'
    ];
    
    $results = [];
    foreach ($endpoints as $ep) {
        $ch = curl_init($ep);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($json_data),
            'User-Agent: ElNemer-1M-IndexNow-Engine/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $resp = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        
        $results[$ep] = [
            'code' => $http_code,
            'response' => $resp,
            'error' => $err,
            'success' => in_array($http_code, [200, 202])
        ];
    }
    
    return [
        'urls_count' => count($url_list),
        'results' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// If invoked via web or cli
if (isset($_GET['action']) && $_GET['action'] === 'submit_core') {
    require_once __DIR__ . '/taxonomy_data.php';
    header('Content-Type: application/json; charset=utf-8');
    
    $urls = [
        'https://million.elnemer-ai.com/',
        'https://million.elnemer-ai.com/guinness',
        'https://million.elnemer-ai.com/sitemap_index.xml',
        'https://million.elnemer-ai.com/sitemap.xml',
        'https://million.elnemer-ai.com/guinness_dossier.pdf',
        'https://million.elnemer-ai.com/institutions_dossier.pdf'
    ];
    
    $all_langs = get_all_78_langs();
    foreach ($all_langs as $l) {
        $urls[] = "https://million.elnemer-ai.com/?lang=" . urlencode($l['code']);
    }
    
    for ($s = 1; $s <= 10; $s++) {
        $urls[] = "https://million.elnemer-ai.com/?sector=" . $s;
    }
    for ($d = 1; $d <= 100; $d++) {
        $urls[] = "https://million.elnemer-ai.com/?discipline=" . $d;
    }
    for ($b = 1; $b <= 100; $b++) {
        $urls[] = "https://million.elnemer-ai.com/book/" . $b;
    }
    
    $res = send_indexnow_batch($urls);
    echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
