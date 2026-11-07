<?php
if (isset($_GET['download']) && $_GET['download'] === 'pdf') {
    require __DIR__ . '/stream_pdf.php';
    exit;
}
require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/barcode_helper.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$gid = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['book']) ? intval($_GET['book']) : 1);
$gid = max(1, min(5000000, $gid));
$b = get_1m_book_metadata($gid);
$pal = $b['palette'];
$all_langs = get_all_78_langs();

$proto = "https";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "million.elnemer-ai.com";
$base_url = $proto . "://" . $host;
$canonical_url = $base_url . "/book/" . $b['global_id'] . "/" . $b['slug'];
$pdf_stream_url = $base_url . "/pdf/" . $b['global_id'] . "/" . $b['isbn13'] . "_ElNemer_Vol_" . sprintf("%07d", $b['global_id']) . "_200Pages.pdf";
$sovereign_mirror_url = "https://pub-ec0807ba7c30446a840968c856a074b1.r2.dev/elnemer-1m-encyclopedia/books/" . $b['isbn13'] . "_ElNemer_Vol_" . sprintf("%07d", $b['global_id']) . "_200Pages.pdf";

$prev_id = ($gid > 1) ? ($gid - 1) : 5000000;
$next_id = ($gid < 5000000) ? ($gid + 1) : 1;
$prev_b = get_1m_book_metadata($prev_id);
$next_b = get_1m_book_metadata($next_id);

$egp_price = number_format(floatval($b['price_usd']) * 50.0, 2);
$sar_price = number_format(floatval($b['price_usd']) * 3.75, 2);
$eur_price = number_format(floatval($b['price_usd']) * 0.92, 2);

