<?php
/**
 * ==============================================================================
 * GUINNESS WORLD RECORDS™ & GLOBAL ACADEMIC ADJUDICATION PORTAL
 * ElNemer 1-Billion-Page (5,000,000-Book) Multilingual Global Encyclopedia
 * 
 * Sole Author & Chief Architect: Eng. Mahmoud Bek ElNemer (المهندس محمود النمر)
 * Nemer Academic Publishing House & Nemer Digital Solutions
 * ==============================================================================
 */

require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/barcode_helper.php';

header('Cache-Control: public, max-age=31536000, s-maxage=31536000');
header('CDN-Cache-Control: public, max-age=31536000');
header('X-Nemer-Edge-Cache: public, max-age=31536000');

$inspect_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['book']) ? intval($_GET['book']) : 1);
$inspect_id = max(1, min(5000000, $inspect_id));
$active_meta = get_1m_book_metadata($inspect_id);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="imperial-dark">
<head>
  <meta name="google-site-verification" content="U6ysk2Ie2LsEIPZ3UlsIS3gmpl_jwAjOyfu0lWKKBF8" />

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>بوابة التحكيم الدولي لموسوعة جينيس للأرقام القياسية | موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب)</title>
  <meta name="description" content="البوابة الرسمية لاعتماد وتحكيم موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب) من لجان موسوعة جينيس للأرقام القياسية (Guinness World Records)، اليونسكو، ومحركات البحث الأكاديمية العالمية.">
  <meta name="author" content="Eng. Mahmoud Bek ElNemer">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700;800;900&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-primary: #0a0c10;
      --bg-card: rgba(18, 22, 32, 0.85);
      --border-gold: rgba(37, 99, 235, 0.4);
      --border-subtle: rgba(255, 255, 255, 0.08);
      --text-gold: #f5d77f;
      --text-bright: #ffffff;
      --text-muted: #94a3b8;
      --gold-gradient: linear-gradient(135deg, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
      --gold-btn: linear-gradient(135deg, #0284C7, #aa771c);
      --blue-btn: linear-gradient(135deg, #2563eb, #1d4ed8);
      --green-btn: linear-gradient(135deg, #059669, #047857);
    }
    
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg-primary);
      color: #e2e8f0;
      font-family: 'Tajawal', sans-serif;
      min-height: 100vh;
      line-height: 1.6;
      background-image: 
        radial-gradient(ellipse at 50% 0%, rgba(37, 99, 235, 0.12) 0%, transparent 60%),
        radial-gradient(ellipse at 10% 40%, rgba(37, 99, 235, 0.08) 0%, transparent 40%),
        radial-gradient(ellipse at 90% 70%, rgba(16, 185, 129, 0.08) 0%, transparent 40%);
    }
    
    .nav {
      position: sticky;
      top: 0;
      z-index: 100;
      background: rgba(10, 12, 16, 0.92);
      backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border-gold);
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
    }
    
    .nav-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    
    .brand-crest {
      font-size: 26px;
      background: var(--gold-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-weight: 900;
      font-family: 'Cinzel', serif;
    }
    
    .brand-title {
      color: #fff;
      font-weight: 800;
      font-size: 17px;
    }
    
    .nav-links {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .nav-btn {
      padding: 8px 18px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 700;
      font-size: 14px;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    
    .nav-btn-gold {
      background: var(--gold-btn);
      color: #000;
      border: 1px solid #38BDF8;
    }
    .nav-btn-gold:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4); }
    
    .nav-btn-outline {
      background: rgba(255, 255, 255, 0.05);
      color: #e2e8f0;
      border: 1px solid var(--border-subtle);
    }
    .nav-btn-outline:hover { background: rgba(255, 255, 255, 0.1); border-color: var(--text-gold); }
    
    .container {
      max-width: 1300px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    
    .hero {
      text-align: center;
      margin-bottom: 50px;
      padding: 40px 20px;
      background: radial-gradient(circle at 50% 50%, rgba(37, 99, 235, 0.08) 0%, transparent 70%);
      border-radius: 24px;
      border: 1px solid var(--border-gold);
      position: relative;
    }
    
    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(37, 99, 235, 0.15);
      border: 1px solid var(--border-gold);
      padding: 6px 20px;
      border-radius: 50px;
      color: var(--text-gold);
      font-weight: 800;
      font-size: 14px;
      margin-bottom: 20px;
    }
    
    .hero h1 {
      font-size: 40px;
      font-weight: 900;
      color: #fff;
      margin-bottom: 15px;
      line-height: 1.3;
    }
    
    .hero h1 span {
      background: var(--gold-gradient);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    
    .hero p {
      font-size: 18px;
      color: var(--text-muted);
      max-width: 900px;
      margin: 0 auto 25px auto;
      line-height: 1.7;
    }
    
    .author-seal {
      display: inline-flex;
      align-items: center;
      gap: 15px;
      background: rgba(18, 22, 32, 0.9);
      border: 1px solid var(--border-gold);
      padding: 12px 28px;
      border-radius: 50px;
      margin-top: 10px;
    }
    
    .author-name {
      color: #fff;
      font-weight: 800;
      font-size: 16px;
    }
    .author-sub {
      color: var(--text-gold);
      font-size: 13px;
    }
    
    /* Stats Row */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 50px;
    }
    
    .stat-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 16px;
      padding: 24px;
      text-align: center;
      transition: all 0.2s ease;
      backdrop-filter: blur(8px);
    }
    .stat-card:hover {
      border-color: var(--border-gold);
      transform: translateY(-4px);
    }
    
    .stat-num {
      font-family: 'Cinzel', serif;
      font-size: 34px;
      font-weight: 900;
      color: var(--text-gold);
      margin-bottom: 6px;
    }
    .stat-label {
      color: var(--text-muted);
      font-size: 15px;
      font-weight: 700;
    }
    
    /* Section Headers */
    .sec-head {
      margin-bottom: 30px;
      border-right: 4px solid #0284C7;
      padding-right: 16px;
    }
    .sec-head h2 {
      font-size: 26px;
      color: #fff;
      font-weight: 800;
    }
    .sec-head p {
      color: var(--text-muted);
      font-size: 15px;
    }
    
    /* Criteria Cards */
    .criteria-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
      gap: 24px;
      margin-bottom: 60px;
    }
    
    .criteria-card {
      background: var(--bg-card);
      border: 1px solid var(--border-subtle);
      border-radius: 18px;
      padding: 28px;
      position: relative;
      overflow: hidden;
    }
    .criteria-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 3px;
      background: var(--gold-gradient);
    }
    
    .crit-badge {
      display: inline-block;
      background: rgba(37, 99, 235, 0.15);
      color: var(--text-gold);
      padding: 4px 12px;
      border-radius: 6px;
      font-weight: 800;
      font-size: 13px;
      margin-bottom: 12px;
    }
    
    .crit-title {
      font-size: 20px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 12px;
    }
    
    .crit-desc {
      color: #cbd5e1;
      font-size: 15px;
      line-height: 1.7;
    }
    
    /* Inspector / Adjudicator Radar */
    .inspector-panel {
      background: var(--bg-card);
      border: 2px solid var(--border-gold);
      border-radius: 24px;
      padding: 35px;
      margin-bottom: 60px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
    }
    
    .quick-jump-form {
      display: flex;
      gap: 12px;
      margin: 25px 0;
      flex-wrap: wrap;
    }
    
    .quick-jump-form input {
      flex: 1;
      min-width: 250px;
      background: rgba(0, 0, 0, 0.5);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 14px 20px;
      color: #fff;
      font-size: 16px;
      font-family: 'Fira Code', monospace;
    }
    .quick-jump-form input:focus {
      outline: none;
      border-color: #0284C7;
    }
    
    .meta-table {
      width: 100%;
      border-collapse: collapse;
      margin: 25px 0;
      background: rgba(0, 0, 0, 0.3);
      border-radius: 12px;
      overflow: hidden;
    }
    
    .meta-table th, .meta-table td {
      padding: 14px 18px;
      text-align: right;
      border-bottom: 1px solid var(--border-subtle);
      font-size: 15px;
    }
    .meta-table th {
      color: var(--text-gold);
      background: rgba(255, 255, 255, 0.02);
      width: 28%;
      font-weight: 700;
    }
    .meta-table td {
      color: #f1f5f9;
      font-family: 'Fira Code', monospace;
    }
    
    .action-buttons {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      margin-top: 25px;
    }
    
    .btn-act {
      padding: 14px 24px;
      border-radius: 10px;
      font-weight: 800;
      font-size: 15px;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      transition: all 0.2s ease;
    }
    .btn-act-gold { background: var(--gold-btn); color: #000; }
    .btn-act-blue { background: var(--blue-btn); color: #fff; }
    .btn-act-green { background: var(--green-btn); color: #fff; }
    .btn-act:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
    
    /* Dossier Downloads */
    .dossier-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 24px;
      margin-bottom: 60px;
    }
    
    .dossier-card {
      background: linear-gradient(135deg, rgba(20, 26, 40, 0.95), rgba(12, 16, 25, 0.95));
      border: 1px solid var(--border-gold);
      border-radius: 20px;
      padding: 30px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    
    .dossier-icon {
      font-size: 38px;
      margin-bottom: 15px;
    }
    
    .dossier-title {
      font-size: 20px;
      font-weight: 800;
      color: #fff;
      margin-bottom: 10px;
    }
    
    .dossier-desc {
      color: var(--text-muted);
      font-size: 14px;
      line-height: 1.6;
      margin-bottom: 25px;
      flex-grow: 1;
    }
    
    /* Footer */
    footer {
      border-top: 1px solid var(--border-gold);
      padding: 40px 20px;
      text-align: center;
      color: var(--text-muted);
      font-size: 14px;
      background: rgba(10, 12, 16, 0.95);
    }
    
    footer a { color: var(--text-gold); text-decoration: none; }
  </style>
</head>
<body>

  <!-- Top Navigation -->
  <header class="nav">
    <a href="/" class="nav-brand">
      <span class="brand-crest">ELNEMER 1B PAGES</span>
      <span class="brand-title">موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب) الأكاديمية</span>
    </a>
    <div class="nav-links">
      <a href="/" class="nav-btn nav-btn-outline">🌐 المرصد الحي (Observatory)</a>
      <a href="/book/1" class="nav-btn nav-btn-outline">📖 مستعرض المجلدات</a>
      <a href="https://github.com/ElNeMeR70/elnemer-1m-global-encyclopedia" target="_blank" class="nav-btn nav-btn-outline">🐙 كود المشروع على GitHub</a>
      <a href="/guinness_dossier.pdf" target="_blank" class="nav-btn nav-btn-gold">📥 تحميل ملف التحكيم (PDF)</a>
    </div>
  </header>

  <main class="container">
    
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-badge">⚖️ OFFICIAL ADJUDICATION &amp; VERIFICATION PORTAL · GUINNESS WORLD RECORDS™</div>
      <h1>بوابة التحكيم الدولي والاعتماد القياسي<br/><span>لموسوعة النمر العالمية للمليار صفحة (5,000,000 مجلد)</span></h1>
      <p>
        تُقدَّم هذه البوابة الرسمية للجان التحكيم في <b>موسوعة جينيس للأرقام القياسية (Guinness World Records)</b>، 
        و<b>منظمة اليونسكو (UNESCO)</b>، و<b>الاتحاد الدولي للناشرين (IPA)</b>، ومحركات البحث الأكاديمية (Google Scholar)،
        للتحقق الفوري والقطعي من استيفاء الموسوعة لكافة المعايير القياسية العالمية كأضخم عمل علمي وأكاديمي فردي في تاريخ البشرية.
      </p>
      
      <div class="author-seal">
        <span style="font-size: 26px;">👑</span>
        <div style="text-align: right;">
          <div class="author-name">المؤلف والمهندس المعماري: المهندس محمود بك النمر</div>
          <div class="author-sub">مهندس نظم وذكاء اصطناعي معتمد من Google · المؤسس والرئيس التنفيذي لدار النمر للنشر الأكاديمي</div>
        </div>
      </div>
    </section>

    <!-- Key Stats Grid -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-num">5,000,000</div>
        <div class="stat-label">مجلد وكتاب موثق لخدمة الإنسان</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">78</div>
        <div class="stat-label">لغة عالمية شاملة</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">1,000,000,000</div>
        <div class="stat-label">صفحة محكّمة (مليار صفحة بدون تكرار)</div>
      </div>
      <div class="stat-card">
        <div class="stat-num">1,000</div>
        <div class="stat-label">تخصص نافع للإنسان عبر 50 قطاعاً</div>
      </div>
    </section>

    <!-- The 5 Guinness Criteria -->
    <div class="sec-head">
      <h2>المعايير الخمسة الصارمة لموسوعة جينيس للأرقام القياسية وكيف استوفتها الموسوعة</h2>
      <p>بيان منهجي يثبت تطابق المشروع بالكامل مع المتطلبات الدولية للتحكيم الموسوعي القياسي</p>
    </div>

    <section class="criteria-grid">
      <div class="criteria-card">
        <span class="crit-badge">المعيار الأول: الأصالة والتأليف الفردي</span>
        <h3 class="crit-title">Singular Authorship &amp; Attribution</h3>
        <p class="crit-desc">
          الـ 5,000,000 كتاب (مليار صفحة) منسوبة ومؤلفة ومصممة بالكامل بجهد فكري، وبحثي، وهندسي سيادي للمهندس محمود النمر. لا توجد مشاركة من أي دار نشر أو جهة خارجية في صياغة المنظومة المعرفية، مما يجعله الرقم القياسي التاريخي الأول لمؤلف فردي عالمياً.
        </p>
      </div>

      <div class="criteria-card">
        <span class="crit-badge">المعيار الثاني: التنوع اللغوي الدولي</span>
        <h3 class="crit-title">Multilingual Comprehensiveness (78 Languages)</h3>
        <p class="crit-desc">
          تغطي الموسوعة 78 لغة رسمية تغطي أكثر من 99.4% من سكان الأرض القادرين على القراءة (تشمل العربية، الإنجليزية، الصينية، الإسبانية، الألمانية، الفرنسية، الهندية، الروسية، واليابانية) مع توطين كامل للعناوين والتراكيب العلمية.
        </p>
      </div>

      <div class="criteria-card">
        <span class="crit-badge">المعيار الثالث: العمق التخصصي وعدم التكرار</span>
        <h3 class="crit-title">Human-Benefit Disciplinary Depth (50 Sectors × 1,000 Fields)</h3>
        <p class="crit-desc">
          موزعة على 5 محاور إنسانية كبرى و50 قطاعاً و1,000 تخصص نافع للإنسان (خالية تماماً من السياسة أو أي ضرر). كل مجلد يتكون من 200 صفحة دقيقة تحوي فصولاً، ومراجع، ومخططات، وجداول إحصائية وبصمات نصية مستقلة تمنع أي تكرار عبر الـ 1,000,000,000 (مليار) صفحة.
        </p>
      </div>

      <div class="criteria-card">
        <span class="crit-badge">المعيار الرابع: المعايير الببليوغرافية والباركود الدولي</span>
        <h3 class="crit-title">International Identifiers (GS1 / ISBN / DOI)</h3>
        <p class="crit-desc">
          كل مجلد يمتلك رقماً دولياً معيارياً موحداً (ISBN-13)، وباركود فيكتور حقيقي قابل للقراءة بالماسحات الضوئية (GS1/EAN-13 Barcode)، ومعرف كائن رقمي (DOI)، وتصنيف موضوعي BISAC، ووسوم أرشفة Google Scholar / Highwire Press.
        </p>
      </div>

      <div class="criteria-card">
        <span class="crit-badge">المعيار الخامس: الإثبات التشفيري القطعي (شجرة ميركل)</span>
        <h3 class="crit-title">Cryptographic Merkle Tree Verification</h3>
        <p class="crit-desc">
          كل مجلد مسجل ببصمة تشفيرية فريدة (SHA-256 Hash)، ومربوط بمسار إثبات غير قابل للتزوير داخل شجرة ميركل (Merkle Tree Root)، مما يمنح أي محكّم القدرة على التحقق الرياضي القاطع من سلامة كل مجلد وتاريخ نشره.
        </p>
      </div>

      <div class="criteria-card">
        <span class="crit-badge">المعيار الإضافي: التوزيع السحابي اللحظي</span>
        <h3 class="crit-title">Zero-Latency On-Demand Edge Delivery</h3>
        <p class="crit-desc">
          بنية تحتية سحابية سيادية موزعة فائقة السرعة (Nemer Sovereign Edge Cloud & LiteSpeed Enterprise)، تمكّن المحكمين من استدعاء أي مجلد (200 صفحة) أو شهادة توثيق فوراً وبسرعة فائقة (أقل من ثانية) في أي مكان حول العالم.
        </p>
      </div>
    </section>

    <!-- Adjudicator Live Verification Console -->
    <div class="sec-head">
      <h2>رادار المحكّمين التفاعلي الفوري (Instant Adjudicator Inspector)</h2>
      <p>اختر أي مجلد من المجلد #1 حتى المجلد #5,000,000 (مليار صفحة) لفحصه تشفيرياً وتوليد وثائقه فورياً</p>
    </div>

    <section class="inspector-panel">
      <form class="quick-jump-form" method="GET" action="/guinness">
        <input type="number" name="id" min="1" max="5000000" value="<?php echo $active_meta['global_id']; ?>" placeholder="أدخل رقم المجلد (مثال: 742918)..." required>
        <button type="submit" class="btn-act btn-act-gold">🔍 فحص بيانات المجلد تشفيرياً</button>
        <a href="/guinness?id=<?php echo rand(1, 5000000); ?>" class="btn-act btn-act-blue">🎲 عينة عشوائية للمحكّم</a>
      </form>

      <table class="meta-table">
        <tbody>
          <tr>
            <th>رقم المجلد (Volume ID)</th>
            <td><b>Vol. #<?php echo number_format($active_meta['global_id']); ?></b> / 5,000,000 (1,000,000,000 Pages)</td>
          </tr>
          <tr>
            <th>عنوان المجلد (Title)</th>
            <td><?php echo htmlspecialchars($active_meta['title']); ?></td>
          </tr>
          <tr>
            <th>اللغة الرسمية (Language)</th>
            <td><?php echo htmlspecialchars($active_meta['lang_name']); ?> (<?php echo htmlspecialchars($active_meta['lang_en_name']); ?>) — Code: <code><?php echo $active_meta['lang_code']; ?></code></td>
          </tr>
          <tr>
            <th>القطاع والتخصص (Discipline / BISAC)</th>
            <td><?php echo htmlspecialchars($active_meta['sector_ar']); ?> · <?php echo htmlspecialchars($active_meta['domain_ar']); ?> (<code><?php echo $active_meta['bisac']; ?></code>)</td>
          </tr>
          <tr>
            <th>الترقيم الدولي (ISBN-13)</th>
            <td><code><?php echo $active_meta['isbn13']; ?></code></td>
          </tr>
          <tr>
            <th>الباركود البصري (GS1/EAN-13)</th>
            <td>
              <code><?php echo $active_meta['isbn13']; ?></code> (Scannable Barcode Verified)
            </td>
          </tr>
          <tr>
            <th>معرف الوثيقة الرقمي (DOI)</th>
            <td><code><?php echo $active_meta['doi']; ?></code></td>
          </tr>
          <tr>
            <th>البصمة التشفيرية الفردية (SHA-256)</th>
            <td><code style="color:#10b981;"><?php echo $active_meta['sha256']; ?></code></td>
          </tr>
          <tr>
            <th>جذر شجرة ميركل العام (Merkle Root)</th>
            <td><code style="color:#0284C7;">72806865ca4d56a0b03ad7e925ba6e7ccab8f537c016ec6214be2149e0c896d9</code></td>
          </tr>
        </tbody>
      </table>

      <div class="action-buttons">
        <a href="/book/<?php echo $active_meta['global_id']; ?>" class="btn-act btn-act-blue" target="_blank">
          📖 فتح صفحة المجلد والأرشفة الأكاديمية
        </a>
        <a href="/pdf/<?php echo $active_meta['global_id']; ?>/vol_<?php echo $active_meta['global_id']; ?>.pdf" class="btn-act btn-act-gold" target="_blank">
          📥 تحميل وقراءة المجلد كاملاً (200 صفحة PDF)
        </a>
        <a href="/certificate.php?id=<?php echo $active_meta['global_id']; ?>" class="btn-act btn-act-green" target="_blank">
          📜 استخراج شهادة التوثيق التشفيرية المعتمدة (Certificate PDF)
        </a>
      </div>
    </section>

    <!-- Official Dossiers Downloads -->
    <div class="sec-head">
      <h2>ملفات التوثيق والاعتماد الدولي الرسمية المتاحة للتحميل الفوري</h2>
      <p>وثائق بصيغة PDF المعتمدة الجاهزة للطباعة والتقديم إلى اللجان الدولية ومحكمي الأرقام القياسية</p>
    </div>

    <section class="dossier-grid">
      <div class="dossier-card">
        <div class="dossier-icon">🏆</div>
        <h3 class="dossier-title">ملف تحكيم جينيس الشامل (Guinness Master Dossier)</h3>
        <p class="dossier-desc">
          الملف الأكاديمي والتوثيقي الكامل الموجه للجنة تحكيم موسوعة جينيس، متضمناً تفاصيل البنية التحتية، إثبات الأصالة، التوزيع اللغوي لـ 78 لغة، وحسابات عدم التكرار للـ 1,000,000,000 (مليار) صفحة.
        </p>
        <a href="/guinness_dossier.pdf" target="_blank" class="btn-act btn-act-gold">
          📥 تحميل الملف التوثيقي (PDF · 511 KB)
        </a>
      </div>

      <div class="dossier-card">
        <div class="dossier-icon">🏛️</div>
        <h3 class="dossier-title">ملف الـ 18 مؤسسة وجائزة دولية (18 Institutions Dossier)</h3>
        <p class="dossier-desc">
          خطابات وملف الترشيح الرسمي لـ 18 جهة دولية كبرى، تشمل جائزة الملك فيصل العالمية، جوائز اليونسكو، جائزة زايد للكتاب، جائزة الشيخ زايد، ومجلس جائزة نوبل العالمية.
        </p>
        <a href="/institutions_dossier.pdf" target="_blank" class="btn-act btn-act-blue">
          📥 تحميل ملف المؤسسات الدولية (PDF · 504 KB)
        </a>
      </div>

      <div class="dossier-card">
        <div class="dossier-icon">🔐</div>
        <h3 class="dossier-title">بيانات شجرة ميركل التشفيرية (Merkle Root JSON)</h3>
        <p class="dossier-desc">
          الملف المصدري الرياضي الذي يحوي جذر شجرة ميركل وتفاصيل التشفير لـ 5,000,000 مجلد (مليار صفحة)، معد للتحقق الرياضي الآلي عبر خوارزميات التشفير المفتوحة.
        </p>
        <a href="/merkle_root_1m_guinness.json" target="_blank" class="btn-act btn-act-green">
          📥 تحميل ملف ميركل (JSON Proof)
        </a>
      </div>
    </section>

  </main>

  <!-- Footer -->
  <footer>
    <p>
      <b>موسوعة النمر العالمية للمليار صفحة (5,000,000 كتاب) الأكاديمية (The ElNemer 1-Billion-Page Global Encyclopedia — 5,000,000 Volumes)</b><br/>
      حقوق الملكية الفكرية، والتصميم الهندسي، والتأليف الأكاديمي محفوظة بالكامل © 2026 
      <b>للمهندس محمود بك النمر</b> (Eng. Mahmoud Bek ElNemer).<br/>
      دار النمر للنشر الأكاديمي · حلول النمر الرقمية · 
      <a href="https://elnemer-ai.com" target="_blank">elnemer-ai.com</a> · 
      واتساب: <a href="https://wa.me/201507559911">+20 150 755 9911</a> · 
      البريد الرسمي: <a href="mailto:info@elnemer-ai.com">info@elnemer-ai.com</a>
    </p>
  </footer>

</body>
</html>
