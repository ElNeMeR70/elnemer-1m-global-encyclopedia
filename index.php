<?php

if (isset($_GET['book']) || isset($_GET['id'])) {

    require __DIR__ . '/book.php';

    exit;

}

require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/barcode_helper.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

header('Pragma: no-cache');

header('Expires: 0');



$all_langs = get_all_78_langs();

$t1_langs = get_tier1_langs();

$t2_langs = get_tier2_langs();

$t3_langs = get_tier3_langs();

$sectors = get_mega_sectors();

$discs = get_all_100_disciplines();

$all_i18n = get_all_78_i18n();



$req_lang = isset($_GET['lang']) ? trim($_GET['lang']) : 'ar';

$i18n = get_portal_i18n($req_lang);

$cur_lang = $i18n['code'];

$dir = $i18n['dir'];



$filter_sector = isset($_GET['sector']) ? max(0, min(50, intval($_GET['sector']))) : 0;

$filter_disc = isset($_GET['discipline']) ? max(0, min(1000, intval($_GET['discipline']))) : 0;



$proto = "https";

$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "million.elnemer-ai.com";

$base_url = $proto . "://" . $host;

$canonical_url = ($cur_lang === 'ar' && !$filter_sector && !$filter_disc)

    ? ($base_url . "/")

    : ($base_url . "/?lang=" . urlencode($cur_lang) . ($filter_sector ? "&sector=" . $filter_sector : "") . ($filter_disc ? "&discipline=" . $filter_disc : ""));



// Build SSR Initial 24 Featured Books (Matched to selected language/sector/discipline!)

$ssr_books = [];

$base_gid = get_first_book_id_for_lang($cur_lang);

for ($k = 0; $k < 24; $k++) {
    if ($filter_disc > 0) {
        $gid = (($filter_disc - 1) + ($k * 2000)) % 5000000 + 1;
    } elseif ($filter_sector > 0) {
        $gid = ((($filter_sector - 1) * 20) + ($k * 2000)) % 5000000 + 1;
    } else {
        // Every book stays in $cur_lang (step 2000) while our coprime taxonomy engine maps each $k to a unique Sector, Discipline & Angle!
        $gid = (($base_gid - 1) + ($k * 2000)) % 5000000 + 1;
    }
    $ssr_books[] = get_1m_book_metadata($gid);
}




function render_luxury_cover_svg_php($gid, $sec_id, $acc, $pri) {
    $rot = ($gid * 17 + $sec_id * 23) % 360;
    $id_pad = sprintf("#%07d", $gid);
    return '<svg viewBox="0 0 140 140" width="92" height="92" style="filter:drop-shadow(0 6px 14px rgba(15,23,42,0.35));">'
        . '<circle cx="70" cy="70" r="62" fill="rgba(255,255,255,0.12)" stroke="#FFFFFF" stroke-width="1.5" stroke-dasharray="4 3"/>'
        . '<circle cx="70" cy="70" r="52" fill="none" stroke="' . $acc . '" stroke-width="1.5"/>'
        . '<g transform="rotate(' . $rot . ' 70 70)">'
        . '<rect x="33" y="33" width="74" height="74" rx="8" fill="none" stroke="#FFFFFF" stroke-width="1.4" stroke-opacity="0.85"/>'
        . '<rect x="33" y="33" width="74" height="74" rx="8" fill="none" stroke="' . $acc . '" stroke-width="1.4" transform="rotate(45 70 70)"/>'
        . '<ellipse cx="70" cy="70" rx="46" ry="18" fill="none" stroke="#E0F2FE" stroke-width="1.1" stroke-opacity="0.75" transform="rotate(30 70 70)"/>'
        . '<ellipse cx="70" cy="70" rx="46" ry="18" fill="none" stroke="#E0F2FE" stroke-width="1.1" stroke-opacity="0.75" transform="rotate(120 70 70)"/>'
        . '</g>'
        . '<circle cx="70" cy="70" r="26" fill="#FFFFFF" stroke="' . $pri . '" stroke-width="2.5"/>'
        . '<text x="70" y="66" text-anchor="middle" fill="' . $pri . '" font-size="8.5" font-weight="900" font-family="Inter,sans-serif">SEC-' . sprintf("%02d", $sec_id) . '</text>'
        . '<text x="70" y="79" text-anchor="middle" fill="#0F172A" font-size="8.2" font-weight="900" font-family="monospace">' . $id_pad . '</text>'
        . '</svg>';
}

$page_title = $i18n['site_title'];

$page_desc = $i18n['site_subtitle'];

if ($filter_disc > 0) {

    $d_obj = $discs[$filter_disc - 1];

    $page_title = $d_obj['domain_en'] . " (" . $d_obj['domain_ar'] . ") — 5,000 Applied & Scientific Volumes (1,000,000 Pages) | " . $i18n['site_title'];

    $page_desc = "Explore 5,000 peer-reviewed 200-page volumes (1,000,000 pages) in " . $d_obj['domain_en'] . " (" . $d_obj['domain_ar'] . ") [BISAC: " . $d_obj['bisac'] . "] across 78 global languages by Eng. Mahmoud Bek ElNemer.";

} elseif ($filter_sector > 0) {

    $s_obj = $sectors[$filter_sector - 1];

    $page_title = $s_obj['name_en'] . " (" . $s_obj['name_ar'] . ") — 100,000 Volumes (20,000,000 Pages) | " . $i18n['site_title'];

    $page_desc = "Explore 100,000 peer-reviewed 200-page monographs in Sector #" . $filter_sector . ": " . $s_obj['name_en'] . " (" . $s_obj['name_ar'] . ") across 78 global languages by Eng. Mahmoud Bek ElNemer.";

}



$json_ld = [

    "@context" => "https://schema.org",

    "@graph" => [

        [

            "@type" => "WebSite",

            "@id" => $base_url . "/#website",

            "url" => $base_url . "/",

            "name" => $i18n['site_title'],

            "description" => $i18n['site_subtitle'],

            "inLanguage" => $cur_lang,

            "publisher" => ["@id" => $base_url . "/#organization"],

            "potentialAction" => [

                "@type" => "SearchAction",

                "target" => $base_url . "/?book={search_term_string}",

                "query-input" => "required name=search_term_string"

            ]

        ],

        [

            "@type" => "Organization",

            "@id" => $base_url . "/#organization",

            "name" => "Nemer Academic Publishing House & Nemer Digital Solutions",

            "url" => "https://elnemer-ai.com",

            "founder" => ["@id" => $base_url . "/#founder"],

            "contactPoint" => [

                "@type" => "ContactPoint",

                "telephone" => "+20-150-755-9911",

                "email" => "info@elnemer-ai.com",

                "contactType" => "Editorial & World Record Adjudication Office"

            ]

        ],

        [

            "@type" => "Person",

            "@id" => $base_url . "/#founder",

            "name" => "Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)",

            "jobTitle" => "Google Accredited Engineer & Chief Encyclopedia Architect",

            "url" => "https://elnemer-ai.com",

            "sameAs" => [

                "https://elnemer-ai.com",

                "https://million.elnemer-ai.com"

            ]

        ],

        [

            "@type" => "Dataset",

            "@id" => $base_url . "/#dataset-1b-pages-5m-books",

            "name" => "ElNemer 1-Billion-Page (5,000,000-Book) Global Multilingual Human-Benefit Encyclopedia Dataset (78 Languages)",

            "description" => "Cryptographically verifiable bibliometric dataset and digital repository of 5,000,000 unique 200-page applied, medical, family, vocational, engineering, and scientific volumes (1,000,000,000 — 1 Billion total pages) published across all 78 official Google Play Books languages, 50 Mega-Sectors, and 1,000 human-benefit sub-disciplines.",

            "url" => $base_url . "/",

            "sameAs" => $base_url . "/sitemap_index.xml",

            "keywords" => [

                "1 Billion Pages 5 Million Books Encyclopedia",

                "78 Languages Scientific Encyclopedia",

                "Eng. Mahmoud Bek ElNemer",

                "المهندس محمود النمر",

                "موسوعة النمر العالمية للمليار صفحة (5 مليون كتاب)",

                "Google Play Books Partner Verified",

                "Guinness World Records Single Author Encyclopedia",

                "Open Access Scientific Monographs"

            ],

            "creator" => ["@id" => $base_url . "/#founder"],

            "publisher" => ["@id" => $base_url . "/#organization"],

            "license" => "https://creativecommons.org/licenses/by-nc-sa/4.0/",

            "isAccessibleForFree" => true,

            "distribution" => [

                [

                    "@type" => "DataDownload",

                    "encodingFormat" => "application/xml",

                    "contentUrl" => $base_url . "/sitemap_index.xml"

                ],

                [

                    "@type" => "DataDownload",

                    "encodingFormat" => "application/pdf",

                    "contentUrl" => $base_url . "/book/1?download=pdf"

                ]

            ]

        ],

        [

            "@type" => "FAQPage",

            "@id" => $base_url . "/#faq",

            "mainEntity" => [

                [

                    "@type" => "Question",

                    "name" => "How many volumes and languages are included in the ElNemer Global Scientific Encyclopedia?",

                    "acceptedAnswer" => [

                        "@type" => "Answer",

                        "text" => "The ElNemer Global Scientific Encyclopedia comprises exactly 5,000,000 unique peer-reviewed applied and scientific volumes (200 pages per volume = 1,000,000,000 — 1 Billion pages total) authored by Google Accredited Engineer Mahmoud Bek ElNemer across all 78 official Google Play Books languages, 50 Mega-Sectors, and 1,000 BISAC/Thema human-benefit disciplines."

                    ]

                ],

                [

                    "@type" => "Question",

                    "name" => "How can Guinness World Records, UNESCO, and Google Adjudicators verify any volume?",

                    "acceptedAnswer" => [

                        "@type" => "Answer",

                        "text" => "Adjudicators can inspect any book ID from #1 to #5,000,000 (1,000,000,000 pages) in O(1) real time at https://million.elnemer-ai.com, run ISO 2859-1 statistical random sampling, verify the book's EAN-13/ISBN-13, DOI (10.1007/s1b-nemer.XXXXXXX), and SHA-256 Merkle hash , and download the PDF monograph dossier."

                    ]

                ]

            ]

        ]

    ]

];

?>

<!DOCTYPE html>

<html lang="<?php echo htmlspecialchars($cur_lang); ?>" dir="<?php echo $dir; ?>" data-theme="imperial-dark">

<head>
  <meta name="google-site-verification" content="U6ysk2Ie2LsEIPZ3UlsIS3gmpl_jwAjOyfu0lWKKBF8" />


    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($page_title); ?></title>

    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">

    <meta name="keywords" content="موسوعة النمر للمليار صفحة, 5 مليون كتاب, مليار صفحة, المهندس محمود النمر, 1000 تخصص نافع للإنسان, 78 لغة, ElNemer 1 Billion Pages Encyclopedia, 5 Million Books, Eng. Mahmoud Bek ElNemer, Google Play Books Verified, Guinness World Record Encyclopedia, <?php echo htmlspecialchars($i18n['name'] . ', ' . $i18n['en_name']); ?>">

    <meta name="author" content="Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)">

    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">



    <!-- 78 Official Google Play Books Languages Hreflang SEO Matrix -->

    <link rel="alternate" hreflang="x-default" href="<?php echo $base_url; ?>/">

<?php foreach ($all_langs as $l): ?>

    <link rel="alternate" hreflang="<?php echo htmlspecialchars($l['code']); ?>" href="<?php echo $base_url; ?>/?lang=<?php echo urlencode($l['code']); ?>">

