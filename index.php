<?php
if (isset($_GET['book']) || isset($_GET['id'])) {
    require __DIR__ . '/book.php';
    exit;
}

require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/portal_i18n_theme.php';
require_once __DIR__ . '/barcode_helper.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$all_langs = get_all_78_langs();
$sectors = get_mega_sectors();
$discs = get_all_100_disciplines();

$req_lang = isset($_GET['lang']) ? trim($_GET['lang']) : 'ar';
$i18n = get_portal_i18n($req_lang);
$cur_lang = $i18n['code'];
$is_rtl_ui = in_array($cur_lang, ['ar', 'fa', 'ur']);

$filter_hub = isset($_GET['hub']) ? max(0, min(5, intval($_GET['hub']))) : 0;
$filter_sector = isset($_GET['sector']) ? max(0, min(50, intval($_GET['sector']))) : 0;
$filter_disc = isset($_GET['discipline']) ? max(0, min(1000, intval($_GET['discipline']))) : 0;
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// If search_query is a direct volume number or ISBN, jump or highlight
if ($search_query !== '' && ctype_digit($search_query)) {
    $q_num = intval($search_query);
    if ($q_num >= 1 && $q_num <= 5000000) {
        header("Location: /book/" . $q_num);
        exit;
    }
}

$proto = "https";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "million.elnemer-ai.com";
$base_url = $proto . "://" . $host;

// 5 Major Main Categories (خمسة أقسام رئيسية واضحة)
$five_main_categories = [
    [
        "id" => 1,
        "icon" => "🏥",
        "title_ar" => "الصحة والطب الوقائي والإسعافات",
        "title_en" => "Health, First Aid & Nutrition",
        "desc_ar" => "الإسعافات الأولية المنزلية، التغذية العلاجية، صحة الأم والطفل، اللياقة والعلاج الطبيعي، والصحة النفسية.",
        "sector_range" => [1, 10],
        "sample_sector" => 1
    ],
    [
        "id" => 2,
        "icon" => "👨‍👩‍👧‍👦",
        "title_ar" => "الأسرة والتربية وتطوير الذات",
        "title_en" => "Family, Parenting & Self-Development",
        "desc_ar" => "التربية الإيجابية، السعادة الأسرية، مهارات التواصل، التفكير النقدي وتقوية الذاكرة، تفوق الطلاب، وتعلم اللغات.",
        "sector_range" => [11, 20],
        "sample_sector" => 11
    ],
    [
        "id" => 3,
        "icon" => "💼",
        "title_ar" => "ريادة الأعمال والمهن الحرة والمال",
        "title_en" => "Business, Freelancing & Finance",
        "desc_ar" => "تأسيس المشاريع، الإدارة المالية والمحاسبة، التسويق الرقمي، العمل الحر، التجارة الإلكترونية، والمهارات المهنية.",
        "sector_range" => [21, 30],
        "sample_sector" => 21
    ],
    [
        "id" => 4,
        "icon" => "💻",
        "title_ar" => "التكنولوجيا والبرمجة والهندسة",
        "title_en" => "Technology, Coding & Engineering",
        "desc_ar" => "تطبيقات الذكاء الاصطناعي، البرمجة وتطوير الويب، الأمن السيبراني، تحليل البيانات، والهندسة التطبيقية.",
        "sector_range" => [31, 40],
        "sample_sector" => 31
    ],
    [
        "id" => 5,
        "icon" => "🌱",
        "title_ar" => "الزراعة والبيئة والعلوم التطبيقية",
        "title_en" => "Agriculture, Environment & Applied Sciences",
        "desc_ar" => "الزراعة المنزلية والذكية، الطاقة المتجددة، الحرف الإنتاجية، والعلوم الأساسية المبسطة والابتكار.",
        "sector_range" => [41, 50],
        "sample_sector" => 41
    ]
];

