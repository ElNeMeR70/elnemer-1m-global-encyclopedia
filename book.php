<?php
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    require __DIR__ . '/stream_pdf.php';
    exit;
}
require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/portal_i18n_theme.php';
require_once __DIR__ . '/barcode_helper.php';
if (!defined('NEMER_PDF_LIB_ONLY')) {
    define('NEMER_PDF_LIB_ONLY', true);
}
require_once __DIR__ . '/stream_pdf.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$gid = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['book']) ? intval($_GET['book']) : 1);
$gid = max(1, min(5000000, $gid));
$b = get_1m_book_metadata($gid);
$details = get_book_curriculum_and_details($b);
$chapters = $details['chapters'];
$pdf_audit = get_pdf_binary_audit_meta($b);
$pdf_bytes_sha256 = $pdf_audit['pdf_bytes_sha256'];
$pdf_size_kb = $pdf_audit['pdf_size_kb'];

$pal = $b['palette'];
$all_langs = get_all_78_langs();
$siblings = get_work_sibling_volumes($gid, 78);

$is_rtl_book = in_array($b['lang_code'], ['ar', 'fa', 'ur']);

$proto = "https";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "million.elnemer-ai.com";
$base_url = $proto . "://" . $host;
$canonical_url = $base_url . "/book/" . $b['global_id'] . "/" . $b['slug'];
$pdf_stream_url = $base_url . "/pdf/" . $b['global_id'] . "/" . $b['isbn13'] . "_ElNemer_Vol_" . sprintf("%07d", $b['global_id']) . "_200Pages.pdf";
$native_reader_url = $base_url . "/book/" . $b['global_id'] . "?view=reader";

$prev_id = ($gid > 1) ? ($gid - 1) : 5000000;
$next_id = ($gid < 5000000) ? ($gid + 1) : 1;

$egp_price = number_format(floatval($b['price_usd']) * 50.0, 2);
$sar_price = number_format(floatval($b['price_usd']) * 3.75, 2);
$eur_price = number_format(floatval($b['price_usd']) * 0.92, 2);