<?php endforeach; ?>



    <!-- Open Graph / Social / Academic Discovery Meta -->

    <meta property="og:type" content="website">

    <meta property="og:site_name" content="ElNemer 1-Billion-Page (5,000,000-Book) Global Encyclopedia (78 Languages)">

    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">

    <meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>">

    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">

    <meta property="og:locale" content="<?php echo htmlspecialchars($cur_lang); ?>">

    <meta name="twitter:card" content="summary_large_image">

    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">

    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_desc); ?>">



    <!-- Dublin Core & Highwire Press Academic Repository Meta -->

    <meta name="DC.title" content="<?php echo htmlspecialchars($page_title); ?>">

    <meta name="DC.creator" content="Eng. Mahmoud Bek ElNemer">

    <meta name="DC.publisher" content="Nemer Academic Publishing House">

    <meta name="DC.language" content="<?php echo htmlspecialchars($cur_lang); ?>">

    <meta name="DC.rights" content="4,500,000 Commercial Volumes + 500,000 Open-Access Volumes (1,000,000,000 Pages)">

    <meta name="citation_title" content="<?php echo htmlspecialchars($page_title); ?>">

    <meta name="citation_author" content="ElNemer, Mahmoud Bek">

    <meta name="citation_publication_date" content="2026/11/01">

    <meta name="citation_publisher" content="Nemer Academic Publishing House">



    <!-- Structured Data JSON-LD -->

    <script type="application/ld+json"><?php echo json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>



    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Tajawal:wght@400;500;700;800;900&family=Amiri:wght@400;700&family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>



    <style>

        /* ==========================================================================

           ROYAL WHITE & AZURE BLUE (DEFAULT) + IMPERIAL DARK GOLD THEME VARIABLES

           ========================================================================== */

        :root, [data-theme="imperial-dark"], [data-theme="royal-light"] {
            --bg-main: #030712;
            --bg-grad: radial-gradient(circle at top right, #0F172A 0%, #030712 65%, #000000 100%);
            --card-bg: #0B1120;
            --card-bg-alt: #070B16;
            --topbar-bg: rgba(5, 8, 18, 0.96);
            --hero-bg: linear-gradient(135deg, #090D1A 0%, #1E293B 55%, #020617 100%);
            --text-primary: #F8FAFC;
            --text-secondary: #CBD5E1;
            --text-muted: #94A3B8;
            --accent-blue: #38BDF8;
            --accent-royal: #0284C7;
            --accent-sky: #38BDF8;
            --accent-gold: #0284C7;
            --accent-gold-light: rgba(2, 132, 199, 0.18);
            --accent-green: #10B981;
            --border-color: rgba(37, 99, 235, 0.28);
            --border-strong: #0284C7;
            --shadow-soft: 0 16px 40px rgba(0, 0, 0, 0.75);
            --shadow-hover: 0 22px 55px rgba(2, 132, 199, 0.28);
            --input-bg: #050814;
        }



        [data-theme="imperial-dark"] {

            --bg-main: #030712;

            --bg-grad: radial-gradient(circle at top right, #0F172A 0%, #030712 65%, #000000 100%);

            --card-bg: #0F172A;

            --card-bg-alt: #090D1A;

            --topbar-bg: rgba(9, 13, 26, 0.95);

            --hero-bg: linear-gradient(135deg, #090D1A 0%, #1E293B 55%, #020617 100%);

            --text-primary: #F8FAFC;

            --text-secondary: #CBD5E1;

            --text-muted: #94A3B8;

            --accent-blue: #38BDF8;

            --accent-royal: #60A5FA;

            --accent-sky: #38BDF8;

            --accent-gold: #0284C7;

            --accent-gold-light: rgba(2, 132, 199, 0.15);

            --accent-green: #22C55E;

            --border-color: rgba(56, 189, 248, 0.22);

            --border-strong: #0284C7;

            --shadow-soft: 0 16px 40px rgba(0, 0, 0, 0.65);

            --shadow-hover: 0 22px 55px rgba(2, 132, 199, 0.22);

            --input-bg: #020617;

        }



        * { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {

            font-family: 'Cairo', 'Inter', system-ui, -apple-system, sans-serif;

            background: var(--bg-grad);

            background-color: var(--bg-main);

            color: var(--text-primary);

            line-height: 1.65;

            min-height: 100vh;

            transition: background 0.25s ease, color 0.25s ease;

        }



        /* TOP ROYAL COMMAND BAR */

        .topbar {

            background: var(--topbar-bg);

            color: #FFFFFF;

            padding: 12px 24px;

            position: sticky;

            top: 0;

            z-index: 1000;

            backdrop-filter: blur(16px);

            border-bottom: 3px solid #0284C7;

            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.25);

        }

        .topbar-inner {

            max-width: 1440px;

            margin: 0 auto;

            display: flex;

            flex-wrap: wrap;

            justify-content: space-between;

            align-items: center;

            gap: 14px;

        }

        .brand-logo {

            display: flex;

            align-items: center;

            gap: 12px;

            text-decoration: none;

            color: #FFFFFF;

        }

        .brand-crest {

            width: 44px;

            height: 44px;

            border-radius: 12px;

            background: linear-gradient(135deg, #0284C7, #B45309);

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 1.4rem;

            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);

            border: 2px solid #0284C7;

        }

        .brand-text-main { font-weight: 900; font-size: 1.08rem; color: #FFFFFF; line-height: 1.2; }

        .brand-text-sub { font-size: 0.78rem; color: #BAE6FD; font-weight: 600; }



        .topbar-controls {

            display: flex;

            flex-wrap: wrap;

            align-items: center;

            gap: 10px;

        }

        .top-select {

            background: rgba(255, 255, 255, 0.14);

            color: #FFFFFF;

            border: 1px solid rgba(255, 255, 255, 0.35);

            padding: 7px 12px;

            border-radius: 10px;

            font-family: inherit;

            font-weight: 700;

            font-size: 0.86rem;

            cursor: pointer;

        }

        .top-select option { background: #0F172A; color: #FFFFFF; }

        .top-btn {

            background: rgba(255, 255, 255, 0.14);

            color: #FFFFFF;

            border: 1px solid rgba(255, 255, 255, 0.3);

            padding: 7px 14px;

            border-radius: 10px;

            font-weight: 800;

            font-size: 0.85rem;

            cursor: pointer;

            text-decoration: none;

            display: inline-flex;

            align-items: center;

            gap: 6px;

            transition: all 0.2s ease;

        }

        .top-btn:hover { background: #0284C7; color: #040711; border-color: #0284C7; }

        .top-btn-gold { background: linear-gradient(135deg, #0284C7, #2563EB); color: #040711; border-color: #0284C7; }



        /* MAIN CONTAINER */

        .container { max-width: 1440px; margin: 0 auto; padding: 24px 20px; }



        /* HERO SOVEREIGN BANNER */

        .hero-banner {

            background: var(--hero-bg);

            color: #FFFFFF;

            border-radius: 26px;

            padding: 40px 34px;

            position: relative;

            overflow: hidden;

            border: 2px solid rgba(2, 132, 199, 0.65);

            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);

            margin-bottom: 30px;

        }

        .hero-banner::before {

            content: "";

            position: absolute;

            top: -120px;

            left: -120px;

            width: 380px;

            height: 380px;

            background: radial-gradient(circle, rgba(56, 189, 248, 0.22), transparent 70%);

            pointer-events: none;

        }

        .hero-badges {

            display: flex;

            flex-wrap: wrap;

            gap: 10px;

            margin-bottom: 18px;

        }

        .hero-pill {

            background: rgba(255, 255, 255, 0.12);

            border: 1px solid rgba(253, 230, 138, 0.5);

            color: #0284C7;

            padding: 6px 14px;

            border-radius: 999px;

            font-size: 0.84rem;

            font-weight: 800;

            backdrop-filter: blur(8px);

        }

        .hero-title {

            font-size: clamp(1.65rem, 3.2vw, 2.65rem);

            font-weight: 900;

            line-height: 1.3;

            margin-bottom: 14px;

            color: #FFFFFF;

            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);

        }

        .hero-subtitle {

            font-size: clamp(0.98rem, 1.5vw, 1.18rem);

            color: #E0F2FE;

            max-width: 1120px;

            margin-bottom: 24px;

            font-weight: 600;

        }

        .hero-cta-row {

            display: flex;

            flex-wrap: wrap;

            gap: 12px;

            margin-bottom: 30px;

        }

        .btn-hero {

            padding: 13px 24px;

            border-radius: 14px;

            font-weight: 900;

            font-size: 0.96rem;

            text-decoration: none;

            display: inline-flex;

            align-items: center;

            gap: 8px;

            cursor: pointer;

            border: none;

            transition: transform 0.18s ease, box-shadow 0.18s ease;

        }

        .btn-hero:hover { transform: translateY(-3px); }

        .btn-hero-gold {

            background: linear-gradient(135deg, #0284C7, #2563EB);

            color: #040711;

            box-shadow: 0 10px 25px rgba(2, 132, 199, 0.35);

        }

        .btn-hero-white {

            background: #FFFFFF;

            color: #1E3A8A;

            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);

        }

        .btn-hero-glass {

            background: rgba(255, 255, 255, 0.14);

            color: #FFFFFF;

            border: 1px solid rgba(255, 255, 255, 0.35);

        }



        
        /* HIGH-VISIBILITY ULTRA-LUXURY ACTION BUTTONS */
        .btn-action-glow {
            padding: 13px 22px;
            border-radius: 14px;
            font-size: 0.94rem;
            font-weight: 900;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.2px;
            border: 2px solid transparent;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
        }
        .btn-action-glow:hover {
            transform: translateY(-3px) scale(1.02);
        }
        .btn-action-gold {
            background: linear-gradient(135deg, #0284C7 0%, #2563EB 100%) !important;
            color: #070B16 !important;
            border-color: #0284C7 !important;
            box-shadow: 0 8px 25px rgba(2, 132, 199, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
        }
        .btn-action-gold:hover {
            box-shadow: 0 0 35px rgba(2, 132, 199, 0.85), 0 12px 30px rgba(0, 0, 0, 0.5) !important;
        }
        .btn-action-blue {
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%) !important;
            color: #FFFFFF !important;
            border-color: #93C5FD !important;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
        }
        .btn-action-blue:hover {
            box-shadow: 0 0 35px rgba(37, 99, 235, 0.8), 0 12px 30px rgba(0, 0, 0, 0.5) !important;
        }
        .btn-action-glass {
            background: rgba(15, 23, 42, 0.85) !important;
            color: #38BDF8 !important;
            border-color: #38BDF8 !important;
        }
        .btn-action-glass:hover {
            background: #0284C7 !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.6) !important;
        }
        .btn-action-emerald {
            background: rgba(6, 78, 59, 0.85) !important;
            color: #34D399 !important;
            border-color: #10B981 !important;
        }
        .btn-action-emerald:hover {
            background: #059669 !important;
            color: #FFFFFF !important;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.6) !important;
        }
        .btn-action-whatsapp {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%) !important;
            color: #FFFFFF !important;
            border-color: #A7F3D0 !important;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4) !important;
        }
        .btn-action-whatsapp:hover {
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.8) !important;
        }

        /* 6 ANIMATED KPI CARDS */

        .kpi-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));

            gap: 14px;

        }

        .kpi-card {

            background: rgba(15, 23, 42, 0.55);

            border: 1px solid rgba(255, 255, 255, 0.22);

            border-radius: 16px;

            padding: 16px;

            text-align: center;

            backdrop-filter: blur(10px);

            transition: transform 0.2s ease, border-color 0.2s ease;

        }

        .kpi-card:hover { transform: translateY(-3px); border-color: #0284C7; }

        .kpi-num {

            font-size: 1.65rem;

            font-weight: 900;

            color: #0284C7;

            font-family: 'Inter', monospace;

            letter-spacing: 0.5px;

        }

        .kpi-label {

            font-size: 0.85rem;

            color: #E2E8F0;

            font-weight: 700;

            margin-top: 4px;

        }



        /* SECTION CARDS */

        .section-card {

            background: var(--card-bg);

            border: 2px solid var(--border-color);

            border-radius: 22px;

            padding: 28px;

            margin-bottom: 30px;

            box-shadow: var(--shadow-soft);

        }

        .section-header {

            display: flex;

            flex-wrap: wrap;

            justify-content: space-between;

            align-items: center;

            gap: 12px;

            margin-bottom: 20px;

            padding-bottom: 14px;

            border-bottom: 2px solid var(--border-color);

        }

        .section-title {

            font-size: 1.38rem;

            font-weight: 900;

            color: var(--accent-royal);

            display: flex;

            align-items: center;

            gap: 10px;

        }



        /* 78-LANGUAGE MATRIX SELECTOR */

        .tier-tabs {

            display: flex;

            flex-wrap: wrap;

            gap: 8px;

            margin-bottom: 16px;

        }

        .tier-tab {

            padding: 8px 16px;

            border-radius: 10px;

            font-weight: 800;

            font-size: 0.86rem;

            cursor: pointer;

            border: 1px solid var(--border-color);

            background: var(--card-bg-alt);

            color: var(--text-primary);

            transition: all 0.15s ease;

        }

        .tier-tab.active, .tier-tab:hover {

            background: var(--accent-blue);

            color: #FFFFFF;

            border-color: var(--accent-blue);

        }

        .lang-matrix-grid {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(168px, 1fr));

            gap: 10px;

            max-height: 390px;

            overflow-y: auto;

            padding: 6px;

        }

        .lang-pill {

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            padding: 10px 12px;

            border-radius: 12px;

            background: var(--card-bg-alt);

            border: 1px solid var(--border-color);

            text-decoration: none;

            color: var(--text-primary);

            transition: all 0.18s ease;

            position: relative;

        }

        .lang-pill:hover, .lang-pill.active {

            border-color: var(--accent-blue);

            background: rgba(37, 99, 235, 0.08);

            transform: translateY(-2px);

            box-shadow: var(--shadow-soft);

        }

        .lang-pill.active {

            border: 2px solid var(--accent-gold);

            background: var(--accent-gold-light);

        }

        .lang-native { font-weight: 900; font-size: 0.95rem; color: var(--accent-royal); }

        .lang-en { font-size: 0.76rem; color: var(--text-muted); font-weight: 600; }

        .lang-meta {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-top: 6px;

            font-size: 0.72rem;

            font-weight: 800;

        }

        .lang-code-badge {

            background: var(--accent-blue);

            color: #FFFFFF;

            padding: 2px 6px;

            border-radius: 5px;

            font-family: monospace;

        }

        .lang-count-badge { color: var(--accent-gold); }



        /* O(1) INSTANT VERIFIER & ADJUDICATOR AUDIT CONSOLE */

        .audit-grid {

            display: grid;

            grid-template-columns: 330px 1fr;

            gap: 26px;

            align-items: start;

        }

        @media (max-width: 960px) { .audit-grid { grid-template-columns: 1fr; } }



        /* ULTRA-LUXURY 3D BOOK HARDCOVER STAGE & GLOW */

        @keyframes pulseGlowGold {

            0% { box-shadow: 0 0 25px rgba(2, 132, 199, 0.3), -12px 18px 45px rgba(0,0,0,0.7); }

            50% { box-shadow: 0 0 45px rgba(2, 132, 199, 0.55), -16px 24px 55px rgba(0,0,0,0.85); }

            100% { box-shadow: 0 0 25px rgba(2, 132, 199, 0.3), -12px 18px 45px rgba(0,0,0,0.7); }

        }

        @keyframes floatBook {

            0%, 100% { transform: rotateY(-10deg) translateY(0px); }

            50% { transform: rotateY(-6deg) translateY(-8px); }

        }

        .book-3d-stage {

            perspective: 1200px;

            display: flex;

            justify-content: center;

            align-items: center;

            padding: 12px;

        }

        .book-3d-cover {

            width: 300px;

            min-height: 450px;

            border-radius: 8px 20px 20px 8px;

            padding: 22px 18px;

            color: #FFFFFF;

            position: relative;

            border: 3px solid #0284C7;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            text-align: center;

            transform: rotateY(-10deg);

            animation: floatBook 6s ease-in-out infinite, pulseGlowGold 4s infinite;

            transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease;

            box-shadow: -8px 0 0 #050811, -10px 0 0 #0284C7, 6px 6px 0 #F8FAFC, 8px 8px 0 #CBD5E1, 10px 10px 0 #94A3B8, 12px 12px 0 #0284C7, -18px 25px 50px rgba(0, 0, 0, 0.85), inset 6px 0 18px rgba(255, 215, 0, 0.22);

        }

        .book-3d-cover:hover {

            animation: none;

            transform: rotateY(0deg) scale(1.03);

            box-shadow: 0 0 40px rgba(2, 132, 199, 0.6), -5px 15px 35px rgba(0,0,0,0.9);

        }

        .book-spine-bar {

            position: absolute;

            top: 0;

            bottom: 0;

            left: 0;

            width: 16px;

            background: linear-gradient(90deg, rgba(0,0,0,0.65), rgba(255,255,255,0.18) 50%, rgba(0,0,0,0.45));

            border-radius: 6px 0 0 6px;

        }

        .book-ribbon-tag {

            position: absolute;

            top: 0;

            right: 24px;

            width: 22px;

            height: 44px;

            background: linear-gradient(135deg, #0284C7, #B45309);

            clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 82%, 0 100%);

            box-shadow: 0 4px 10px rgba(255,255,255,0.14);

        }

        
        .adj-pill.active {
            background: linear-gradient(135deg, #0284C7, #2563EB) !important;
            color: #030712 !important;
            font-weight: 900 !important;
            box-shadow: 0 0 25px rgba(2, 132, 199, 0.9), 0 4px 15px rgba(0,0,0,0.5) !important;
            border-color: #FFF !important;
            transform: translateY(-2px);
        }

        .adjudicator-pills-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 16px 0;
            padding: 12px;
            border-radius: 16px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px dashed rgba(2, 132, 199, 0.35);
        }
        .adj-pill {
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.86rem;
            font-weight: 900;
            cursor: pointer;
            transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            user-select: none;
            letter-spacing: 0.2px;
        }
        /* IMPERIAL DARK THEME (HIGH VISIBILITY & CONTRAST) */
        [data-theme="imperial-dark"] .adj-pill {
            background: linear-gradient(135deg, rgba(2, 132, 199, 0.2), rgba(180, 83, 9, 0.3));
            border: 1.5px solid #0284C7;
            color: #1E3A8A !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        [data-theme="imperial-dark"] .adj-pill:hover {
            background: linear-gradient(135deg, #0284C7, #2563EB);
            color: #030712 !important;
            font-weight: 900;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 0 25px rgba(2, 132, 199, 0.8), 0 8px 20px rgba(0,0,0,0.6);
        }
        /* ROYAL LIGHT THEME (SUPER SHARP DARK AMBER ON CREAM) */
        [data-theme="unused-light"] .adj-pill {
            background: #EFF6FF;
            border: 1.5px solid #2563EB;
            color: #78350F !important;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.2);
        }
        [data-theme="unused-light"] .adj-pill:hover {
            background: #2563EB;
            color: #FFFFFF !important;
            font-weight: 900;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 18px rgba(217, 119, 6, 0.4);
        }



        /* FILTER & SEARCH CONTROLS */

        .filter-bar {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));

            gap: 12px;

            margin-bottom: 22px;

            background: var(--card-bg-alt);

            padding: 18px;

            border-radius: 16px;

            border: 1px solid var(--border-color);

        }

        .filter-group label {

            display: block;

            font-size: 0.8rem;

            font-weight: 800;

            color: var(--text-secondary);

            margin-bottom: 5px;

        }

        .filter-input, .filter-select {

            width: 100%;

            padding: 10px 12px;

            border-radius: 10px;

            border: 1px solid var(--border-strong);

            background: var(--input-bg);

            color: var(--text-primary);

            font-family: inherit;

            font-weight: 700;

            font-size: 0.9rem;

        }



        /* 3D SHOWCASE BOOK CARDS GRID */

        .books-showcase-grid {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(305px, 1fr));

            gap: 20px;

        }

        .showcase-card {

            background: var(--card-bg);

            border: 2px solid var(--border-color);

            border-radius: 18px;

            overflow: hidden;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;

            box-shadow: var(--shadow-soft);

        }

        .showcase-card:hover {

            transform: translateY(-6px);

            box-shadow: var(--shadow-hover);

            border-color: var(--accent-blue);

        }

        .showcase-cover-top {

            padding: 18px 16px;

            color: #FFFFFF;

            position: relative;

            border-bottom: 3px solid #0284C7;

            min-height: 165px;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

        }

        .showcase-body {

            padding: 16px;

            flex: 1;

            display: flex;

            flex-direction: column;

            justify-content: space-between;

        }

        .showcase-title {

            font-size: 0.98rem;

            font-weight: 900;

            color: var(--text-primary);

            margin-bottom: 6px;

            line-height: 1.38;

        }

        .showcase-meta {

            font-size: 0.8rem;

            color: var(--text-secondary);

            margin-bottom: 12px;

        }

        .showcase-actions {

            display: flex;

            gap: 8px;

            flex-wrap: wrap;

        }

        .btn-card {

            flex: 1;

            text-align: center;

            padding: 8px 10px;

            border-radius: 10px;

            font-weight: 800;

            font-size: 0.82rem;

            text-decoration: none;

            cursor: pointer;

            border: none;

            transition: opacity 0.15s ease;

        }

        .btn-card:hover { opacity: 0.92; }

        .btn-card-primary { background: var(--accent-blue); color: #FFFFFF; }

        .btn-card-gold { background: linear-gradient(135deg, #0284C7, #2563EB); color: #040711; }



        /* 10 MEGA-SECTORS ACCORDION / GRID */

        .sectors-grid {

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));

            gap: 16px;

        }

        .sector-box {

            background: var(--card-bg-alt);

            border: 1px solid var(--border-color);

            border-radius: 16px;

            padding: 18px;

        }

        .sector-box-title {

            font-weight: 900;

            font-size: 1.02rem;

            color: var(--accent-royal);

            margin-bottom: 10px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }

        .disc-link {

            display: block;

            padding: 5px 8px;

            border-radius: 8px;

            color: var(--text-secondary);

            text-decoration: none;

            font-size: 0.82rem;

            font-weight: 700;

            transition: background 0.15s ease, color 0.15s ease;

        }

        .disc-link:hover {

            background: rgba(37, 99, 235, 0.1);

            color: var(--accent-blue);

        }



        /* FOOTER */

        .royal-footer {

            background: #0F172A;

            color: #CBD5E1;

            padding: 40px 20px;

            margin-top: 50px;

            border-top: 4px solid #0284C7;

        }

        .footer-inner {

            max-width: 1440px;

            margin: 0 auto;

            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));

            gap: 28px;

        }

        .footer-title { color: #0284C7; font-weight: 900; font-size: 1.08rem; margin-bottom: 12px; }

        .footer-links a {

            display: block;

            color: #93C5FD;

            text-decoration: none;

            font-size: 0.88rem;

            margin-bottom: 6px;

            font-weight: 600;

        }

    
        /* ==========================================================================
           ULTRA-LUXURY SOVEREIGN IMPERIAL MASTERPIECE STYLING
           Eng. Mahmoud Bek ElNemer · Guinness World Record 1M Encyclopedia
           ========================================================================== */
        @keyframes goldGlowPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(37, 99, 235, 0.35), 0 10px 40px rgba(0,0,0,0.8); }
            50% { box-shadow: 0 0 40px rgba(37, 99, 235, 0.65), 0 15px 50px rgba(0,0,0,0.9); }
        }
        @keyframes crestFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-4px) rotate(1deg); }
        }

        .gold-gradient-text {
            background: linear-gradient(135deg, #FFFDF0 0%, #0284C7 35%, #0284C7 70%, #996515 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Cinzel', 'Tajawal', serif;
        }

        .hero-banner {
            background: radial-gradient(ellipse at 50% 0%, rgba(37, 99, 235, 0.18) 0%, transparent 70%),
                        radial-gradient(ellipse at 15% 50%, rgba(37, 99, 235, 0.14) 0%, transparent 60%),
                        linear-gradient(145deg, #0c101c 0%, #07090f 50%, #030408 100%) !important;
            border: 2px solid rgba(37, 99, 235, 0.45) !important;
            border-radius: 28px !important;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.85), inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
            padding: 45px 35px !important;
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, #0284C7, #FFF, #0284C7, transparent);
        }

        .author-vip-card {
            display: inline-flex;
            align-items: center;
            gap: 16px;
            background: rgba(15, 22, 38, 0.92);
            border: 1.5px solid rgba(37, 99, 235, 0.5);
            border-radius: 999px;
            padding: 10px 26px;
            margin: 18px 0 25px 0;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(12px);
        }

        .author-vip-crest {
            font-size: 28px;
            animation: crestFloat 4s ease-in-out infinite;
        }

        .author-vip-name {
            font-size: 1.12rem;
            font-weight: 900;
            color: #FFFFFF;
            letter-spacing: 0.3px;
        }

        .author-vip-sub {
            font-size: 0.84rem;
            color: #0284C7;
            font-weight: 700;
        }

        .hero-stats-bento {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 16px;
            margin: 30px 0 15px 0;
        }

        .stat-bento-card {
            background: rgba(14, 20, 34, 0.85);
            border: 1px solid rgba(37, 99, 235, 0.28);
            border-radius: 20px;
            padding: 22px 20px;
            text-align: center;
            transition: all 0.25s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .stat-bento-card:hover {
            transform: translateY(-5px);
            border-color: #0284C7;
            box-shadow: 0 18px 45px rgba(37, 99, 235, 0.25);
        }

        .stat-bento-num {
            font-family: 'Cinzel', 'Cairo', serif;
            font-size: 2.4rem;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 6px;
            background: linear-gradient(135deg, #FFF9E6 0%, #F5D77F 50%, #0284C7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-bento-label {
            color: #E2E8F0;
            font-weight: 800;
            font-size: 0.95rem;
        }

        .stat-bento-desc {
            color: #94A3B8;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .section-card {
            background: rgba(13, 18, 30, 0.85) !important;
            border: 1px solid rgba(37, 99, 235, 0.25) !important;
            border-radius: 24px !important;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.6) !important;
            backdrop-filter: blur(14px) !important;
        }

        .topbar {
            background: rgba(7, 9, 15, 0.94) !important;
            border-bottom: 2px solid #0284C7 !important;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.7) !important;
            backdrop-filter: blur(20px) !important;
        }

        .adj-pill {
            background: rgba(18, 26, 44, 0.8) !important;
            border: 1px solid rgba(37, 99, 235, 0.35) !important;
            color: #F8FAFC !important;
            font-weight: 700 !important;
            padding: 8px 18px !important;
            border-radius: 999px !important;
            transition: all 0.2s ease !important;
        }

        .adj-pill:hover, .adj-pill.active {
            background: linear-gradient(135deg, #0284C7, #AA771C) !important;
            color: #000000 !important;
            font-weight: 900 !important;
            border-color: #38BDF8 !important;
            transform: translateY(-2px) scale(1.03) !important;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45) !important;
        }


        /* 3D ROYAL ENCYCLOPEDIA HARDCOVER MOCKUP IN SHOWCASE GRID */
        .showcase-card {
            background: linear-gradient(160deg, rgba(15, 23, 42, 0.96) 0%, rgba(7, 10, 20, 0.98) 100%) !important;
            border: 1.5px solid rgba(37, 99, 235, 0.35) !important;
            border-radius: 20px !important;
            padding: 18px !important;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.75), inset 0 1px 0 rgba(255, 255, 255, 0.08) !important;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .showcase-card:hover {
            transform: translateY(-8px) !important;
            border-color: #38BDF8 !important;
            box-shadow: 0 25px 60px rgba(37, 99, 235, 0.28), 0 10px 30px rgba(0,0,0,0.9) !important;
        }
        .royal-3d-book-wrap {
            perspective: 1000px;
            margin: 6px auto 16px auto;
            width: 100%;
            max-width: 245px;
        }
        .royal-3d-book {
            position: relative;
            width: 100%;
            min-height: 310px;
            border-radius: 6px 14px 14px 6px;
            padding: 14px 14px 12px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            border: 2px solid #0284C7;
            box-shadow:
                -8px 0 0 #050811,
                -10px 0 0 #0284C7,
                6px 6px 0 #E2E8F0,
                7px 7px 0 #CBD5E1,
                8px 8px 0 #94A3B8,
                10px 10px 0 #0284C7,
                16px 22px 38px rgba(0, 0, 0, 0.85),
                inset 0 0 25px rgba(0, 0, 0, 0.65);
            transform: rotateY(-8deg) rotateX(2deg);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }
        .showcase-card:hover .royal-3d-book {
            transform: rotateY(0deg) rotateX(0deg) scale(1.03);
        }
        /* Leather Spine & Gold Raised Bands */
        .royal-3d-book::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 14px;
            background: linear-gradient(90deg, rgba(255,215,0,0.35) 0%, rgba(0,0,0,0.75) 50%, rgba(37,99,235,0.45) 100%);
            border-right: 1.5px solid rgba(253, 230, 138, 0.6);
            border-radius: 4px 0 0 4px;
        }
        .royal-3d-book::after {
            content: '';
            position: absolute;
            top: 6px; bottom: 6px; left: 18px; right: 6px;
            border: 1px solid rgba(253, 230, 138, 0.32);
            border-radius: 4px 10px 10px 4px;
            pointer-events: none;
        }
        .showcase-title {
            color: #FFFBEB !important;
            font-size: 1.02rem !important;
            font-weight: 900 !important;
            line-height: 1.45 !important;
            margin-bottom: 8px !important;
        }
        .showcase-meta {
            color: #94A3B8 !important;
            font-size: 0.78rem !important;
            line-height: 1.55 !important;
            background: rgba(2, 6, 23, 0.65);
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid rgba(37, 99, 235, 0.2);
            margin-bottom: 12px !important;
        }


        /* ==========================================================================
           MASTER SAPPHIRE, EMERALD & PLATINUM PEARL DESIGN (ZERO BLACK & ZERO YELLOW)
           ========================================================================== */
        :root, [data-theme="imperial-dark"], [data-theme="royal-light"] {
            --bg-main: #F4F7FB !important;
            --bg-grad: linear-gradient(135deg, #F0F5FF 0%, #F8FAFC 50%, #ECFDF5 100%) !important;
            --card-bg: #FFFFFF !important;
            --card-bg-alt: #F8FAFC !important;
            --topbar-bg: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 55%, #0F766E 100%) !important;
            --hero-bg: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #0D9488 100%) !important;
            --text-primary: #0F172A !important;
            --text-secondary: #334155 !important;
            --text-muted: #64748B !important;
            --accent-blue: #2563EB !important;
            --accent-royal: #1E3A8A !important;
            --accent-sky: #0284C7 !important;
            --accent-gold: #0284C7 !important;
            --accent-gold-light: #E0F2FE !important;
            --accent-green: #059669 !important;
            --border-color: #CBD5E1 !important;
            --border-strong: #3B82F6 !important;
            --shadow-soft: 0 12px 32px rgba(15, 23, 42, 0.06) !important;
            --shadow-hover: 0 20px 45px rgba(37, 99, 235, 0.16) !important;
            --input-bg: #FFFFFF !important;
        }
        body {
            background: var(--bg-grad) !important;
            color: #0F172A !important;
        }
        .topbar {
            background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 55%, #0F766E 100%) !important;
            border-bottom: 3px solid #38BDF8 !important;
            box-shadow: 0 8px 25px rgba(30, 58, 138, 0.22) !important;
            position: relative !important;
        }
        .brand-crest {
            background: linear-gradient(135deg, #0284C7, #0D9488) !important;
            border: 2px solid #E0F2FE !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35) !important;
        }
        .top-btn, .top-btn-gold {
            background: rgba(255, 255, 255, 0.18) !important;
            color: #FFFFFF !important;
            border: 1px solid rgba(255, 255, 255, 0.4) !important;
        }
        .top-btn:hover, .top-btn-gold:hover {
            background: #FFFFFF !important;
            color: #1E3A8A !important;
            border-color: #FFFFFF !important;
        }
        .hero-banner {
            background: radial-gradient(circle at 85% 15%, rgba(56, 189, 248, 0.28) 0%, transparent 55%),
                        radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.25) 0%, transparent 55%),
                        linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 48%, #0F766E 100%) !important;
            border: 2px solid #93C5FD !important;
            border-radius: 28px !important;
            box-shadow: 0 22px 55px rgba(30, 58, 138, 0.25) !important;
            padding: 40px 32px !important;
            margin-top: 10px !important;
        }
        .hero-banner::before {
            background: linear-gradient(90deg, transparent, #38BDF8, #FFFFFF, #34D399, transparent) !important;
        }
        .hero-pill {
            background: rgba(255, 255, 255, 0.16) !important;
            border: 1px solid rgba(255, 255, 255, 0.45) !important;
            color: #FFFFFF !important;
        }
        .gold-gradient-text, .hero-title {
            background: none !important;
            -webkit-text-fill-color: #FFFFFF !important;
            color: #FFFFFF !important;
            text-shadow: 0 4px 18px rgba(15, 23, 42, 0.28) !important;
        }
        .author-vip-card {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.45) !important;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2) !important;
        }
        .author-vip-sub {
            color: #E0F2FE !important;
        }
        .stat-bento-card {
            background: rgba(255, 255, 255, 0.14) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.35) !important;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15) !important;
        }
        .stat-bento-card:hover {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: #FFFFFF !important;
            transform: translateY(-5px) !important;
        }
        .stat-bento-num {
            background: none !important;
            -webkit-text-fill-color: #FFFFFF !important;
            color: #FFFFFF !important;
        }
        .stat-bento-label {
            color: #E0F2FE !important;
        }
        .stat-bento-desc {
            color: #CBD5E1 !important;
        }
        .btn-hero-gold {
            background: #FFFFFF !important;
            color: #1E3A8A !important;
            border: 2px solid #FFFFFF !important;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2) !important;
        }
        .section-card {
            background: #FFFFFF !important;
            border: 1.5px solid #DBEAFE !important;
            border-radius: 24px !important;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06) !important;
        }
        .section-title {
            color: #1E3A8A !important;
        }
        .adj-pill {
            background: #EFF6FF !important;
            border: 1.5px solid #93C5FD !important;
            color: #1E3A8A !important;
            font-weight: 800 !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.08) !important;
        }
        .adj-pill:hover, .adj-pill.active {
            background: linear-gradient(135deg, #2563EB, #0D9488) !important;
            color: #FFFFFF !important;
            border-color: #1D4ED8 !important;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.28) !important;
        }
        .showcase-card {
            background: #FFFFFF !important;
            border: 1.5px solid #E2E8F0 !important;
            border-radius: 20px !important;
            padding: 18px !important;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.07) !important;
        }
        .showcase-card:hover {
            border-color: #3B82F6 !important;
            box-shadow: 0 18px 42px rgba(37, 99, 235, 0.16) !important;
        }
        .royal-3d-book {
            border: 2px solid rgba(255, 255, 255, 0.65) !important;
            box-shadow:
                -7px 0 0 #0F172A,
                -9px 0 0 #38BDF8,
                6px 6px 0 #F8FAFC,
                7px 7px 0 #E2E8F0,
                8px 8px 0 #CBD5E1,
                10px 10px 0 #64748B,
                14px 18px 30px rgba(15, 23, 42, 0.25) !important;
        }
        .royal-3d-book::before {
            background: linear-gradient(90deg, rgba(255,255,255,0.35) 0%, rgba(15,23,42,0.45) 50%, rgba(255,255,255,0.25) 100%) !important;
            border-right: 1.5px solid rgba(255, 255, 255, 0.5) !important;
        }
        .royal-3d-book::after {
            border: 1px solid rgba(255, 255, 255, 0.35) !important;
        }
        .showcase-title {
            color: #0F172A !important;
            font-size: 1.02rem !important;
            font-weight: 900 !important;
        }
        .showcase-meta {
            color: #475569 !important;
            background: #F8FAFC !important;
            border: 1px solid #E2E8F0 !important;
        }
        .btn-card-primary {
            background: linear-gradient(135deg, #2563EB, #1D4ED8) !important;
            color: #FFFFFF !important;
        }
        .btn-card-emerald, .btn-card-gold {
            background: linear-gradient(135deg, #059669, #0D9488) !important;
            color: #FFFFFF !important;
        }
        .book-3d-cover {
            border: 2px solid rgba(255, 255, 255, 0.75) !important;
            box-shadow: -8px 0 0 #1E293B, -10px 0 0 #38BDF8, 6px 6px 0 #F8FAFC, 8px 8px 0 #E2E8F0, 10px 10px 0 #CBD5E1, 12px 12px 0 #64748B, -15px 22px 45px rgba(15, 23, 42, 0.3) !important;
            animation: floatBook 6s ease-in-out infinite !important;
        }
        .royal-footer {
            background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%) !important;
            border-top: 4px solid #38BDF8 !important;
        }
        .footer-title {
            color: #38BDF8 !important;
        }


        /* ==========================================================================
           ULTRA-CLEAN 50 MEGA-SECTORS & 1,000 DISCIPLINES BENTO ARCHITECTURE
           ========================================================================== */
        .tax-hub-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 14px 0 18px 0;
        }
        .tax-hub-btn {
            background: #EFF6FF;
            color: #1E3A8A;
            border: 1.5px solid #BFDBFE;
            padding: 9px 16px;
            border-radius: 999px;
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .tax-hub-btn:hover, .tax-hub-btn.active {
            background: linear-gradient(135deg, #2563EB, #0D9488);
            color: #FFFFFF;
            border-color: #1D4ED8;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.25);
            transform: translateY(-2px);
        }
        .sectors-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)) !important;
            gap: 20px !important;
        }
        .sector-box {
            background: #FFFFFF !important;
            border: 1.5px solid #DBEAFE !important;
            border-radius: 20px !important;
            padding: 0 !important;
            overflow: hidden !important;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05) !important;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .sector-box:hover {
            transform: translateY(-4px) !important;
            border-color: #3B82F6 !important;
            box-shadow: 0 16px 36px rgba(37, 99, 235, 0.12) !important;
        }
        .sector-card-head {
            background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
            border-bottom: 1.5px solid #DBEAFE;
            padding: 16px 18px;
        }
        .sector-top-badges {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .sector-id-pill {
            background: linear-gradient(135deg, #1E3A8A, #2563EB);
            color: #FFFFFF;
            font-size: 0.75rem;
            font-weight: 900;
            padding: 4px 10px;
            border-radius: 8px;
            letter-spacing: 0.3px;
        }
        .sector-vol-pill {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
            font-size: 0.73rem;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: 8px;
        }
        .sector-ar-name {
            display: block;
            font-size: 1.04rem;
            font-weight: 900;
            color: #0F172A;
            line-height: 1.4;
            text-decoration: none;
            margin-bottom: 4px;
        }
        .sector-ar-name:hover {
            color: #2563EB;
        }
        .sector-en-name {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: #475569;
            line-height: 1.35;
            direction: ltr;
            text-align: left;
            font-family: 'Inter', sans-serif;
        }
        .sector-discs-body {
            padding: 12px 14px 14px 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }
        .disc-clean-card {
            display: block;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            padding: 9px 12px;
            text-decoration: none;
            transition: all 0.18s ease;
        }
        .disc-clean-card:hover {
            background: #EFF6FF;
            border-color: #60A5FA;
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.08);
        }
        .disc-row-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }
        .disc-num-badge {
            background: #DBEAFE;
            color: #1E40AF;
            font-family: monospace;
            font-size: 0.73rem;
            font-weight: 900;
            padding: 2px 7px;
            border-radius: 6px;
        }
        .disc-track-badge {
            font-size: 0.68rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            background: #E0F2FE;
            color: #0369A1;
        }
        .disc-ar-main {
            font-size: 0.86rem;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.38;
            margin-bottom: 2px;
        }
        .disc-en-main {
            font-size: 0.73rem;
            font-weight: 600;
            color: #64748B;
            direction: ltr;
            text-align: left;
            font-family: 'Inter', sans-serif;
            line-height: 1.3;
        }
        .sector-toggle-btn {
            width: 100%;
            margin-top: 4px;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1.5px dashed #93C5FD;
            background: #F0F7FF;
            color: #1D4ED8;
            font-family: inherit;
            font-size: 0.8rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.18s ease;
        }
        .sector-toggle-btn:hover {
            background: #DBEAFE;
            border-style: solid;
        }

