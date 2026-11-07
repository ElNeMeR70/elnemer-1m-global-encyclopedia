<?php
/**
 * ==============================================================================
 * NEMER ACADEMIC PUBLISHING HOUSE — GLOBAL CITATION & CATALOGING EXPORT ENGINE
 * Supports: BibTeX (.bib), EndNote/Zotero RIS (.ris), Library of Congress MARC21 (.xml),
 * CSL-JSON / Schema.org JSON-LD (.json), APA 7th, IEEE, Harvard, MLA & Chicago
 *
 * Principal Author & Chief Architect: Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)
 * ==============================================================================
 */

require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/portal_i18n_theme.php';

$book_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$book_id = max(1, min(5000000, $book_id));
$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : '';

$b = get_1m_book_metadata($book_id);
$pad_id = sprintf("%07d", $book_id);
$url = "https://million.elnemer-ai.com/book/" . $book_id;
$doi_url = "https://million.elnemer-ai.com/doi/" . $b['doi'];

$bibtex_str = "@book{ElNemer2026_Vol" . $pad_id . ",\n"
    . "  author    = {ElNemer, Mahmoud Bek},\n"
    . "  title     = {" . $b['title'] . "},\n"
    . "  subtitle  = {" . $b['subtitle'] . "},\n"
    . "  series    = {ElNemer 1-Billion-Page (5,000,000-Volume) Global Multilingual Encyclopedia},\n"
    . "  volume    = {" . $book_id . "},\n"
    . "  publisher = {Nemer Academic Publishing House \\& Nemer Digital Solutions},\n"
    . "  address   = {Cairo / Global},\n"
    . "  year      = {2026},\n"
    . "  isbn      = {" . $b['isbn13'] . "},\n"
    . "  doi       = {" . $b['doi'] . "},\n"
    . "  pages     = {200},\n"
    . "  language  = {" . $b['lang_en_name'] . " (" . $b['lang_code'] . ")},\n"
    . "  url       = {" . $url . "},\n"
    . "  note      = {SHA-256: " . $b['sha256'] . "}\n"
    . "}\n";

$ris_str = "TY  - BOOK\r\n"
    . "T1  - " . $b['title'] . "\r\n"
    . "T2  - " . $b['subtitle'] . "\r\n"
    . "T3  - ElNemer 1-Billion-Page (5,000,000-Volume) Global Multilingual Scientific Encyclopedia\r\n"
    . "AU  - ElNemer, Mahmoud Bek\r\n"
    . "A1  - المهندس محمود بك النمر\r\n"
    . "PY  - 2026\r\n"
    . "DA  - 2026/11/01\r\n"
    . "PB  - Nemer Academic Publishing House & Nemer Digital Solutions\r\n"
    . "CY  - Cairo / Global\r\n"
    . "VL  - " . $book_id . "\r\n"
    . "SP  - 1\r\n"
    . "EP  - 200\r\n"
    . "SN  - " . $b['isbn13'] . "\r\n"
    . "DO  - " . $b['doi'] . "\r\n"
    . "LA  - " . $b['lang_code'] . "\r\n"
    . "KW  - " . $b['sector_en'] . "\r\n"
    . "KW  - " . $b['domain_en'] . "\r\n"
    . "KW  - BISAC " . $b['bisac'] . "\r\n"
    . "UR  - " . $url . "\r\n"
    . "N1  - Cryptographic SHA-256: " . $b['sha256'] . "\r\n"
    . "ER  - \r\n";

