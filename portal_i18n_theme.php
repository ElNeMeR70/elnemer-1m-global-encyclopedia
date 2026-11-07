<?php
/**
 * ==============================================================================
 * NEMER SOVEREIGN UNIFIED SAPPHIRE-EMERALD THEME & 78-LANGUAGE TRANSLATION ENGINE
 * Unifies the exact visual design, topbar, Royal Sapphire Hero Banner,
 * 78-language selector, and live multilingual translation across all pages
 * of million.elnemer-ai.com
 *
 * Sole Author & Chief Architect: Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)
 * ==============================================================================
 */

require_once __DIR__ . '/taxonomy_data.php';

$all_langs = get_all_78_langs();
$req_lang = isset($_GET['lang']) ? trim($_GET['lang']) : (isset($_COOKIE['nemer_lang']) ? trim($_COOKIE['nemer_lang']) : 'ar');
$i18n = get_portal_i18n($req_lang);
$cur_lang = $i18n['code'];
$dir = $i18n['dir'];

if (isset($_GET['lang'])) {
    @setcookie('nemer_lang', $cur_lang, time() + 86400 * 365, '/');
}

function with_lang($url, $lang = null) {
    global $cur_lang;
    $l = $lang !== null ? $lang : $cur_lang;
    if ($l === 'ar') return $url;
    $sep = (strpos($url, '?') !== false) ? '&' : '?';
    return $url . $sep . 'lang=' . urlencode($l);
}

function ui_t($key) {
    global $cur_lang;
    $dict = [
        'ar' => [
            'theme_btn' => '💎 التصميم الياقوتي والزمردي الأكاديمي',
            'nav_home' => '🏠 المرصد الرئيسي (5M مجلد)',
            'nav_guinness' => '⚖️ بوابة تحكيم جينيس',
            'nav_witness' => '📜 سجل البراهين الجنائية',
            'nav_audit' => '🔐 مختبر التدقيق التشفيري',
            'nav_oai' => '🏛️ مستودع مكتبة الكونجرس OAI',
            'nav_dossier' => '📥 ملف التحكيم (PDF)',
            'nav_sitemap' => '🗺️ XML Sitemap (1B Pages / 5M)',
            'sub_brand' => 'Eng. Mahmoud Bek ElNemer • Google Play Partner Official Partner',
            'pill_1' => '👑 الصرح القياسي العالمي للمليار صفحة (جينيس) • 5,000,000 مجلد لخدمة الإنسان • شريك جوجل المعتمد',
            'pill_3' => '🔐 موثق ومفهرس دولياً (ISO / ISBN-13 / DOI / MARC21)',
            'pill_4' => '📚 1,000,000,000 (1 Billion) Pages • 5,000,000 Volumes • 1,000 Human-Benefit Disciplines',
            'author_main' => 'المؤلف والمهندس المعماري: المهندس محمود بك النمر',
            'author_sub' => 'Google Accredited AI & Systems Engineer - Founder & CEO of Nemer Academic Publishing House',
            'author_verified' => '● اعتماد دولي وتشفيري موثق'
        ],
        'en' => [
            'theme_btn' => '💎 Sapphire & Emerald Academic Design',
            'nav_home' => '🏠 Main Observatory (5M Volumes)',
            'nav_guinness' => '⚖️ Guinness Adjudication',
            'nav_witness' => '📜 Forensic Evidence Ledger',
            'nav_audit' => '🔐 Cryptographic Audit Lab',
            'nav_oai' => '🏛️ Library of Congress OAI-PMH',
            'nav_dossier' => '📥 Master Dossier (PDF)',
            'nav_sitemap' => '🗺️ XML Sitemap (1B Pages / 5M)',
            'sub_brand' => 'Eng. Mahmoud Bek ElNemer • Google Play Partner Official Partner',
            'pill_1' => '👑 Guinness World Record Monument (1 Billion Pages) • 5,000,000 Volumes • Official Google Partner',
            'pill_3' => '🔐 Internationally Indexed & Verified (ISO / ISBN-13 / DOI / MARC21)',
            'pill_4' => '📚 1,000,000,000 (1 Billion) Pages • 5,000,000 Volumes • 1,000 Human-Benefit Disciplines',
            'author_main' => 'Sole Author & Chief Architect: Eng. Mahmoud Bek ElNemer',
            'author_sub' => 'Google Accredited AI & Systems Engineer - Founder & CEO of Nemer Academic Publishing House',
            'author_verified' => '● Verified International & Cryptographic Accreditation'
        ]
    ];
    if (isset($dict[$cur_lang][$key])) return $dict[$cur_lang][$key];
    if ($cur_lang !== 'ar' && isset($dict['en'][$key])) return $dict['en'][$key];
    return $dict['ar'][$key];
}