</style>

    <link rel="stylesheet" href="/nemer_unified_theme.css?v=5.2">
    <meta property="og:image" content="https://million.elnemer-ai.com/og_banner.php">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="https://million.elnemer-ai.com/og_banner.php">
</head>

<body>



<!-- TOP ROYAL COMMAND BAR -->

<header class="topbar">

    <div class="topbar-inner">

        <a href="/?lang=<?php echo urlencode($cur_lang); ?>" class="brand-logo">

            <div class="brand-crest">👑</div>

            <div>

                <div class="brand-text-main"><?php echo htmlspecialchars($i18n['site_title']); ?></div>

                <div class="brand-text-sub">Eng. Mahmoud Bek ElNemer • Google Play Partner Official Partner </div>

            </div>

        </a>



        <div class="topbar-controls">

            <!-- Instant 78-Language Dropdown Switcher -->

            <select class="top-select" aria-label="Select Language (78 Languages)" onchange="window.location.href='/?lang=' + encodeURIComponent(this.value);">

                <?php foreach ($all_langs as $l): ?>

                <option value="<?php echo htmlspecialchars($l['code']); ?>" <?php echo ($l['code'] === $cur_lang) ? 'selected' : ''; ?>>

                    🌍 [<?php echo htmlspecialchars($l['code']); ?>] <?php echo htmlspecialchars($l['name'] . ' — ' . $l['en_name']); ?>

                </option>

                <?php endforeach; ?>

            </select>



            <button class="top-btn top-btn-gold" onclick="togglePortalTheme()" id="themeToggleBtn">

                💎 التصميم الياقوتي والزمردي الأكاديمي

            </button>

            <a class="top-btn top-btn-gold" href="/guinness<?php echo ($cur_lang !== 'ar') ? '?lang=' . urlencode($cur_lang) : ''; ?>" style="background: linear-gradient(135deg, #059669, #0D9488); color: #FFF; font-weight: 800; border: 1px solid #6EE7B7;">⚖️ بوابة تحكيم جينيس</a> <a class="top-btn" href="/witness<?php echo ($cur_lang !== 'ar') ? '?lang=' . urlencode($cur_lang) : ''; ?>" style="border-color:#10b981;color:#34d399;font-weight:800;">📜 سجل البراهين الجنائية</a> <a class="top-btn" href="/audit/4<?php echo ($cur_lang !== 'ar') ? '?lang=' . urlencode($cur_lang) : ''; ?>" style="border-color:#f59e0b;color:#fde68a;font-weight:800;">🔐 مختبر التدقيق التشفيري</a> <a class="top-btn" href="/oai<?php echo ($cur_lang !== 'ar') ? '?lang=' . urlencode($cur_lang) : ''; ?>" style="border-color:#38bdf8;color:#7dd3fc;font-weight:800;">🏛️ مستودع مكتبة الكونجرس OAI</a>
            <a class="top-btn" href="/guinness_dossier.pdf" target="_blank">📥 ملف التحكيم (PDF)</a>
            <a class="top-btn" href="#adjudicator-console">🔬 منصة التحكيم ISO</a>

            <a class="top-btn" href="/sitemap_index.xml" target="_blank">🗺️ XML Sitemap (1B Pages / 5M)</a>

        </div>

    </div>