$marc21_str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
    . '<marc:record xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">' . "\n"
    . "  <marc:leader>01452nam a2200361 a 4500</marc:leader>\n"
    . '  <marc:controlfield tag="001">ELNEMER-1M-' . $pad_id . '</marc:controlfield>' . "\n"
    . '  <marc:controlfield tag="003">EG-CaNAP</marc:controlfield>' . "\n"
    . '  <marc:controlfield tag="005">20261101000000.0</marc:controlfield>' . "\n"
    . '  <marc:datafield tag="020" ind1=" " ind2=" "><marc:subfield code="a">' . htmlspecialchars($b['isbn13']) . '</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="024" ind1="7" ind2=" "><marc:subfield code="a">' . htmlspecialchars($b['doi']) . '</marc:subfield><marc:subfield code="2">doi</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="041" ind1="0" ind2=" "><marc:subfield code="a">' . htmlspecialchars($b['lang_code']) . '</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="100" ind1="1" ind2=" "><marc:subfield code="a">ElNemer, Mahmoud Bek</marc:subfield><marc:subfield code="c">Eng.</marc:subfield><marc:subfield code="e">author</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="245" ind1="1" ind2="0"><marc:subfield code="a">' . htmlspecialchars($b['title']) . ' :</marc:subfield><marc:subfield code="b">' . htmlspecialchars($b['subtitle']) . ' /</marc:subfield><marc:subfield code="c">Eng. Mahmoud Bek ElNemer.</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="260" ind1=" " ind2=" "><marc:subfield code="a">Cairo :</marc:subfield><marc:subfield code="b">Nemer Academic Publishing House,</marc:subfield><marc:subfield code="c">2026.</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="300" ind1=" " ind2=" "><marc:subfield code="a">200 pages : illustrations, vector charts, mathematical equations ; 28 cm.</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="490" ind1="1" ind2=" "><marc:subfield code="a">ElNemer 1-Billion-Page (5,000,000-Volume) Global Multilingual Scientific Encyclopedia ;</marc:subfield><marc:subfield code="v">Volume ' . $book_id . '</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="500" ind1=" " ind2=" "><marc:subfield code="a">Cryptographic Leaf SHA-256: ' . htmlspecialchars($b['sha256']) . '</marc:subfield></marc:datafield>' . "\n"
    . '  <marc:datafield tag="856" ind1="4" ind2="0"><marc:subfield code="u">' . $url . '</marc:subfield></marc:datafield>' . "\n"
    . "</marc:record>\n";

$jsonld_arr = [
    "@context" => "https://schema.org",
    "@type" => "Book",
    "id" => "urn:isbn:" . $b['isbn13'],
    "url" => $url,
    "name" => $b['title'],
    "alternativeHeadline" => $b['subtitle'],
    "volumeNumber" => $book_id,
    "numberOfPages" => 200,
    "isbn" => $b['isbn13'],
    "inLanguage" => $b['lang_code'],
    "datePublished" => "2026-11-01",
    "author" => [
        "@type" => "Person",
        "name" => "Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)",
        "jobTitle" => "Google Accredited Systems & AI Engineer · Founder & CEO",
        "url" => "https://elnemer-ai.com"
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => "Nemer Academic Publishing House & Nemer Digital Solutions",
        "url" => "https://elnemer-ai.com"
    ],
    "identifier" => [
        ["@type" => "PropertyValue", "propertyID" => "ISBN-13", "value" => $b['isbn13']],
        ["@type" => "PropertyValue", "propertyID" => "DOI", "value" => $b['doi'], "url" => $doi_url],
        ["@type" => "PropertyValue", "propertyID" => "SHA-256", "value" => $b['sha256']],
        ["@type" => "PropertyValue", "propertyID" => "MerkleRoot", "value" => "72806865ca4d56a0b03ad7e925ba6e7ccab8f537c016ec6214be2149e0c896d9"]
    ]
];