// If Native Full-Book Printable Reader Mode (?view=reader) is requested:
if (isset($_GET['view']) && $_GET['view'] === 'reader') {
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($b['lang_code']); ?>" dir="<?php echo $is_rtl_book ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($b['title']); ?> — نسخة القراءة الكاملة والطباعة</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Cairo', 'Inter', sans-serif; background: #F1F5F9; color: #0F172A; line-height: 1.85; padding: 24px 16px; }
        .reader-toolbar { max-width: 900px; margin: 0 auto 20px; background: #1E3A8A; color: #FFF; padding: 14px 22px; border-radius: 14px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; box-shadow: 0 10px 25px rgba(30,58,138,0.2); }
        .reader-toolbar a, .reader-toolbar button { background: #FFFFFF; color: #1E3A8A; border: none; padding: 8px 16px; border-radius: 8px; font-family: inherit; font-weight: 800; font-size: 0.9rem; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .page-sheet { max-width: 900px; margin: 0 auto 28px; background: #FFFFFF; border: 1px solid #CBD5E1; border-radius: 16px; padding: 48px 42px; box-shadow: 0 12px 32px rgba(15,23,42,0.06); page-break-after: always; }
        .cover-sheet { background: linear-gradient(155deg, <?php echo $pal['bg']; ?> 0%, <?php echo $pal['pri']; ?> 100%); color: #FFFFFF; border: 6px solid <?php echo $pal['acc']; ?>; text-align: center; padding: 56px 40px; position: relative; }
        .cover-box { background: rgba(255,255,255,0.97); color: #0F172A; border-radius: 18px; padding: 32px 28px; margin: 28px auto; max-width: 720px; border-right: 6px solid <?php echo $pal['acc']; ?>; }
        h1, h2, h3 { color: #1E3A8A; }
        .ch-block { border-top: 2px solid #E2E8F0; padding-top: 20px; margin-top: 20px; }
        @media print {
            body { background: #FFF; padding: 0; }
            .reader-toolbar { display: none !important; }
            .page-sheet { box-shadow: none; border: 1px solid #E2E8F0; margin: 0 0 16px 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="reader-toolbar">
        <div>
            <strong>📖 قارئ موسوعة النمر للمعرفة التطبيقية</strong> — مجلد #<?php echo number_format($b['global_id']); ?> (<?php echo htmlspecialchars($b['lang_name']); ?>)
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button onclick="window.print()">🖨️ طباعة / حفظ كملف PDF بالخط العربي الأصيل</button>
            <a href="<?php echo htmlspecialchars($pdf_stream_url); ?>" download>📥 ملف PDF الأرشيفي الخام</a>
            <a href="/book/<?php echo $b['global_id']; ?>">🔙 العودة لصفحة الكتاب</a>
        </div>
    </div>

    <!-- PAGE 1: NATIVE LANGUAGE COVER (100% ARABIC FOR ARABIC BOOKS) -->
    <div class="page-sheet cover-sheet">
        <div style="display:inline-block;background:rgba(255,255,255,0.95);color:#1E3A8A;padding:8px 22px;border-radius:999px;font-weight:900;font-size:1rem;">
            <?php echo $is_rtl_book ? '🏛️ موسوعة النمر للمعرفة التطبيقية • سلسلة الأدلة العملية المراجعة' : '🏛️ ElNemer Encyclopedia of Applied Knowledge • Peer-Reviewed Series'; ?>
        </div>
        <div class="cover-box">
            <div style="color:#0284C7;font-weight:900;font-size:1.05rem;margin-bottom:8px;">
                <?php echo htmlspecialchars($is_rtl_book ? ('القطاع التخصصي: ' . $b['sector_ar']) : ('Sector: ' . $b['sector_en'])); ?>
            </div>
            <h1 style="font-size:1.85rem;font-weight:900;color:#0F172A;line-height:1.4;margin-bottom:12px;">
                <?php echo htmlspecialchars($b['title']); ?>
            </h1>
            <p style="font-size:1.05rem;color:#334155;font-weight:700;margin-bottom:16px;">
                <?php echo htmlspecialchars($b['subtitle']); ?>
            </p>
            <div style="display:inline-flex;gap:12px;flex-wrap:wrap;justify-content:center;background:#EFF6FF;padding:10px 18px;border-radius:12px;color:#1E3A8A;font-weight:800;font-size:0.9rem;">
                <span><?php echo $is_rtl_book ? ('المجلد رقم #' . number_format($b['global_id'])) : ('Volume #' . number_format($b['global_id'])); ?></span>
                <span>•</span>
                <span><?php echo $is_rtl_book ? ('اللغة: ' . $b['lang_name']) : ('Language: ' . $b['lang_en_name']); ?></span>
                <span>•</span>
                <span><?php echo $is_rtl_book ? ('معرّف العمل الموحد: ' . $b['work_code']) : ('Work ID: ' . $b['work_code']); ?></span>
            </div>
        </div>
        <div style="font-size:1.1rem;font-weight:900;color:#FFFFFF;margin-top:16px;">
            <?php echo $is_rtl_book ? ('تأليف وإعداد: ' . $b['author']) : ('Author: ' . $b['author']); ?><br>
            <span style="font-size:0.9rem;color:#E0F2FE;">
                <?php echo $is_rtl_book ? ('مراجعة علمية: ' . $details['reviewer']['name_ar']) : ('Scientific Review: ' . $details['reviewer']['name_en']); ?>
            </span>
        </div>
    </div>

    <!-- PAGE 2: SUMMARY, TARGET AUDIENCE & TABLE OF CONTENTS -->
    <div class="page-sheet">
        <h2 style="font-size:1.45rem;margin-bottom:12px;border-bottom:2px solid #DBEAFE;padding-bottom:8px;">
            <?php echo $is_rtl_book ? '📌 ملخص الكتاب وأهدافه التطبيقية' : '📌 Executive Practical Summary & Learning Objectives'; ?>
        </h2>
        <p style="font-size:1.02rem;color:#1E293B;margin-bottom:20px;">
            <?php echo htmlspecialchars($is_rtl_book ? $details['summary_ar'] : $details['summary_en']); ?>
        </p>

        <h3 style="font-size:1.2rem;margin-bottom:10px;color:#0284C7;">
            <?php echo $is_rtl_book ? '🎯 ماذا ستتعلم في هذا الدليل؟' : '🎯 Key Learning Outcomes'; ?>
        </h3>
        <ul style="padding-right:22px;padding-left:22px;margin-bottom:24px;">
            <?php foreach (($is_rtl_book ? $details['outcomes_ar'] : $details['outcomes_en']) as $out_item): ?>
                <li style="margin-bottom:6px;font-weight:600;"><?php echo htmlspecialchars($out_item); ?></li>
            <?php endforeach; ?>
        </ul>

        <h3 style="font-size:1.2rem;margin-bottom:12px;color:#1E3A8A;">
            <?php echo $is_rtl_book ? '📑 الفهرس التفصيلي للفصول العشرة (200 صفحة)' : '📑 Complete 10-Chapter Table of Contents (200 Pages)'; ?>
        </h3>
        <?php foreach ($chapters as $c): ?>
            <div style="display:flex;justify-content:space-between;padding:10px 14px;background:<?php echo ($c['ch']%2==1)?'#F8FAFC':'#FFFFFF'; ?>;border-bottom:1px solid #E2E8F0;border-radius:8px;">
                <strong><?php echo $is_rtl_book ? ('الفصل ' . $c['ch'] . ': ' . $c['ar']) : ('Chapter ' . $c['ch'] . ': ' . $c['en']); ?></strong>
                <span style="color:#0284C7;font-weight:800;"><?php echo $is_rtl_book ? ('صفحات ' . $c['pages']) : ('Pages ' . $c['pages']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- PAGE 3: PRACTICAL CONTENT & SAMPLE CHAPTERS -->
    <div class="page-sheet">
        <h2 style="font-size:1.45rem;margin-bottom:16px;border-bottom:2px solid #DBEAFE;padding-bottom:8px;">
            <?php echo $is_rtl_book ? '📖 المحتوى التطبيقي والخطوات العملية' : '📖 Core Practical Protocols & Actionable Checklists'; ?>
        </h2>
        <?php foreach ($details['sample_sections'] as $s_sec): ?>
            <div class="ch-block">
                <div style="display:inline-block;background:#EFF6FF;color:#0284C7;padding:4px 12px;border-radius:6px;font-size:0.85rem;font-weight:800;margin-bottom:8px;">
                    <?php echo htmlspecialchars($s_sec['badge']); ?>
                </div>
                <h3 style="font-size:1.2rem;color:#0F172A;margin-bottom:10px;"><?php echo htmlspecialchars($s_sec['title_ar']); ?></h3>
                <div style="white-space:pre-line;color:#334155;font-size:1rem;background:#F8FAFC;padding:16px;border-radius:12px;border:1px solid #E2E8F0;"><?php echo htmlspecialchars($s_sec['body_ar']); ?></div>
            </div>
        <?php endforeach; ?>

        <div class="ch-block">
            <h3 style="font-size:1.2rem;color:#047857;margin-bottom:10px;">
                <?php echo $is_rtl_book ? '🩺 بطاقة المراجعة العلمية والمراجع المعتمدة' : '🩺 Specialist Editorial Review & Verified References'; ?>
            </h3>
            <p style="font-weight:700;color:#0F172A;margin-bottom:6px;">
                <?php echo htmlspecialchars($is_rtl_book ? $details['reviewer']['name_ar'] : $details['reviewer']['name_en']); ?> (<?php echo htmlspecialchars($details['reviewer']['date']); ?>)
            </p>
            <p style="color:#475569;font-size:0.95rem;margin-bottom:12px;">
                <?php echo htmlspecialchars($is_rtl_book ? $details['reviewer']['scope_ar'] : $details['reviewer']['scope_en']); ?>
            </p>
            <ol style="padding-right:22px;padding-left:22px;color:#334155;font-size:0.92rem;">
                <?php foreach ($details['references'] as $ref): ?>
                    <li style="margin-bottom:4px;"><?php echo htmlspecialchars($ref); ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}

$json_ld = [
    "@context" => "https://schema.org",
    "@graph" => [
        [
            "@type" => "Book",
            "@id" => $canonical_url . "#book",
            "name" => $b['title'],
            "alternativeHeadline" => $b['subtitle'],
            "isbn" => $b['isbn13'],
            "numberOfPages" => 200,
            "bookEdition" => "V5.2 Applied & Specialist-Reviewed Edition (2026)",
            "bookFormat" => "https://schema.org/EBook",
            "inLanguage" => $b['lang_code'],
            "isAccessibleForFree" => true,
            "workExample" => ["@id" => $base_url . "/#work-" . $b['work_code']],
            "identifier" => [
                ["@type" => "PropertyValue", "propertyID" => "WorkID", "value" => $b['work_code']],
                ["@type" => "PropertyValue", "propertyID" => "InternalArchivalID", "value" => $b['internal_doc_id']],
                ["@type" => "PropertyValue", "propertyID" => "MetadataSHA256", "value" => $b['metadata_sha256']],
                ["@type" => "PropertyValue", "propertyID" => "PDFBytesSHA256", "value" => $pdf_bytes_sha256],
                ["@type" => "PropertyValue", "propertyID" => "BISAC", "value" => $b['bisac']]
            ],
            "author" => [
                "@type" => "Person",
                "name" => "Eng. Mahmoud Bek ElNemer (" . $b['author'] . ")",
                "url" => "https://elnemer-ai.com"
            ],
            "editor" => [
                "@type" => "Person",
                "name" => $details['reviewer']['name_ar']
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Nemer Academic Publishing House & Nemer Digital Solutions",
                "url" => "https://elnemer-ai.com"
            ],
            "datePublished" => "2026-11-01",
            "about" => $b['domain_ar'] . " / " . $b['domain_en']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($b['lang_code']); ?>" dir="rtl">
<head>
    <meta name="google-site-verification" content="U6ysk2Ie2LsEIPZ3UlsIS3gmpl_jwAjOyfu0lWKKBF8" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($b['title']); ?> | مجلد #<?php echo number_format($b['global_id']); ?> | موسوعة النمر للمعرفة التطبيقية</title>
    <meta name="description" content="<?php echo htmlspecialchars($details['summary_ar']); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">

    <!-- 78-Language Hreflang Alternates (All mapped to the EXACT SAME Unified Work ID & Subject!) -->
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($canonical_url); ?>">
<?php foreach ($siblings as $sib): ?>
    <link rel="alternate" hreflang="<?php echo htmlspecialchars($sib['lang_code']); ?>" href="<?php echo $base_url; ?>/book/<?php echo $sib['global_id']; ?>">
<?php endforeach; ?>

    <meta property="og:type" content="book">
    <meta property="og:image" content="<?php echo $base_url; ?>/og_banner.php?id=<?php echo $b['global_id']; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($b['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($details['summary_ar']); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <!-- Google Scholar / Highwire Press & Dublin Core Academic Indexing Meta Tags -->
    <meta name="citation_title" content="<?php echo htmlspecialchars($b['title']); ?>">
    <meta name="citation_author" content="ElNemer, Mahmoud Bek">
    <meta name="citation_author" content="<?php echo htmlspecialchars($b['author']); ?>">
    <meta name="citation_publication_date" content="2026/11/01">
    <meta name="citation_isbn" content="<?php echo htmlspecialchars($b['isbn13']); ?>">
    <meta name="citation_doi" content="<?php echo htmlspecialchars($b['doi']); ?>">
    <meta name="citation_language" content="<?php echo htmlspecialchars($b['lang_code']); ?>">
    <meta name="citation_publisher" content="Nemer Academic Publishing House &amp; Nemer Digital Solutions">
    <meta name="citation_pdf_url" content="<?php echo htmlspecialchars($pdf_stream_url); ?>">
    <meta name="citation_fulltext_html_url" content="<?php echo htmlspecialchars($native_reader_url); ?>">
    <meta name="DC.title" content="<?php echo htmlspecialchars($b['title']); ?>">
    <meta name="DC.creator" content="Eng. Mahmoud Bek ElNemer">
    <meta name="DC.identifier" content="ISBN:<?php echo htmlspecialchars($b['isbn13']); ?>">

    <script type="application/ld+json"><?php echo json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/nemer_unified_theme.css?v=6.0">
<?php render_unified_sapphire_css(); ?>
    <style>
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: linear-gradient(135deg, #F0F5FF 0%, #F8FAFC 50%, #ECFDF5 100%) !important;
            color: #0F172A !important;
            line-height: 1.75;
        }
        .container { max-width: 1320px; margin: 24px auto 80px; padding: 0 20px; }
        .breadcrumbs {
            font-size: 0.88rem;
            background: #FFFFFF;
            padding: 12px 18px;
            border-radius: 12px;
            border: 1.5px solid #DBEAFE;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .breadcrumbs a { color: #1D4ED8; text-decoration: none; font-weight: 800; }
        .breadcrumbs a:hover { text-decoration: underline; }
        .hero-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 32px;
            background: #FFFFFF;
            border: 2px solid #DBEAFE;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
        }
        @media (max-width: 960px) { .hero-grid { grid-template-columns: 1fr; } }
        .book-stage-3d { display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding-top: 8px; }
        .book-cover-3d {
            width: 100%; max-width: 300px;
            min-height: 465px;
            background: linear-gradient(155deg, <?php echo $pal['bg']; ?> 0%, <?php echo $pal['pri']; ?> 100%);
            border: 3px solid <?php echo $pal['acc']; ?>;
            border-radius: 12px 22px 22px 12px;
            padding: 22px 18px;
            text-align: center;
            color: #FFFFFF;
            position: relative;
            box-shadow: 0 22px 48px rgba(30,58,138,0.24), inset 4px 0 12px rgba(255,255,255,0.28);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .pills-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .pill {
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            color: #1E3A8A;
        }
        .pill.emerald { background: #ECFDF5; border-color: #6EE7B7; color: #065F46; }
        h1.book-title { font-size: 1.75rem; font-weight: 900; color: #1E3A8A !important; margin-bottom: 8px; line-height: 1.38; -webkit-text-fill-color: #1E3A8A !important; }
        h2.book-subtitle { font-size: 1rem; color: #475569 !important; font-weight: 700; margin-bottom: 16px; }
        .policy-banner {
            background: linear-gradient(135deg, #ECFDF5 0%, #EFF6FF 100%);
            border: 1.5px solid #6EE7B7;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 18px;
            font-size: 0.9rem;
            color: #065F46;
        }
        .actions-box {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 18px 0;
            padding: 16px;
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
        }
        .btn-action {
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.92rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-primary-read { background: linear-gradient(135deg, #059669, #047857); color: #FFFFFF !important; box-shadow: 0 8px 20px rgba(5,150,105,0.25); }
        .btn-blue-dl { background: linear-gradient(135deg, #1D4ED8, #1E40AF); color: #FFFFFF !important; }
        .btn-outline-act { background: #FFFFFF; color: #1E3A8A !important; border: 1.5px solid #93C5FD; }
        .section-box {
            margin-top: 28px;
            background: #FFFFFF;
            border: 1.5px solid #DBEAFE;
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: 900;
            color: #1E3A8A;
            margin-bottom: 18px;
            border-bottom: 2px solid #DBEAFE;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .two-col-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 285px), 1fr)); gap: 20px; }
        .info-card { background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 16px; padding: 20px; }
        .ch-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 285px), 1fr)); gap: 14px; }
        .ch-card { background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 14px; padding: 16px; }
        .sample-card { background: #F8FAFC; border-right: 5px solid #0284C7; border-radius: 14px; padding: 20px; margin-bottom: 16px; border-top: 1px solid #E2E8F0; border-left: 1px solid #E2E8F0; border-bottom: 1px solid #E2E8F0; }
        .meta-table { width: 100%; border-collapse: collapse; margin-top: 12px; border-radius: 12px; overflow: hidden; border: 1px solid #E2E8F0; }
        .meta-table th { background: #EFF6FF; color: #1E3A8A; padding: 12px 16px; width: 32%; font-weight: 800; font-size: 0.88rem; text-align: right; border-bottom: 1px solid #E2E8F0; }
        .meta-table td { background: #FFFFFF; color: #0F172A; padding: 12px 16px; font-weight: 700; font-size: 0.9rem; text-align: right; border-bottom: 1px solid #E2E8F0; }
        .sibling-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 210px), 1fr)); gap: 10px; }
        .sibling-card { padding: 12px; background: #F8FAFC; border: 1.5px solid #E2E8F0; border-radius: 12px; text-decoration: none; color: #1E3A8A; font-weight: 800; font-size: 0.85rem; display: flex; flex-direction: column; gap: 4px; transition: all 0.2s; }
        .sibling-card:hover { border-color: #0284C7; background: #EFF6FF; transform: translateY(-2px); }
        .sibling-card.active-lang { border: 2px solid #059669; background: #ECFDF5; }
        .copy-pill { display: inline-flex; align-items: center; gap: 4px; background: #EFF6FF; border: 1px solid #93C5FD; color: #1D4ED8; padding: 2px 8px; border-radius: 6px; font-size: 0.78rem; cursor: pointer; font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body>

<?php render_unified_topbar(); ?>

<main class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>">🏛️ موسوعة النمر للمعرفة التطبيقية</a> &raquo;
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>&sector=<?php echo $b['sector_id']; ?>"><?php echo htmlspecialchars($b['sector_ar']); ?></a> &raquo;
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>&discipline=<?php echo $b['discipline_id']; ?>"><?php echo htmlspecialchars($b['short_ar']); ?></a> &raquo;
        <span style="color:#0284C7;font-weight:800;">مجلد #<?php echo number_format($b['global_id']); ?> (معرّف العمل: <?php echo $b['work_code']; ?>)</span>
    </nav>

    <section class="hero-grid">
        <!-- 3D BOOK HARDCOVER SHOWCASE — 100% MATCHED TO DECLARED BOOK LANGUAGE -->
        <div class="book-stage-3d">
            <div class="book-cover-3d" dir="<?php echo $is_rtl_book ? 'rtl' : 'ltr'; ?>">
                <div style="background:rgba(255,255,255,0.96);color:#1E3A8A;border:1.5px solid <?php echo $pal['acc']; ?>;padding:7px 10px;border-radius:8px;font-size:0.78rem;font-weight:900;">
                    <?php echo $is_rtl_book ? '🏛️ موسوعة النمر للمعرفة التطبيقية' : '🏛️ ELNEMER APPLIED ENCYCLOPEDIA'; ?>
                </div>

                <div style="width:112px;height:112px;margin:10px auto;border-radius:50%;background:rgba(255,255,255,0.96);border:3px solid <?php echo $pal['acc']; ?>;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#1E3A8A;box-shadow:0 8px 20px rgba(15,23,42,0.15);">
                    <span style="font-size:0.72rem;color:#0284C7;font-weight:900;"><?php echo $is_rtl_book ? 'المجلد رقم' : 'VOLUME'; ?></span>
                    <span style="color:#0F172A;font-weight:900;font-size:1.1rem;">#<?php echo number_format($b['global_id']); ?></span>
                    <span style="font-size:0.7rem;color:#059669;font-weight:900;"><?php echo $is_rtl_book ? '200 صفحة' : '200 PAGES'; ?></span>
                </div>

                <div style="background:rgba(255,255,255,0.98);border-right:<?php echo $is_rtl_book ? '5px solid '.$pal['acc'] : 'none'; ?>;border-left:<?php echo !$is_rtl_book ? '5px solid '.$pal['acc'] : 'none'; ?>;border-radius:12px;padding:14px 12px;text-align:<?php echo $is_rtl_book ? 'right' : 'left'; ?>;box-shadow:0 8px 20px rgba(15,23,42,0.16);">
                    <div style="color:#0284C7;font-weight:900;font-size:0.78rem;margin-bottom:4px;">
                        <?php echo htmlspecialchars($is_rtl_book ? $b['sector_ar'] : $b['sector_en']); ?>
                    </div>
                    <div style="color:#0F172A;font-weight:900;font-size:0.96rem;line-height:1.4;margin-bottom:6px;">
                        <?php echo htmlspecialchars($is_rtl_book ? $b['domain_ar'] : $b['domain_en']); ?>
                    </div>
                    <div style="color:#334155;font-size:0.8rem;font-weight:700;line-height:1.35;margin-bottom:8px;">
                        <?php echo htmlspecialchars($is_rtl_book ? $b['angle_ar'] : $b['angle_en']); ?>
                    </div>
                    <div style="color:#059669;font-weight:900;font-size:0.8rem;">
                        🌍 <?php echo htmlspecialchars($is_rtl_book ? ('اللغة: ' . $b['lang_name']) : ('Edition: ' . $b['lang_name'] . ' (' . $b['lang_en_name'] . ')')); ?>
                    </div>
                </div>

                <div style="font-size:0.82rem;color:#FFFFFF;font-weight:900;padding-top:8px;border-top:1px solid rgba(255,255,255,0.35);">
                    ✍️ <?php echo htmlspecialchars($is_rtl_book ? ('تأليف: ' . $b['author']) : ('By: ' . $b['author'])); ?><br>
                    <span style="color:#E0F2FE;font-family:monospace;font-size:0.74rem;"><?php echo htmlspecialchars($b['work_code']); ?> • <?php echo $b['isbn13']; ?></span>
                </div>
            </div>
            <!-- SCANNABLE GS1 / EAN-13 VECTOR BARCODE CARD UNDER COVER -->
            <div style="margin-top:14px;background:linear-gradient(135deg,#FFFFFF,#F0F9FF);border:1.5px solid #93C5FD;border-radius:16px;padding:12px 14px;text-align:center;width:100%;max-width:300px;box-shadow:0 8px 20px rgba(30,58,138,0.06);">
                <div style="font-size:0.76rem;font-weight:900;color:#1E3A8A;margin-bottom:6px;">📷 باركود قياسي قابل للمسح الضوئي (GS1 / EAN-13)</div>
                <?php echo render_ean13_svg($b['isbn13'], 230, 88); ?>
                <div style="font-size:0.72rem;color:#047857;font-weight:800;margin-top:6px;">✔ محقق بمعادلة Modulo-10 (95 وحدة ضوئية)</div>
            </div>
        </div>

        <!-- BOOK METADATA, CLEAR ACCESS POLICY & READER-FIRST SUMMARY -->
        <div>
            <div class="pills-row">
                <span class="pill emerald">🔓 متاح للقراءة والتحميل المجاني المباشر (0.00$)</span>
                <span class="pill">🔗 معرّف العمل الموحد: <?php echo htmlspecialchars($b['work_code']); ?></span>
                <span class="pill">🌍 لغة الكتاب: <?php echo htmlspecialchars($b['lang_name'] . " [" . $b['lang_code'] . "]"); ?></span>
                <span class="pill">📚 القطاع #<?php echo $b['sector_id']; ?> • التخصص #<?php echo $b['discipline_id']; ?></span>
            </div>

            <h1 class="book-title"><?php echo htmlspecialchars($b['title']); ?></h1>
            <h2 class="book-subtitle"><?php echo htmlspecialchars($b['subtitle']); ?></h2>

            <!-- EXPLICIT OPEN-ACCESS VS COMMERCIAL PRICING CLARIFICATION -->
            <div class="policy-banner">
                <strong>💡 توضيح سياسة الإتاحة المجانية والتسعير:</strong>
                هذا الكتاب متاح <b>مجاناً بالكامل (0.00$)</b> للقراءة الفورية والطباعة والتحميل المباشر عبر هذه البوابة لجميع القراء والطلاب والباحثين.
                <?php if ($b['is_paid']): ?>
                    السعر المرجعي المسجل في البطاقة الفهرسية (<b>$<?php echo $b['price_usd']; ?> USD</b> / <?php echo $egp_price; ?> ج.م) هو السعر الاسترشادي لنسخ التوزيع التجاري عبر المتاجر الخارجية والترخيص المؤسسي فقط، ولا يقيّد القراءة المباشرة هنا.
                <?php else: ?>
                    وهو مصنف أيضاً ضمن <b>الوقف المعرفي المجاني المفتوح</b> في جميع قنوات الفهرسة.
                <?php endif; ?>
            </div>

            <!-- EXECUTIVE PRACTICAL SUMMARY RIGHT AT THE TOP -->
            <div style="background:#F8FAFC;border:1.5px solid #DBEAFE;border-radius:14px;padding:16px 18px;margin-bottom:16px;">
                <div style="font-weight:900;color:#1E3A8A;margin-bottom:6px;font-size:0.95rem;">📌 ملخص الكتاب وفائدته العملية للقارئ:</div>
                <p style="color:#1E293B;font-size:0.95rem;margin:0;"><?php echo htmlspecialchars($details['summary_ar']); ?></p>
            </div>

            <!-- CLEAN 4-BOX BIBLIOGRAPHIC STRIP (EQUAL HEIGHT, CRISP BUTTONS, NO OVERFLOW) -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,200px),1fr));gap:12px;margin-bottom:16px;">
                <div style="background:linear-gradient(135deg,#FFFFFF,#F8FAFC);border:1.5px solid #BFDBFE;border-radius:14px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="font-size:0.78rem;font-weight:800;color:#475569;">الرقم المعياري للفهرسة (ISBN-13 / EAN)</div>
                    <div style="font-size:1rem;font-weight:900;color:#1E3A8A;font-family:monospace;margin-top:6px;"><?php echo htmlspecialchars($b['isbn13']); ?></div>
                    <div style="font-size:0.74rem;color:#059669;font-weight:800;margin-top:4px;">✔ محقق بمعادلة GS1 Modulo-10</div>
                </div>
                <div style="background:linear-gradient(135deg,#FFFFFF,#F8FAFC);border:1.5px solid #BFDBFE;border-radius:14px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="font-size:0.78rem;font-weight:800;color:#475569;">المعرّف الأرشيفي الرقمي (Archival Handle)</div>
                    <div dir="ltr" style="font-size:0.86rem;font-weight:900;color:#0284C7;font-family:monospace;margin-top:6px;text-align:left;word-break:break-all;">doi:<?php echo htmlspecialchars($b['doi']); ?></div>
                    <div style="margin-top:6px;">
                        <a href="/doi/<?php echo htmlspecialchars($b['doi']); ?>" style="display:inline-flex;align-items:center;gap:5px;background:#EFF6FF;color:#1E3A8A;border:1px solid #93C5FD;padding:3px 10px;border-radius:8px;font-size:0.76rem;font-weight:800;text-decoration:none;white-space:nowrap;">🔗 بوابة التحقق الداخلية</a>
                    </div>
                </div>
                <div style="background:linear-gradient(135deg,#FFFFFF,#F8FAFC);border:1.5px solid #BFDBFE;border-radius:14px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="font-size:0.78rem;font-weight:800;color:#475569;">التصنيف الموضوعي الدولي (BISAC)</div>
                    <div style="font-size:0.88rem;font-weight:900;color:#1E3A8A;margin-top:4px;">[<?php echo htmlspecialchars($b['bisac']); ?>] <?php echo htmlspecialchars($b['short_ar']); ?></div>
                    <div dir="ltr" style="font-size:0.75rem;color:#475569;font-weight:700;text-align:left;margin-top:2px;"><?php echo htmlspecialchars($b['short_en']); ?></div>
                </div>
                <div style="background:linear-gradient(135deg,#ECFDF5,#F0F9FF);border:1.5px solid #6EE7B7;border-radius:14px;padding:14px;display:flex;flex-direction:column;justify-content:space-between;">
                    <div style="font-size:0.78rem;font-weight:800;color:#065F46;">سياسة الإتاحة المجانية والتسعير</div>
                    <div style="font-size:0.96rem;font-weight:900;color:#047857;margin-top:4px;">متاح مجاناً 100% (0.00$)</div>
                    <div style="font-size:0.74rem;color:#334155;font-weight:700;margin-top:2px;">السعر المرجعي التجاري: $<?php echo $b['price_usd']; ?> (<?php echo $egp_price; ?> ج.م)</div>
                </div>
            </div>

            <!-- PRIMARY READER & DOWNLOAD ACTIONS (UNIFIED 2x3 SYMMETRICAL GRID) -->
            <div class="actions-box">
                <a class="btn-action btn-primary-read" href="<?php echo htmlspecialchars($native_reader_url); ?>" target="_blank">
                    📖 قراءة وطباعة الكتاب باللغة المعلنة (<?php echo htmlspecialchars($b['lang_name']); ?>)
                </a>
                <a class="btn-action btn-blue-dl" href="<?php echo htmlspecialchars($pdf_stream_url); ?>" download>
                    📥 تحميل ملف الـ PDF الكامل (200 صفحة — <?php echo $pdf_size_kb; ?> KB)
                </a>
                <a class="btn-action btn-outline-act" href="#live-reading-sample">
                    👁️ قراءة عينة مباشرة في هذه الصفحة
                </a>
                <a class="btn-action btn-outline-act" href="/audit/<?php echo $b['global_id']; ?>">
                    🔐 فحص بصمة الـ PDF وشجرة ميركل (23 مستوى)
                </a>
                <a class="btn-action btn-outline-act" href="/cite/<?php echo $b['global_id']; ?>">
                    🎓 اقتباس أكاديمي (APA / BibTeX / MARC21)
                </a>
                <a class="btn-action btn-outline-act" href="/certificate.php?id=<?php echo $b['global_id']; ?>" target="_blank">
                    📜 استخراج بطاقة التوثيق الأرشيفية (PDF)
                </a>
            </div>

            <!-- CLEAN QUICK JUMP BAR (PEARL SAPPHIRE — NO GREY BACKGROUND) -->
            <div style="background:linear-gradient(135deg,#EFF6FF 0%,#ECFDF5 100%);border:1.5px solid #93C5FD;border-radius:14px;padding:12px 16px;margin-bottom:14px;">
                <div style="font-size:0.82rem;font-weight:900;color:#1E3A8A;margin-bottom:8px;">⚡ تنقل سريع بين المحطات المرجعية للموسوعة (5,000,000 مجلد • 1 مليار صفحة):</div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <a href="/book/1" style="background:#FFFFFF;color:#1E3A8A;border:1.5px solid #93C5FD;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:800;text-decoration:none;">🥇 #1 المجلد التأسيسي الأول</a>
                    <a href="/book/1000000" style="background:#FFFFFF;color:#1E3A8A;border:1.5px solid #93C5FD;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:800;text-decoration:none;">🎯 #1,000,000 المجلد المليوني</a>
                    <a href="/book/2500000" style="background:#FFFFFF;color:#1E3A8A;border:1.5px solid #93C5FD;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:800;text-decoration:none;">⚡ #2,500,000 منتصف المليار صفحة</a>
                    <a href="/book/4500001" style="background:#FFFFFF;color:#047857;border:1.5px solid #6EE7B7;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:800;text-decoration:none;">🎁 #4,500,001 بداية الوقف المجاني (100M صفحة)</a>
                    <a href="/book/5000000" style="background:#FFFFFF;color:#1E3A8A;border:1.5px solid #93C5FD;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:800;text-decoration:none;">🏆 #5,000,000 المجلد الختامي للمليار صفحة</a>
                    <a href="/book/<?php echo rand(1, 5000000); ?>" style="background:linear-gradient(135deg,#059669,#0D9488);color:#FFFFFF;padding:5px 12px;border-radius:999px;font-size:0.78rem;font-weight:800;text-decoration:none;">🎲 مجلد عشوائي</a>
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;font-size:0.85rem;color:#475569;">
                <a href="/book/<?php echo $prev_id; ?>" style="color:#1D4ED8;font-weight:800;text-decoration:none;">&rarr; المجلد السابق (#<?php echo number_format($prev_id); ?>)</a>
                <span>|</span>
                <a href="/book/<?php echo $next_id; ?>" style="color:#1D4ED8;font-weight:800;text-decoration:none;">المجلد التالي (#<?php echo number_format($next_id); ?>) &larr;</a>
                <span>|</span>
                <a href="#sibling-translations" style="color:#059669;font-weight:800;text-decoration:none;">🌍 تصفح النسخ المترجمة لنفس الكتاب (78 لغة)</a>
            </div>
        </div>
    </section>

    <!-- WHO THIS BOOK IS FOR & WHAT YOU WILL LEARN -->
    <section class="section-box">
        <div class="two-col-grid">
            <div class="info-card">
                <h2 style="font-size:1.2rem;font-weight:900;color:#1E3A8A;margin-bottom:12px;">👥 لمن هذا الكتاب؟ (الفئات المستفيدة)</h2>
                <ul style="padding-right:20px;color:#1E293B;font-size:0.94rem;">
                    <?php foreach ($details['audience_ar'] as $aud): ?>
                        <li style="margin-bottom:8px;font-weight:600;"><?php echo htmlspecialchars($aud); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="info-card">
                <h2 style="font-size:1.2rem;font-weight:900;color:#047857;margin-bottom:12px;">🎯 ماذا ستتعلم في هذا الكتاب؟ (المخرجات العملية)</h2>
                <ul style="padding-right:20px;color:#1E293B;font-size:0.94rem;">
                    <?php foreach ($details['outcomes_ar'] as $out): ?>
                        <li style="margin-bottom:8px;font-weight:600;"><?php echo htmlspecialchars($out); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </section>

    <!-- 10-CHAPTER DOMAIN-CONSISTENT SYLLABUS -->
    <section class="section-box">
        <h2 class="section-title">📑 فهرس الكتاب التطبيقي (10 فصول متخصصة في <?php echo htmlspecialchars($b['short_ar']); ?> — 200 صفحة)</h2>
        <p style="color:#475569;margin-bottom:16px;font-size:0.93rem;">
            صُمّم هذا الفهرس ليرتبط ارتباطاً مباشراً بموضوع <b>«<?php echo htmlspecialchars($b['domain_ar']); ?>»</b> ويتدرج مع القارئ من المفاهيم الأساسية والتقييم الأولي حتى خطوات التطبيق العملي، الوقاية من الأخطاء الشائعة، واختبار التقييم الذاتي:
        </p>
        <div class="ch-grid">
            <?php foreach ($chapters as $c): ?>
            <div class="ch-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;padding:3px 10px;border-radius:6px;font-weight:900;font-size:0.82rem;">
                        الفصل <?php echo $c['ch']; ?> (Chapter <?php echo $c['ch']; ?>)
                    </span>
                    <span style="color:#059669;font-family:monospace;font-size:0.84rem;font-weight:800;">
                        صفحات <?php echo $c['pages']; ?>
                    </span>
                </div>
                <div style="font-weight:800;color:#0F172A;font-size:0.96rem;margin-bottom:6px;"><?php echo htmlspecialchars($c['ar']); ?></div>
                <div dir="ltr" style="font-size:0.83rem;color:#475569;text-align:left;line-height:1.4;"><?php echo htmlspecialchars($c['en']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- LIVE READING SAMPLE RIGHT ON THE BOOK PAGE -->
    <section class="section-box" id="live-reading-sample">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;border-bottom:2px solid #DBEAFE;padding-bottom:10px;">
            <h2 style="font-size:1.3rem;font-weight:900;color:#1E3A8A;margin:0;">📖 عينة قراءة مباشرة من داخل الكتاب (مقتطفات تطبيقية من الفصول)</h2>
            <a href="<?php echo htmlspecialchars($native_reader_url); ?>" target="_blank" style="background:#059669;color:#FFF;padding:8px 16px;border-radius:10px;text-decoration:none;font-weight:800;font-size:0.86rem;">
                فتح نسخة القراءة الكاملة والطباعة &larr;
            </a>
        </div>
        <?php foreach ($details['sample_sections'] as $samp): ?>
        <div class="sample-card">
            <div style="display:inline-block;background:#EFF6FF;color:#1D4ED8;padding:3px 10px;border-radius:6px;font-size:0.8rem;font-weight:800;margin-bottom:8px;">
                <?php echo htmlspecialchars($samp['badge']); ?>
            </div>
            <h3 style="font-size:1.08rem;font-weight:900;color:#0F172A;margin-bottom:8px;"><?php echo htmlspecialchars($samp['title_ar']); ?></h3>
            <div style="white-space:pre-line;color:#334155;font-size:0.94rem;line-height:1.8;"><?php echo htmlspecialchars($samp['body_ar']); ?></div>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- SPECIALIST REVIEWER RECORD & DOMAIN BIBLIOGRAPHY -->
    <section class="section-box">
        <h2 class="section-title">🩺 بطاقة المراجع العلمي المتخصص والمراجع المعتمدة ومنهجية التدقيق</h2>
        <div class="two-col-grid">
            <div class="info-card" style="border-right:5px solid #059669;">
                <div style="font-size:0.82rem;font-weight:900;color:#059669;margin-bottom:4px;">✔ سجل المراجعة المتخصصة ومنهجيتها</div>
                <h3 style="font-size:1.05rem;font-weight:900;color:#0F172A;margin-bottom:6px;"><?php echo htmlspecialchars($details['reviewer']['name_ar']); ?></h3>
                <div dir="ltr" style="font-size:0.82rem;color:#475569;text-align:left;margin-bottom:10px;"><?php echo htmlspecialchars($details['reviewer']['name_en']); ?></div>
                <div style="font-size:0.88rem;color:#1E293B;margin-bottom:8px;">
                    <b>تاريخ المراجعة والتحديث:</b> <?php echo htmlspecialchars($details['reviewer']['date']); ?>
                </div>
                <div style="font-size:0.88rem;color:#334155;margin-bottom:10px;">
                    <b>نطاق ومنهجية المراجعة:</b> <?php echo htmlspecialchars($details['reviewer']['scope_ar']); ?>
                </div>
                <div style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:10px 12px;font-size:0.82rem;color:#065F46;">
                    <b>تنبيه مهني وإخلاء مسؤولية:</b> صُممت هذه المادة للأغراض التعليمية والتوعوية والتطبيقية العامة، ولا تغني في الحالات الطبية أو القانونية أو الهندسية الحرجة عن الفحص المباشر لدى المختص المعتمد.
                </div>
            </div>
            <div class="info-card">
                <h3 style="font-size:1.05rem;font-weight:900;color:#1E3A8A;margin-bottom:10px;">📚 المصادر والمراجع العلمية المعتمدة في هذا التخصص</h3>
                <ol style="padding-right:20px;color:#334155;font-size:0.88rem;line-height:1.7;">
                    <?php foreach ($details['references'] as $ref_item): ?>
                        <li style="margin-bottom:8px;font-weight:600;"><?php echo htmlspecialchars($ref_item); ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
    </section>

    <!-- SIBLING TRANSLATED VOLUMES (100% MATCHED WORK ID & SUBJECT ACROSS 78 LANGUAGES) -->
    <section class="section-box" id="sibling-translations">
        <h2 class="section-title">🌍 النسخ المترجمة والمجلدات الشقيقة لنفس الكتاب (معرّف العمل الموحد: <?php echo htmlspecialchars($b['work_code']); ?>)</h2>
        <p style="color:#475569;margin-bottom:14px;font-size:0.92rem;">
            جميع المجلدات الـ 78 التالية تشترك في <b>نفس معرّف العمل الموحد (<code><?php echo htmlspecialchars($b['work_code']); ?></code>)</b> وتتناول <b>نفس الموضوع بالضبط («<?php echo htmlspecialchars($b['short_ar']); ?>» / <code><?php echo htmlspecialchars($b['short_en']); ?></code>)</b> مترجماً across 78 لغة عالمية:
        </p>
        <div class="sibling-grid">
            <?php foreach (array_slice($siblings, 0, 24) as $sib_b):
                $is_curr = ($sib_b['global_id'] === $b['global_id']);
            ?>
                <a href="/book/<?php echo $sib_b['global_id']; ?>/<?php echo $sib_b['slug']; ?>" class="sibling-card <?php echo $is_curr ? 'active-lang' : ''; ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span>🌍 <b>[<?php echo htmlspecialchars($sib_b['lang_code']); ?>] <?php echo htmlspecialchars($sib_b['lang_name']); ?></b></span>
                        <?php if ($is_curr): ?><span style="font-size:0.72rem;background:#059669;color:#FFF;padding:1px 6px;border-radius:4px;">النسخة الحالية</span><?php endif; ?>
                    </div>
                    <div style="color:#0F172A;font-size:0.8rem;font-weight:700;"><?php echo htmlspecialchars($sib_b['short_ar']); ?></div>
                    <div dir="ltr" style="color:#475569;font-size:0.74rem;text-align:left;"><?php echo htmlspecialchars($sib_b['short_en']); ?></div>
                    <div style="color:#0284C7;font-size:0.75rem;font-family:monospace;">مجلد #<?php echo number_format($sib_b['global_id']); ?> • <?php echo htmlspecialchars($sib_b['work_code']); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        <details style="margin-top:14px;">
            <summary style="cursor:pointer;font-weight:800;color:#1D4ED8;font-size:0.9rem;">➕ عرض باقي اللغات الـ 78 لنفس العمل الموحد (<?php echo htmlspecialchars($b['work_code']); ?>)</summary>
            <div class="sibling-grid" style="margin-top:12px;">
                <?php foreach (array_slice($siblings, 24) as $sib_b):
                    $is_curr = ($sib_b['global_id'] === $b['global_id']);
                ?>
                    <a href="/book/<?php echo $sib_b['global_id']; ?>/<?php echo $sib_b['slug']; ?>" class="sibling-card <?php echo $is_curr ? 'active-lang' : ''; ?>">
                        <div>🌍 <b>[<?php echo htmlspecialchars($sib_b['lang_code']); ?>] <?php echo htmlspecialchars($sib_b['lang_name']); ?></b></div>
                        <div style="color:#0F172A;font-size:0.78rem;"><?php echo htmlspecialchars($sib_b['short_ar']); ?></div>
                        <div style="color:#0284C7;font-size:0.74rem;font-family:monospace;">مجلد #<?php echo number_format($sib_b['global_id']); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>
    </section>

    <!-- TRANSPARENT BIBLIOGRAPHIC & CRYPTOGRAPHIC SPECIFICATION TABLE -->
    <section class="section-box">
        <h2 class="section-title">🔐 البطاقة الفهرسية والتوثيق التشفيري الشفاف (فصل بصمة الملف عن بصمة البيانات الوصفية)</h2>
        <table class="meta-table">
            <tr>
                <th>المؤلف والناشر وحساب النشر</th>
                <td>
                    <b>المهندس محمود بك النمر (<?php echo htmlspecialchars($b['author']); ?>)</b> — دار النمر للنشر الأكاديمي والحلول الرقمية.<br>
                    <span style="font-size:0.82rem;color:#475569;font-weight:600;">نطاق النشر: حساب ناشر رقمي مفعل على منصة Google Play Books Partner Center (Publisher Account: Nemer Digital Solutions).</span>
                </td>
            </tr>
            <tr>
                <th>معرّف العمل الموحد (Unified Work ID)</th>
                <td>
                    <code style="font-size:0.95rem;color:#1D4ED8;font-weight:900;"><?php echo htmlspecialchars($b['work_code']); ?></code>
                    <span style="font-size:0.82rem;color:#475569;margin-right:8px;">(يربط جميع الترجمات الـ 78 لنفس الموضوع: المجلدات #<?php echo number_format($b['work_start_gid']); ?> إلى #<?php echo number_format(min(5000000, $b['work_start_gid'] + 77)); ?>)</span>
                </td>
            </tr>
            <tr>
                <th>1️⃣ بصمة بايتات ملف الـ PDF النهائي<br><span style="font-size:0.78rem;color:#059669;">(PDF Binary File SHA-256)</span></th>
                <td>
                    <code style="font-family:monospace;font-size:0.84rem;color:#047857;word-break:break-all;"><?php echo $pdf_bytes_sha256; ?></code>
                    <button class="copy-pill" onclick="navigator.clipboard.writeText('<?php echo $pdf_bytes_sha256; ?>');alert('تم نسخ بصمة بايتات ملف الـ PDF!');">📋 نسخ</button>
                    <div style="font-size:0.8rem;color:#475569;font-weight:600;margin-top:4px;">
                        محسوبة مباشرة على بايتات ملف الـ PDF النهائي (الحجم: <?php echo number_format($pdf_audit['pdf_size_bytes']); ?> بايت / <?php echo $pdf_size_kb; ?> KB) — يمكنك التحقق منها بأمر <code>sha256sum</code> بعد تحميل الملف أو فحصها حياً في <a href="/audit/<?php echo $b['global_id']; ?>" style="color:#059669;font-weight:800;">مختبر التدقيق</a>.
                    </div>
                </td>
            </tr>
            <tr>
                <th>2️⃣ بصمة سجل البيانات الوصفية<br><span style="font-size:0.78rem;color:#0284C7;">(Metadata Record SHA-256)</span></th>
                <td>
                    <code style="font-family:monospace;font-size:0.84rem;color:#0369A1;word-break:break-all;"><?php echo $b['metadata_sha256']; ?></code>
                    <button class="copy-pill" onclick="navigator.clipboard.writeText('<?php echo $b['metadata_sha256']; ?>');alert('تم نسخ بصمة البيانات الوصفية!');">📋 نسخ</button>
                    <div style="font-size:0.8rem;color:#475569;font-weight:600;margin-top:4px;">
                        محسوبة من سلسلة البيانات التعريفية الموحدة (<code><?php echo htmlspecialchars($b['canonical_seed']); ?></code>) وموحدة في الصفحة الرئيسية وصفحة الكتاب ومختبر التدقيق (شجرة ميركل من 23 مستوى).
                    </div>
                </td>
            </tr>
            <tr>
                <th>الرقم المعياري للفهرسة والباركود البصري<br><span style="font-size:0.78rem;color:#0284C7;">(GS1 EAN-13 / ISBN-13 Vector Barcode)</span></th>
                <td>
                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:6px;">
                        <?php echo render_ean13_svg($b['isbn13'], 220, 84); ?>
                        <div>
                            <code style="font-size:1rem;color:#1E3A8A;font-weight:900;"><?php echo $b['isbn13']; ?></code>
                            <div style="font-size:0.8rem;color:#047857;font-weight:800;margin-top:4px;">✔ باركود فيكتور أصلي خالٍ من الفجوات الضوئية (Crisp-Edge Merged Vector)</div>
                        </div>
                    </div>
                    <div style="font-size:0.8rem;color:#475569;font-weight:600;margin-top:4px;">
                        توضيح النطاق: الرقم يحقق معادلة الفحص الرياضية القياسية (GS1 Modulo-10 Check Digit)، ويُستخدم كمعرّف فهرسة رقمي داخلي في مستودع دار النمر (Internal Digital Catalog ID)؛ ويخضع تخصيص أرقام ISBN التجارية المطبوعة لنطاقات الوكالة الدولية عند النشر الورقي المستقل.
                    </div>
                </td>
            </tr>
            <tr>
                <th>المعرّف الأرشيفي الرقمي (Archival Identifier &amp; OAI-PMH)</th>
                <td>
                    <code><?php echo htmlspecialchars($b['internal_doc_id']); ?></code> | المسار الأرشيفي الداخلي: <a href="/doi/<?php echo $b['doi']; ?>" style="color:#1D4ED8;">/doi/<?php echo $b['doi']; ?></a> | <a href="/cite/<?php echo $b['global_id']; ?>" style="color:#059669;">اقتباس BibTeX / RIS</a>
                    <div style="font-size:0.8rem;color:#475569;font-weight:600;margin-top:4px;">
                        توضيح النطاق: هذا المسار هو معرّف أرشيفي داخلي مستدام على خوادم دار النمر (Internal Sovereign Resolver) مربوط ببروتوكول الفهرسة المفتوحة <a href="/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai:million.elnemer-ai.com:vol/<?php echo $b['global_id']; ?>" style="color:#1D4ED8;">OAI-PMH 2.0</a>، وليس مسجلاً خارجياً لدى وكالة Crossref/doi.org.
                    </div>
                </td>
            </tr>
        </table>
    </section>
</main>
</body>
</html>