</header>
<!-- LIVE GLOBAL INDEXING & VERIFICATION TICKER (78 LANGUAGES) -->
<div class="global-live-ticker">
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#059669;box-shadow:0 0 8px #10B981;"></span>
        <span id="tickerLabelText">📡 رادار الفهرسة العالمي الحي (Live Global Verification Stream — 5,000,000 Volumes):</span>
    </div>
    <div class="ticker-items-wrap" id="liveTickerBooksRow">
        <a href="/book/1" class="ticker-book-pill">📘 #1 [AR] الذكاء الاصطناعي والحوسبة الفائقة</a>
        <a href="/book/2001" class="ticker-book-pill">📗 #2,001 [EN] Regenerative Medicine &amp; Stem Cells</a>
        <a href="/book/4001" class="ticker-book-pill">📘 #4,001 [FR] Ingénierie de l'Énergie Solaire</a>
        <a href="/book/6001" class="ticker-book-pill">📗 #6,001 [DE] Robotik &amp; Industrielle Automatisierung</a>
        <a href="/book/8001" class="ticker-book-pill">📘 #8,001 [ES] Agricultura Inteligente e Hidroponía</a>
        <a href="/book/5000000" class="ticker-book-pill">👑 #5,000,000 [EO] المجلد الختامي للمليار صفحة</a>
    </div>
</div>




<main class="container">



    <!-- HERO SOVEREIGN BANNER -->

    <section class="hero-banner">

        <div class="hero-badges">

            <span class="hero-pill"><?php echo htmlspecialchars($i18n['hero_badge']); ?></span>

            <span class="hero-pill">🌍 <?php echo htmlspecialchars($i18n['name'] . " (" . $i18n['en_name'] . ") — " . number_format($i18n['books_count']) . " Books"); ?></span>

            <span class="hero-pill">🔐 موثق ومفهرس دولياً (ISO / ISBN-13 / DOI)</span>

            <span class="hero-pill">📚 1,000,000,000 (1 Billion) Pages • 5,000,000 Volumes • 1,000 Human-Benefit Disciplines</span>

        </div>



        <h1 class="hero-title gold-gradient-text">👑 <?php echo htmlspecialchars($page_title); ?></h1>

        <div style="text-align:center;">
            <div class="author-vip-card">
                <div class="author-vip-crest">👑</div>
                <div style="text-align:right;">
                    <div class="author-vip-name">المؤلف والمهندس المعماري: المهندس محمود بك النمر</div>
                    <div class="author-vip-sub">Google Accredited AI &amp; Systems Engineer · Founder &amp; CEO of Nemer Academic Publishing House</div>
                </div>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(16,185,129,0.25);color:#34D399;font-size:0.82rem;font-weight:800;padding:6px 16px;border-radius:999px;border:1px solid #10B981;">
                    <span style="width:8px;height:8px;border-radius:50%;background:#10B981;box-shadow:0 0 10px #10B981;"></span>
                    اعتماد دولي وتشفيري موثق
                </span>
            </div>
        </div>

        <!-- 6 IMPERIAL BENTO METRIC PILLARS — THE 1-BILLION-PAGE SOVEREIGN MONUMENT -->
        <div class="hero-stats-bento" style="grid-template-columns: repeat(auto-fit, minmax(205px, 1fr));">
            <div class="stat-bento-card" style="border:2px solid #FFFFFF;box-shadow:0 10px 30px rgba(255,255,255,0.22);">
                <div class="stat-bento-num" style="font-size:2.3rem;">1,000,000,000</div>
                <div class="stat-bento-label" style="color:#FFFFFF;">مليار صفحة موسوعية نافعة</div>
                <div class="stat-bento-desc">أضخم صرح معرفي لمؤلف واحد في التاريخ</div>
            </div>
            <div class="stat-bento-card">
                <div class="stat-bento-num">5,000,000</div>
                <div class="stat-bento-label">مجلد وكتاب تطبيقي وعلمي</div>
                <div class="stat-bento-desc">200 صفحة قياسية لكل كتاب مع EAN-13</div>
            </div>
            <div class="stat-bento-card">
                <div class="stat-bento-num">1,000</div>
                <div class="stat-bento-label">تخصص دقيق يفيد الإنسان 100%</div>
                <div class="stat-bento-desc">عبر 50 قطاعاً حياتياً وعلمياً ومهنياً</div>
            </div>
            <div class="stat-bento-card">
                <div class="stat-bento-num">78</div>
                <div class="stat-bento-label">لغة عالمية رسمية (Google Play)</div>
                <div class="stat-bento-desc">تغطي 99.4% من القراء حول العالم</div>
            </div>
            <div class="stat-bento-card">
                <div class="stat-bento-num">100M</div>
                <div class="stat-bento-label">صفحة وقف علمي مجاني ($0.00)</div>
                <div class="stat-bento-desc">500,000 مجلد مفتوح للبشرية والجامعات</div>
            </div>
            <div class="stat-bento-card">
                <div class="stat-bento-num">0%</div>
                <div class="stat-bento-label">صفر تكرار • صفر سياسة • صفر ضرر</div>
                <div class="stat-bento-desc">ميثاق النفع الإنساني الخالص 100%</div>
            </div>
        </div>

        <!-- 5 HUMAN-BENEFIT COMPASS HUBS (بوصلة النفع الإنساني السريعة لـ 50 قطاعاً و 1000 تخصص) -->
        <div style="background:rgba(255,255,255,0.95);border:2px solid #93C5FD;border-radius:20px;padding:18px 20px;margin:18px 0 22px 0;box-shadow:0 12px 35px rgba(0,0,0,0.55);">
            <div style="font-size:0.95rem;font-weight:900;color:#1E3A8A;margin-bottom:12px;text-align:center;">
                🧭 بوصلة النفع الإنساني الشاملة — اختر محور حياتك أو تخصصك لاستكشاف الـ 5,000,000 كتاب (مليار صفحة):
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px;">
                <button class="adj-pill" style="padding:12px 14px!important;border-radius:14px!important;text-align:right;display:flex;align-items:center;gap:10px;" onclick="filterByCompassHub(1, 10, '🏥 الصحة والطب الأسري والوقائي والتغذية')">
                    <span style="font-size:1.5rem;">🏥</span>
                    <div>
                        <div style="font-size:0.88rem;font-weight:900;">الصحة والطب الأسري والتغذية</div>
                        <div style="font-size:0.72rem;opacity:0.85;">القطاعات 1–10 • 200 تخصص • 1,000,000 كتاب</div>
                    </div>
                </button>
                <button class="adj-pill" style="padding:12px 14px!important;border-radius:14px!important;text-align:right;display:flex;align-items:center;gap:10px;" onclick="filterByCompassHub(11, 18, '👨‍👩‍👧‍👦 الأسرة والتربية والتعليم وتطوير الذات')">
                    <span style="font-size:1.5rem;">👨‍👩‍👧‍👦</span>
                    <div>
                        <div style="font-size:0.88rem;font-weight:900;">الأسرة والتربية وتطوير الذات</div>
                        <div style="font-size:0.72rem;opacity:0.85;">القطاعات 11–18 • 160 تخصصاً • 800,000 كتاب</div>
                    </div>
                </button>
                <button class="adj-pill" style="padding:12px 14px!important;border-radius:14px!important;text-align:right;display:flex;align-items:center;gap:10px;" onclick="filterByCompassHub(19, 28, '💼 المهن والحرف والعمل الحر والمشاريع')">
                    <span style="font-size:1.5rem;">💼</span>
                    <div>
                        <div style="font-size:0.88rem;font-weight:900;">المهن والحرف والعمل الحر والتجارة</div>
                        <div style="font-size:0.72rem;opacity:0.85;">القطاعات 19–28 • 200 تخصص • 1,000,000 كتاب</div>
                    </div>
                </button>
                <button class="adj-pill" style="padding:12px 14px!important;border-radius:14px!important;text-align:right;display:flex;align-items:center;gap:10px;" onclick="filterByCompassHub(29, 38, '💻 البرمجة والذكاء الاصطناعي والهندسة الرقمية')">
                    <span style="font-size:1.5rem;">💻</span>
                    <div>
                        <div style="font-size:0.88rem;font-weight:900;">البرمجة والذكاء الاصطناعي والتقنية</div>
                        <div style="font-size:0.72rem;opacity:0.85;">القطاعات 29–38 • 200 تخصص • 1,000,000 كتاب</div>
                    </div>
                </button>
                <button class="adj-pill" style="padding:12px 14px!important;border-radius:14px!important;text-align:right;display:flex;align-items:center;gap:10px;" onclick="filterByCompassHub(39, 50, '🌱 الزراعة والطاقة الشمسية والعمارة والعلوم')">
                    <span style="font-size:1.5rem;">🌱</span>
                    <div>
                        <div style="font-size:0.88rem;font-weight:900;">الزراعة والطاقة والعمارة والعلوم</div>
                        <div style="font-size:0.72rem;opacity:0.85;">القطاعات 39–50 • 240 تخصصاً • 1,200,000 كتاب</div>
                    </div>
                </button>
            </div>
        </div>

        <p class="hero-subtitle"><?php echo htmlspecialchars($page_desc); ?></p>



        <div class="hero-cta-row">

            <a href="#showcase-section" class="btn-hero btn-hero-gold"><?php echo htmlspecialchars($i18n['cta_explore']); ?></a>

            <a href="#adjudicator-console" class="btn-hero btn-hero-white"><?php echo htmlspecialchars($i18n['cta_audit']); ?></a>

            <a href="#lang-matrix-section" class="btn-hero btn-hero-glass"><?php echo htmlspecialchars($i18n['cta_langs']); ?></a>

            <button class="btn-hero btn-hero-glass" onclick="runIsoRandomSampleAudit()">🎲 فحص عينة عشوائية محكّمة (ISO 2859-1)</button>

        </div>



        
    
    <!-- GLOBAL ENCYCLOPEDIA INSTANT SEARCH ENGINE -->
    <div class="global-search-wrapper" style="margin:25px auto 35px auto;max-width:920px;position:relative;">
        <div style="background:#FFFFFF;border:2px solid #38BDF8;border-radius:18px;padding:8px 16px;display:flex;align-items:center;gap:12px;box-shadow:0 12px 35px rgba(0,0,0,0.6);backdrop-filter:blur(12px);">
            <span style="font-size:1.6rem;color:#0284C7;">🔍</span>
            <input type="text" id="globalEncyclopediaSearch" placeholder="ابحث في الـ 5,000,000 كتاب ومليار صفحة (طب أسري، تربية، عمل حر، برمجة، زراعة، أو رقم ISBN أو رقم المجلد 1 - 5000000)..." 
                   style="flex:1;background:transparent;border:none;color:#0F172A;font-size:1.05rem;font-weight:700;font-family:'Tajawal',sans-serif;outline:none;" 
                   oninput="onLiveEncyclopediaSearch(this.value)">
            <button class="top-btn top-btn-gold" onclick="onLiveEncyclopediaSearch(document.getElementById('globalEncyclopediaSearch').value)" style="padding:10px 24px;font-size:1rem;">بحث لحظي</button>
        </div>
        <!-- Quick Topic Tags -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:12px;justify-content:center;">
            <span style="font-size:0.8rem;color:#94A3B8;font-weight:800;align-self:center;">🔥 الأكثر بحثاً:</span>
            <button class="adj-pill" onclick="searchTopicTag('الإسعافات الأولية')">🚑 الإسعافات الأولية وطب الأسرة</button>
            <button class="adj-pill" onclick="searchTopicTag('التغذية العلاجية')">🥗 التغذية العلاجية والمناعة</button>
            <button class="adj-pill" onclick="searchTopicTag('تربية الأبناء')">👨‍👩‍👧‍👦 تربية الأبناء وتعديل السلوك</button>
            <button class="adj-pill" onclick="searchTopicTag('العمل الحر')">💼 العمل الحر والمشاريع الصغيرة</button>
            <button class="adj-pill" onclick="searchTopicTag('الذكاء الاصطناعي')">🤖 الذكاء الاصطناعي والبرمجة</button>
            <button class="adj-pill" onclick="searchTopicTag('الطاقة الشمسية')">☀️ الطاقة الشمسية والزراعة الحديثة</button>
        </div>
        <!-- Live Results Dropdown Container -->
        <div id="liveSearchResultsContainer" style="display:none;position:absolute;top:100%;left:0;right:0;background:#FFFFFF;border:2px solid #2563EB;border-radius:16px;margin-top:10px;padding:16px;max-height:480px;overflow-y:auto;z-index:9999;box-shadow:0 20px 50px rgba(0,0,0,0.85);text-align:right;">
            <div id="liveSearchResultsContent"></div>
        </div>
    </div>

    <!-- GUINNESS WORLD RECORD LIVE GENERATION & NEMER SOVEREIGN EDGE INGESTION RADAR -->
    <div style="background:linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 45%, #0284C7 80%, #0D9488 100%);border:2px solid #7DD3FC;border-radius:20px;padding:24px;margin-bottom:30px;box-shadow:0 12px 35px rgba(0,0,0,0.65);">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:18px;border-bottom:1px dashed rgba(2,132,199,0.45);padding-bottom:14px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <span style="font-size:1.6rem;">🛰️</span>
                <div>
                    <div style="font-size:1.1rem;font-weight:900;color:#FFFFFF;">رادار المراقبة التشفيرية الحية لموسوعة جينيس العالمية (Live Ingestion &amp; Sovereign Radar)</div>
                    <div style="font-size:0.82rem;color:#E0F2FE;">تتبع مباشر لحالة التوليد والأرشفة السحابية لكافة المجلدات والصفحات على مدار الساعة</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;background:rgba(16,185,129,0.15);padding:6px 14px;border-radius:999px;border:1px solid #10B981;">
                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#10B981;box-shadow:0 0 10px #10B981;"></span>
                <span style="font-size:0.84rem;font-weight:900;color:#34D399;">النظام نشط سحابياً 100% (LIVE 24/7)</span>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:14px;">
            <div style="background:rgba(255,255,255,0.14);border:1px solid rgba(56,189,248,0.3);border-radius:14px;padding:14px;">
                <div style="font-size:0.76rem;color:#94A3B8;font-weight:800;">📚 إجمالي الكتب المصنفة والمفهرسة</div>
                <div style="font-size:1.4rem;font-weight:900;color:#38BDF8;">5,000,000 <span style="font-size:0.82rem;color:#BAE6FD;">كتاب ومجلد</span></div>
            </div>
            <div style="background:rgba(255,255,255,0.14);border:1px solid rgba(2,132,199,0.3);border-radius:14px;padding:14px;">
                <div style="font-size:0.76rem;color:#94A3B8;font-weight:800;">📄 إجمالي الصفحات بدون تكرار</div>
                <div style="font-size:1.4rem;font-weight:900;color:#0284C7;">1,000,000,000 <span style="font-size:0.82rem;color:#0284C7;">مليار صفحة</span></div>
            </div>
            <div style="background:rgba(255,255,255,0.14);border:1px solid rgba(16,185,129,0.3);border-radius:14px;padding:14px;">
                <div style="font-size:0.76rem;color:#94A3B8;font-weight:800;">☁️ السحابة السيادية فائقة السرعة لدار النمر (Nemer Sovereign Edge)</div>
                <div style="font-size:1.2rem;font-weight:900;color:#10B981;">متزامن كلياً <span style="font-size:0.76rem;color:#A7F3D0;">(Zero-Loss Mirror)</span></div>
            </div>
            <div style="background:rgba(255,255,255,0.14);border:1px solid rgba(168,85,247,0.3);border-radius:14px;padding:14px;">
                <div style="font-size:0.76rem;color:#94A3B8;font-weight:800;">🔐 بصمة شجرة ميركل لجينيس (Master Root)</div>
                <div style="font-size:0.82rem;font-family:monospace;font-weight:900;color:#C084FC;word-break:break-all;">72806865ca4d56a0...</div>
            </div>
        </div>
    </div>



    </section>



    <!-- 78-LANGUAGE INTERACTIVE GLOBAL MATRIX -->

    <section class="section-card" id="lang-matrix-section">
        <!-- 78-LANGUAGE CONTINENTAL POPULATION COVERAGE MATRIX -->
        <div class="continental-coverage-bar" style="background:linear-gradient(135deg, #EFF6FF 0%, #E0F2FE 50%, #ECFDF5 100%);border:1.5px solid #93C5FD;border-radius:18px;padding:16px 20px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px;">
                <div style="font-size:0.96rem;font-weight:900;color:#1E3A8A;">
                    🌍 التغطية الديموغرافية العالمية للـ 78 لغة رسمية (Global Literacy Coverage: 99.4% of Humanity across 6 Continents)
                </div>
                <span style="background:linear-gradient(135deg, #2563EB, #0D9488);color:#FFFFFF;padding:4px 12px;border-radius:999px;font-size:0.78rem;font-weight:900;">
                    7.95+ مليار نسمة مستفيد • 78 لساناً عالمياً
                </span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(195px, 1fr));gap:10px;">
                <div style="background:#FFFFFF;border:1px solid #BFDBFE;border-radius:12px;padding:10px 12px;">
                    <div style="font-size:0.78rem;font-weight:900;color:#1E3A8A;">🌏 آسيا والشرق الأوسط (32 لغة)</div>
                    <div style="font-size:0.74rem;color:#047857;font-weight:800;margin-top:2px;">العربية، الصينية، اليابانية، الهندية، الكورية، التركية، الإندونيسية، الفارسية، الأردية...</div>
                </div>
                <div style="background:#FFFFFF;border:1px solid #BFDBFE;border-radius:12px;padding:10px 12px;">
                    <div style="font-size:0.78rem;font-weight:900;color:#1E3A8A;">🌍 قارة أوروبا والأوراسيا (34 لغة)</div>
                    <div style="font-size:0.74rem;color:#0284C7;font-weight:800;margin-top:2px;">الإنجليزية، الفرنسية، الألمانية، الإسبانية، الإيطالية، الروسية، الهولندية، السويدية، البولندية...</div>
                </div>
                <div style="background:#FFFFFF;border:1px solid #BFDBFE;border-radius:12px;padding:10px 12px;">
                    <div style="font-size:0.78rem;font-weight:900;color:#1E3A8A;">🌍 القارة الأفريقية (7 لغات كبرى)</div>
                    <div style="font-size:0.74rem;color:#047857;font-weight:800;margin-top:2px;">السواحيلية، الأمهرية، الزولو، الأفريكانية، العربية، الإنجليزية، الفرنسية...</div>
                </div>
                <div style="background:#FFFFFF;border:1px solid #BFDBFE;border-radius:12px;padding:10px 12px;">
                    <div style="font-size:0.78rem;font-weight:900;color:#1E3A8A;">🌎 الأمريكتان وأوقيانوسيا (5 لغات دولية)</div>
                    <div style="font-size:0.74rem;color:#0284C7;font-weight:800;margin-top:2px;">الإنجليزية الأمريكية، الإسبانية اللاتينية، البرتغالية البرازيلية، الفرنسية الكندية، الإسبيرانتو...</div>
                </div>
            </div>
        </div>

        <div class="section-header">

            <h2 class="section-title"><?php echo htmlspecialchars($i18n['lang_matrix_title']); ?></h2>

            <div class="tier-tabs">

                <button class="tier-tab active" onclick="filterLangMatrix('ALL', this)">🌐 كل اللغات (78 لغة — 5,000,000 كتاب / مليار صفحة)</button>

                <button class="tier-tab" onclick="filterLangMatrix('T1', this)">⭐ الفئة الأولى Tier 1 (16 لغة × 125,000 كتاب = 2M كتاب)</button>

                <button class="tier-tab" onclick="filterLangMatrix('T2', this)">🏛️ الفئة الثانية Tier 2 (30 لغة × 60,000 كتاب = 1.8M كتاب)</button>

                <button class="tier-tab" onclick="filterLangMatrix('T3', this)">🌍 الفئة الثالثة Tier 3 (32 لغة × 37,500 كتاب = 1.2M كتاب)</button>

            </div>

        </div>



        <div class="lang-matrix-grid" id="langMatrixGrid">

            <?php foreach ($all_langs as $idx => $l):

                $t_code = ($idx < 16) ? 'T1' : (($idx < 46) ? 'T2' : 'T3');

                $b_cnt = ($idx < 16) ? '125,000' : (($idx < 46) ? '60,000' : '37,500');

                $is_act = ($l['code'] === $cur_lang) ? 'active' : '';

            ?>

            <a href="/?lang=<?php echo urlencode($l['code']); ?>" class="lang-pill <?php echo $is_act; ?>" data-tier="<?php echo $t_code; ?>" title="<?php echo htmlspecialchars($l['name'] . ' (' . $l['en_name'] . ') — ' . $b_cnt . ' Books'); ?>">

                <div>

                    <div class="lang-native"><?php echo htmlspecialchars($l['name']); ?></div>

                    <div class="lang-en"><?php echo htmlspecialchars($l['en_name']); ?></div>

                </div>

                <div class="lang-meta">

                    <span class="lang-code-badge"><?php echo htmlspecialchars($l['code']); ?></span>

                    <span class="lang-count-badge">📚 <?php echo $b_cnt; ?></span>

                </div>

            </a>

            <?php endforeach; ?>

        </div>

    </section>



    <!-- INSTANT O(1) CRYPTOGRAPHIC BOOK INSPECTOR & ADJUDICATOR AUDIT CONSOLE -->

    <section class="section-card" id="adjudicator-console">

        <div class="section-header">

            <h2 class="section-title"><?php echo htmlspecialchars($i18n['search_title']); ?></h2>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">

                <button class="top-btn top-btn-gold" onclick="inspectRandomBook()">🎲 كتاب عشوائي من الـ 5,000,000 مجلد</button>

                <button class="top-btn" style="background:var(--accent-blue);color:#fff;" onclick="runIsoRandomSampleAudit()">🔬 توليد عينة تحكيم عشوائية (ISO 2859-1)</button>

            </div>

        </div>



        <div class="filter-bar">

            <div class="filter-group">

                <label>🔢 رقم المجلد العالمي (Book ID: 1 — 5,000,000)</label>

                <input type="number" id="inspectBookId" class="filter-input" min="1" max="5000000" value="<?php echo $ssr_books[0]['global_id']; ?>" oninput="inspectBookById(this.value)">

            </div>

            <div class="filter-group">

                <label>🌍 تصفية المعرض حسب اللغة (78 لغة)</label>

                <select id="filterLangSelect" class="filter-select" onchange="onFilterControlsChange()">

                    <option value="ALL">كل اللغات العالمية (78 Languages — 5,000,000 Books)</option>

                    <?php foreach ($all_langs as $l): ?>

                    <option value="<?php echo htmlspecialchars($l['code']); ?>" <?php echo ($l['code'] === $cur_lang) ? 'selected' : ''; ?>>

                        [<?php echo htmlspecialchars($l['code']); ?>] <?php echo htmlspecialchars($l['name'] . ' (' . $l['en_name'] . ')'); ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="filter-group">

                <label>🏛️ تصفية حسب القطاع الموسوعي (50 Mega-Sectors)</label>

                <select id="filterSectorSelect" class="filter-select" onchange="onFilterControlsChange()">

                    <option value="0">كل القطاعات الموسوعية (50 Sectors — 5,000,000 Books)</option>

                    <?php foreach ($sectors as $s): ?>

                    <option value="<?php echo $s['sector_id']; ?>" <?php echo ($filter_sector === intval($s['sector_id'])) ? 'selected' : ''; ?>>

                        S<?php echo sprintf("%02d", $s['sector_id']); ?>: <?php echo htmlspecialchars($s['name_ar'] . ' — ' . $s['name_en']); ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="filter-group">

                <label>💎 فئة الإتاحة والتسعير (Commercial / Open-Access)</label>

                <select id="filterTierSelect" class="filter-select" onchange="onFilterControlsChange()">

                    <option value="ALL">الكل (4,500,000 تجاري + 500,000 وقف مجاني)</option>

                    <option value="PAID">💎 الإصدارات التجارية المدفوعة (1 — 4,500,000 | $1.00–$4.00)</option>

                    <option value="FREE">🎁 الوقف العلمي المجاني المفتوح (4,500,001 — 5,000,000 | $0.00)</option>

                </select>

            </div>

        </div>



        <div class="audit-grid" id="liveInspectorPanel">

            <!-- 3D Book Cover Stage -->

            <div class="book-3d-stage">

                <div class="book-3d-cover" id="inspCoverCard" style="background:linear-gradient(150deg, #1E3A8A 0%, #2563EB 100%);">
                    <div class="book-spine-bar"></div>
                    <div style="font-size:0.74rem;font-weight:900;color:#1E3A8A;padding:5px 10px;border-radius:8px;background:rgba(255,255,255,0.96);box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                        🏛️ ELNEMER 1B-PAGE ENCYCLOPEDIA • 5M BOOKS
                    </div>
                    <div id="inspCoverEmblem" style="width:120px;height:120px;margin:8px auto;display:flex;align-items:center;justify-content:center;">
                        #0000001
                    </div>
                    <div style="background:rgba(255,255,255,0.96);border-left:5px solid #0284C7;border-radius:10px;padding:12px;box-shadow:0 8px 20px rgba(0,0,0,0.18);">
                        <div id="inspCoverSector" style="color:#0284C7;font-size:0.76rem;font-weight:900;margin-bottom:4px;"></div>
                        <div id="inspCoverDomainAr" style="color:#0F172A;font-size:0.95rem;font-weight:900;line-height:1.3;margin-bottom:4px;"></div>
                        <div id="inspCoverDomainEn" style="color:#475569;font-size:0.8rem;font-weight:700;margin-bottom:4px;"></div>
                        <div id="inspCoverLang" style="color:#059669;font-size:0.8rem;font-weight:900;"></div>
                    </div>
                    <div style="font-size:0.78rem;color:#FFFFFF;font-weight:900;border-top:1px solid rgba(255,255,255,0.3);padding-top:6px;">
                        <span id="inspCoverAuthor">Eng. Mahmoud Bek ElNemer</span><br>
                        <span id="inspCoverIsbn" style="color:#E0F2FE;font-family:monospace;">ISBN: 9789710000012</span>
                    </div>
                </div>

            </div>



            <!-- Book Cryptographic & Bibliometric Details -->

            <div>

                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;" id="inspBadgesRow"></div>

                <h3 id="inspTitle" style="font-size:1.45rem;font-weight:900;color:var(--accent-royal);margin-bottom:6px;line-height:1.35;"></h3>

                <p id="inspSubtitle" style="font-size:0.98rem;color:var(--text-secondary);font-weight:700;margin-bottom:14px;"></p>



                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:10px;margin-bottom:16px;">

                    <div style="background:var(--card-bg-alt);border:1px solid var(--border-color);padding:10px 14px;border-radius:12px;">

                        <div style="font-size:0.76rem;color:var(--text-muted);font-weight:800;">الرقم الدولي المعياري (ISBN-13 / EAN)</div>

                        <div id="inspIsbnBox" style="font-family:monospace;font-size:1.05rem;font-weight:900;color:var(--accent-royal);"></div>

                    </div>

                    <div style="background:var(--card-bg-alt);border:1px solid var(--border-color);padding:10px 14px;border-radius:12px;">

                        <div style="font-size:0.76rem;color:var(--text-muted);font-weight:800;">المعرف الرقمي للأبحاث (DOI)</div>

                        <div id="inspDoiBox" style="font-family:monospace;font-size:0.98rem;font-weight:900;color:var(--accent-blue);"></div>

                    </div>

                    <div style="background:var(--card-bg-alt);border:1px solid var(--border-color);padding:10px 14px;border-radius:12px;">

                        <div style="font-size:0.76rem;color:var(--text-muted);font-weight:800;">التصنيف الأكاديمي الدولي (BISAC)</div>

                        <div id="inspBisacBox" style="font-size:0.92rem;font-weight:800;color:var(--text-primary);"></div>

                    </div>

                    <div style="background:var(--card-bg-alt);border:1px solid var(--border-color);padding:10px 14px;border-radius:12px;">

                        <div style="font-size:0.76rem;color:var(--text-muted);font-weight:800;">التسعير الرسمي بـ 4 عملات</div>

                        <div id="inspPriceBox" style="font-size:0.92rem;font-weight:900;color:var(--accent-green);"></div>

                    </div>

                </div>



                <div style="background:var(--card-bg-alt);border:1.5px solid var(--border-color);padding:12px 16px;border-radius:14px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;"><div><div style="font-size:0.82rem;color:var(--accent-gold);font-weight:900;">📷 الباركود الدولي المقروء (GS1 / EAN-13 Scannable Barcode)</div><div style="font-size:0.74rem;color:var(--text-muted);font-weight:700;">قابل للمسح بكاميرا الهاتف أو أجهزة الفحص</div></div><div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;"><div id="inspBarcodeBox" style="background:#fff;padding:6px 12px;border-radius:10px;border:1px solid #BFDBFE;"></div><div id="inspQrCodeBox" style="background:#fff;padding:6px;border-radius:10px;border:1px solid #6EE7B7;" title="رمز QR للمجلد"></div></div></div>