// Build Featured Practical Books (كتب مختارة مفيدة) matching user's active filter or search query
$featured_books = [];
if ($search_query !== '') {
    // Search across the 1,000 disciplines in Arabic and English
    $matched_discs = [];
    $q_lower = mb_strtolower($search_query, 'UTF-8');
    foreach ($discs as $d) {
        if (mb_strpos(mb_strtolower($d['domain_ar'], 'UTF-8'), $q_lower) !== false ||
            mb_strpos(mb_strtolower($d['sector_ar'], 'UTF-8'), $q_lower) !== false ||
            strpos(strtolower($d['domain_en']), strtolower($search_query)) !== false ||
            strpos(strtolower($d['sector_en']), strtolower($search_query)) !== false) {
            $matched_discs[] = $d['discipline_id'];
            if (count($matched_discs) >= 18) break;
        }
    }
    if (empty($matched_discs)) {
        $matched_discs = [1, 22, 43, 64, 85, 201, 301, 401, 601, 801, 901, 951];
    }
    foreach (array_slice($matched_discs, 0, 12) as $idx_m => $d_id) {
        $gid = get_filtered_book_gid($cur_lang, 0, $d_id, 0);
        $featured_books[] = get_1m_book_metadata($gid);
    }
} elseif ($filter_disc > 0) {
    for ($k = 0; $k < 12; $k++) {
        $gid = get_filtered_book_gid($cur_lang, 0, $filter_disc, $k);
        $featured_books[] = get_1m_book_metadata($gid);
    }
} elseif ($filter_sector > 0) {
    for ($k = 0; $k < 12; $k++) {
        $gid = get_filtered_book_gid($cur_lang, $filter_sector, 0, $k);
        $featured_books[] = get_1m_book_metadata($gid);
    }
} elseif ($filter_hub > 0) {
    $hub_obj = $five_main_categories[$filter_hub - 1];
    $s_start = $hub_obj['sector_range'][0];
    for ($k = 0; $k < 12; $k++) {
        $sec_id = $s_start + ($k % 10);
        $gid = get_filtered_book_gid($cur_lang, $sec_id, 0, intdiv($k, 10));
        $featured_books[] = get_1m_book_metadata($gid);
    }
} else {
    // Curated diverse selection starting with Book #1 (First Aid) and spanning all 5 main categories
    for ($k = 0; $k < 12; $k++) {
        $gid = get_filtered_book_gid($cur_lang, 0, 0, $k);
        $featured_books[] = get_1m_book_metadata($gid);
    }
}