function render_unified_sapphire_css() {
?>
<style>
  /* ==========================================================================
     MASTER SAPPHIRE, EMERALD & PLATINUM PEARL DESIGN (UNIFIED ACROSS ALL PAGES)
     Matches million.elnemer-ai.com Main Observatory Screenshot 100%
     ========================================================================== */
  :root, body, [data-theme="imperial-dark"], [data-theme="royal-light"] {
    --bg-main: #F4F7FB !important;
    --bg-primary: #F4F7FB !important;
    --bg: #F4F7FB !important;
    --bg-grad: linear-gradient(135deg, #F0F5FF 0%, #F8FAFC 50%, #ECFDF5 100%) !important;
    --card-bg: #FFFFFF !important;
    --bg-card: #FFFFFF !important;
    --card: #FFFFFF !important;
    --card-surface: #FFFFFF !important;
    --card-sub: #F8FAFC !important;
    --topbar-bg: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 55%, #0F766E 100%) !important;
    --hero-bg: radial-gradient(circle at 85% 15%, rgba(56, 189, 248, 0.28) 0%, transparent 55%),
               radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.25) 0%, transparent 55%),
               linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 48%, #0F766E 100%) !important;
    --text-primary: #0F172A !important;
    --text-main: #0F172A !important;
    --text-bright: #0F172A !important;
    --text-secondary: #334155 !important;
    --text-sub: #334155 !important;
    --text-muted: #475569 !important;
    --text-gold: #1E3A8A !important;
    --gold: #0284C7 !important;
    --gold-light: #1D4ED8 !important;
    --cyan: #0284C7 !important;
    --emerald: #059669 !important;
    --blue: #2563EB !important;
    --border-gold: #93C5FD !important;
    --border: #BFDBFE !important;
    --card-border: #BFDBFE !important;
    --border-subtle: #E2E8F0 !important;
    --gold-btn: linear-gradient(135deg, #059669, #0D9488) !important;
    --blue-btn: linear-gradient(135deg, #2563EB, #1D4ED8) !important;
    --green-btn: linear-gradient(135deg, #059669, #047857) !important;
  }

  body {
    background: var(--bg-grad) !important;
    background-image: var(--bg-grad) !important;
    color: #0F172A !important;
    font-family: 'Tajawal', 'Cairo', 'Inter', sans-serif !important;
    top: 0 !important;
  }

  .goog-te-banner-frame.skiptranslate, .goog-te-gadget-icon, #goog-gt-tt, .goog-te-balloon-frame {
    display: none !important;
  }

  /* Unified Top Royal Command Bar */
  .topbar, header.nav {
    background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 55%, #0F766E 100%) !important;
    border-bottom: 3px solid #38BDF8 !important;
    box-shadow: 0 8px 25px rgba(30, 58, 138, 0.22) !important;
    padding: 14px 24px !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 1000 !important;
  }
  .topbar-inner {
    max-width: 1440px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    width: 100%;
  }
  .brand-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: #FFFFFF !important;
  }
  .brand-crest {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0284C7, #0D9488) !important;
    border: 2px solid #E0F2FE !important;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
  }
  .brand-text-main {
    font-weight: 900;
    font-size: 1.06rem;
    color: #FFFFFF !important;
    line-height: 1.25;
  }
  .brand-text-sub {
    font-size: 0.78rem;
    color: #BAE6FD !important;
    font-weight: 600;
  }
  .topbar-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
  }
  .top-select {
    background: rgba(255, 255, 255, 0.18) !important;
    color: #FFFFFF !important;
    border: 1px solid rgba(255, 255, 255, 0.45) !important;
    padding: 7px 12px;
    border-radius: 10px;
    font-family: inherit;
    font-weight: 800;
    font-size: 0.85rem;
    cursor: pointer;
  }
  .top-select option {
    background: #0F172A !important;
    color: #FFFFFF !important;
  }
  .top-btn {
    background: rgba(255, 255, 255, 0.18) !important;
    color: #FFFFFF !important;
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
    padding: 7px 14px;
    border-radius: 10px;
    font-weight: 800;
    font-size: 0.84rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
  }
  .top-btn:hover {
    background: #FFFFFF !important;
    color: #1E3A8A !important;
    border-color: #FFFFFF !important;
    transform: translateY(-2px);
  }
  .top-btn-emerald {
    background: linear-gradient(135deg, #059669, #0D9488) !important;
    border: 1px solid #6EE7B7 !important;
    color: #FFFFFF !important;
  }

  /* Quick 12-Language Strip */
  .lang-quick-strip {
    background: #FFFFFF;
    border-bottom: 1.5px solid #DBEAFE;
    padding: 8px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
  }
  .lang-pill-mini {
    background: #EFF6FF;
    color: #1E3A8A;
    border: 1px solid #BFDBFE;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 800;
    text-decoration: none;
    transition: all 0.15s ease;
  }
  .lang-pill-mini:hover, .lang-pill-mini.active {
    background: linear-gradient(135deg, #2563EB, #0D9488);
    color: #FFFFFF;
    border-color: #1D4ED8;
  }

  /* Unified Royal Sapphire Master Hero Banner (Matches Screenshot 100%) */
  .unified-sapphire-hero {
    max-width: 1220px;
    margin: 22px auto 10px auto;
    padding: 0 20px;
  }
  .unified-sapphire-hero-inner {
    background: radial-gradient(circle at 85% 15%, rgba(56, 189, 248, 0.28) 0%, transparent 55%),
                radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.25) 0%, transparent 55%),
                linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 48%, #0F766E 100%) !important;
    border: 2px solid #93C5FD !important;
    border-radius: 28px !important;
    box-shadow: 0 22px 55px rgba(30, 58, 138, 0.25) !important;
    padding: 32px 28px !important;
    text-align: center;
    color: #FFFFFF !important;
    position: relative;
    overflow: hidden;
  }
  .unified-hero-pills {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-bottom: 18px;
  }
  .u-pill {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.45);
    color: #FFFFFF;
    padding: 6px 16px;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .u-hero-title {
    font-size: 1.85rem;
    font-weight: 900;
    color: #FFFFFF !important;
    line-height: 1.4;
    margin-bottom: 16px;
    text-shadow: 0 4px 18px rgba(15, 23, 42, 0.28);
  }
  .u-author-vip {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
    gap: 14px;
    background: rgba(255, 255, 255, 0.15);
    border: 1.5px solid rgba(255, 255, 255, 0.45);
    border-radius: 999px;
    padding: 10px 26px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
  }

  /* Royal Sapphire Hero Sections inside pages */
  section.hero, .hero-card {
    background: var(--hero-bg) !important;
    border: 2px solid #93C5FD !important;
    border-radius: 28px !important;
    padding: 36px 30px !important;
    color: #FFFFFF !important;
    box-shadow: 0 22px 55px rgba(30, 58, 138, 0.22) !important;
    margin-bottom: 28px !important;
    text-align: center;
  }
  section.hero h1, .hero-card h1, section.hero h1 span {
    color: #FFFFFF !important;
    -webkit-text-fill-color: #FFFFFF !important;
    background: none !important;
  }
  section.hero p, .hero-card .sub {
    color: #E0F2FE !important;
  }
  .hero-badge, .status-badge, .badge {
    background: rgba(255, 255, 255, 0.18) !important;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    color: #FFFFFF !important;
  }
  .author-seal {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    background: rgba(255, 255, 255, 0.16) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.45) !important;
    padding: 12px 26px;
    border-radius: 999px;
    margin-top: 18px;
    color: #FFFFFF !important;
  }
  .author-name { color: #FFFFFF !important; font-weight: 900; font-size: 1.05rem; }
  .author-sub { color: #E0F2FE !important; font-size: 0.85rem; }

  /* White Pearl Content Cards & Tables */
  .card, .doi-card, .criteria-card, .inspector-panel, .dossier-card, .verb-card, .stat-card, .hero-grid, .section-box {
    background: #FFFFFF !important;
    border: 1.5px solid #DBEAFE !important;
    border-radius: 22px !important;
    box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06) !important;
    color: #0F172A !important;
  }
  .card h1, .card h2, .doi-title, .crit-title, .dossier-title, .sec-head h2, h2 {
    color: #1E3A8A !important;
  }
  .crit-desc, .dossier-desc, .verb-desc, .sec-head p, .meta-label {
    color: #334155 !important;
  }
  .stat-num {
    color: #1E3A8A !important;
    -webkit-text-fill-color: #1E3A8A !important;
    background: none !important;
    font-size: clamp(1.1rem, 1.5vw, 1.5rem) !important;
    font-weight: 900 !important;
    line-height: 1.25 !important;
    display: block !important;
  }
  .stat-label { color: #475569 !important; font-weight: 800; }

  /* Symmetrical Action Buttons & Responsive Mobile Layout */
  .actions-box, .action-buttons, .btn-row {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 250px), 1fr)) !important;
    gap: 12px !important;
    align-items: stretch !important;
  }
  .btn-action, .btn-act, .btn {
    min-height: 50px !important;
    width: 100% !important;
    padding: 12px 18px !important;
    border-radius: 14px !important;
    font-weight: 800 !important;
    font-size: 0.92rem !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-align: center !important;
    gap: 8px !important;
    line-height: 1.35 !important;
    transition: all 0.2s ease !important;
  }
  html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
  }
  @media (max-width: 768px) {
    .topbar, header.nav {
      padding: 10px 12px !important;
      position: relative !important;
    }
    .topbar-inner {
      flex-direction: column !important;
      align-items: stretch !important;
      gap: 10px !important;
    }
    .brand-logo {
      justify-content: center !important;
      text-align: center !important;
    }
    .topbar-controls {
      display: grid !important;
      grid-template-columns: repeat(2, 1fr) !important;
      gap: 6px !important;
      width: 100% !important;
    }
    .top-select {
      grid-column: 1 / -1 !important;
      width: 100% !important;
      text-align: center !important;
      min-height: 42px !important;
    }
    .top-btn {
      justify-content: center !important;
      text-align: center !important;
      font-size: 0.78rem !important;
      padding: 8px 8px !important;
    }
    .lang-quick-strip {
      justify-content: flex-start !important;
      overflow-x: auto !important;
      flex-wrap: nowrap !important;
      padding: 8px 12px !important;
    }
    .lang-pill-mini {
      flex-shrink: 0 !important;
    }
    .hero-grid, .two-col-grid, .ch-grid, .books-grid, .cats-5-grid, .sibling-grid, .criteria-grid, .dossier-grid, .actions-box, .action-buttons, .btn-row {
      grid-template-columns: 1fr !important;
    }
    .stats-grid {
      grid-template-columns: repeat(2, 1fr) !important;
    }
    .book-cover-3d {
      width: 100% !important;
      max-width: 280px !important;
      margin: 0 auto !important;
    }
    .meta-table, .meta-table tbody, .meta-table tr, .meta-table th, .meta-table td {
      display: block !important;
      width: 100% !important;
    }
    .meta-table th {
      border-bottom: none !important;
      padding: 10px 14px 4px !important;
    }
    .meta-table td {
      padding: 6px 14px 12px !important;
      word-break: break-word !important;
    }
  }
  @media (max-width: 480px) {
    .stats-grid {
      grid-template-columns: 1fr !important;
    }
  }

  table, .meta-table, .spec-table {
    background: #FFFFFF !important;
    border: 1px solid #DBEAFE !important;
    border-radius: 14px !important;
    overflow: hidden;
  }
  th, .meta-table th, .spec-table th {
    background: linear-gradient(135deg, #EFF6FF, #E0F2FE) !important;
    color: #1E3A8A !important;
    border-bottom: 1.5px solid #BFDBFE !important;
    font-weight: 900 !important;
  }
  td, .meta-table td, .spec-table td {
    background: #FFFFFF !important;
    color: #0F172A !important;
    border-bottom: 1px solid #E2E8F0 !important;
  }
  tr:hover td {
    background: #F8FAFC !important;
  }

  .code-block, .cite-box, .doi-box, .endpoint-box, .meta-grid, .en-block, .shard-box, .lang-item {
    background: #F8FAFC !important;
    border: 1.5px solid #BFDBFE !important;
    color: #0F172A !important;
  }
  .en-block p, .verb-name, .label, .meta-val {
    color: #1E3A8A !important;
  }
  input[type="text"], input[type="number"], .search-input, .quick-jump-form input {
    background: #FFFFFF !important;
    border: 2px solid #93C5FD !important;
    color: #0F172A !important;
    font-weight: 700;
  }

  .btn-gold, .btn-act-gold, .verb-btn, .pill-gold, .btn-audit {
    background: linear-gradient(135deg, #059669, #0D9488) !important;
    color: #FFFFFF !important;
    border: none !important;
    box-shadow: 0 6px 18px rgba(5, 150, 105, 0.25) !important;
  }
  .btn-blue, .btn-act-blue {
    background: linear-gradient(135deg, #2563EB, #1D4ED8) !important;
    color: #FFFFFF !important;
    border: none !important;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25) !important;
  }
  .btn-act-green, .pill {
    background: #EFF6FF !important;
    color: #1E3A8A !important;
    border: 1.5px solid #93C5FD !important;
  }
  .pill:hover, .btn-act-green:hover {
    background: linear-gradient(135deg, #2563EB, #0D9488) !important;
    color: #FFFFFF !important;
  }

  footer {
    background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%) !important;
    border-top: 4px solid #38BDF8 !important;
    color: #E2E8F0 !important;
    padding: 36px 20px !important;
    text-align: center;
  }
  footer a { color: #38BDF8 !important; font-weight: 800; }
</style>
<?php
}

function render_unified_topbar() {
    global $all_langs, $cur_lang, $i18n;
    $top_12 = [
        ['code' => 'ar', 'label' => '🇪🇬 العربية'],
        ['code' => 'en', 'label' => '🇬🇧 English'],
        ['code' => 'fr', 'label' => '🇫🇷 Français'],
        ['code' => 'de', 'label' => '🇩🇪 Deutsch'],
        ['code' => 'es', 'label' => '🇪🇸 Español'],
        ['code' => 'zh-CN', 'label' => '🇨🇳 中文'],
        ['code' => 'ru', 'label' => '🇷🇺 Русский'],
        ['code' => 'ja', 'label' => '🇯🇵 日本語'],
        ['code' => 'hi', 'label' => '🇮🇳 हिन्दी'],
        ['code' => 'tr', 'label' => '🇹🇷 Türkçe'],
        ['code' => 'pt', 'label' => '🇵🇹 Português'],
        ['code' => 'it', 'label' => '🇮🇹 Italiano'],
    ];
?>
<header class="topbar no-print">
  <div class="topbar-inner">
    <a href="<?php echo htmlspecialchars(with_lang('/')); ?>" class="brand-logo">
      <div class="brand-crest">📘</div>
      <div>
        <div class="brand-text-main">موسوعة النمر للمعرفة التطبيقية</div>
        <div class="brand-text-sub">ElNemer Encyclopedia of Applied Knowledge • المهندس محمود النمر</div>
      </div>
    </a>

    <div class="topbar-controls">
      <select class="top-select" aria-label="Select Language (78 Languages)" onchange="switchPageLanguage(this.value)">
        <?php foreach ($all_langs as $l): ?>
        <option value="<?php echo htmlspecialchars($l['code']); ?>" <?php echo (strtolower($l['code']) === strtolower($cur_lang)) ? 'selected' : ''; ?>>
          🌍 [<?php echo htmlspecialchars($l['code']); ?>] <?php echo htmlspecialchars($l['name'] . ' — ' . $l['en_name']); ?>
        </option>
        <?php endforeach; ?>
      </select>

      <a class="top-btn" href="<?php echo htmlspecialchars(with_lang('/')); ?>">🏠 الرئيسية والكتب</a>
      <a class="top-btn" href="<?php echo htmlspecialchars(with_lang('/#taxonomy-section')); ?>">📂 الأقسام والتخصصات</a>
      <a class="top-btn top-btn-emerald" href="<?php echo htmlspecialchars(with_lang('/guinness')); ?>">📋 التوثيق وملف الترشح</a>
      <a class="top-btn" href="<?php echo htmlspecialchars(with_lang('/audit/1')); ?>">🔐 مختبر فحص البصمات</a>
    </div>
  </div>
</header>

<div class="lang-quick-strip no-print" translate="no">
  <span style="font-size:0.78rem;font-weight:900;color:#1E3A8A;margin-left:8px;">🌍 لغة التصفح (78 لغة):</span>
  <?php foreach ($top_12 as $tl): ?>
  <a href="javascript:void(0)" onclick="switchPageLanguage('<?php echo $tl['code']; ?>')" class="lang-pill-mini <?php echo (strtolower($cur_lang) === strtolower($tl['code'])) ? 'active' : ''; ?>">
    <?php echo $tl['label']; ?>
  </a>
  <?php endforeach; ?>
</div>

<div id="google_translate_element" style="display:none;"></div>
<script>
  function switchPageLanguage(langCode) {
    document.cookie = "nemer_lang=" + encodeURIComponent(langCode) + "; path=/; max-age=31536000";
    if (langCode === 'ar') {
      document.cookie = "googtrans=/ar/ar; path=/";
    } else {
      document.cookie = "googtrans=/ar/" + langCode + "; path=/";
    }
    const url = new URL(window.location.href);
    url.searchParams.set('lang', langCode);
    window.location.href = url.toString();
  }

  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'ar',
      autoDisplay: false
    }, 'google_translate_element');
  }

  (function autoActivate78LangTranslation() {
    const activeLang = <?php echo json_encode($cur_lang); ?>;
    if (activeLang && activeLang !== 'ar') {
      document.cookie = "googtrans=/ar/" + activeLang + "; path=/";
      const s = document.createElement('script');
      s.src = "https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit";
      s.async = true;
      document.head.appendChild(s);
    }
  })();
</script>
<?php
}
?>