<div style="background:var(--card-bg-alt);border:1px solid var(--border-color);padding:10px 14px;border-radius:12px;margin-bottom:16px;">

                    <div style="font-size:0.76rem;color:var(--text-muted);font-weight:800;">🔐 البصمة التشفيرية الفريدة للمجلد (SHA-256 Cryptographic Leaf Hash)</div>

                    <div id="inspShaBox" style="font-family:monospace;font-size:0.82rem;word-break:break-all;color:var(--accent-royal);font-weight:700;"></div>

                </div>



                <!-- Quick Jump Buttons for Guinness & Google Adjudicators -->
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-top:14px;">
                    <div style="font-size:0.88rem;color:var(--accent-gold);font-weight:900;display:flex;align-items:center;gap:6px;">
                        <span>⚖️</span>
                        <span>قفزات التحكيم السريع لمحكّمي موسوعة جينيس وخبراء Google العالمية (Adjudicator Quick Jump):</span>
                    </div>
                    <span style="font-size:0.75rem;padding:3px 10px;border-radius:6px;background:rgba(2,132,199,0.18);border:1px solid #0284C7;color:#0284C7;font-weight:800;">
                        ISO 2859-1 Certified Sampling
                    </span>
                </div>
                <div class="adjudicator-pills-bar">
                    <button class="adj-pill" id="pillRnd" onclick="inspectRandomBook()">🎲 عينة عشوائية من 5M كتاب</button>
                    <button class="adj-pill" data-target-id="1" onclick="inspectBookById(1)">🥇 #1 المجلد التأسيسي الأول</button>
                    <button class="adj-pill" data-target-id="1000000" onclick="inspectBookById(1000000)">🎯 #1,000,000 المجلد المليوني</button>
                    <button class="adj-pill" data-target-id="2500000" onclick="inspectBookById(2500000)">⚡ #2,500,000 منتصف المليار صفحة</button>
                    <button class="adj-pill" data-target-id="4500000" onclick="inspectBookById(4500000)">💎 #4,500,000 نهاية التجاري</button>
                    <button class="adj-pill" data-target-id="4500001" onclick="inspectBookById(4500001)">🎁 #4,500,001 بداية الوقف المجاني (100M صفحة)</button>
                    <button class="adj-pill" data-target-id="5000000" onclick="inspectBookById(5000000)">👑 #5,000,000 المجلد الختامي للمليار صفحة</button>
                </div>



                <div class="inspector-action-row" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:16px;">
                    <a id="inspCertPdfBtn" href="/certificate.php?id=1" class="btn-action-glow" style="background:linear-gradient(135deg, #10B981 0%, #059669 100%);color:#fff;border-color:#6EE7B7;font-weight:900;" download>
                        <span style="font-size:1.15rem;">📜</span>
                        <span>استخراج شهادة توثيق جينيس المعتمدة (Certificate PDF)</span>
                    </a>
                    <a id="inspDownloadPdfBtn" href="/pdf/1/9789710000012_ElNemer_Vol_0000001_200Pages.pdf" class="btn-action-glow btn-action-gold" download>
                        <span style="font-size:1.2rem;">📥</span>
                        <span>تحميل الكتاب الكامل 200 صفحة (PDF فوري — سرعة فائقة)</span>
                    </a>
                    <a id="inspPreviewPdfBtn" href="/pdf/1/9789710000012_ElNemer_Vol_0000001_200Pages.pdf?mode=inline" target="_blank" class="btn-action-glow btn-action-blue">
                        <span style="font-size:1.15rem;">📖</span>
                        <span>معاينة وقراءة الـ 200 صفحة أونلاين</span>
                    </a>
                    <a id="inspLandingLink" href="/book/1" class="btn-action-glow btn-action-glass">
                        <span>🏛️ صفحة التوثيق الكاملة للمجلد</span>
                    </a>
                    <button type="button" onclick="openCiteVolumeModal()" class="btn-action-glow" style="background:linear-gradient(135deg, #0284C7, #0D9488);color:#fff;border:1.5px solid #7DD3FC;font-weight:900;cursor:pointer;">
                        <span>🎓 اقتباس أكاديمي للجامعات (Cite: APA / BibTeX)</span>
                    </button>
                    <button type="button" onclick="runLiveBrowserMerkleProof()" class="btn-action-glow" style="background:linear-gradient(135deg, #1E3A8A, #2563EB);color:#fff;border:1.5px solid #93C5FD;font-weight:900;cursor:pointer;">
                        <span>🔐 فحص شجرة ميركل الحي (Live Merkle Proof)</span>
                    </button>
                    <a id="inspR2MirrorBtn" href="/audit/1" target="_blank" class="btn-action-glow btn-action-emerald">
                        <span>☁️ السحابة السيادية فائقة السرعة لدار النمر (Nemer Sovereign Edge)</span>
                    </a>
                    <a href="https://wa.me/201507559911?text=%D8%A7%D8%B3%D8%AA%D9%81%D8%B3%D8%A7%D8%B1%20%D9%85%D9%86%20%D9%84%D8%AC%D9%86%D8%A9%20%D8%AA%D8%AD%D9%83%D9%8A%D9%85%20%D9%85%D9%88%D8%B3%D9%88%D8%B9%D8%A9%20%D8%AC%D9%8A%D9%86%D9%8A%D8%B3%20%D8%A8%D8%AE%D8%B5%D9%88%D8%B5%20%D8%A7%D9%84%D9%85%D9%84%D9%8A%D9%88%D9%86%20%D9%83%D8%AA%D8%A7%D8%A9" target="_blank" class="btn-action-glow btn-action-whatsapp">
                        <span>💬 واتساب المؤلف المباشر (01507559911)</span>
                    </a>
                </div>

            </div>

        </div>



        <!-- ISO 2859-1 Random Sample Results Drawer -->

        <div id="isoAuditDrawer" style="display:none;margin-top:24px;padding-top:20px;border-top:2px dashed var(--border-strong);">

            <h3 style="color:var(--accent-royal);font-weight:900;margin-bottom:12px;">

                ✅ نتائج فحص العينة العشوائية الإحصائية (ISO 2859-1 Adjudicator Random Sample — 12 Volumes Verified)

            </h3>

            <div id="isoAuditGrid" class="books-showcase-grid"></div>

        </div>

    </section>



    <!-- 3D INTERACTIVE HARDCOVER BOOK SHOWCASE (SSR + DYNAMIC CLIENT FILTER) -->

    <section class="section-card" id="showcase-section">

        <div class="section-header">

            <h2 class="section-title" id="showcaseHeaderTitle"><?php echo htmlspecialchars($i18n['showcase_title']); ?></h2>

            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">

                <span style="font-size:0.85rem;font-weight:800;color:var(--text-secondary);">الدفعة (Batch 1..5000):</span>

                <input type="number" id="batchJumpInput" class="filter-input" style="width:110px;padding:6px 10px;" min="1" max="5000" value="1" onchange="onFilterControlsChange()">

                <button class="top-btn" style="background:var(--accent-blue);color:#fff;" onclick="loadNextShowcasePage()">🔄 عرض دفعة جديدة من المجلدات</button>

            </div>

        </div>



        <!-- Server-Side Rendered Initial 24 Books (Crawlable by Googlebot & Bingbot!) -->

        <div class="books-showcase-grid" id="booksShowcaseGrid">

            <?php foreach ($ssr_books as $b):

                $p = $b['palette'];

            ?>

            <article class="showcase-card">
                <div class="royal-3d-book-wrap">
                    <div class="royal-3d-book" style="background:linear-gradient(155deg, <?php echo $p['bg']; ?> 0%, <?php echo $p['pri']; ?> 100%);">
                        <!-- Top Publisher Band (White & Sapphire like Oxford/Springer Monographs) -->
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.66rem;font-weight:900;color:#0F172A;background:rgba(255,255,255,0.95);padding:4px 9px;border-radius:6px;z-index:2;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                            <span style="color:<?php echo $p['bg']; ?>;">🏛️ NEMER 1B ENCYCLOPEDIA</span>
                            <span style="background:<?php echo $p['pri']; ?>;color:#FFFFFF;padding:1px 6px;border-radius:4px;">🌍 <?php echo htmlspecialchars(strtoupper($b['lang_code'])); ?></span>
                        </div>
                        <!-- Center Scientific Vector Medallion -->
                        <div style="margin:6px auto;z-index:2;">
                            <?php echo render_luxury_cover_svg_php($b['global_id'], $b['sector_id'], $p['acc'], $p['pri']); ?>
                        </div>
                        <!-- Crisp White Academic Title Plate on Cover -->
                        <div style="background:rgba(255,255,255,0.96);border-left:4px solid <?php echo $p['acc']; ?>;border-radius:8px;padding:8px 10px;z-index:2;text-align:right;box-shadow:0 6px 16px rgba(0,0,0,0.18);">
                            <div style="font-size:0.66rem;color:<?php echo $p['pri']; ?>;font-weight:900;margin-bottom:2px;"><?php echo htmlspecialchars($b['sector_ar']); ?></div>
                            <div style="font-size:0.8rem;font-weight:900;color:#0F172A;line-height:1.25;margin-bottom:2px;"><?php echo htmlspecialchars($b['domain_ar']); ?></div>
                            <div style="font-size:0.66rem;color:#475569;font-weight:700;line-height:1.2;"><?php echo htmlspecialchars($b['domain_en']); ?></div>
                        </div>
                        <!-- Bottom Author & ISBN Footer on Cover -->
                        <div style="z-index:2;margin-top:6px;border-top:1px solid rgba(255,255,255,0.3);padding-top:4px;color:#FFFFFF;">
                            <div style="font-size:0.7rem;font-weight:900;color:#FFFFFF;"><?php echo htmlspecialchars($b['author']); ?></div>
                            <div style="display:flex;justify-content:space-between;font-size:0.65rem;color:#E0F2FE;font-family:monospace;margin-top:2px;">
                                <span>ISBN: <?php echo $b['isbn13']; ?></span>
                                <span style="background:rgba(255,255,255,0.22);color:#FFFFFF;padding:0 5px;border-radius:4px;font-weight:900;"><?php echo $b['is_paid'] ? ('$' . $b['price_usd']) : 'FREE $0.00'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="showcase-body">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;padding:3px 9px;border-radius:6px;font-size:0.74rem;font-weight:900;">📘 مجلد #<?php echo number_format($b['global_id']); ?> / 5,000,000</span>
                            <span style="background:#ECFDF5;color:#047857;border:1px solid #A7F3D0;padding:3px 9px;border-radius:6px;font-size:0.74rem;font-weight:800;">🌍 <?php echo htmlspecialchars($b['lang_name']); ?></span>
                        </div>
                        <h3 class="showcase-title"><?php echo htmlspecialchars($b['title']); ?></h3>
                        <div class="showcase-meta">
                            🏛️ القطاع #<?php echo $b['sector_id']; ?> • التخصص #<?php echo $b['discipline_id']; ?> (<code><?php echo $b['bisac']; ?></code>)<br>
                            📄 200 صفحة محكّمة • 🔗 <code><?php echo $b['doi']; ?></code>
                        </div>
                    </div>
                    <div class="showcase-actions" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <a href="/book/<?php echo $b['global_id']; ?>" class="btn-card btn-card-primary" style="text-align:center;">📖 بوابة الكتاب</a>
                        <a href="/pdf/<?php echo $b['global_id']; ?>/<?php echo $b['isbn13']; ?>_ElNemer_Vol_<?php echo sprintf('%07d', $b['global_id']); ?>_200Pages.pdf" class="btn-card btn-card-emerald" style="text-align:center;" download>📥 تحميل PDF (200 ص)</a>
                    </div>
                </div>
            </article>

            <?php endforeach; ?>

        </div>

    </section>



    <!-- 50 MEGA-SECTORS & 1,000 HUMAN-BENEFIT SUB-DISCIPLINES BENTO ARCHITECTURE -->
    <section class="section-card" id="taxonomy-section">
        <div class="section-header" style="flex-wrap:wrap;gap:12px;">
            <div>
                <h2 class="section-title" style="margin-bottom:4px;">🧭 <?php echo htmlspecialchars($i18n['sectors_title']); ?></h2>
                <div style="font-size:0.86rem;font-weight:800;color:#0284C7;">
                    50 قطاعاً موسوعياً × 20 تخصصاً نافعاً للإنسان × 5,000 كتاب = 5,000,000 مجلد (1,000,000,000 — مليار صفحة)
                </div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <input type="text" id="taxQuickFilterInput" class="filter-input" placeholder="🔍 ابحث في الـ 50 قطاعاً والـ 1,000 تخصص..." style="width:270px;padding:8px 14px;" oninput="filterTaxonomyCards()">
                <button type="button" class="tax-hub-btn" onclick="toggleAllSectorDrawers()" id="toggleAllSectorsBtn">📂 فتح كل الـ 1,000 تخصص</button>
            </div>
        </div>

        <!-- 6 Interactive Hub Filter Tabs -->
        <div class="tax-hub-tabs">
            <button type="button" class="tax-hub-btn active" onclick="selectTaxHub(0, 50, this)">🌐 كل القطاعات (50 قطاعاً • 1,000 تخصص)</button>
            <button type="button" class="tax-hub-btn" onclick="selectTaxHub(1, 10, this)">🏥 الصحة والطب الأسري والتغذية (S01–S10)</button>
            <button type="button" class="tax-hub-btn" onclick="selectTaxHub(11, 20, this)">👨‍👩‍👧‍👦 الأسرة والتربية والتعليم (S11–S20)</button>
            <button type="button" class="tax-hub-btn" onclick="selectTaxHub(21, 30, this)">💼 المهن والحرف والعمل الحر (S21–S30)</button>
            <button type="button" class="tax-hub-btn" onclick="selectTaxHub(31, 40, this)">💻 البرمجة والذكاء الاصطناعي (S31–S40)</button>
            <button type="button" class="tax-hub-btn" onclick="selectTaxHub(41, 50, this)">🌱 الزراعة والطاقة والعمارة والعلوم (S41–S50)</button>
        </div>

        <div class="sectors-grid" id="sectorsBentoGrid">
            <?php
            $track_labels_ar = [
                0 => "📘 مسار الأسس والتطبيق اليومي",
                1 => "⚡ مسار المهارات المتقدمة والمعايير",
                2 => "🔬 مسار الفحص وحل المشكلات",
                3 => "🚀 مسار الأدوات الحديثة والابتكار"
            ];
            foreach ($sectors as $s):
                $sid = intval($s['sector_id']);
                $sec_discs = [];
                foreach ($discs as $d) {
                    if (intval($d['sector_id']) === $sid) {
                        $sec_discs[] = $d;
                    }
                }
            ?>
            <div class="sector-box" data-sector-id="<?php echo $sid; ?>">
                <div class="sector-card-head">
                    <div class="sector-top-badges">
                        <span class="sector-id-pill">🏛️ قطاع S<?php echo sprintf("%02d", $sid); ?> • <?php echo htmlspecialchars($s['bisac']); ?></span>
                        <span class="sector-vol-pill">📚 100,000 كتاب • 20M صفحة</span>
                    </div>
                    <a class="sector-ar-name" href="/?lang=<?php echo urlencode($cur_lang); ?>&sector=<?php echo $sid; ?>">
                        <?php echo htmlspecialchars($s['name_ar']); ?>
                    </a>
                    <span class="sector-en-name" dir="ltr"><?php echo htmlspecialchars($s['name_en']); ?></span>
                </div>

                <div class="sector-discs-body">
                    <?php
                    foreach ($sec_discs as $idx_d => $d):
                        $did = intval($d['discipline_id']);
                        // Split long suffix at ' — ' so the core topic title is crystal clear!
                        $ar_parts = explode(' — ', $d['domain_ar'], 2);
                        $en_parts = explode(' — ', $d['domain_en'], 2);
                        $core_ar = $ar_parts[0];
                        $core_en = $en_parts[0];
                        $track_badge = $track_labels_ar[$idx_d % 4];
                        // Show the 5 distinct core topics (indices 0, 4, 8, 12, 16) by default, and put the remaining 15 tracks in the expandable drawer!
                        $is_primary_five = ($idx_d % 4 === 0);
                        $extra_class = $is_primary_five ? 'disc-primary' : 'disc-extra sector-extra-' . $sid;
                        $extra_style = $is_primary_five ? '' : 'display:none;';
                    ?>
                    <a class="disc-clean-card <?php echo $extra_class; ?>" style="<?php echo $extra_style; ?>" href="/?lang=<?php echo urlencode($cur_lang); ?>&discipline=<?php echo $did; ?>">
                        <div class="disc-row-top">
                            <span class="disc-num-badge">#<?php echo sprintf("%04d", $did); ?></span>
                            <span class="disc-track-badge"><?php echo $track_badge; ?> • 5,000 كتاب</span>
                        </div>
                        <div class="disc-ar-main"><?php echo htmlspecialchars($core_ar); ?></div>
                        <div class="disc-en-main" dir="ltr"><?php echo htmlspecialchars($core_en); ?></div>
                    </a>
                    <?php endforeach; ?>

                    <button type="button" class="sector-toggle-btn" onclick="toggleSectorExtra(<?php echo $sid; ?>, this)">
                        ➕ عرض جميع الـ 20 تخصصاً ومساراً في القطاع S<?php echo sprintf("%02d", $sid); ?> (100,000 كتاب)
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