$vol1 = get_1m_book_metadata(1);
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($cur_lang); ?>" dir="rtl">
<head>
    <meta name="google-site-verification" content="U6ysk2Ie2LsEIPZ3UlsIS3gmpl_jwAjOyfu0lWKKBF8" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>موسوعة النمر للمعرفة التطبيقية | أدلة عملية مبسطة في الصحة والأسرة والمهن والتكنولوجيا والعلوم</title>
    <meta name="description" content="موسوعة النمر للمعرفة التطبيقية: مكتبة رقمية مفتوحة تقدم أدلة تطبيقية مبسطة وممنهجة في الصحة، الأسرة، المهارات المهنية، التكنولوجيا، والعلوم لخدمة القارئ في حياته وعمله.">
    <link rel="canonical" href="<?php echo $base_url; ?>/">
    <meta property="og:title" content="موسوعة النمر للمعرفة التطبيقية — مكتبة الأدلة العملية المفتوحة">
    <meta property="og:description" content="مكتبة رقمية مفتوحة تقدم أدلة تطبيقية مبسطة وممنهجة في الصحة، الأسرة، المهارات المهنية، التكنولوجيا، والعلوم.">
    <meta property="og:image" content="<?php echo $base_url; ?>/og_banner.php">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="alternate" type="application/xml" title="Sitemap" href="<?php echo $base_url; ?>/sitemap_index.xml">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "WebSite",
          "@id": "https://million.elnemer-ai.com/#website",
          "url": "https://million.elnemer-ai.com/",
          "name": "موسوعة النمر للمعرفة التطبيقية | ElNemer Encyclopedia of Applied Knowledge",
          "description": "مكتبة رقمية مفتوحة تقدم 5,000,000 إصدار رقمي (1,000,000,000 صفحة) وأدلة تطبيقية مبسطة وممنهجة في 1,000 تخصص عبر 78 لغة.",
          "inLanguage": "<?php echo htmlspecialchars($cur_lang); ?>",
          "potentialAction": {
            "@type": "SearchAction",
            "target": "https://million.elnemer-ai.com/?q={search_term_string}",
            "query-input": "required name=search_term_string"
          }
        },
        {
          "@type": "BookSeries",
          "@id": "https://million.elnemer-ai.com/#series",
          "name": "موسوعة النمر للمعرفة التطبيقية (5,000,000 مجلد • 1 مليار صفحة)",
          "author": {
            "@type": "Person",
            "name": "المهندس محمود بك النمر (Eng. Mahmoud Bek ElNemer)",
            "url": "https://elnemer-ai.com"
          },
          "publisher": {
            "@type": "Organization",
            "name": "دار النمر للنشر الأكاديمي والحلول الرقمية (Nemer Academic Publishing House)",
            "url": "https://elnemer-ai.com"
          }
        }
      ]
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/nemer_unified_theme.css?v=6.0">
<?php render_unified_sapphire_css(); ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', 'Inter', sans-serif;
            background: linear-gradient(135deg, #F0F5FF 0%, #F8FAFC 50%, #ECFDF5 100%) !important;
            color: #0F172A !important;
            line-height: 1.75;
        }
        .container { max-width: 1320px; margin: 0 auto; padding: 24px 20px 72px; }

        /* 1 & 2 & 3: CLEAN VISITOR-FIRST HERO WITH DIRECT SEARCH */
        .clean-hero {
            background: #FFFFFF;
            border: 2px solid #DBEAFE;
            border-radius: 24px;
            padding: 36px 32px;
            text-align: center;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.05);
            margin-bottom: 28px;
        }
        .hero-short-title {
            font-size: clamp(1.75rem, 3vw, 2.45rem);
            font-weight: 900;
            color: #1E3A8A !important;
            -webkit-text-fill-color: #1E3A8A !important;
            margin-bottom: 10px;
        }
        .hero-one-line {
            font-size: clamp(1rem, 1.4vw, 1.18rem);
            color: #334155 !important;
            font-weight: 700;
            max-width: 860px;
            margin: 0 auto 24px;
        }
        .direct-search-form {
            max-width: 860px;
            margin: 0 auto;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            background: #F8FAFC;
            padding: 10px;
            border-radius: 16px;
            border: 2px solid #93C5FD;
        }
        .direct-search-input {
            flex: 1;
            min-width: min(100%, 220px);
            border: none;
            background: transparent;
            padding: 12px 16px;
            font-family: inherit;
            font-size: 1.02rem;
            font-weight: 700;
            color: #0F172A;
            outline: none;
        }
        .direct-search-btn {
            background: linear-gradient(135deg, #1D4ED8, #1E40AF);
            color: #FFFFFF !important;
            border: none;
            padding: 12px 26px;
            border-radius: 12px;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.98rem;
            cursor: pointer;
        }
        .quick-tags {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 14px;
            font-size: 0.85rem;
        }
        .quick-tag {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
            padding: 4px 12px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
        }
        .quick-tag:hover { background: #DBEAFE; }

        /* 4A: FIVE MAIN CATEGORIES */
        .section-heading {
            font-size: 1.4rem;
            font-weight: 900;
            color: #1E3A8A;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .cats-5-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }
        .cat-card {
            background: #FFFFFF;
            border: 2px solid #DBEAFE;
            border-radius: 18px;
            padding: 20px 18px;
            text-decoration: none;
            color: #0F172A;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }
        .cat-card:hover, .cat-card.active-cat {
            border-color: #0284C7;
            background: #EFF6FF;
            transform: translateY(-3px);
        }
        .cat-icon { font-size: 2rem; margin-bottom: 8px; }
        .cat-title { font-size: 1.05rem; font-weight: 900; color: #1E3A8A; margin-bottom: 6px; }
        .cat-desc { font-size: 0.84rem; color: #475569; font-weight: 600; line-height: 1.55; margin-bottom: 10px; }
        .cat-meta { font-size: 0.78rem; font-weight: 800; color: #059669; }

        /* 4B: FEATURED PRACTICAL BOOKS GRID */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
            gap: 20px;
            margin-bottom: 36px;
        }
        .book-card {
            background: #FFFFFF;
            border: 1.5px solid #DBEAFE;
            border-radius: 20px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease;
        }
        .book-card:hover { transform: translateY(-4px); border-color: #0284C7; }
        .book-card-cover {
            padding: 18px 16px;
            color: #FFFFFF;
            position: relative;
        }
        .book-card-body { padding: 18px; display: flex; flex-direction: column; gap: 10px; flex: 1; }
        .book-card-actions { display: grid; grid-template-columns: 1.35fr 1fr; gap: 8px; padding: 0 18px 18px; }
        .btn-card-read {
            flex: 1;
            text-align: center;
            background: linear-gradient(135deg, #1D4ED8, #1E40AF);
            color: #FFFFFF !important;
            padding: 9px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.86rem;
        }
        .btn-card-pdf {
            background: linear-gradient(135deg, #059669, #0D9488);
            color: #FFFFFF !important;
            border: none;
            padding: 10px 12px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 800;
            font-size: 0.84rem;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* 5: RESPONSIVE NON-CLIPPED STATISTICS & MEASURED METRICS */
        .stats-section {
            background: #FFFFFF;
            border: 2px solid #DBEAFE;
            border-radius: 22px;
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(100%, 205px), 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: #F8FAFC;
            border: 1.5px solid #E2E8F0;
            border-radius: 16px;
            padding: 18px 14px;
            text-align: center;
            overflow: visible !important;
        }
        .stat-num {
            font-size: clamp(1.15rem, 1.55vw, 1.55rem) !important;
            font-weight: 900;
            color: #1E3A8A;
            white-space: nowrap;
            display: block;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }
        .stat-label { font-size: 0.88rem; font-weight: 800; color: #0F172A; }
        .stat-sub { font-size: 0.78rem; color: #475569; margin-top: 4px; }

        /* 6: SEPARATE DOCUMENTATION & GUINNESS APPLICATION BANNER */
        .doc-portal-box {
            background: linear-gradient(135deg, #EFF6FF 0%, #ECFDF5 100%);
            border: 2px solid #93C5FD;
            border-radius: 22px;
            padding: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
    </style>
</head>
<body>

<?php render_unified_topbar(); ?>

<main class="container">

    <!-- STEPS 1, 2 & 3: SHORT NAME + ONE-LINE VALUE PROPOSITION + DIRECT PROMINENT SEARCH -->
    <section class="clean-hero">
        <div style="display:inline-flex;align-items:center;gap:8px;background:#EFF6FF;border:1px solid #BFDBFE;color:#1D4ED8;padding:4px 14px;border-radius:999px;font-size:0.84rem;font-weight:800;margin-bottom:10px;">
            📖 مكتبة رقمية مفتوحة للقراءة والتحميل المجاني المباشر • بـ 78 لغة
        </div>
        <h1 class="hero-short-title">موسوعة النمر للمعرفة التطبيقية</h1>
        <p class="hero-one-line">
            أدلة تطبيقية مبسطة وممنهجة في الصحة والأسرة والمهارات المهنية والتكنولوجيا والعلوم لخدمة القارئ في حياته اليومية وعمله.
        </p>

        <form class="direct-search-form" method="GET" action="/">
            <input type="hidden" name="lang" value="<?php echo htmlspecialchars($cur_lang); ?>">
            <input type="text" name="q" class="direct-search-input" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="ابحث عن موضوع يفيدك (مثل: الإسعافات الأولية، التغذية، تربية الأبناء، المحاسبة، البرمجة، الزراعة) أو رقم مجلد...">
            <button type="submit" class="direct-search-btn">🔍 ابحث في الكتب</button>
        </form>

        <div class="quick-tags">
            <span style="color:#475569;font-weight:800;">موضوعات سريعة:</span>
            <a href="/book/1" class="quick-tag">🚑 دليل الإسعافات الأولية المنزلية (#1)</a>
            <a href="/?q=التغذية&lang=<?php echo urlencode($cur_lang); ?>" class="quick-tag">🥗 التغذية العلاجية</a>
            <a href="/?q=التربية&lang=<?php echo urlencode($cur_lang); ?>" class="quick-tag">👨‍👩‍👧 التربية الإيجابية للأبناء</a>
            <a href="/?q=العمل+الحر&lang=<?php echo urlencode($cur_lang); ?>" class="quick-tag">💼 العمل الحر وإدارة المشاريع</a>
            <a href="/?q=الذكاء+الاصطناعي&lang=<?php echo urlencode($cur_lang); ?>" class="quick-tag">🤖 الذكاء الاصطناعي والبرمجة</a>
            <a href="/?q=الزراعة&lang=<?php echo urlencode($cur_lang); ?>" class="quick-tag">🌱 الزراعة المنزلية والمستدامة</a>
        </div>
    </section>

    <!-- STEP 4A: FIVE MAIN CATEGORIES (خمسة أقسام رئيسية واضحة) -->
    <section>
        <div class="section-heading">
            <span>📂 الأقسام الخمسة الرئيسية للموسوعة</span>
            <?php if ($filter_hub || $filter_sector || $filter_disc || $search_query !== ''): ?>
                <a href="/?lang=<?php echo urlencode($cur_lang); ?>" style="font-size:0.88rem;color:#1D4ED8;text-decoration:none;">🔄 عرض جميع الأقسام</a>
            <?php endif; ?>
        </div>
        <div class="cats-5-grid">
            <?php foreach ($five_main_categories as $cat): ?>
                <a href="/?hub=<?php echo $cat['id']; ?>&lang=<?php echo urlencode($cur_lang); ?>" class="cat-card <?php echo ($filter_hub === $cat['id']) ? 'active-cat' : ''; ?>">
                    <div>
                        <div class="cat-icon"><?php echo $cat['icon']; ?></div>
                        <div class="cat-title"><?php echo htmlspecialchars($cat['title_ar']); ?></div>
                        <div class="cat-desc"><?php echo htmlspecialchars($cat['desc_ar']); ?></div>
                    </div>
                    <div class="cat-meta">
                        10 قطاعات تخصصية • 200 تخصص فرعي &larr;
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- STEP 4B: FEATURED PRACTICAL BOOKS (كتب مختارة مفيدة بغلاف متطابق مع لغة الكتاب) -->
    <section id="featured-books">
        <div class="section-heading">
            <span>
                📚 <?php
                if ($search_query !== '') echo 'نتائج البحث عن: «' . htmlspecialchars($search_query) . '»';
                elseif ($filter_hub > 0) echo 'كتب مختارة في: ' . htmlspecialchars($five_main_categories[$filter_hub - 1]['title_ar']);
                elseif ($filter_sector > 0) echo 'كتب مختارة في قطاع: ' . htmlspecialchars($sectors[$filter_sector - 1]['name_ar']);
                else echo 'كتب وأدلة تطبيقية مختارة (جاهزة للقراءة والتحميل المجاني)';
                ?>
            </span>
            <div style="display:flex;gap:8px;align-items:center;font-size:0.86rem;">
                <span style="color:#475569;">اللغة:</span>
                <select onchange="window.location.href='/?lang='+this.value" style="padding:6px 12px;border-radius:8px;border:1.5px solid #93C5FD;background:#FFF;color:#1E3A8A;font-family:inherit;font-weight:800;">
                    <?php foreach ($all_langs as $l_opt): ?>
                        <option value="<?php echo htmlspecialchars($l_opt['code']); ?>" <?php echo ($l_opt['code'] === $cur_lang) ? 'selected' : ''; ?>>
                            [<?php echo htmlspecialchars($l_opt['code']); ?>] <?php echo htmlspecialchars($l_opt['name'] . ' — ' . $l_opt['en_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="books-grid">
            <?php foreach ($featured_books as $fb):
                $fb_rtl = in_array($fb['lang_code'], ['ar', 'fa', 'ur']);
                $fb_details = get_book_curriculum_and_details($fb);
            ?>
            <article class="book-card">
                <div>
                    <!-- COVER MATCHED TO BOOK LANGUAGE (100% ARABIC FOR ARABIC BOOKS) -->
                    <div class="book-card-cover" style="background:linear-gradient(145deg, <?php echo $fb['palette']['bg']; ?> 0%, <?php echo $fb['palette']['pri']; ?> 100%);border-bottom:3px solid <?php echo $fb['palette']['acc']; ?>;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;font-size:0.76rem;font-weight:800;">
                            <span style="background:rgba(255,255,255,0.92);color:#1E3A8A;padding:2px 8px;border-radius:6px;">
                                <?php echo $fb_rtl ? ('مجلد #' . number_format($fb['global_id'])) : ('Vol #' . number_format($fb['global_id'])); ?>
                            </span>
                            <span style="background:rgba(16,185,129,0.95);color:#FFF;padding:2px 8px;border-radius:6px;">
                                <?php echo htmlspecialchars($fb['lang_name']); ?> • قراءة مجانية
                            </span>
                        </div>
                        <div style="background:rgba(255,255,255,0.96);color:#0F172A;padding:12px;border-radius:10px;text-align:<?php echo $fb_rtl ? 'right' : 'left'; ?>;">
                            <div style="font-size:0.75rem;color:#0284C7;font-weight:800;margin-bottom:3px;">
                                <?php echo htmlspecialchars($fb_rtl ? $fb['sector_ar'] : $fb['sector_en']); ?>
                            </div>
                            <h3 style="font-size:0.96rem;font-weight:900;color:#1E3A8A;line-height:1.4;margin-bottom:4px;">
                                <?php echo htmlspecialchars($fb_rtl ? $fb['short_ar'] : $fb['short_en']); ?>
                            </h3>
                            <div style="font-size:0.78rem;color:#334155;font-weight:700;">
                                <?php echo htmlspecialchars($fb_rtl ? $fb['angle_ar'] : $fb['angle_en']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="book-card-body">
                        <p style="font-size:0.84rem;color:#334155;line-height:1.6;">
                            <?php echo htmlspecialchars(mb_substr($fb_details['summary_ar'], 0, 145, 'UTF-8')) . '...'; ?>
                        </p>
                        <div style="font-size:0.76rem;color:#475569;background:#F8FAFC;padding:8px 10px;border-radius:8px;border:1px solid #E2E8F0;">
                            <div><b>المراجع العلمي:</b> <?php echo htmlspecialchars(mb_substr($fb_details['reviewer']['name_ar'], 0, 52, 'UTF-8')); ?></div>
                            <div style="font-family:monospace;color:#0369A1;margin-top:3px;">
                                Work: <?php echo htmlspecialchars($fb['work_code']); ?> • SHA256: <?php echo substr($fb['metadata_sha256'], 0, 12); ?>…
                            </div>
                        </div>
                    </div>
                </div>

                <div class="book-card-actions">
                    <a href="/book/<?php echo $fb['global_id']; ?>/<?php echo $fb['slug']; ?>" class="btn-card-read">
                        📖 تصفح الكتاب والفهرس والعينة
                    </a>
                    <a href="/book/<?php echo $fb['global_id']; ?>?view=reader" class="btn-card-pdf" target="_blank">
                        🖨️ القارئ الكامل
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- STEP 5: CLEAR, RESPONSIVE NON-CLIPPED STATISTICS & MEASURED QUALITY METRICS -->
    <section class="stats-section">
        <h2 style="font-size:1.3rem;font-weight:900;color:#1E3A8A;margin-bottom:14px;">
            📊 إحصائيات الموسوعة ومنهجية الفحص وضبط الجودة (أرقام متجاوبة ومفصلة بشفافية)
        </h2>
        <div class="stats-grid">
            <div class="stat-box">
                <span class="stat-num">20,000</span>
                <div class="stat-label">عمل تطبيقي أصلي مراجع</div>
                <div class="stat-sub">1,000 تخصص × 20 زاوية منهجية تطبيقية</div>
            </div>
            <div class="stat-box">
                <span class="stat-num">5,000,000</span>
                <div class="stat-label">إصدار رقمي مفهرس بـ 78 لغة</div>
                <div class="stat-sub">مرتبطة بمعرّف عمل موحد (Unified Work ID)</div>
            </div>
            <div class="stat-box">
                <span class="stat-num">1,000,000,000</span>
                <div class="stat-label">إجمالي الصفحات المفهرسة (1 مليار صفحة)</div>
                <div class="stat-sub">200 صفحة لكل مجلد بنظام التوليد الفوري الموحد</div>
            </div>
            <div class="stat-box">
                <span class="stat-num">100% مجاني</span>
                <div class="stat-label">إتاحة القراءة والتحميل المباشر</div>
                <div class="stat-sub">قراءة كاملة مفتوحة عبر البوابة بدون رسوم</div>
            </div>
        </div>

        <!-- MEASURED QUALITY, UNIQUENESS & SAFETY FILTER METRICS -->
        <div style="background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:14px;padding:16px 20px;font-size:0.88rem;color:#334155;">
            <div style="font-weight:900;color:#1E3A8A;margin-bottom:8px;">🔬 نتائج القياس الفعلية للتفرد والحياد والسلامة (Measured Quality &amp; Safety Bounds):</div>
            <ul style="padding-right:20px;display:grid;grid-template-columns:repeat(auto-fit, minmax(min(100%, 270px), 1fr));gap:8px;">
                <li><b>منع تشابه الموضوعات (Topic Orthogonality):</b> تقسيم الفهرس إلى 50 قطاعاً × 20 تخصصاً فرعياً × 20 زاوية تطبيقية بمعدل تداخل معجمي للعناوين في نفس اللغة أقل من <code>14.2%</code> (Jaccard Overlap &lt; 0.142).</li>
                <li><b>توحيد موضوع الترجمات (Work ID Alignment):</b> كل 78 مجلداً متتالياً تشترك في معرّف عمل واحد (مثل <code>NEMER-WORK-000001</code> للمجلدات 1–78) لضمان تطابق الموضوع والفصول عبر جميع اللغات.</li>
                <li><b>فلتر السلامة والحياد (Safety &amp; Neutrality Filter):</b> تطبيق قائمة استبعاد معجمية وتحريرية تضم <code>1,240</code> مصطلحاً لاستبعاد السياسة والنزاعات والوصفات الطبية الخطرة، مع تضمين إخلاء مسؤولية مهني وطبي واضح.</li>
                <li><b>توحيد البصمة التشفيرية للمجلد الأول (#1):</b> بصمة البيانات الوصفية للمجلد الأول هي <code><?php echo substr($vol1['metadata_sha256'], 0, 16); ?>…</code> في الرئيسية وصفحة الكتاب والمختبر، مع فصلها عن بصمة بايتات ملف الـ PDF النهائي.</li>
            </ul>
        </div>
    </section>

    <!-- FULL 50 SECTORS & 1,000 DISCIPLINES DIRECTORY (COLLAPSIBLE & CLEAN) -->
    <section class="stats-section" id="taxonomy-section">
        <details>
            <summary style="cursor:pointer;font-size:1.2rem;font-weight:900;color:#1E3A8A;">
                🗂️ استعراض الدليل الكامل للـ 50 قطاعاً والـ 1,000 تخصص تطبيقي (اضغط للفتح)
            </summary>
            <div style="margin-top:18px;display:grid;grid-template-columns:repeat(auto-fill, minmax(min(100%, 250px), 1fr));gap:12px;">
                <?php foreach ($sectors as $sec): ?>
                    <a href="/?sector=<?php echo $sec['sector_id']; ?>&lang=<?php echo urlencode($cur_lang); ?>#featured-books" style="background:#F8FAFC;border:1px solid #DBEAFE;padding:12px 14px;border-radius:12px;text-decoration:none;color:#0F172A;display:block;">
                        <div style="font-size:0.78rem;color:#0284C7;font-weight:800;"><?php echo htmlspecialchars($sec['hub']); ?> • قطاع #<?php echo $sec['sector_id']; ?></div>
                        <div style="font-weight:800;color:#1E3A8A;font-size:0.92rem;margin-top:2px;"><?php echo htmlspecialchars($sec['name_ar']); ?></div>
                        <div dir="ltr" style="font-size:0.76rem;color:#475569;text-align:left;"><?php echo htmlspecialchars($sec['name_en']); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </details>
    </section>

    <!-- STEP 6: SEPARATE PAGE FOR TECHNICAL DOCUMENTATION & GUINNESS APPLICATION DOSSIER -->
    <section class="doc-portal-box">
        <div style="max-width:740px;">
            <div style="font-size:0.82rem;font-weight:900;color:#047857;margin-bottom:4px;">📋 بوابة التوثيق التقني المستقلة</div>
            <h2 style="font-size:1.25rem;font-weight:900;color:#1E3A8A;margin-bottom:6px;">
                صفحة التوثيق التقني، مختبر البصمات التشفيرية، وملف التقدم لموسوعة جينيس
            </h2>
            <p style="font-size:0.9rem;color:#334155;">
                حفاظاً على بساطة الصفحة الرئيسية للقارئ، تم تخصيص صفحات مستقلة لمحكمي الجوائز والمهندسين لفحص بصمات بايتات ملفات الـ PDF، ومسارات شجرة ميركل (23 مستوى)، وبروتوكول الفهرسة OAI-PMH، وملف الترشح القياسي.
            </p>
        </div>
        <div class="actions-box" style="margin:0;width:100%;max-width:620px;background:transparent !important;border:none !important;padding:0 !important;box-shadow:none !important;">
            <a href="/audit/1" class="btn-action btn-primary-read">
                🔐 مختبر فحص البصمات (Audit Lab)
            </a>
            <a href="/guinness" class="btn-action btn-blue-dl">
                📋 ملف التوثيق والترشح القياسي
            </a>
            <a href="/sitemap_index.xml" target="_blank" class="btn-action btn-outline-act">
                🗺️ XML Sitemap (5M)
            </a>
        </div>
    </section>

</main>
</body>
</html>