if ($format === 'bibtex' || $format === 'bib') {
    header('Content-Type: application/x-bibtex; charset=UTF-8');
    header('Content-Disposition: attachment; filename="ElNemer_Vol_' . $pad_id . '_' . $b['isbn13'] . '.bib"');
    echo $bibtex_str;
    exit;
}
if ($format === 'ris') {
    header('Content-Type: application/x-research-info-systems; charset=UTF-8');
    header('Content-Disposition: attachment; filename="ElNemer_Vol_' . $pad_id . '_' . $b['isbn13'] . '.ris"');
    echo $ris_str;
    exit;
}
if ($format === 'marc21' || $format === 'xml') {
    header('Content-Type: application/xml; charset=UTF-8');
    header('Content-Disposition: inline; filename="ElNemer_Vol_' . $pad_id . '_MARC21.xml"');
    echo $marc21_str;
    exit;
}
if ($format === 'jsonld' || $format === 'json') {
    header('Content-Type: application/ld+json; charset=UTF-8');
    echo json_encode($jsonld_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$apa_cite = "ElNemer, M. B. (2026). " . $b['title'] . " (Vol. " . number_format($book_id) . "). Nemer Academic Publishing House. ISBN " . $b['isbn13'] . ". " . $doi_url;
$ieee_cite = "M. B. ElNemer, \"" . $b['title'] . ",\" in ElNemer 1-Billion-Page (5,000,000-Volume) Global Multilingual Scientific Encyclopedia, vol. " . $book_id . ", Cairo: Nemer Academic Publishing House, 2026, pp. 1–200, ISBN: " . $b['isbn13'] . ", doi: " . $b['doi'] . ".";
$harvard_cite = "ElNemer, M.B., 2026. " . $b['title'] . ". Vol. " . $book_id . ". Cairo: Nemer Academic Publishing House. Available at: " . $url . ".";

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الاقتباس الأكاديمي والفهرسة الدولية (Cite Volume #<?php echo number_format($book_id); ?>) | موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب)</title>
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root { --bg: #07090e; --card: rgba(15,21,35,0.95); --gold: #0284C7; --border: rgba(212,175,55,0.4); }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { background: var(--bg); color: #f1f5f9; font-family: 'Tajawal', sans-serif; min-height: 100vh; }
    .nav { background:linear-gradient(135deg, #FFFFFF 0%, #EFF6FF 55%, #ECFDF5 100%); border-bottom: 2px solid var(--border); padding: 16px 28px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; }
    .container { max-width: 960px; margin: 36px auto; padding: 0 20px; }
    .card { background: var(--card); border: 2px solid var(--border); border-radius: 22px; padding: 34px; margin-bottom: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.75); }
    h1 { font-size: 24px; font-weight: 900; color:#1E3A8A; margin-bottom: 10px; }
    .cite-box { background:linear-gradient(135deg, #FFFFFF 0%, #EFF6FF 55%, #ECFDF5 100%); border: 1px solid rgba(212,175,55,0.28); border-radius: 12px; padding: 16px; margin-bottom: 18px; direction: ltr; text-align: left; font-family: 'Fira Code', monospace; font-size: 0.88rem; color:#334155; white-space: pre-wrap; word-break: break-word; }
    .label { font-weight: 800; color: #0284C7; margin-bottom: 8px; display: block; font-size: 0.95rem; }
    .btn-row { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
    .btn { padding: 12px 20px; border-radius: 10px; text-decoration: none; font-weight: 800; font-size: 0.92rem; display: inline-flex; align-items: center; gap: 8px; }
    .btn-gold { background: linear-gradient(135deg, #0284C7, #996515); color: #000; }
    .btn-dark { background: rgba(255,255,255,0.08); color:#1E3A8A; border: 1px solid rgba(255,255,255,0.2); }
  </style>
  <link rel="stylesheet" href="/nemer_unified_theme.css?v=5.2">
  <meta property="og:image" content="https://million.elnemer-ai.com/og_banner.php">
<?php render_unified_sapphire_css(); ?>
</head>
<body>
  <?php render_unified_topbar(); ?>

  <main class="container">
    <div class="card">
      <h1>🎓 بطاقة الاقتباس الأكاديمي والفهرسة الدولية — مجلد #<?php echo number_format($book_id); ?></h1>
      <p style="color:#334155;margin-bottom:22px;"><?php echo htmlspecialchars($b['title']); ?> | ISBN-13: <b><?php echo $b['isbn13']; ?></b></p>

      <div class="btn-row" style="margin-bottom:26px;">
        <a href="/cite/<?php echo $book_id; ?>?format=bibtex" class="btn btn-gold">📥 تحميل ملف BibTeX (.bib)</a>
        <a href="/cite/<?php echo $book_id; ?>?format=ris" class="btn btn-gold">📥 تحميل ملف EndNote / Zotero (.ris)</a>
        <a href="/cite/<?php echo $book_id; ?>?format=marc21" target="_blank" class="btn btn-dark">🏛️ بطاقة مكتبة الكونجرس MARC21 (.xml)</a>
        <a href="/cite/<?php echo $book_id; ?>?format=jsonld" target="_blank" class="btn btn-dark">🔗 Schema.org JSON-LD (.json)</a>
      </div>

      <span class="label">📌 صيغة APA (الإصدار السابع — APA 7th Edition):</span>
      <div class="cite-box"><?php echo htmlspecialchars($apa_cite); ?></div>

      <span class="label">📌 صيغة IEEE الهندسية والعلمية (IEEE Standard Citation):</span>
      <div class="cite-box"><?php echo htmlspecialchars($ieee_cite); ?></div>

      <span class="label">📌 صيغة هارفارد (Harvard Academic Reference):</span>
      <div class="cite-box"><?php echo htmlspecialchars($harvard_cite); ?></div>

      <span class="label">📌 كود LaTeX / BibTeX الجاهز للنسخ:</span>
      <div class="cite-box"><?php echo htmlspecialchars($bibtex_str); ?></div>
    </div>
  </main>
</body>
</html>
