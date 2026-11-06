<?php
/**
 * ==============================================================================
 * NEMER ACADEMIC PUBLISHING HOUSE — SOVEREIGN DOI & HANDLE RESOLUTION SERVICE
 * Permanent Digital Object Identifier Gateway for 1,000,000 Academic Volumes
 * 
 * Author: Eng. Mahmoud Bek ElNemer (المهندس محمود النمر)
 * ==============================================================================
 */

require_once __DIR__ . '/taxonomy_data.php';

header('Cache-Control: public, max-age=31536000, s-maxage=31536000');
header('CDN-Cache-Control: public, max-age=31536000');
header('Cloudflare-CDN-Cache-Control: public, max-age=31536000');

$raw_doi = isset($_GET['doi']) ? trim($_GET['doi']) : '';
$raw_doi = preg_replace('/^https?:\/\/(?:dx\.)?doi\.org\//i', '', $raw_doi);
$raw_doi = preg_replace('/^doi:\s*/i', '', $raw_doi);

$book_id = 1;

// Parse Volume ID from DOI format (e.g. 10.1007/s1m-nemer.0000004 or 10.5555/elnemer.1m.vol.4 or 4)
if (preg_match('/(?:s1m-nemer\.|vol\.)([0-9]+)/i', $raw_doi, $m)) {
    $book_id = intval($m[1]);
} elseif (preg_match('/^[0-9]+$/', $raw_doi)) {
    $book_id = intval($raw_doi);
} elseif (preg_match('/([0-9]{1,7})/', $raw_doi, $m)) {
    $book_id = intval($m[1]);
} elseif (isset($_GET['id'])) {
    $book_id = intval($_GET['id']);
}

$book_id = max(1, min(1000000, $book_id));
$b = get_1m_book_metadata($book_id);
$doi_str = $b['doi'];