$chapters = [
    ["ch" => 1, "pages" => "09–28", "en" => "Foundational Epistemology, Axiomatic Formulations & Historical Paradigms in " . $b['domain_en'], "ar" => "الأسس الأكسيومية، التأصيل المعرفي والهندسة النظرية في " . $b['domain_ar']],
    ["ch" => 2, "pages" => "29–48", "en" => "Critical Literature Review, High-Dimensional Tensor State Formulations & Boundary Conditions", "ar" => "مراجعة الأدبيات النقدية وصياغات المصفوفات الممتدة والنمذجة التفاضلية"],
    ["ch" => 3, "pages" => "49–74", "en" => "Stochastic Optimization, Lyapunov Second Stability Proofs & Spectral Convergence Bounds", "ar" => "التحسين الاستوكاستيكي، براهين استقرار ليابونوف وحدود التقارب الطيفي"],
    ["ch" => 4, "pages" => "75–100", "en" => "Algorithmic Architecture, Lock-Free Ring Synchronization & High-Performance Computing Kernels", "ar" => "البنية الخوارزمية، المزامنة الحلقية فائقة السرعة ومحركات الحوسبة المتجهة"],
    ["ch" => 5, "pages" => "101–125", "en" => "Empirical Laboratory Benchmarks, Multi-Scale Simulation Protocols & Hardware Telemetry", "ar" => "بروتوكولات المحاكاة متعددة المقاييس والاختبارات المعملية والقياس العتادي"],
    ["ch" => 6, "pages" => "126–150", "en" => "Quantitative Results, Non-Parametric Wilcoxon Statistical Auditing & Sensitivity Testing", "ar" => "النتائج الكمية، التدقيق الإحصائي اللابارامتري واختبارات الحساسية المتقدمة"],
    ["ch" => 7, "pages" => "151–175", "en" => "Scalable Industrial Blueprints, Sovereign Cloud Deployment & Multi-Topology Case Studies", "ar" => "المخططات الهندسية الصناعية، النشر السحابي السيادي ودراسات الحالة العالمية"],
    ["ch" => 8, "pages" => "176–185", "en" => "Ethical Algorithmic Governance, Global Risk Thresholds & Horizon 2030–2050 Problems", "ar" => "الحوكمة الخوارزمية الأخلاقية، عتبات المخاطر والمسائل العلمية المفتوحة"],
    ["ch" => 9, "pages" => "186–196", "en" => "Standardized Post-Graduate Certification Board Examination (22 Questions & Verified Key)", "ar" => "اختبار الاعتماد القياسي لطلاب الدراسات العليا (22 سؤالاً تحليلياً ومفتاح الإجابة)"],
    ["ch" => 10, "pages" => "197–200", "en" => "Exhaustive International Peer-Reviewed Bibliography (45 Citations) & Official Author Colophon", "ar" => "قائمة المراجع الأكاديمية الدولية المحكّمة (45 مرجعاً موثقاً) والتعريف الرسمي بالمؤلف"]
];

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
            "bookEdition" => "V2.5 Zero-Repetition Peer-Reviewed Academic Edition (2026)",
            "bookFormat" => "https://schema.org/EBook",
            "inLanguage" => $b['lang_code'],
            "identifier" => [
                ["@type" => "PropertyValue", "propertyID" => "DOI", "value" => $b['doi']],
                ["@type" => "PropertyValue", "propertyID" => "SHA256", "value" => $b['sha256']],
                ["@type" => "PropertyValue", "propertyID" => "BISAC", "value" => $b['bisac']],
                ["@type" => "PropertyValue", "propertyID" => "GooglePlayBatch", "value" => "BATCH #" . sprintf("%04d", $b['batch_number'])],
                ["@type" => "PropertyValue", "propertyID" => "NemerSovereignEdgeMirror", "value" => $sovereign_mirror_url]
            ],
            "author" => [
                "@type" => "Person",
                "name" => "Eng. Mahmoud Bek ElNemer (" . $b['author'] . ")",
                "jobTitle" => "Google Accredited Engineer & Chief Encyclopedia Architect",
                "url" => "https://elnemer-ai.com"
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Nemer Academic Publishing House & Nemer Digital Solutions",
                "url" => "https://elnemer-ai.com"
            ],
            "datePublished" => "2026-11-01",
            "about" => $b['domain_en'] . " / " . $b['domain_ar'],
            "offers" => [
                "@type" => "Offer",
                "price" => $b['price_usd'],
                "priceCurrency" => "USD",
                "availability" => "https://schema.org/InStock",
                "url" => $canonical_url
            ]
        ],
        [
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                ["@type" => "ListItem", "position" => 1, "name" => "ElNemer 1-Billion-Page (5M Books) Encyclopedia", "item" => $base_url . "/?lang=" . $b['lang_code']],
                ["@type" => "ListItem", "position" => 2, "name" => $b['sector_en'], "item" => $base_url . "/?lang=" . $b['lang_code'] . "&sector=" . $b['sector_id']],
                ["@type" => "ListItem", "position" => 3, "name" => $b['domain_en'], "item" => $base_url . "/?lang=" . $b['lang_code'] . "&discipline=" . $b['discipline_id']],
                ["@type" => "ListItem", "position" => 4, "name" => "Volume #" . $b['global_id'], "item" => $canonical_url]
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($b['lang_code']); ?>" dir="rtl" data-theme="royal-dark">
<head>
  <meta name="google-site-verification" content="U6ysk2Ie2LsEIPZ3UlsIS3gmpl_jwAjOyfu0lWKKBF8" />

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مجلد #<?php echo number_format($b['global_id']); ?> | <?php echo htmlspecialchars($b['title']); ?> | ISBN <?php echo $b['isbn13']; ?> | موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب)</title>
    <meta name="description" content="<?php echo htmlspecialchars($b['title'] . ' — ' . $b['subtitle'] . ' | Volume #' . number_format($b['global_id']) . ' of 5,000,000 (1,000,000,000 Pages) | ISBN-13: ' . $b['isbn13'] . ' | DOI: ' . $b['doi'] . ' | 200 Pages in ' . $b['lang_en_name'] . ' (' . $b['lang_name'] . '). Single-Author Guinness World Record.'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($b['domain_en'] . ', ' . $b['domain_ar'] . ', ' . $b['sector_en'] . ', ' . $b['sector_ar'] . ', ' . $b['lang_name'] . ', ' . $b['lang_en_name'] . ', ISBN ' . $b['isbn13'] . ', المهندس محمود النمر, Eng. Mahmoud ElNemer, Google Play Books, موسوعة جينيس مليار صفحة 5 مليون كتاب'); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">

    <!-- 78-Language Hreflang Alternates -->
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($canonical_url); ?>">
<?php foreach ($all_langs as $idx => $l):
    $sib_id = (($gid - 1 + $idx) % 5000000) + 1;
?>
    <link rel="alternate" hreflang="<?php echo htmlspecialchars($l['code']); ?>" href="<?php echo $base_url; ?>/book/<?php echo $sib_id; ?>">
<?php endforeach; ?>

    <!-- Google Scholar & Academic Meta -->
    <meta name="citation_title" content="<?php echo htmlspecialchars($b['title']); ?>">
    <meta name="citation_author" content="ElNemer, Mahmoud Bek">
    <meta name="citation_author" content="<?php echo htmlspecialchars($b['author']); ?>">
    <meta name="citation_publication_date" content="2026/11/01">
    <meta name="citation_isbn" content="<?php echo $b['isbn13']; ?>">
    <meta name="citation_doi" content="<?php echo $b['doi']; ?>">
    <meta name="citation_language" content="<?php echo htmlspecialchars($b['lang_code']); ?>">
    <meta name="citation_publisher" content="Nemer Academic Publishing House">
    <meta name="citation_keywords" content="<?php echo htmlspecialchars($b['domain_en'] . '; ' . $b['sector_en'] . '; ' . $b['bisac']); ?>">
    <meta name="citation_pdf_url" content="<?php echo htmlspecialchars($pdf_stream_url); ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="book">
    <meta property="og:title" content="<?php echo htmlspecialchars($b['title']); ?> (ISBN: <?php echo $b['isbn13']; ?>)">
    <meta property="og:description" content="<?php echo htmlspecialchars($b['subtitle']); ?> — 200-Page Monograph (#<?php echo number_format($b['global_id']); ?> / 5,000,000 (مليار صفحة))">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <meta property="book:isbn" content="<?php echo $b['isbn13']; ?>">

    <!-- Structured Data JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800;900&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-page: #060913;
            --bg-gradient: radial-gradient(circle at 50% 0%, #111a36 0%, #060913 70%, #020408 100%);
            --card-surface: rgba(15, 23, 42, 0.75);
            --card-sub: rgba(9, 14, 28, 0.85);
            --card-border: rgba(56, 189, 248, 0.22);
            --gold: #F59E0B;
            --gold-glow: #FBBF24;
            --cyan: #00F0FF;
            --sapphire: #3B82F6;
            --emerald: #10B981;
            --text-main: #F8FAFC;
            --text-sub: #94A3B8;
            --text-muted: #64748B;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: var(--bg-page);
            background-image: var(--bg-gradient);
            color: var(--text-main);
            line-height: 1.7;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* TOP COMMAND BAR */
        .topbar {
            background: rgba(8, 12, 24, 0.88);
            backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(245, 158, 11, 0.4);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .topbar-inner {
            max-width: 1440px;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .brand {
            font-weight: 900;
            font-size: 1.05rem;
            color: #FFFFFF;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .brand span.badge {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: #060913;
            font-size: 0.75rem;
            font-weight: 900;
            padding: 2px 8px;
            border-radius: 6px;
        }
        .nav-links { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .nav-btn {
            color: #E2E8F0;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.84rem;
            padding: 6px 14px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-btn:hover {
            background: rgba(245, 158, 11, 0.15);
            border-color: #F59E0B;
            color: #FDE68A;
            transform: translateY(-2px);
        }
        .nav-btn.gold {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: #060913;
            border: none;
            font-weight: 900;
        }

        .container { max-width: 1400px; margin: 24px auto 80px; padding: 0 20px; }

        /* BREADCRUMBS */
        .breadcrumbs {
            font-size: 0.86rem;
            color: var(--text-sub);
            margin-bottom: 20px;
            background: var(--card-surface);
            backdrop-filter: blur(12px);
            padding: 10px 18px;
            border-radius: 12px;
            border: 1px solid var(--card-border);
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .breadcrumbs a { color: var(--cyan); text-decoration: none; font-weight: 700; }
        .breadcrumbs a:hover { color: var(--gold); text-decoration: underline; }

        /* HERO CARD WITH 3D BOOK & METADATA */
        .hero-grid {
            display: grid;
            grid-template-columns: 370px 1fr;
            gap: 32px;
            background: var(--card-surface);
            backdrop-filter: blur(20px);
            border: 2px solid var(--card-border);
            border-radius: 26px;
            padding: 32px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 40px rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
        }
        .hero-grid::before {
            content: '';
            position: absolute;
            top: -150px;
            right: -150px;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12), transparent 70%);
            pointer-events: none;
        }
        @media (max-width: 980px) { .hero-grid { grid-template-columns: 1fr; } }

        /* REALISTIC 3D HARDCOVER BOOK SIMULATOR */
        .book-stage-3d {
            perspective: 1200px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }
        .book-cover-3d {
            width: 310px;
            min-height: 470px;
            background: <?php echo $pal['bg']; ?>;
            border: 3px solid <?php echo $pal['acc']; ?>;
            border-radius: 8px 20px 20px 8px;
            padding: 22px 18px;
            text-align: center;
            color: #FFFFFF;
            position: relative;
            box-shadow: -16px 20px 45px rgba(0,0,0,0.7), inset 5px 0 14px rgba(255,255,255,0.18), inset -4px 0 10px rgba(0,0,0,0.5);
            transform: rotateY(-10deg) rotateX(2deg);
            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .book-cover-3d:hover {
            transform: rotateY(0deg) scale(1.03);
            box-shadow: 0 25px 60px rgba(245, 158, 11, 0.25), -5px 15px 35px rgba(0,0,0,0.8);
        }
        .book-spine-effect {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 16px;
            background: linear-gradient(90deg, rgba(0,0,0,0.65), rgba(255,255,255,0.15) 50%, rgba(0,0,0,0.45));
            border-radius: 8px 0 0 8px;
        }
        .book-ribbon {
            position: absolute;
            top: 0;
            right: 28px;
            width: 24px;
            height: 48px;
            background: linear-gradient(135deg, #F59E0B, #B45309);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 82%, 0 100%);
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
        }
        .book-gold-seal {
            width: 110px;
            height: 110px;
            margin: 12px auto;
            border-radius: 50%;
            border: 3px dashed <?php echo $pal['pri']; ?>;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 1.25rem;
            color: #FFFFFF;
            background: radial-gradient(circle, rgba(255,255,255,0.12), transparent);
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.2);
        }

        /* METADATA & ACTIONS */
        .pills-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .pill {
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .pill.gold { background: rgba(245, 158, 11, 0.15); border-color: var(--gold); color: #FDE68A; }
        .pill.emerald { background: rgba(16, 185, 129, 0.15); border-color: var(--emerald); color: #6EE7B7; }
        .pill.cyan { background: rgba(0, 240, 255, 0.12); border-color: var(--cyan); color: #7DD3FC; }

        h1.book-title {
            font-size: 1.85rem;
            font-weight: 900;
            color: #FFFFFF;
            margin-bottom: 8px;
            line-height: 1.35;
            background: linear-gradient(135deg, #FFFFFF 60%, var(--gold));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        h2.book-subtitle {
            font-size: 1.05rem;
            color: var(--text-sub);
            font-weight: 700;
            margin-bottom: 20px;
        }

        /* ACTION BUTTONS */
        .actions-box {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 22px 0;
            padding: 16px;
            background: rgba(9, 14, 28, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 16px;
        }
        .btn-action {
            padding: 13px 22px;
            border-radius: 12px;
            font-weight: 900;
            font-size: 0.96rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            border: none;
            font-family: inherit;
        }
        .btn-gold-glow {
            background: linear-gradient(135deg, #F59E0B, #D97706);
            color: #060913;
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.4), 0 8px 20px rgba(0, 0, 0, 0.4);
        }
        .btn-gold-glow:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 0 35px rgba(245, 158, 11, 0.65), 0 12px 25px rgba(0, 0, 0, 0.5);
        }
        .btn-blue {
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            color: #FFFFFF;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.3);
        }
        .btn-blue:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.5); }
        .btn-r2 {
            background: rgba(0, 240, 255, 0.12);
            border: 1px solid var(--cyan);
            color: var(--cyan);
        }
        .btn-r2:hover { background: rgba(0, 240, 255, 0.25); color: #FFFFFF; transform: translateY(-2px); }
        .btn-wa {
            background: linear-gradient(135deg, #15803D, #166534);
            color: #FFFFFF;
        }
        .btn-wa:hover { transform: translateY(-2px); background: #16A34A; }

        /* SPECIFICATION TABLE */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            border-radius: 12px;
            overflow: hidden;
            background: rgba(2, 6, 23, 0.5);
            border: 1px solid var(--card-border);
        }
        .meta-table tr { border-bottom: 1px solid rgba(255, 255, 255, 0.07); }
        .meta-table tr:last-child { border-bottom: none; }
        .meta-table th {
            padding: 12px 16px;
            color: var(--text-sub);
            width: 32%;
            font-weight: 800;
            font-size: 0.9rem;
            text-align: right;
            background: rgba(255, 255, 255, 0.02);
        }
        .meta-table td {
            padding: 12px 16px;
            color: var(--text-main);
            font-weight: 700;
            font-size: 0.92rem;
            text-align: right;
        }

        /* COPYABLE BADGE */
        .copy-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 240, 255, 0.08);
            border: 1px solid rgba(0, 240, 255, 0.3);
            color: var(--cyan);
            padding: 4px 10px;
            border-radius: 6px;
            font-family: monospace;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .copy-pill:hover { background: rgba(0, 240, 255, 0.2); }

        /* SECTION BOX */
        .section-box {
            margin-top: 32px;
            background: var(--card-surface);
            backdrop-filter: blur(18px);
            border: 2px solid var(--card-border);
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.4);
        }
        .section-title {
            font-size: 1.35rem;
            font-weight: 900;
            color: var(--gold);
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(245, 158, 11, 0.3);
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 14px;
        }
        .ch-card {
            background: var(--card-sub);
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 16px;
            transition: all 0.2s ease;
        }
        .ch-card:hover {
            border-color: var(--gold);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        /* SIBLING VOLUMES GRID */
        .sibling-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 10px;
        }
        .sibling-card {
            padding: 12px;
            background: var(--card-sub);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            text-decoration: none;
            color: var(--cyan);
            font-weight: 800;
            font-size: 0.86rem;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .sibling-card:hover {
            border-color: var(--gold);
            color: #FDE68A;
            transform: translateY(-2px);
            background: rgba(245, 158, 11, 0.08);
        }

        /* FLOATING ADJUDICATOR BAR */
        .floating-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(6, 9, 19, 0.92);
            backdrop-filter: blur(20px);
            border-top: 2px solid var(--gold);
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 999;
            box-shadow: 0 -10px 30px rgba(0,0,0,0.6);
        }
        .floating-inner {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>
<body>

<header class="topbar">
    <div class="topbar-inner">
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>" class="brand">
            🏛️ <b>موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب)</b>
            <span class="badge">مليار صفحة • 5M كتاب • 78 لغة</span>
        </a>
        <nav class="nav-links">
            <a class="nav-btn" href="/book/<?php echo $prev_id; ?>">⬅️ المجلد السابق (#<?php echo number_format($prev_id); ?>)</a>
            <a class="nav-btn" href="/book/<?php echo $next_id; ?>">➡️ المجلد التالي (#<?php echo number_format($next_id); ?>)</a>
            <a class="nav-btn gold" href="/guinness?id=<?php echo $b['global_id']; ?>">⚖️ تحكيم جينيس</a>
            <a class="nav-btn" href="/guinness_dossier.pdf" target="_blank">📥 ملف التحكيم (PDF)</a>
            <a class="nav-btn gold" href="/book/<?php echo rand(1, 5000000); ?>">🎲 مجلد عشوائي</a>
            <a class="nav-btn" href="/?lang=<?php echo urlencode($b['lang_code']); ?>">🌍 لغة <?php echo htmlspecialchars($b['lang_name']); ?></a>
            <a class="nav-btn" href="/sitemap_index.xml">🗺️ فهرس المليار صفحة</a>
        </nav>
    </div>
</header>

<main class="container">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>">🏛️ موسوعة المليار صفحة الرئيسية</a> &raquo;
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>&sector=<?php echo $b['sector_id']; ?>"><?php echo htmlspecialchars($b['sector_ar']); ?> (<?php echo htmlspecialchars($b['sector_en']); ?>)</a> &raquo;
        <a href="/?lang=<?php echo urlencode($b['lang_code']); ?>&discipline=<?php echo $b['discipline_id']; ?>"><?php echo htmlspecialchars($b['domain_ar']); ?></a> &raquo;
        <span style="color:#FDE68A;">مجلد رقم #<?php echo number_format($b['global_id']); ?> (ISBN: <?php echo $b['isbn13']; ?>)</span>
    </nav>

    <section class="hero-grid">
        <!-- 3D BOOK HARDCOVER SHOWCASE -->
        <div class="book-stage-3d">
            <div class="book-cover-3d">
                <div class="book-spine-effect"></div>
                <div class="book-ribbon"></div>

                <div style="background:rgba(0,0,0,0.65);color:<?php echo $pal['acc']; ?>;border:1px solid <?php echo $pal['acc']; ?>;padding:6px 10px;border-radius:8px;font-size:0.75rem;font-weight:900;letter-spacing:0.5px;">
                    ELNEMER 1B-PAGE ENCYCLOPEDIA • 5M BOOKS
                </div>

                <div class="book-gold-seal">
                    <span style="font-size:0.65rem;color:<?php echo $pal['acc']; ?>;letter-spacing:1px;">VOLUME</span>
                    <span>#<?php echo sprintf("%07d", $b['global_id']); ?></span>
                    <span style="font-size:0.6rem;color:#cbd5e1;">200 PAGES</span>
                </div>

                <div style="background:rgba(9,13,26,0.92);border:2px solid <?php echo $pal['pri']; ?>;border-radius:12px;padding:12px;box-shadow:0 8px 20px rgba(0,0,0,0.5);">
                    <div style="color:<?php echo $pal['acc']; ?>;font-weight:800;font-size:0.82rem;margin-bottom:4px;"><?php echo htmlspecialchars($b['sector_ar']); ?></div>
                    <div style="color:#FFFFFF;font-weight:900;font-size:0.95rem;line-height:1.3;margin-bottom:6px;"><?php echo htmlspecialchars($b['domain_en']); ?></div>
                    <div style="color:#CBD5E1;font-size:0.82rem;margin-bottom:6px;"><?php echo htmlspecialchars($b['domain_ar']); ?></div>
                    <div style="color:<?php echo $pal['pri']; ?>;font-weight:900;font-size:0.85rem;">🌍 <?php echo htmlspecialchars($b['lang_name'] . " (" . $b['lang_en_name'] . ")"); ?></div>
                </div>

                <div style="font-size:0.78rem;color:#FDE68A;font-weight:800;padding-top:8px;">
                    ✍️ <?php echo htmlspecialchars($b['author']); ?><br>
                    <span style="color:#94A3B8;font-family:monospace;font-size:0.76rem;">ISBN: <?php echo $b['isbn13']; ?></span>
                </div>
            </div>
        </div>

        <!-- BOOK METADATA, SUMMARY & ACTIONS -->
        <div>
            <div class="pills-row">
                <span class="pill gold">🏆 مجلد رقم #<?php echo number_format($b['global_id']); ?> من أصل 5,000,000 (مليار صفحة)</span>
                <span class="pill cyan">🌍 اللغة: <?php echo htmlspecialchars($b['lang_name'] . " (" . $b['lang_en_name'] . ")"); ?> [<?php echo $b['lang_code']; ?>]</span>
                <span class="pill <?php echo $b['is_paid'] ? 'gold' : 'emerald'; ?>">
                    <?php echo $b['is_paid'] ? ("💎 إصدار تجاري: $" . $b['price_usd'] . " USD") : "🎁 وقف علمي مفتوح ($0.00)"; ?>
                </span>
                <span class="pill">📦 دفعة Google Play رقم #<?php echo sprintf("%04d", $b['batch_number']); ?></span>
                <span class="pill cyan">⚡ Nemer Sovereign Edge Cloud</span>
            </div>

            <h1 class="book-title"><?php echo htmlspecialchars($b['title']); ?></h1>
            <h2 class="book-subtitle"><?php echo htmlspecialchars($b['subtitle']); ?></h2>

            <!-- JAW-DROPPING ACTION BUTTONS -->
            <div class="actions-box">
                <a class="btn-action" href="/certificate.php?id=<?php echo $b['global_id']; ?>" style="background:linear-gradient(135deg, #10B981, #059669);color:#fff;border:2px solid #6EE7B7;box-shadow:0 0 25px rgba(16,185,129,0.45);font-weight:900;" download>
                    📜 استخراج شهادة توثيق جينيس المعتمدة (Certificate PDF)
                </a>
                <a class="btn-action btn-gold-glow" href="<?php echo $pdf_stream_url; ?>" download>
                    📥 تحميل الكتاب الكامل 200 صفحة (PDF فوري - سرعة فائقة)
                </a>
                <a class="btn-action btn-blue" href="<?php echo $pdf_stream_url; ?>?mode=inline" target="_blank">
                    📖 قراءة ومعاينة الـ 200 صفحة أونلاين
                </a>
                <a class="btn-action btn-r2" href="/stream_pdf.php?id=<?php echo $b['global_id']; ?>&r2_sync=1&mode=r2_url" target="_blank">
                    ☁️ مزامنة السحابة السيادية لدار النمر (Sovereign Edge)
                </a>
                <a class="btn-action btn-wa" href="https://wa.me/201507559911?text=<?php echo urlencode('مرحباً مهندس محمود النمر، أرغب في الاستفسار عن المجلد رقم #' . $b['global_id'] . ' (ISBN: ' . $b['isbn13'] . ') في موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب)'); ?>" target="_blank" rel="noopener">
                    💬 تواصل مباشر مع المؤلف والناشر
                </a>
            </div>

            
            <!-- GUINNESS & GOOGLE ADJUDICATOR QUICK JUMP BAR -->
            <div style="margin:20px 0;padding:16px;background:rgba(15,23,42,0.75);border:1.5px dashed rgba(245,158,11,0.5);border-radius:16px;">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    <div style="font-size:0.88rem;font-weight:900;color:#F59E0B;display:flex;align-items:center;gap:6px;">
                        <span>⚖️</span>
                        <span>قفزات التحكيم السريع لمحكّمي موسوعة جينيس وخبراء Google العالمية (Adjudicator Quick Jump):</span>
                    </div>
                    <span style="font-size:0.75rem;padding:3px 10px;border-radius:6px;background:rgba(245,158,11,0.18);border:1px solid #F59E0B;color:#FDE68A;font-weight:800;">
                        ISO 2859-1 Certified Sampling
                    </span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <a href="/book/<?php echo rand(1, 5000000); ?>" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;">🎲 عينة عشوائية من 5M كتاب</a>
                    <a href="/book/1" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;<?php echo ($b['global_id']==1)?'background:#F59E0B!important;color:#000!important;font-weight:900;':''; ?>">🥇 #1 المجلد الأول</a>
                    <a href="/book/1000000" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;<?php echo ($b['global_id']==1000000)?'background:#F59E0B!important;color:#000!important;font-weight:900;':''; ?>">🎯 #1,000,000 المجلد المليوني</a>
                    <a href="/book/2500000" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;<?php echo ($b['global_id']==2500000)?'background:#F59E0B!important;color:#000!important;font-weight:900;':''; ?>">⚡ #2,500,000 منتصف المليار صفحة</a>
                    <a href="/book/4500000" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;<?php echo ($b['global_id']==4500000)?'background:#F59E0B!important;color:#000!important;font-weight:900;':''; ?>">💎 #4,500,000 نهاية التجاري</a>
                    <a href="/book/4500001" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;<?php echo ($b['global_id']==4500001)?'background:#F59E0B!important;color:#000!important;font-weight:900;':''; ?>">🎁 #4,500,001 بداية الوقف المجاني (100M صفحة)</a>
                    <a href="/book/5000000" class="btn-action" style="padding:8px 16px;border-radius:999px;font-size:0.84rem;background:rgba(245,158,11,0.18);border:1.5px solid #F59E0B;color:#FCD34D;<?php echo ($b['global_id']==5000000)?'background:#F59E0B!important;color:#000!important;font-weight:900;':''; ?>">👑 #5,000,000 المجلد الختامي للمليار صفحة</a>
                </div>
            </div>

            <!-- COMPREHENSIVE SPECIFICATIONS TABLE -->
            
            <!-- SCANNABLE EAN-13 BARCODE CARD -->
            <div style="margin:20px 0;background:rgba(15,23,42,0.85);border:1.5px solid #F59E0B;border-radius:16px;padding:16px;text-align:center;box-shadow:0 8px 25px rgba(0,0,0,0.5);">
                <div style="font-size:0.86rem;font-weight:900;color:#F59E0B;margin-bottom:8px;">
                    📷 باركود دولي مقروء رقمياً بالهاتف وأجهزة التحكيم (GS1 / EAN-13 Scannable Barcode)
                </div>
                <div style="display:inline-block;background:#fff;padding:8px 14px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.4);">
                    <?php echo render_ean13_svg($b['isbn13'], 260, 85); ?>
                </div>
                <div style="font-size:0.75rem;color:#94A3B8;margin-top:8px;">
                    مسجل رسمياً بنظام الترقيم الدولي للكتب • قابل للقراءة الفورية بأي قارئ باركود أو كاميرا هاتف
                </div>
            </div>

            <table class="meta-table">
                <tr>
                    <th>المؤلف الرئيسي (Principal Author)</th>
                    <td>
                        <b>المهندس محمود بك النمر (Eng. Mahmoud Bek ElNemer)</b> — <?php echo htmlspecialchars($b['author']); ?>
                        <span style="color:#10B981;margin-right:6px;">✔ شريك ناشر معتمد في Google Play Books</span>
                    </td>
                </tr>
                <tr>
                    <th>الرقم الدولي المعياري (ISBN-13 / EAN)</th>
                    <td style="font-family:monospace;font-size:1.05rem;color:#FDE68A;">
                        <?php echo $b['isbn13']; ?>
                        <span class="copy-pill" onclick="navigator.clipboard.writeText('<?php echo $b['isbn13']; ?>');alert('تم نسخ رقم ISBN-13!');">📋 نسخ</span>
                    </td>
                </tr>
                <tr>
                    <th>المعرف الرقمي للأبحاث (DOI)</th>
                    <td style="font-family:monospace;">
                        <a href="/doi/<?php echo $b['doi']; ?>" target="_blank" style="color:var(--cyan);text-decoration:none;font-weight:700;">doi:<?php echo $b['doi']; ?></a> <a href="/doi/<?php echo $b['doi']; ?>" target="_blank" style="margin-right:10px;padding:3px 8px;background:rgba(6,182,212,0.15);border:1px solid rgba(6,182,212,0.35);border-radius:6px;font-size:0.8rem;color:var(--cyan);text-decoration:none;font-weight:600;">🔗 بوابة التحقق السيادية (Sovereign Resolver)</a> <a href="/audit/<?php echo $b['global_id']; ?>" target="_blank" style="margin-right:6px;padding:3px 8px;background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);border-radius:6px;font-size:0.8rem;color:#34D399;text-decoration:none;font-weight:700;">🔐 مختبر التدقيق التشفيري</a> <a href="/cite/<?php echo $b['global_id']; ?>" target="_blank" style="margin-right:6px;padding:3px 8px;background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.4);border-radius:6px;font-size:0.8rem;color:#FBBF24;text-decoration:none;font-weight:700;">🎓 BibTeX / RIS / MARC21</a>
                    </td>
                </tr>
                <tr>
                    <th>التصنيف الدولي (BISAC Subject)</th>
                    <td>[<code><?php echo $b['bisac']; ?></code>] <?php echo htmlspecialchars($b['sector_ar'] . " — " . $b['domain_ar'] . " (" . $b['domain_en'] . ")"); ?></td>
                </tr>
                <tr>
                    <th>عدد الصفحات والمواصفات العلمية</th>
                    <td><b>200 صفحة علمية محكّمة</b> (10 فصول + صياغات تفاضلية + رسوم متجهة + اختبار قياسي 22 سؤالاً + 45 مرجعاً دولياً)</td>
                </tr>
                <tr>
                    <th>التسعير الرسمي بـ 4 عملات دولية</th>
                    <td>
                        <span style="color:#F59E0B;font-weight:900;">$<?php echo $b['price_usd']; ?> USD</span> |
                        <span><?php echo $egp_price; ?> EGP</span> |
                        <span><?php echo $sar_price; ?> SAR</span> |
                        <span>€<?php echo $eur_price; ?> EUR</span>
                    </td>
                </tr>
                <tr>
                    <th>البصمة التشفيرية (SHA-256 Merkle Leaf)</th>
                    <td style="font-family:monospace;font-size:0.8rem;word-break:break-all;color:#7DD3FC;">
                        <?php echo $b['sha256']; ?>
                        <span class="copy-pill" onclick="navigator.clipboard.writeText('<?php echo $b['sha256']; ?>');alert('تم نسخ بصمة SHA-256!');">📋 نسخ</span>
                    </td>
                </tr>
                <tr>
                    <th>سيرفر التخزين السحابي السيادي (Nemer Sovereign Edge Cloud)</th>
                    <td style="font-family:monospace;font-size:0.8rem;">
                        <a href="<?php echo $sovereign_mirror_url; ?>" target="_blank" style="color:var(--cyan);text-decoration:none;">
                            ☁️ <?php echo $b['isbn13']; ?>_ElNemer_Vol_<?php echo sprintf('%07d', $b['global_id']); ?>_200Pages.pdf
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </section>

    <!-- 10-CHAPTER ACADEMIC SYLLABUS -->
    <section class="section-box">
        <h2 class="section-title">📑 الفهرس الأكاديمي التفصيلي للفصول العشرة (10 Chapters — 200 Pages Academic Syllabus)</h2>
        <div class="ch-grid">
            <?php foreach ($chapters as $c): ?>
            <div class="ch-card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="background:rgba(245, 158, 11, 0.15);color:#FDE68A;border:1px solid #F59E0B;padding:2px 8px;border-radius:6px;font-weight:900;font-size:0.8rem;">
                        الفصل <?php echo $c['ch']; ?> (Chapter <?php echo $c['ch']; ?>)
                    </span>
                    <span style="color:var(--cyan);font-family:monospace;font-size:0.82rem;font-weight:800;">
                        صفحات <?php echo $c['pages']; ?>
                    </span>
                </div>
                <div style="font-weight:800;color:#FFFFFF;font-size:0.95rem;margin-bottom:6px;"><?php echo htmlspecialchars($c['ar']); ?></div>
                <div dir="ltr" style="font-size:0.84rem;color:var(--text-sub);text-align:left;line-height:1.4;"><?php echo htmlspecialchars($c['en']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SIBLING VOLUMES ACROSS 78 LANGUAGES -->
    <section class="section-box">
        <h2 class="section-title">🌍 النسخ المترجمة والمجلدات الشقيقة بـ 78 لغة (Multilingual Sibling Volumes)</h2>
        <p style="color:var(--text-sub);margin-bottom:14px;font-size:0.92rem;">
            يمكن لمحكمي موسوعة جينيس وخبراء شركة Google الانتقال الفوري لأي مجلد شقيق بذات التخصص العلمي عبر الـ 78 لغة عالمية:
        </p>
        <div class="sibling-grid">
            <?php
            for ($i = 0; $i < 16; $i++) {
                $l = $all_langs[$i];
                $sib_id = (($gid - 1 + ($i * 100)) % 5000000) + 1;
                $sib_b = get_1m_book_metadata($sib_id);
                echo '<a href="/book/' . $sib_b['global_id'] . '/' . $sib_b['slug'] . '" class="sibling-card">';
                echo '<div>🌍 <b>[' . htmlspecialchars($sib_b['lang_code']) . '] ' . htmlspecialchars($sib_b['lang_name']) . '</b></div>';
                echo '<div style="color:var(--text-sub);font-size:0.78rem;">مجلد #' . number_format($sib_b['global_id']) . ' • ' . $sib_b['is_paid_txt'] = ($sib_b['is_paid'] ? '$'.$sib_b['price_usd'] : 'مجاني') . '</div>';
                echo '</a>';
            }
            ?>
        </div>
    </section>
</main>

<!-- FLOATING ADJUDICATOR QUICK BAR AT BOTTOM -->
<div class="floating-bar">
    <div class="floating-inner">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-weight:900;color:#FDE68A;font-size:0.95rem;">
                مجلد #<?php echo number_format($b['global_id']); ?> / 5,000,000 (مليار صفحة)
            </span>
            <span style="color:var(--text-sub);font-size:0.85rem;display:none;md:inline;">
                ISBN: <?php echo $b['isbn13']; ?>
            </span>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a class="nav-btn gold" href="<?php echo $pdf_stream_url; ?>" download>
                📥 تحميل 200 صفحة PDF
            </a>
            <a class="nav-btn" href="<?php echo $pdf_stream_url; ?>?mode=inline" target="_blank">
                📖 معاينة فورية
            </a>
            <a class="nav-btn" href="/book/<?php echo rand(1, 5000000); ?>">
                🎲 كتاب عشوائي
            </a>
        </div>
    </div>
</div>

</body>
</html>
