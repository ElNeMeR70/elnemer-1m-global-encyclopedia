<?php
require_once __DIR__ . '/taxonomy_data.php';
header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=86400, s-maxage=86400');
header('X-Robots-Tag: all');

$proto = "https";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "million.elnemer-ai.com";
$base_url = $proto . "://" . $host;

$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$shard = isset($_GET['shard']) ? intval($_GET['shard']) : 0;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

if ($type === 'langs') {
    $all_langs = get_all_78_langs();
    $sectors = get_mega_sectors();
    $discs = get_all_100_disciplines();
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    // Core Portal Pages
    $core_pages = ['/', '/guinness', '/witness', '/audit/1', '/book/1', '/oai'];
    foreach ($core_pages as $cp) {
        echo "  <url>\n";
        echo "    <loc>{$base_url}{$cp}</loc>\n";
        echo "    <lastmod>2026-11-07</lastmod>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>1.0</priority>\n";
        echo "  </url>\n";
    }
    // All 78 Language Portal URLs
    foreach ($all_langs as $l) {
        $code = urlencode($l['code']);
        echo "  <url>\n";
        echo "    <loc>{$base_url}/?lang={$code}</loc>\n";
        echo "    <lastmod>2026-11-07</lastmod>\n";
        echo "    <changefreq>daily</changefreq>\n";
        echo "    <priority>0.95</priority>\n";
        echo "  </url>\n";
    }
    // All 50 Human-Benefit Mega-Sectors
    foreach ($sectors as $s) {
        echo "  <url>\n";
        echo "    <loc>{$base_url}/?sector={$s['sector_id']}</loc>\n";
        echo "    <lastmod>2026-11-07</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.90</priority>\n";
        echo "  </url>\n";
    }
    // All 1,000 Human-Benefit Sub-Disciplines
    foreach ($discs as $d) {
        echo "  <url>\n";
        echo "    <loc>{$base_url}/?discipline={$d['discipline_id']}</loc>\n";
        echo "    <lastmod>2026-11-07</lastmod>\n";
        echo "    <changefreq>weekly</changefreq>\n";
        echo "    <priority>0.85</priority>\n";
        echo "  </url>\n";
    }
    echo "</urlset>";
    exit;
}

// Master Sitemap Index: 1 Languages/Taxonomy Sitemap + 1,000 Book Sitemap Shards (100 Shards × 10 Pages × 5,000 Books = 5,000,000 Volumes)
if ($shard <= 0 || $shard > 100) {
    echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    echo "  <sitemap>\n";
    echo "    <loc>{$base_url}/sitemaps/sitemap_languages_78.xml</loc>\n";
    echo "    <lastmod>2026-11-07</lastmod>\n";
    echo "  </sitemap>\n";
    for ($s = 1; $s <= 100; $s++) {
        $s_pad = sprintf("%02d", $s);
        for ($pg = 1; $pg <= 10; $pg++) {
            echo "  <sitemap>\n";
            echo "    <loc>{$base_url}/sitemaps/sitemap_books_{$s_pad}_p{$pg}.xml</loc>\n";
            echo "    <lastmod>2026-11-07</lastmod>\n";
            echo "  </sitemap>\n";
        }
    }
    echo "</sitemapindex>";
    exit;
}

$page = isset($_GET['page']) ? max(1, min(10, intval($_GET['page']))) : 1;
$start_id = ($shard - 1) * 50000 + ($page - 1) * 5000 + 1;
$end_id = min(5000000, $start_id + 4999);

echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
for ($gid = $start_id; $gid <= $end_id; $gid++) {
    $isbn = make_1m_isbn13($gid);
    echo "  <url>\n";
    echo "    <loc>{$base_url}/book/{$gid}/isbn-{$isbn}</loc>\n";
    echo "    <lastmod>2026-11-07</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.80</priority>\n";
    echo "  </url>\n";
}
echo "</urlset>";
?>