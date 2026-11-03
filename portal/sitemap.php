<?php
require_once __DIR__ . '/taxonomy_data.php';
header('Content-Type: application/xml; charset=utf-8');

$proto = "https";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "million.elnemer-ai.com";
$base_url = $proto . "://" . $host;

$type = isset($_GET['type']) ? $_GET['type'] : '';
$shard = isset($_GET['shard']) ? intval($_GET['shard']) : 0;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

if ($type === 'langs') {
    $all_langs = get_all_78_langs();
    $sectors = get_mega_sectors();
    $discs = get_all_100_disciplines();
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
    // Root URL
    echo "  <url>\n";
    echo "    <loc>{$base_url}/</loc>\n";
    echo "    <changefreq>daily</changefreq>\n";
    echo "    <priority>1.0</priority>\n";
    echo "  </url>\n";
    // All 78 Language Portal URLs
    foreach ($all_langs as $l) {
        $code = urlencode($l['code']);
        echo "  <url>\n";
        echo "    <loc>{$base_url}/?lang={$code}</loc>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>0.95</priority>\n";
        echo "  </url>\n";
    }
    // All 10 Mega-Sectors
    foreach ($sectors as $s) {
        echo "  <url>\n";
        echo "    <loc>{$base_url}/?sector={$s['sector_id']}</loc>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.9</priority>\n";
        echo "  </url>\n";
    }
    // All 100 BISAC Sub-Disciplines
    foreach ($discs as $d) {
        echo "  <url>\n";
        echo "    <loc>{$base_url}/?discipline={$d['discipline_id']}</loc>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.85</priority>\n";
        echo "  </url>\n";
    }
    echo "</urlset>";
    exit;
}

if ($shard <= 0 || $shard > 20) {
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo "  <sitemap>\n";
    echo "    <loc>{$base_url}/sitemaps/sitemap_languages_78.xml</loc>\n";
    echo "    <lastmod>2026-11-02</lastmod>\n";
    echo "  </sitemap>\n";
    for ($s = 1; $s <= 20; $s++) {
        $s_pad = sprintf("%02d", $s);
        for ($pg = 1; $pg <= 10; $pg++) {
            echo "  <sitemap>\n";
            echo "    <loc>{$base_url}/sitemaps/sitemap_books_{$s_pad}.xml?page={$pg}</loc>\n";
            echo "    <lastmod>2026-11-03</lastmod>\n";
            echo "  </sitemap>\n";
        }
    }
    echo "</sitemapindex>";
    exit;
}

$page = isset($_GET['page']) ? max(1, min(10, intval($_GET['page']))) : 1;
$start_id = ($shard - 1) * 50000 + ($page - 1) * 5000 + 1;
$end_id = min(1000000, $start_id + 4999);

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
for ($gid = $start_id; $gid <= $end_id; $gid++) {
    $isbn = make_1m_isbn13($gid);
    echo "  <url>\n";
    echo "    <loc>{$base_url}/book/{$gid}/isbn-{$isbn}</loc>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>";
?>