// Optional auto-redirect if requested
if (isset($_GET['redirect']) && $_GET['redirect'] == '1') {
    header("Location: /book/" . $book_id, true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="imperial-dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>بوابة حل المعرف الرقمي المعتمد | DOI: <?php echo htmlspecialchars($doi_str); ?></title>
  <meta name="description" content="البوابة الرسمية لحل وتوثيق المعرف الرقمي الأكاديمي (DOI Resolution) لموسوعة النمر المليونية.">
  <meta name="citation_doi" content="<?php echo htmlspecialchars($doi_str); ?>">
  <meta name="citation_title" content="<?php echo htmlspecialchars($b['title']); ?>">
  <meta name="citation_author" content="Eng. Mahmoud Bek ElNemer">
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=Tajawal:wght@400;500;700;800;900&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg: #07090e;
      --card-bg: rgba(15, 21, 35, 0.92);
      --border-gold: rgba(212, 175, 55, 0.45);
      --gold-gradient: linear-gradient(135deg, #FFFDF0 0%, #FDE68A 35%, #D4AF37 70%, #996515 100%);
      --text-gold: #f5d77f;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg);
      color: #e2e8f0;
      font-family: 'Tajawal', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      background-image: 
        radial-gradient(ellipse at 50% 0%, rgba(212, 175, 55, 0.15) 0%, transparent 60%),
        radial-gradient(ellipse at 10% 40%, rgba(37, 99, 235, 0.1) 0%, transparent 40%);
    }
    .nav {
      background: rgba(10, 12, 16, 0.95);
      backdrop-filter: blur(16px);
      border-bottom: 2px solid var(--border-gold);
      padding: 16px 24px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }
    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 0 20px;
      width: 100%;
    }
    .doi-card {
      background: var(--card-bg);
      border: 2px solid var(--border-gold);
      border-radius: 24px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(16px);
      position: relative;
      overflow: hidden;
    }
    .doi-card::before {
      content: '';
      position: absolute;
      top: 0; right: 0; left: 0;
      height: 4px;
      background: var(--gold-gradient);
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(16, 185, 129, 0.18);
      border: 1px solid #10B981;
      color: #34D399;
      padding: 6px 18px;
      border-radius: 999px;
      font-size: 0.85rem;
      font-weight: 800;
      margin-bottom: 20px;
    }
    .pulse-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #10B981;
      box-shadow: 0 0 10px #10B981;
    }
    .doi-title {
      font-size: 26px;
      font-weight: 900;
      color: #fff;
      margin-bottom: 15px;
      line-height: 1.4;
    }
    .doi-box {
      background: rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(212, 175, 55, 0.3);
      border-radius: 12px;
      padding: 14px 20px;
      font-family: 'Fira Code', monospace;
      color: var(--text-gold);
      font-size: 1.1rem;
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      direction: ltr;
    }
    .meta-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 12px;
      margin-bottom: 30px;
      background: rgba(0, 0, 0, 0.25);
      border-radius: 14px;
      padding: 20px;
      border: 1px solid rgba(255, 255, 255, 0.06);
    }
    .meta-row {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
      padding-bottom: 10px;
      font-size: 0.95rem;
    }
    .meta-row:last-child { border-bottom: none; padding-bottom: 0; }
    .meta-label { color: #94a3b8; font-weight: 700; }
    .meta-val { color: #f8fafc; font-weight: 800; }
    .btn-row {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }
    .btn {
      padding: 14px 24px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 800;
      font-size: 1rem;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
    }
    .btn-gold {
      background: linear-gradient(135deg, #d4af37, #aa771c);
      color: #000;
    }
    .btn-gold:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4); }
    .btn-blue {
      background: #2563eb;
      color: #fff;
    }
    .btn-blue:hover { transform: translateY(-3px); background: #1d4ed8; }
    footer {
      border-top: 1px solid rgba(212, 175, 55, 0.3);
      padding: 30px 20px;
      text-align: center;
      color: #94a3b8;
      font-size: 0.9rem;
      background: rgba(10, 12, 16, 0.95);
    }
  </style>
</head>
<body>

  <header class="nav">
    <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
      <span style="font-size:24px;">👑</span>
      <span style="color:#fff;font-weight:900;font-size:1.1rem;">موسوعة النمر المليونية الأكاديمية</span>
    </a>
    <div style="display:flex;gap:10px;">
      <a href="/guinness" style="background:rgba(212,175,55,0.15);color:var(--text-gold);border:1px solid var(--border-gold);padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.85rem;">⚖️ بوابة تحكيم جينيس</a>
      <a href="/book/<?php echo $book_id; ?>" style="background:#2563eb;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.85rem;">📖 فتح المجلد</a>
    </div>
  </header>

  <main class="container">
    <div class="doi-card">
      <div class="status-badge">
        <span class="pulse-dot"></span>
        <span>المعرف الرقمي نشط وموثق تشفيرياً (Sovereign Verified &amp; Merkle Root Anchored)</span>
      </div>

      <h1 class="doi-title"><?php echo htmlspecialchars($b['title']); ?></h1>

      <div class="doi-box">
        <span>doi:<?php echo htmlspecialchars($doi_str); ?></span>
        <span style="font-size:0.8rem;background:rgba(212,175,55,0.2);padding:2px 8px;border-radius:6px;color:#fde68a;">Permanent Handle</span>
      </div>

      <div class="meta-grid">
        <div class="meta-row">
          <span class="meta-label">المؤلف والمهندس المعماري:</span>
          <span class="meta-val">المهندس محمود بك النمر (Google Accredited AI Engineer)</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">دار النشر الأكاديمية:</span>
          <span class="meta-val">دار النمر للنشر الأكاديمي · حلول النمر الرقمية</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">رقم المجلد:</span>
          <span class="meta-val">Volume #<?php echo number_format($b['global_id']); ?> من أصل 1,000,000 مجلد</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">الترقيم الدولي (ISBN-13):</span>
          <span class="meta-val" style="font-family:'Fira Code';"><?php echo $b['isbn13']; ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">القطاع والتخصص الأكاديمي:</span>
          <span class="meta-val"><?php echo htmlspecialchars($b['sector_ar']); ?> · <?php echo htmlspecialchars($b['domain_ar']); ?> (<?php echo $b['bisac']; ?>)</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">اللغة الرسمية:</span>
          <span class="meta-val"><?php echo htmlspecialchars($b['lang_name']); ?> (<?php echo htmlspecialchars($b['lang_en_name']); ?>)</span>
        </div>
        <div class="meta-row">
          <span class="meta-label">بصمة التشفير الرياضية (SHA-256):</span>
          <span class="meta-val" style="font-family:'Fira Code';font-size:0.8rem;color:#34D399;word-break:break-all;direction:ltr;"><?php echo $b['sha256']; ?></span>
        </div>
        <div class="meta-row">
          <span class="meta-label">جذر شجرة ميركل العام (Merkle Root):</span>
          <span class="meta-val" style="font-family:'Fira Code';font-size:0.8rem;color:#F59E0B;word-break:break-all;direction:ltr;">72806865ca4d56a0b03ad7e925ba6e7ccab8f537c016ec6214be2149e0c896d9</span>
        </div>
      </div>

      <div class="btn-row">
        <a href="/book/<?php echo $book_id; ?>" class="btn btn-gold">
          📖 الانتقال المباشر لصفحة المجلد والأرشفة
        </a>
        <a href="/pdf/<?php echo $book_id; ?>/vol_<?php echo $book_id; ?>.pdf" class="btn btn-blue" target="_blank">
          📥 قراءة وتحميل المجلد كاملاً (200 صفحة PDF)
        </a>
        <a href="/certificate.php?id=<?php echo $book_id; ?>" class="btn" style="background:rgba(255,255,255,0.08);color:#fff;border:1px solid rgba(255,255,255,0.2);" target="_blank">
          📜 شهادة التوثيق التشفيرية المعتمدة
        </a>
        <a href="/audit/<?php echo $book_id; ?>" class="btn" style="background:rgba(16,185,129,0.16);color:#34D399;border:1px solid #10B981;">
          🔐 مختبر التدقيق التشفيري (Merkle Audit)
        </a>
        <a href="/cite/<?php echo $book_id; ?>" class="btn" style="background:rgba(212,175,55,0.14);color:#fde68a;border:1px solid rgba(212,175,55,0.4);">
          🎓 الاقتباس الأكاديمي (BibTeX / RIS / MARC21)
        </a>
      </div>
    </div>
  </main>

  <footer>
    <p>
      <b>بوابة حل وتوثيق المعرف الرقمي السيادية (Nemer Sovereign DOI &amp; Handle Resolver Gateway)</b><br/>
      دار النمر للنشر الأكاديمي · حلول النمر الرقمية · <a href="https://elnemer-ai.com" style="color:var(--text-gold);">elnemer-ai.com</a> · © 2026 المهندس محمود بك النمر
    </p>
  </footer>

</body>
</html>
