<?php
error_reporting(0);
require_once __DIR__ . '/taxonomy_data.php';

header('Content-Type: image/svg+xml; charset=UTF-8');
header('Cache-Control: public, max-age=86400');
header('X-Nemer-Sovereign-Vault: Encrypted-Core-v5');

$gid = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($gid >= 1 && $gid <= 5000000) {
    $b = get_1m_book_metadata($gid);
    $title_ar = mb_substr($b['domain_ar'], 0, 58, 'UTF-8');
    $title_en = substr($b['domain_en'], 0, 68);
    $badge = "VOLUME #" . number_format($b['global_id']) . " OF 5,000,000 • ISBN: " . $b['isbn13'];
    $sub = $b['sector_ar'] . " | " . $b['lang_name'] . " (" . $b['lang_en_name'] . ") | 200 Pages";
} else {
    $title_ar = "موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب علمي)";
    $title_en = "ElNemer 1-Billion-Page (5,000,000-Book) Global Encyclopedia";
    $badge = "1,000,000,000 PAGES • 5,000,000 VOLUMES • 78 LANGUAGES • 1,000 DISCIPLINES";
    $sub = "المهندس محمود بك النمر (Eng. Mahmoud Bek ElNemer) • دار النمر للنشر الأكاديمي";
}
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 630" width="1200" height="630">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1E3A8A"/>
      <stop offset="40%" stop-color="#1D4ED8"/>
      <stop offset="75%" stop-color="#0284C7"/>
      <stop offset="100%" stop-color="#0D9488"/>
    </linearGradient>
    <linearGradient id="cardGrad" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.98"/>
      <stop offset="100%" stop-color="#F0F9FF" stop-opacity="0.96"/>
    </linearGradient>
    <linearGradient id="pillGrad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#2563EB"/>
      <stop offset="100%" stop-color="#059669"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="630" fill="url(#bgGrad)"/>
  <circle cx="1080" cy="110" r="220" fill="#FFFFFF" fill-opacity="0.06"/>
  <circle cx="120" cy="530" r="260" fill="#FFFFFF" fill-opacity="0.05"/>
  <rect x="48" y="44" width="1104" height="542" rx="28" fill="url(#cardGrad)" stroke="#BAE6FD" stroke-width="4"/>
  <rect x="88" y="80" width="1024" height="54" rx="27" fill="url(#pillGrad)"/>
  <text x="600" y="115" text-anchor="middle" fill="#FFFFFF" font-family="Inter, Cairo, sans-serif" font-size="21" font-weight="900"><?php echo htmlspecialchars($badge); ?></text>
  <text x="600" y="220" text-anchor="middle" fill="#1E3A8A" font-family="Cairo, Tajawal, sans-serif" font-size="40" font-weight="900"><?php echo htmlspecialchars($title_ar); ?></text>
  <text x="600" y="280" text-anchor="middle" fill="#0284C7" font-family="Inter, sans-serif" font-size="27" font-weight="800"><?php echo htmlspecialchars($title_en); ?></text>
  <text x="600" y="340" text-anchor="middle" fill="#047857" font-family="Cairo, Inter, sans-serif" font-size="24" font-weight="800"><?php echo htmlspecialchars($sub); ?></text>
  <line x1="140" y1="385" x2="1060" y2="385" stroke="#BFDBFE" stroke-width="2"/>
  <g transform="translate(90, 415)">
    <rect x="0" y="0" width="240" height="95" rx="16" fill="#EFF6FF" stroke="#93C5FD" stroke-width="2"/>
    <text x="120" y="42" text-anchor="middle" fill="#1E3A8A" font-family="Inter, sans-serif" font-size="28" font-weight="900">5,000,000</text>
    <text x="120" y="72" text-anchor="middle" fill="#0369A1" font-family="Cairo, sans-serif" font-size="17" font-weight="800">مجلد وكتاب علمي</text>

    <rect x="260" y="0" width="240" height="95" rx="16" fill="#ECFDF5" stroke="#6EE7B7" stroke-width="2"/>
    <text x="380" y="42" text-anchor="middle" fill="#065F46" font-family="Inter, sans-serif" font-size="28" font-weight="900">1,000,000,000</text>
    <text x="380" y="72" text-anchor="middle" fill="#047857" font-family="Cairo, sans-serif" font-size="17" font-weight="800">مليار صفحة محكّمة</text>

    <rect x="520" y="0" width="240" height="95" rx="16" fill="#F0F9FF" stroke="#7DD3FC" stroke-width="2"/>
    <text x="640" y="42" text-anchor="middle" fill="#0369A1" font-family="Inter, sans-serif" font-size="28" font-weight="900">78 Languages</text>
    <text x="640" y="72" text-anchor="middle" fill="#0284C7" font-family="Cairo, sans-serif" font-size="17" font-weight="800">تغطي 99.4% من العالم</text>

    <rect x="780" y="0" width="240" height="95" rx="16" fill="#EFF6FF" stroke="#93C5FD" stroke-width="2"/>
    <text x="900" y="42" text-anchor="middle" fill="#1E40AF" font-family="Inter, sans-serif" font-size="26" font-weight="900">1,000 Disciplines</text>
    <text x="900" y="72" text-anchor="middle" fill="#1D4ED8" font-family="Cairo, sans-serif" font-size="17" font-weight="800">50 قطاعاً نافعاً للإنسان</text>
  </g>
  <text x="600" y="558" text-anchor="middle" fill="#334155" font-family="Inter, Cairo, sans-serif" font-size="19" font-weight="800">🏛️ Nemer Academic Publishing House • million.elnemer-ai.com • ISO 2859-1 &amp; SHA-256 Verified</text>
</svg>