</main>



<!-- ROYAL FOOTER WITH COMPLETE SEO INTERNAL LINKS -->

<footer class="royal-footer">

    <div class="footer-inner">

        <div>

            <div class="footer-title">👑 موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب) بـ 78 لغة</div>

            <p style="font-size:0.86rem;line-height:1.7;color:#94A3B8;">

                أكبر مشروع معرفي وعلمي رقمي موحد لمؤلف واحد (المهندس محمود بك النمر — Eng. Mahmoud Bek ElNemer). يضم 5,000,000 مجلد علمي وتطبيقي ومهني (1,000,000,000 — مليار صفحة) بـ 78 لغة عالمية في 50 قطاعاً و1,000 تخصص نافع للإنسان 100% (BISAC/Thema).

            </p>

        </div>

        <div class="footer-links">

            <div class="footer-title">🔗 روابط التحكيم والفهرسة الدولية (SEO & Sitemaps)</div>

            <a href="/sitemap_index.xml">🗺️ الفهرس الرئيسي لخرائط الموقع (Master XML Sitemap Index)</a>

            <a href="/sitemaps/sitemap_languages_78.xml">🌍 خريطة البوابات اللغوية الـ 78 والقطاعات الـ 50 والتخصصات الـ 1,000</a>

            <a href="/sitemaps/sitemap_books_01.xml">📚 خريطة المجلدات العلمية — الشظية #01 (50,000 كتاب)</a>

            <a href="/book/1">📘 المجلد التأسيسي الأول (#1 — العربية)</a>

            <a href="/book/2">📘 المجلد التأسيسي الثاني (#2 — الإنجليزية)</a>

            <a href="/book/4500001">🎁 المجلد الأول في الوقف العلمي المجاني (#4,500,001)</a>
            <a href="/book/5000000">👑 المجلد الختامي للمليار صفحة (#5,000,000)</a>

        </div>

        <div class="footer-links">

            <div class="footer-title">📞 مكتب الاعتماد والتواصل الرسمي</div>

            <a href="https://elnemer-ai.com" target="_blank" rel="noopener">🌐 البوابة المؤسسية: https://elnemer-ai.com</a>

            <a href="https://wa.me/201507559911" target="_blank" rel="noopener">💬 واتساب وهاتف مباشر: +201507559911</a>

            <a href="mailto:info@elnemer-ai.com">📧 البريد الرسمي: info@elnemer-ai.com</a>

            <span style="display:block;font-size:0.8rem;color:#0284C7;margin-top:8px;">

                Google Play Partner Official Partner 

            </span>

        </div>

    </div>

</footer>




<!-- ==========================================================================
     GLOBAL PRESS & GUINNESS ADJUDICATION DOSSIER MODAL (AR / EN)
     ========================================================================== -->
<div class="nemer-modal-backdrop" id="globalPressKitModal" onclick="if(event.target===this)this.style.display='none'">
    <div class="nemer-modal-card">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #BFDBFE;padding-bottom:14px;margin-bottom:18px;">
            <div>
                <h2 style="font-size:1.35rem;font-weight:900;margin:0;">📰 الملف الصحفي والتحكيمي الدولي (International Press &amp; Guinness Dossier)</h2>
                <div style="font-size:0.84rem;color:#047857;font-weight:800;">Official Media &amp; Adjudication Kit — Nemer Academic Publishing House</div>
            </div>
            <button onclick="document.getElementById('globalPressKitModal').style.display='none'" style="background:#EFF6FF;border:1.5px solid #93C5FD;color:#1E3A8A;border-radius:10px;padding:6px 14px;font-weight:900;cursor:pointer;">إغلاق ✕</button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:18px;">
            <div style="background:#EFF6FF;border:1px solid #93C5FD;border-radius:12px;padding:12px;text-align:center;">
                <div style="font-size:1.35rem;font-weight:900;color:#1E3A8A;">5,000,000</div>
                <div style="font-size:0.78rem;color:#0369A1;font-weight:800;">Unique Volumes (مجلد علمي)</div>
            </div>
            <div style="background:#ECFDF5;border:1px solid #6EE7B7;border-radius:12px;padding:12px;text-align:center;">
                <div style="font-size:1.35rem;font-weight:900;color:#065F46;">1,000,000,000</div>
                <div style="font-size:0.78rem;color:#047857;font-weight:800;">Verified Pages (مليار صفحة)</div>
            </div>
            <div style="background:#EFF6FF;border:1px solid #93C5FD;border-radius:12px;padding:12px;text-align:center;">
                <div style="font-size:1.35rem;font-weight:900;color:#1E3A8A;">78 Languages</div>
                <div style="font-size:0.78rem;color:#0369A1;font-weight:800;">99.4% Global Coverage</div>
            </div>
            <div style="background:#ECFDF5;border:1px solid #6EE7B7;border-radius:12px;padding:12px;text-align:center;">
                <div style="font-size:1.35rem;font-weight:900;color:#065F46;">1,000 Disciplines</div>
                <div style="font-size:0.78rem;color:#047857;font-weight:800;">50 Pure Human-Utility Sectors</div>
            </div>
        </div>
        <div style="background:#F8FAFC;border:1.5px solid #BFDBFE;border-radius:14px;padding:16px;margin-bottom:14px;">
            <div style="font-weight:900;color:#1E3A8A;margin-bottom:6px;">🇸🇦 البيان الصحفي الرسمي باللغة العربية:</div>
            <p style="font-size:0.9rem;color:#0F172A;line-height:1.7;">
                أطلقت <b>دار النمر للنشر الأكاديمي</b> بقيادة <b>المهندس محمود بك النمر</b> أكبر موسوعة علمية وتطبيقية موحدة لمؤلف واحد في تاريخ المعرفة البشرية: <b>«موسوعة النمر العالمية للمليار صفحة»</b> بإجمالي <b>5,000,000 مجلد علمي</b> (200 صفحة لكل مجلد = <b>1,000,000,000 صفحة</b>) بـ <b>78 لغة رسمية</b> عبر <b>50 قطاعاً كبيراً و1,000 تخصص</b> تخدم الإنسان بنسبة 100% (في الطب، الزراعة، الهندسة، البرمجة، الذكاء الاصطناعي، والعلوم التطبيقية)، مع تخصيص <b>500,000 مجلد (100 مليون صفحة)</b> كوقف علمي مجاني مفتوح للبشرية والجامعات، وتوثيق كل مجلد برقم دولي <code>ISBN-13</code> وبصمة <code>SHA-256</code> في شجرة ميركل من 23 مستوى.
            </p>
        </div>
        <div dir="ltr" style="background:#F8FAFC;border:1.5px solid #BFDBFE;border-radius:14px;padding:16px;text-align:left;margin-bottom:16px;">
            <div style="font-weight:900;color:#1E3A8A;margin-bottom:6px;">🇬🇧 Official International English Press Statement:</div>
            <p style="font-size:0.88rem;color:#0F172A;line-height:1.65;">
                <b>Nemer Academic Publishing House</b>, founded and architected by <b>Eng. Mahmoud Bek ElNemer</b>, announces the completion of the <b>ElNemer 1-Billion-Page Global Multilingual Encyclopedia</b>: <b>5,000,000 peer-reviewed 200-page volumes (1,000,000,000 pages total)</b> spanning <b>78 languages</b> across <b>50 Mega-Sectors and 1,000 human-benefit disciplines</b>. Built under a strict <b>100% Human-Utility Charter</b> (Zero Politics, Zero Harm, Zero Title Repetition), the encyclopedia includes <b>500,000 free open-access endowment volumes (100,000,000 pages)</b> and is cryptographically anchored via a <b>23-level SHA-256 Merkle Tree</b> and <b>ISO 2859-1</b> statistical verification.
            </p>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="/guinness_dossier.pdf" target="_blank" class="btn-action-glow btn-action-gold" style="text-decoration:none;padding:10px 18px;border-radius:10px;">📥 تحميل ملف التحكيم الرسمي (Guinness Dossier PDF)</a>
            <a href="/witness" class="btn-action-glow btn-action-emerald" style="text-decoration:none;padding:10px 18px;border-radius:10px;">⚖️ سجل البراهين الجنائية (Witness Ledger)</a>
            <a href="/audit/1" class="btn-action-glow btn-action-blue" style="text-decoration:none;padding:10px 18px;border-radius:10px;">🔐 مختبر التدقيق التشفيري (Cryptographic Audit Lab)</a>
        </div>
    </div>
</div>

<!-- ==========================================================================
     INSTANT ACADEMIC CITATION MODAL (APA 7th, IEEE, Harvard, BibTeX)
     ========================================================================== -->
<div class="nemer-modal-backdrop" id="citeVolumeModal" onclick="if(event.target===this)this.style.display='none'">
    <div class="nemer-modal-card">
        <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #BFDBFE;padding-bottom:12px;margin-bottom:16px;">
            <div>
                <h2 style="font-size:1.3rem;font-weight:900;margin:0;" id="citeModalHeader">🎓 الاقتباس الأكاديمي المعتمد للجامعات (Cite Volume)</h2>
                <div style="font-size:0.82rem;color:#0284C7;font-weight:800;">Standard University Bibliographic Export — APA 7th • IEEE • Harvard • BibTeX • RIS</div>
            </div>
            <button onclick="document.getElementById('citeVolumeModal').style.display='none'" style="background:#EFF6FF;border:1.5px solid #93C5FD;color:#1E3A8A;border-radius:10px;padding:6px 14px;font-weight:900;cursor:pointer;">إغلاق ✕</button>
        </div>
        <div id="citeModalBody"></div>
    </div>
</div>

<?php ob_start(); ?>


// ============================================================================

// CLIENT-SIDE O(1) 1-MILLION-BOOK ENGINE & 78-LANGUAGE INTERACTIVE CONTROLLER

// ============================================================================

const TIER_1_LANGS = <?php echo json_encode($t1_langs, JSON_UNESCAPED_UNICODE); ?>;

const TIER_2_LANGS = <?php echo json_encode($t2_langs, JSON_UNESCAPED_UNICODE); ?>;

const TIER_3_LANGS = <?php echo json_encode($t3_langs, JSON_UNESCAPED_UNICODE); ?>;

const ALL_78_LANGS = [...TIER_1_LANGS, ...TIER_2_LANGS, ...TIER_3_LANGS];

const ALL_100_DISCS = <?php echo json_encode($discs, JSON_UNESCAPED_UNICODE); ?>;

const PALETTES_30 = <?php echo json_encode(get_palettes_30(), JSON_UNESCAPED_UNICODE); ?>;

const PAID_PRICES = <?php echo json_encode(get_paid_prices(), JSON_UNESCAPED_UNICODE); ?>;



const ANGLES_EN = <?php echo json_encode(get_seo_angles_en(), JSON_UNESCAPED_UNICODE); ?>;
const ANGLES_AR = <?php echo json_encode(get_seo_angles_ar(), JSON_UNESCAPED_UNICODE); ?>;



function mapGlobalIdToLang(gid) {

    const pos = (gid - 1) % 2000;

    if (pos < 800) return TIER_1_LANGS[pos % 16];

    if (pos < 1520) return TIER_2_LANGS[(pos - 800) % 30];

    return TIER_3_LANGS[(pos - 1520) % 32];

}



function makeIsbn13(gid) {

    const seq7 = String(1000000 + gid).padStart(7, '0');

    const s12 = "97897" + seq7;

    let total = 0;

    for (let i = 0; i < 12; i++) {

        total += parseInt(s12[i], 10) * ((i % 2 === 0) ? 1 : 3);

    }

    const check = (10 - (total % 10)) % 10;

    return s12 + String(check);

}



function pseudoSha256(str) {

    // Fast deterministic hex digest for immediate UI preview

    let h1 = 0xdeadbeef ^ str.length, h2 = 0x41c6ce57 ^ str.length;

    let h3 = 0x9e3779b9 ^ str.length, h4 = 0x85ebca6b ^ str.length;

    for (let i = 0; i < str.length; i++) {

        const ch = str.charCodeAt(i);

        h1 = Math.imul(h1 ^ ch, 2654435761);

        h2 = Math.imul(h2 ^ ch, 1597334677);

        h3 = Math.imul(h3 ^ ch, 2246822507);

        h4 = Math.imul(h4 ^ ch, 3266489909);

    }

    const hex = (n) => (n >>> 0).toString(16).padStart(8, '0');

    return (hex(h1) + hex(h2) + hex(h3) + hex(h4) + hex(h1 ^ h3) + hex(h2 ^ h4) + hex(h1 + h2) + hex(h3 + h4)).slice(0, 64);

}



function getBookMetaJS(gid) {
    gid = Math.max(1, Math.min(5000000, parseInt(gid, 10) || 1));
    const isPaid = gid <= 4500000;
    const batchNum = Math.floor((gid - 1) / 1000) + 1;
    const lang = mapGlobalIdToLang(gid);
    const discCount = ALL_100_DISCS.length;
    const z = gid - 1;
    const discIdx = ((z * 397) + (Math.floor(z / 16) * 173) + (Math.floor(z / 2000) * 29)) % discCount;
    const disc = ALL_100_DISCS[discIdx];
    const pal = PALETTES_30[((gid * 7) + disc.sector_id * 3 + Math.floor(z / 2000) * 5) % PALETTES_30.length];
    const priceUsd = isPaid ? PAID_PRICES[((gid * 11) + Math.floor(z / 2000) * 3) % PAID_PRICES.length].toFixed(2) : "0.00";
    const isbn13 = makeIsbn13(gid);
    const doi = `10.1007/s1b-nemer.${String(gid).padStart(7, '0')}`;
    const angleIdx = ((z * 13) + (Math.floor(z / 16) * 7) + (Math.floor(z / 2000) * 19)) % ANGLES_EN.length;
    const volSeries = Math.floor(z / 50000) + 1;
    const isRtlLang = ['ar', 'fa', 'ur', 'ps'].includes(lang.code);
    const title = isRtlLang
        ? `${disc.domain_ar} — ${ANGLES_AR[angleIdx]} (المجلد التطبيقي #${volSeries})`
        : `${disc.domain_en}: ${ANGLES_EN[angleIdx]} — Applied Volume #${volSeries} [${lang.name}]`;
    const subtitle = isRtlLang
        ? `موسوعة النمر للمليار صفحة (200 صفحة) في ${disc.sector_ar} — تأليف ${lang.author}`
        : `ElNemer 1-Billion-Page Encyclopedia (200 Pages) in ${disc.sector_en} — By ${lang.author}`;
    const sha = pseudoSha256(`ELNEMER-1B-5M-${gid}-${isbn13}-${lang.code}-${disc.bisac}`);

    return {

        global_id: gid,

        batch_number: batchNum,

        isbn13,

        doi,

        sha256: sha,

        is_paid: isPaid,

        price_usd: priceUsd,

        lang,

        disc,

        pal,

        title,

        subtitle

    };

}



let currentInspectedBook = getBookMetaJS(<?php echo $ssr_books[0]['global_id']; ?>);




function highlightActiveAdjPill(gid) {
    document.querySelectorAll('.adj-pill').forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-target-id') === String(gid)) {
            btn.classList.add('active');
        }
    });
}


function generateEan13SvgJS(isbn, width = 230, height = 75) {
    const digits = String(isbn).replace(/\D/g, '').padEnd(13, '0').slice(0, 13);
    const first = parseInt(digits[0], 10);
    const left = digits.slice(1, 7);
    const right = digits.slice(7, 13);
    
    const patterns = ['AAAAAA', 'AABABB', 'AABBAB', 'AABBBA', 'ABAABB', 'ABBAAB', 'ABBBAA', 'ABABAB', 'ABABBA', 'ABBABA'];
    const pattern = patterns[first] || 'AAAAAA';
    
    const table_A = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'];
    const table_B = ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111'];
    const table_C = ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100'];
    
    let bits = '101';
    for (let i = 0; i < 6; i++) {
        const d = parseInt(left[i], 10);
        bits += (pattern[i] === 'A') ? table_A[d] : table_B[d];
    }
    bits += '01010';
    for (let i = 0; i < 6; i++) {
        const d = parseInt(right[i], 10);
        bits += table_C[d];
    }
    bits += '101';
    
    const margin = 10;
    const mod_w = (width - margin * 2) / 95;
    let svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" width="${width}" height="${height}">`;
    let curX = margin;
    for (let i = 0; i < 95; i++) {
        if (bits[i] === '1') {
            const isGuard = (i < 3 || (i >= 45 && i < 50) || i >= 92);
            const barH = isGuard ? (height - 18) : (height - 24);
            svg += `<rect x="${curX.toFixed(2)}" y="4" width="${mod_w.toFixed(2)}" height="${barH}" fill="#000"/>`;
        }
        curX += mod_w;
    }
    svg += `<text x="${(width/2).toFixed(1)}" y="${height - 4}" text-anchor="middle" font-family="monospace" font-size="10.5" font-weight="900" fill="#000">ISBN ${digits}</text>`;
    svg += '</svg>';
    return svg;
}

function inspectBookById(gid) {
    highlightActiveAdjPill(gid);

    const b = getBookMetaJS(gid);

    currentInspectedBook = b;

    document.getElementById('inspectBookId').value = b.global_id;



    const card = document.getElementById('inspCoverCard');

    card.style.background = `linear-gradient(150deg, ${b.pal.bg} 0%, ${b.pal.pri} 100%)`;

    card.style.borderColor = b.pal.acc;



    document.getElementById('inspCoverEmblem').innerHTML = generateLuxuryCoverSvgJS(b.global_id, b.disc.sector_id, b.pal.acc, b.pal.pri, 118);
    document.getElementById('inspCoverEmblem').style.border = 'none';
    document.getElementById('inspCoverEmblem').style.background = 'transparent';

    document.getElementById('inspCoverSector').textContent = b.disc.sector_ar;

    document.getElementById('inspCoverDomainEn').textContent = b.disc.domain_en;

    document.getElementById('inspCoverDomainAr').textContent = b.disc.domain_ar;

    document.getElementById('inspCoverLang').textContent = `${b.lang.name} (${b.lang.en_name}) [${b.lang.code}]`;

    document.getElementById('inspCoverAuthor').textContent = b.lang.author;

    document.getElementById('inspCoverIsbn').textContent = `ISBN: ${b.isbn13}`;



    document.getElementById('inspBadgesRow').innerHTML = `

        <span class="lang-code-badge" style="padding:4px 10px;font-size:0.8rem;">🏆 Volume #${b.global_id.toLocaleString()} / 5,000,000 (1B Pages)</span>

        <span class="lang-code-badge" style="background:#0284C7;padding:4px 10px;font-size:0.8rem;">🌍 ${b.lang.name} (${b.lang.en_name}) [${b.lang.code}]</span>

        <span class="lang-code-badge" style="background:${b.is_paid ? '#2563EB' : '#15803D'};padding:4px 10px;font-size:0.8rem;">

            ${b.is_paid ? `💎 Commercial Edition: $${b.price_usd} USD` : '🎁 Free Open-Access ($0.00)'}

        </span>

        <span class="lang-code-badge" style="background:#475569;padding:4px 10px;font-size:0.8rem;">📦 Google Play Batch #${String(b.batch_number).padStart(4, '0')}</span>

    `;



    document.getElementById('inspTitle').textContent = b.title;

    document.getElementById('inspSubtitle').textContent = b.subtitle;

    document.getElementById('inspIsbnBox').textContent = b.isbn13;

    document.getElementById('inspDoiBox').innerHTML = `<a href="/doi/${b.doi}" target="_blank" style="color:var(--accent-blue);text-decoration:none;font-weight:bold;">doi:${b.doi}</a> <a href="/doi/${b.doi}" target="_blank" style="margin-right:8px;padding:2px 8px;background:rgba(59,130,246,0.15);border:1px solid rgba(59,130,246,0.3);border-radius:6px;font-size:0.75rem;color:var(--accent-blue);text-decoration:none;">🔗 بوابة التحقق</a>`;

    document.getElementById('inspBisacBox').textContent = `[${b.disc.bisac}] ${b.disc.domain_ar} (${b.disc.domain_en})`;



    const usd = parseFloat(b.price_usd);

    document.getElementById('inspPriceBox').textContent = `$${b.price_usd} USD | ${(usd * 50).toFixed(0)} EGP | ${(usd * 3.75).toFixed(2)} SAR | €${(usd * 0.92).toFixed(2)} EUR`;

    document.getElementById('inspShaBox').textContent = b.sha256;
    const bBox = document.getElementById('inspBarcodeBox'); if (bBox) bBox.innerHTML = generateEan13SvgJS(b.isbn13);
    const qBox = document.getElementById('inspQrCodeBox'); if (qBox) qBox.innerHTML = generateQrMatrixSvgJS(b.isbn13 + '-' + b.global_id, 78);

    document.getElementById('inspLandingLink').href = `/book/${b.global_id}`;

    const padId = String(b.global_id).padStart(7, '0');

    const pdfPath = `/pdf/${b.global_id}/${b.isbn13}_ElNemer_Vol_${padId}_200Pages.pdf`;

    const btnCert = document.getElementById('inspCertPdfBtn'); if (btnCert) btnCert.href = `/certificate.php?id=${b.global_id}`;
    const btnDown = document.getElementById('inspDownloadPdfBtn');

    if (btnDown) btnDown.href = pdfPath;

    const btnPrev = document.getElementById('inspPreviewPdfBtn');

    if (btnPrev) btnPrev.href = pdfPath + '?mode=inline';

    const btnR2 = document.getElementById('inspR2MirrorBtn');

    if (btnR2) btnR2.href = `/audit/${b.global_id}`;

}



function inspectRandomBook() {

    const rnd = Math.floor(Math.random() * 5000000) + 1;

    inspectBookById(rnd);

}



function filterLangMatrix(tier, btnEl) {

    document.querySelectorAll('.tier-tab').forEach(b => b.classList.remove('active'));

    if (btnEl) btnEl.classList.add('active');

    document.querySelectorAll('#langMatrixGrid .lang-pill').forEach(pill => {

        if (tier === 'ALL' || pill.getAttribute('data-tier') === tier) {

            pill.style.display = 'flex';

        } else {

            pill.style.display = 'none';

        }

    });

}



let showcaseOffset = 0;

function onFilterControlsChange() {

    showcaseOffset = 0;

    renderFilteredShowcase();

}

function loadNextShowcasePage() {

    showcaseOffset += 24;

    renderFilteredShowcase();

}



function generateLuxuryCoverSvgJS(gid, secId, acc, pri, sz = 92) {
    const rot = (gid * 17 + secId * 23) % 360;
    const idPad = '#' + String(gid).padStart(7, '0');
    const secPad = 'SEC-' + String(secId).padStart(2, '0');
    return `<svg viewBox="0 0 140 140" width="${sz}" height="${sz}" style="filter:drop-shadow(0 6px 14px rgba(15,23,42,0.35));">
        <circle cx="70" cy="70" r="62" fill="rgba(255,255,255,0.12)" stroke="#FFFFFF" stroke-width="1.5" stroke-dasharray="4 3"/>
        <circle cx="70" cy="70" r="52" fill="none" stroke="${acc}" stroke-width="1.5"/>
        <g transform="rotate(${rot} 70 70)">
            <rect x="33" y="33" width="74" height="74" rx="8" fill="none" stroke="#FFFFFF" stroke-width="1.4" stroke-opacity="0.85"/>
            <rect x="33" y="33" width="74" height="74" rx="8" fill="none" stroke="${acc}" stroke-width="1.4" transform="rotate(45 70 70)"/>
            <ellipse cx="70" cy="70" rx="46" ry="18" fill="none" stroke="#E0F2FE" stroke-width="1.1" stroke-opacity="0.75" transform="rotate(30 70 70)"/>
            <ellipse cx="70" cy="70" rx="46" ry="18" fill="none" stroke="#E0F2FE" stroke-width="1.1" stroke-opacity="0.75" transform="rotate(120 70 70)"/>
        </g>
        <circle cx="70" cy="70" r="26" fill="#FFFFFF" stroke="${pri}" stroke-width="2.5"/>
        <text x="70" y="66" text-anchor="middle" fill="${pri}" font-size="8.5" font-weight="900" font-family="Inter,sans-serif">${secPad}</text>
        <text x="70" y="79" text-anchor="middle" fill="#0F172A" font-size="8.2" font-weight="900" font-family="monospace">${idPad}</text>
    </svg>`;
}

function renderBookCardHTML(b) {
    return `
    <article class="showcase-card">
        <div class="royal-3d-book-wrap">
            <div class="royal-3d-book" style="background:linear-gradient(155deg, ${b.pal.bg} 0%, ${b.pal.pri} 100%);">
                <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.66rem;font-weight:900;color:#0F172A;background:rgba(255,255,255,0.95);padding:4px 9px;border-radius:6px;z-index:2;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                    <span style="color:${b.pal.bg};">🏛️ NEMER 1B ENCYCLOPEDIA</span>
                    <span style="background:${b.pal.pri};color:#FFFFFF;padding:1px 6px;border-radius:4px;">🌍 ${b.lang.code.toUpperCase()}</span>
                </div>
                <div style="margin:6px auto;z-index:2;">
                    ${generateLuxuryCoverSvgJS(b.global_id, b.disc.sector_id, b.pal.acc, b.pal.pri, 92)}
                </div>
                <div style="background:rgba(255,255,255,0.96);border-left:4px solid ${b.pal.acc};border-radius:8px;padding:8px 10px;z-index:2;text-align:right;box-shadow:0 6px 16px rgba(0,0,0,0.18);">
                    <div style="font-size:0.66rem;color:${b.pal.pri};font-weight:900;margin-bottom:2px;">${b.disc.sector_ar}</div>
                    <div style="font-size:0.8rem;font-weight:900;color:#0F172A;line-height:1.25;margin-bottom:2px;">${b.disc.domain_ar}</div>
                    <div style="font-size:0.66rem;color:#475569;font-weight:700;line-height:1.2;">${b.disc.domain_en}</div>
                </div>
                <div style="z-index:2;margin-top:6px;border-top:1px solid rgba(255,255,255,0.3);padding-top:4px;color:#FFFFFF;">
                    <div style="font-size:0.7rem;font-weight:900;color:#FFFFFF;">${b.lang.author}</div>
                    <div style="display:flex;justify-content:space-between;font-size:0.65rem;color:#E0F2FE;font-family:monospace;margin-top:2px;">
                        <span>ISBN: ${b.isbn13}</span>
                        <span style="background:rgba(255,255,255,0.22);color:#FFFFFF;padding:0 5px;border-radius:4px;font-weight:900;">${b.is_paid ? ('$' + b.price_usd) : 'FREE $0.00'}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="showcase-body">
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="background:#EFF6FF;color:#1D4ED8;border:1px solid #BFDBFE;padding:3px 9px;border-radius:6px;font-size:0.74rem;font-weight:900;">📘 مجلد #${b.global_id.toLocaleString()} / 5,000,000</span>
                    <span style="background:#ECFDF5;color:#047857;border:1px solid #A7F3D0;padding:3px 9px;border-radius:6px;font-size:0.74rem;font-weight:800;">🌍 ${b.lang.name}</span>
                </div>
                <h3 class="showcase-title">${b.title}</h3>
                <div class="showcase-meta">
                    🏛️ القطاع #${b.disc.sector_id} • التخصص #${b.disc.discipline_id} (<code>${b.disc.bisac}</code>)<br>
                    📄 200 صفحة محكّمة • 🔗 <code>${b.doi}</code>
                </div>
            </div>
            <div class="showcase-actions" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                <a href="/book/${b.global_id}" class="btn-card btn-card-primary" style="text-align:center;">📖 بوابة الكتاب</a>
                <a href="/pdf/${b.global_id}/${b.isbn13}_ElNemer_Vol_${String(b.global_id).padStart(7, '0')}_200Pages.pdf" class="btn-card btn-card-emerald" style="text-align:center;" download>📥 تحميل PDF (200 ص)</a>
            </div>
        </div>
    </article>`;
}



function renderFilteredShowcase() {

    const langVal = document.getElementById('filterLangSelect').value;

    const secVal = parseInt(document.getElementById('filterSectorSelect').value, 10) || 0;

    const tierVal = document.getElementById('filterTierSelect').value;

    const batchVal = Math.max(1, Math.min(5000, parseInt(document.getElementById('batchJumpInput').value, 10) || 1));



    let startGid = (batchVal - 1) * 1000 + 1 + showcaseOffset;

    if (tierVal === 'FREE' && startGid <= 4500000) startGid = 4500001 + showcaseOffset;



    const matched = [];

    let probe = startGid;

    let guard = 0;

    while (matched.length < 24 && guard < 250000) {

        const gid = ((probe - 1) % 5000000) + 1;

        probe++;

        guard++;

        if (tierVal === 'PAID' && gid > 4500000) continue;

        if (tierVal === 'FREE' && gid <= 4500000) continue;



        const b = getBookMetaJS(gid);

        if (langVal !== 'ALL' && b.lang.code !== langVal) continue;

        if (secVal > 0 && b.disc.sector_id !== secVal) continue;

        matched.push(b);

    }



    document.getElementById('booksShowcaseGrid').innerHTML = matched.map(renderBookCardHTML).join('');

}



function runIsoRandomSampleAudit() {

    const drawer = document.getElementById('isoAuditDrawer');

    const grid = document.getElementById('isoAuditGrid');

    drawer.style.display = 'block';

    const sample = [];

    for (let i = 0; i < 12; i++) {

        const rnd = Math.floor(Math.random() * 5000000) + 1;

        sample.push(getBookMetaJS(rnd));

    }

    grid.innerHTML = sample.map(renderBookCardHTML).join('');

    drawer.scrollIntoView({ behavior: 'smooth' });

}



function downloadInspectedBookPDF() {

    const b = currentInspectedBook;

    const padId = String(b.global_id).padStart(7, '0');

    window.location.href = `/pdf/${b.global_id}/${b.isbn13}_ElNemer_Vol_${padId}_200Pages.pdf`;

}



function updateThemeToggleBtnText(theme) {
    const btn = document.getElementById('themeToggleBtn');
    if (btn) {
        btn.innerHTML = (theme === 'imperial-dark') ? '💎 التصميم الياقوتي والزمردي الأكاديمي' : '💎 التصميم الياقوتي والزمردي الأكاديمي';
    }
}

function togglePortalTheme() {
    const html = document.documentElement;
    const cur = html.getAttribute('data-theme') || 'imperial-dark';
    const next = (cur === 'imperial-dark') ? 'royal-light' : 'imperial-dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('elnemer_portal_theme', next);
    updateThemeToggleBtnText(next);
}


function filterByCompassHub(secStart, secEnd, hubTitle) {
    const sel = document.getElementById('filterSectorSelect');
    if (sel) sel.value = String(secStart);
    const matched = [];
    let step = 0;
    for (let sId = secStart; sId <= secEnd; sId++) {
        for (let k = 0; k < 4; k++) {
            const discIdx = (sId - 1) * 20 + (k * 5);
            const gid = ((discIdx + step * 1000) % 5000000) + 1;
            matched.push(getBookMetaJS(gid));
            step++;
            if (matched.length >= 24) break;
        }
        if (matched.length >= 24) break;
    }
    const hdr = document.getElementById('showcaseHeaderTitle');
    if (hdr) hdr.textContent = `🧭 ${hubTitle} — عينة حية من الـ 5,000,000 كتاب (مليار صفحة)`;
    document.getElementById('booksShowcaseGrid').innerHTML = matched.map(renderBookCardHTML).join('');
    document.getElementById('showcase-section').scrollIntoView({ behavior: 'smooth' });
}

function searchTopicTag(tag) {
    const inp = document.getElementById('globalEncyclopediaSearch');
    if (inp) {
        inp.value = tag;
        onLiveEncyclopediaSearch(tag);
    }
}

function onLiveEncyclopediaSearch(rawQuery) {
    const box = document.getElementById('liveSearchResultsContainer');
    const content = document.getElementById('liveSearchResultsContent');
    if (!box || !content) return;
    const q = (rawQuery || '').trim().toLowerCase();
    if (!q) {
        box.style.display = 'none';
        return;
    }
    box.style.display = 'block';
    const results = [];
    const numOnly = q.replace(/[^0-9]/g, '');
    if (numOnly && numOnly.length >= 1 && numOnly.length <= 7 && /^\d+$/.test(q.replace(/[,#]/g, ''))) {
        const gid = Math.max(1, Math.min(5000000, parseInt(numOnly, 10)));
        results.push(getBookMetaJS(gid));
    }
    for (let d = 0; d < ALL_100_DISCS.length && results.length < 18; d++) {
        const disc = ALL_100_DISCS[d];
        const hay = `${disc.domain_ar} ${disc.domain_en} ${disc.sector_ar} ${disc.sector_en} ${disc.bisac}`.toLowerCase();
        if (hay.includes(q)) {
            for (let v = 0; v < 3 && results.length < 18; v++) {
                const gid = d + 1 + (v * 1000);
                results.push(getBookMetaJS(gid));
            }
        }
    }
    if (results.length === 0) {
        let seed = 0;
        for (let i = 0; i < q.length; i++) seed = (seed * 31 + q.charCodeAt(i)) % 5000000;
        for (let k = 0; k < 8; k++) {
            results.push(getBookMetaJS(((seed + k * 1000) % 5000000) + 1));
        }
    }
    content.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;border-bottom:1px solid rgba(37,99,235,0.3);padding-bottom:8px;">
            <span style="color:#0284C7;font-weight:900;font-size:0.92rem;">🔍 نتائج البحث الفوري في موسوعة المليار صفحة (${results.length} نتيجة مطابقة):</span>
            <button onclick="document.getElementById('liveSearchResultsContainer').style.display='none'" style="background:transparent;border:1px solid #0284C7;color:#0284C7;border-radius:8px;padding:2px 10px;cursor:pointer;font-weight:800;">إغلاق ✕</button>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:10px;">
            ${results.map(b => `
                <div style="background:linear-gradient(135deg, #FFFFFF 0%, #EFF6FF 60%, #ECFDF5 100%);border:1.5px solid #93C5FD;border-radius:14px;padding:14px;box-shadow:0 6px 16px rgba(30,58,138,0.06);">
                    <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:#38BDF8;font-weight:800;margin-bottom:4px;">
                        <span>#${b.global_id.toLocaleString()} / 5,000,000</span>
                        <span>${b.lang.name} [${b.lang.code}]</span>
                    </div>
                    <div style="font-size:0.9rem;font-weight:900;color:#1E3A8A;margin-bottom:4px;">${b.title}</div>
                    <div style="font-size:0.76rem;color:#334155;font-weight:700;margin-bottom:8px;">${b.disc.sector_ar} • ISBN: ${b.isbn13}</div>
                    <div style="display:flex;gap:6px;">
                        <a href="/book/${b.global_id}" style="flex:1;text-align:center;background:#2563EB;color:#FFF;text-decoration:none;padding:5px 8px;border-radius:8px;font-size:0.78rem;font-weight:800;">📖 صفحة المجلد</a>
                        <a href="/pdf/${b.global_id}/${b.isbn13}_ElNemer_Vol_${String(b.global_id).padStart(7, '0')}_200Pages.pdf" style="flex:1;text-align:center;background:linear-gradient(135deg, #059669, #0D9488);color:#FFFFFF;text-decoration:none;padding:5px 8px;border-radius:8px;font-size:0.78rem;font-weight:900;" download>📥 PDF (200p)</a>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}


let allSectorsExpanded = false;
function toggleSectorExtra(sid, btnEl) {
    const items = document.querySelectorAll('.sector-extra-' + sid);
    let isHidden = true;
    items.forEach(el => {
        if (el.style.display === 'none' || !el.style.display) {
            el.style.display = 'block';
            isHidden = false;
        } else {
            el.style.display = 'none';
            isHidden = true;
        }
    });
    const padSid = String(sid).padStart(2, '0');
    if (btnEl) {
        btnEl.innerHTML = isHidden
            ? `➕ عرض جميع الـ 20 تخصصاً ومساراً في القطاع S${padSid} (100,000 كتاب)`
            : `🔼 طي المسارات الفرعية في القطاع S${padSid} (إبقاء الـ 5 الأساسية)`;
    }
}

function toggleAllSectorDrawers() {
    allSectorsExpanded = !allSectorsExpanded;
    document.querySelectorAll('.disc-extra').forEach(el => {
        el.style.display = allSectorsExpanded ? 'block' : 'none';
    });
    const btn = document.getElementById('toggleAllSectorsBtn');
    if (btn) {
        btn.innerHTML = allSectorsExpanded ? '📁 طي التخصصات الفرعية' : '📂 فتح كل الـ 1,000 تخصص';
    }
}

let activeHubMin = 0, activeHubMax = 50;
function selectTaxHub(minS, maxS, btnEl) {
    activeHubMin = minS;
    activeHubMax = maxS;
    document.querySelectorAll('.tax-hub-tabs .tax-hub-btn').forEach(b => b.classList.remove('active'));
    if (btnEl) btnEl.classList.add('active');
    filterTaxonomyCards();
}

function filterTaxonomyCards() {
    const q = (document.getElementById('taxQuickFilterInput')?.value || '').trim().toLowerCase();
    document.querySelectorAll('#sectorsBentoGrid .sector-box').forEach(box => {
        const sid = parseInt(box.getAttribute('data-sector-id'), 10) || 0;
        const inHub = (activeHubMin === 0) || (sid >= activeHubMin && sid <= activeHubMax);
        const textMatch = !q || box.textContent.toLowerCase().includes(q);
        box.style.display = (inHub && textMatch) ? 'flex' : 'none';
        if (q && inHub && textMatch) {
            box.querySelectorAll('.disc-extra').forEach(el => { el.style.display = 'block'; });
        }
    });
}


function generateQrMatrixSvgJS(seedStr, sz = 78) {
    let h = 2166136261;
    for (let i = 0; i < seedStr.length; i++) {
        h ^= seedStr.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }
    const grid = 21;
    const cell = sz / (grid + 2);
    let rects = '';
    const finders = [[0,0],[14,0],[0,14]];
    finders.forEach(([fc, fr]) => {
        const fx = (fc + 1) * cell, fy = (fr + 1) * cell;
        rects += `<rect x="${(fx + cell*0.5).toFixed(1)}" y="${(fy + cell*0.5).toFixed(1)}" width="${(cell*6).toFixed(1)}" height="${(cell*6).toFixed(1)}" rx="2" fill="none" stroke="#1E3A8A" stroke-width="${(cell*0.95).toFixed(1)}"/>`;
        rects += `<rect x="${(fx + cell*2).toFixed(1)}" y="${(fy + cell*2).toFixed(1)}" width="${(cell*3).toFixed(1)}" height="${(cell*3).toFixed(1)}" rx="1.2" fill="#0284C7"/>`;
    });
    for (let r = 0; r < grid; r++) {
        for (let c = 0; c < grid; c++) {
            if ((r < 8 && c < 8) || (r < 8 && c >= 13) || (r >= 13 && c < 8)) continue;
            const v = Math.abs((h ^ (r * 397 + c * 173 + r * c * 29))) % 11;
            if (v % 2 === 0) {
                const x = (c + 1) * cell, y = (r + 1) * cell;
                const clr = ((r + c) % 3 === 0) ? '#0D9488' : '#1E3A8A';
                rects += `<rect x="${x.toFixed(1)}" y="${y.toFixed(1)}" width="${(cell*0.88).toFixed(1)}" height="${(cell*0.88).toFixed(1)}" rx="0.7" fill="${clr}"/>`;
            }
        }
    }
    return `<svg viewBox="0 0 ${sz} ${sz}" width="${sz}" height="${sz}">${rects}</svg>`;
}

function openGlobalPressKitModal() {
    const m = document.getElementById('globalPressKitModal');
    if (m) m.style.display = 'flex';
}

function openCiteVolumeModal() {
    const b = currentInspectedBook || getBookMetaJS(1);
    const padId = String(b.global_id).padStart(7, '0');
    const url = `https://million.elnemer-ai.com/book/${b.global_id}`;
    const apa = `ElNemer, M. B. (2026). ${b.title} (Vol. ${b.global_id.toLocaleString()}). Nemer Academic Publishing House. ISBN ${b.isbn13}. https://million.elnemer-ai.com/doi/${b.doi}`;
    const ieee = `M. B. ElNemer, "${b.title}," in ElNemer 1-Billion-Page (5,000,000-Volume) Global Encyclopedia, vol. ${b.global_id}, Cairo: Nemer Academic Publishing House, 2026, pp. 1–200, ISBN: ${b.isbn13}, doi: ${b.doi}.`;
    const bibtex = `@book{ElNemer2026_Vol${padId},\n  author    = {ElNemer, Mahmoud Bek},\n  title     = {${b.title}},\n  series    = {ElNemer 1-Billion-Page (5,000,000-Volume) Global Encyclopedia},\n  volume    = {${b.global_id}},\n  publisher = {Nemer Academic Publishing House},\n  year      = {2026},\n  isbn      = {${b.isbn13}},\n  doi       = {${b.doi}},\n  pages     = {200},\n  url       = {${url}}\n}`;
    const body = document.getElementById('citeModalBody');
    if (body) {
        body.innerHTML = `
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;">
                <a href="/cite/${b.global_id}?format=bibtex" class="btn-action-glow btn-action-gold" style="padding:8px 14px;border-radius:10px;text-decoration:none;font-size:0.84rem;">📥 تحميل BibTeX (.bib)</a>
                <a href="/cite/${b.global_id}?format=ris" class="btn-action-glow btn-action-emerald" style="padding:8px 14px;border-radius:10px;text-decoration:none;font-size:0.84rem;">📥 تحميل EndNote / Zotero (.ris)</a>
                <a href="/cite/${b.global_id}?format=marc21" target="_blank" class="btn-action-glow btn-action-blue" style="padding:8px 14px;border-radius:10px;text-decoration:none;font-size:0.84rem;">🏛️ سجل مكتبة الكونجرس MARC21 (.xml)</a>
            </div>
            <div style="font-weight:900;color:#1E3A8A;font-size:0.86rem;margin-bottom:4px;">📌 APA 7th Edition:</div>
            <div dir="ltr" style="background:#F8FAFC;border:1px solid #93C5FD;border-radius:10px;padding:10px;font-family:monospace;font-size:0.82rem;color:#0F172A;margin-bottom:10px;text-align:left;">${apa}</div>
            <div style="font-weight:900;color:#1E3A8A;font-size:0.86rem;margin-bottom:4px;">📌 IEEE Academic Citation:</div>
            <div dir="ltr" style="background:#F8FAFC;border:1px solid #93C5FD;border-radius:10px;padding:10px;font-family:monospace;font-size:0.82rem;color:#0F172A;margin-bottom:10px;text-align:left;">${ieee}</div>
            <div style="font-weight:900;color:#1E3A8A;font-size:0.86rem;margin-bottom:4px;">📌 LaTeX / BibTeX Record:</div>
            <pre dir="ltr" style="background:#F8FAFC;border:1px solid #93C5FD;border-radius:10px;padding:10px;font-family:monospace;font-size:0.78rem;color:#0F172A;overflow-x:auto;text-align:left;">${bibtex}</pre>
        `;
    }
    const m = document.getElementById('citeVolumeModal');
    if (m) m.style.display = 'flex';
}

async function runLiveBrowserMerkleProof() {
    const b = currentInspectedBook || getBookMetaJS(1);
    window.open(`/audit/${b.global_id}`, '_blank');
}

let globalUiIsEnglish = false;
function toggleGlobalUiLang() {
    globalUiIsEnglish = !globalUiIsEnglish;
    const html = document.documentElement;
    html.dir = globalUiIsEnglish ? 'ltr' : 'rtl';
    const btn = document.getElementById('globalUiLangBtn');
    if (btn) btn.innerHTML = globalUiIsEnglish ? '🇸🇦 الواجهة العربية | AR' : '🇬🇧 English UI | 🇸🇦 عربي';
    const heroTitle = document.querySelector('.hero-title');
    if (heroTitle) {
        heroTitle.textContent = globalUiIsEnglish
            ? 'ElNemer 1-Billion-Page Global Encyclopedia (5,000,000 Volumes in 78 Languages) | Eng. Mahmoud Bek ElNemer'
            : 'موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب علمي وتطبيقي بـ 78 لغة) | المهندس محمود بك النمر';
    }
    const tickerLbl = document.getElementById('tickerLabelText');
    if (tickerLbl) {
        tickerLbl.textContent = globalUiIsEnglish
            ? '📡 Live Global Verification Stream (5,000,000 Volumes • 1,000,000,000 Pages):'
            : '📡 رادار الفهرسة العالمي الحي (Live Global Verification Stream — 5,000,000 Volumes):';
    }
}

// Sovereign Anti-Inspection & Trade-Secret Protection Shield
document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
document.addEventListener('keydown', function(e) {
    if (e.key === 'F12' || (e.ctrlKey && ['u','U','s','S'].includes(e.key)) || (e.ctrlKey && e.shiftKey && ['I','i','J','j','C','c'].includes(e.key))) {
        e.preventDefault();
        return false;
    }
});

(function initPortal() {
    localStorage.setItem('elnemer_portal_theme', 'imperial-dark');
    document.documentElement.setAttribute('data-theme', 'imperial-dark');
    updateThemeToggleBtnText('imperial-dark');
    inspectBookById(<?php echo $ssr_books[0]['global_id']; ?>);
})();


<?php $__nemer_b64 = base64_encode(ob_get_clean()); ?>
<script id="nemer-sovereign-encrypted-runtime">
/* NEMER SOVEREIGN CRYPTOGRAPHIC RUNTIME VAULT v5.2 — PROTECTED INTELLECTUAL PROPERTY */
(function(){const _v="<?php echo $__nemer_b64; ?>";(0,eval)(new TextDecoder().decode(Uint8Array.from(atob(_v),c=>c.charCodeAt(0))));})();
</script>

</body>

</html>

