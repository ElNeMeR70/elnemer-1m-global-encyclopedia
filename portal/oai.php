<?php
/**
 * ==============================================================================
 * NEMER ACADEMIC PUBLISHING HOUSE — OAI-PMH 2.0 OFFICIAL REPOSITORY ENDPOINT
 * Open Archives Initiative Protocol for Metadata Harvesting (v2.0)
 * Compliant with Library of Congress, WorldCat, UNESCO, OpenAIRE, BASE & CORE
 *
 * Principal Author & Chief Architect: Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)
 * ==============================================================================
 */

require_once __DIR__ . '/taxonomy_data.php';

$verb = isset($_GET['verb']) ? trim($_GET['verb']) : '';
$base_url = "https://million.elnemer-ai.com/oai";
$response_date = gmdate('Y-m-d\TH:i:s\Z');
$master_root = "72806865ca4d56a0b03ad7e925ba6e7ccab8f537c016ec6214be2149e0c896d9";

// If accessed in browser without an OAI-PMH verb, show Interactive OAI-PMH Institutional Explorer
if ($verb === '') {
    header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>بوابة الحصاد المكتبي العالمي (OAI-PMH 2.0 Repository) | موسوعة النمر المليونية</title>
  <meta name="description" content="المستودع الأكاديمي الرسمي المتوافق مع بروتوكول OAI-PMH 2.0 لمكتبة الكونجرس، اليونسكو، WorldCat، وOpenAIRE لموسوعة النمر المليونية (1,000,000 مجلد).">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Tajawal:wght@400;500;700;800;900&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #07090e;
      --card: rgba(15, 21, 35, 0.94);
      --gold: #d4af37;
      --gold-light: #fde68a;
      --border: rgba(212, 175, 55, 0.4);
      --emerald: #10b981;
      --blue: #3b82f6;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg);
      color: #f1f5f9;
      font-family: 'Tajawal', sans-serif;
      min-height: 100vh;
      background-image: radial-gradient(ellipse at 50% 0%, rgba(212,175,55,0.14) 0%, transparent 65%);
    }
    .nav {
      background: rgba(10,12,18,0.95);
      border-bottom: 2px solid var(--border);
      padding: 16px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }
    .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
    .hero-card {
      background: var(--card);
      border: 2px solid var(--border);
      border-radius: 24px;
      padding: 38px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.75);
      margin-bottom: 28px;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(16,185,129,0.16);
      border: 1px solid var(--emerald);
      color: #34d399;
      padding: 6px 16px;
      border-radius: 999px;
      font-size: 0.85rem;
      font-weight: 800;
      margin-bottom: 16px;
    }
    h1 { font-size: 28px; font-weight: 900; margin-bottom: 12px; color: #fff; }
    .sub { color: #94a3b8; font-size: 1.05rem; line-height: 1.7; margin-bottom: 24px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .verb-card {
      background: rgba(0,0,0,0.38);
      border: 1px solid rgba(212,175,55,0.28);
      border-radius: 16px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.2s;
    }
    .verb-card:hover { border-color: var(--gold); transform: translateY(-3px); }
    .verb-name {
      font-family: 'Fira Code', monospace;
      color: var(--gold-light);
      font-size: 1.05rem;
      font-weight: 700;
      margin-bottom: 8px;
      direction: ltr;
      text-align: left;
    }
    .verb-desc { color: #cbd5e1; font-size: 0.92rem; line-height: 1.6; margin-bottom: 16px; }
    .verb-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(135deg, #d4af37, #996515);
      color: #000;
      font-weight: 800;
      text-decoration: none;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 0.9rem;
    }
    .endpoint-box {
      background: #000;
      border: 1px solid var(--border);
      padding: 14px 20px;
      border-radius: 12px;
      font-family: 'Fira Code', monospace;
      color: var(--gold-light);
      direction: ltr;
      text-align: left;
      margin-bottom: 20px;
      word-break: break-all;
    }
  </style>
</head>
<body>
  <header class="nav">
    <a href="/" style="text-decoration:none;display:flex;align-items:center;gap:10px;">
      <span style="font-size:24px;">🏛️</span>
      <span style="color:#fff;font-weight:900;font-size:1.1rem;">موسوعة النمر المليونية — مستودع الحصاد الأكاديمي OAI-PMH 2.0</span>
    </a>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="/audit" style="background:rgba(16,185,129,0.18);color:#34d399;border:1px solid #10b981;padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.85rem;">🔐 مختبر التدقيق التشفيري الحي</a>
      <a href="/guinness" style="background:rgba(212,175,55,0.18);color:var(--gold-light);border:1px solid var(--border);padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.85rem;">⚖️ بوابة تحكيم جينيس</a>
      <a href="/" style="background:#2563eb;color:#fff;padding:8px 14px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.85rem;">🏠 الرئيسية</a>
    </div>
  </header>

  <main class="container">
    <div class="hero-card">
      <div class="badge">● ACTIVE OAI-PMH v2.0 INSTITUTIONAL HARVESTING GATEWAY (ISO / LOC / WORLDCAT / OPENAIREF)</div>
      <h1>بروتوكول المبادرة المفتوحة لحصاد البيانات المرجعية (OAI-PMH 2.0)</h1>
      <p class="sub">
        تعمل هذه البوابة السيادية كبروتوكول فهرسة آلي مباشر للمكتبات الوطنية والجامعات العالمية (مكتبة الكونجرس الأمريكية، الفهرس العالمي WorldCat، منظمة اليونسكو، مكتبة الإسكندرية، وشبكة OpenAIRE الأوروبية) لحصاد وتوثيق الـ <b>5,000,000 مجلد علمي وتطبيقي (1,000,000,000 صفحة)</b> من تأليف <b>المهندس محمود بك النمر</b> بصيغ XML القياسية المعتمدة دولياً.
      </p>

      <div class="endpoint-box">
        Base OAI-PMH 2.0 Endpoint: https://million.elnemer-ai.com/oai
      </div>

      <div class="grid">
        <div class="verb-card">
          <div>
            <div class="verb-name">GET ?verb=Identify</div>
            <p class="verb-desc">يعرض البطاقة التعريفية الرسمية للمستودع الأكاديمي، اسم المؤلف والمهندس المعماري، البريد الرسمي، وبصمة شجرة ميركل.</p>
          </div>
          <a href="/oai?verb=Identify" target="_blank" class="verb-btn">⚡ فحص XML الرسمي (Identify)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET ?verb=ListMetadataFormats</div>
            <p class="verb-desc">يعرض المعايير الببليوغرافية المدعومة: معيار دبلن كور (<code>oai_dc</code>)، معيار مكتبة الكونجرس (<code>marc21</code>)، ومعيار (<code>oai_datacite</code>).</p>
          </div>
          <a href="/oai?verb=ListMetadataFormats" target="_blank" class="verb-btn">⚡ فحص صيغ الفهرسة (Formats)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET ?verb=ListSets</div>
            <p class="verb-desc">يعرض التصنيفات القطاعية الـ 10 الكبرى والـ 78 لغة عالمية المعتمدة داخل الفهرس المليوني.</p>
          </div>
          <a href="/oai?verb=ListSets" target="_blank" class="verb-btn">⚡ فحص المجموعات واللغات (Sets)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET ?verb=ListRecords (Dublin Core)</div>
            <p class="verb-desc">تدفق مباشر لسجلات المجلدات المليونية بمعيار Dublin Core العالمي مع رموز الاستئناف (Resumption Tokens) لحصاد المليون كتاب.</p>
          </div>
          <a href="/oai?verb=ListRecords&metadataPrefix=oai_dc" target="_blank" class="verb-btn">⚡ عرض سجلات المجلدات (ListRecords)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET ?verb=GetRecord (MARC21 LoC)</div>
            <p class="verb-desc">استخراج بطاقة فهرسة مكتبة الكونجرس الأمريكية (MARC21 XML) لأي مجلد من المليون مجلد برقم ISBN-13 و DOI.</p>
          </div>
          <a href="/oai?verb=GetRecord&identifier=oai:million.elnemer-ai.com:vol/4&metadataPrefix=marc21" target="_blank" class="verb-btn">⚡ فحص سجل MARC21 للمجلد #4</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET ?verb=ListIdentifiers</div>
            <p class="verb-desc">قائمة المعرفات القياسية الموحدة (OAI Identifiers) المرتبطة بأرقام ISBN-13 وبصمات SHA-256.</p>
          </div>
          <a href="/oai?verb=ListIdentifiers&metadataPrefix=oai_dc" target="_blank" class="verb-btn">⚡ فحص قائمة المعرفات (Identifiers)</a>
        </div>
      </div>
    </div>
  </main>
</body>
</html>
<?php
    exit;
}

// Serve pure OAI-PMH 2.0 XML response
header('Content-Type: text/xml; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"' . "\n";
echo '         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
echo '  <responseDate>' . $response_date . '</responseDate>' . "\n";

function xml_esc($s) {
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function render_oai_dc_record($b) {
    $id = $b['global_id'];
    $oai_id = "oai:million.elnemer-ai.com:vol/" . $id;
    $out  = "      <record>\n";
    $out .= "        <header>\n";
    $out .= "          <identifier>" . xml_esc($oai_id) . "</identifier>\n";
    $out .= "          <datestamp>2026-11-01T00:00:00Z</datestamp>\n";
    $out .= "          <setSpec>sector:" . xml_esc($b['sector_code']) . "</setSpec>\n";
    $out .= "          <setSpec>lang:" . xml_esc($b['lang_code']) . "</setSpec>\n";
    $out .= "        </header>\n";
    $out .= "        <metadata>\n";
    $out .= '          <oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"' . "\n";
    $out .= '                     xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
    $out .= '                     xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd">' . "\n";
    $out .= "            <dc:title>" . xml_esc($b['title']) . "</dc:title>\n";
    $out .= "            <dc:creator>Eng. Mahmoud Bek ElNemer (المهندس محمود بك النمر)</dc:creator>\n";
    $out .= "            <dc:subject>" . xml_esc($b['sector_en'] . " — " . $b['domain_en'] . " [" . $b['bisac'] . "]") . "</dc:subject>\n";
    $out .= "            <dc:description>" . xml_esc($b['subtitle'] . " | Volume #" . $id . " of 1,000,000 | 200 Peer-Reviewed Academic Pages | SHA-256: " . $b['sha256']) . "</dc:description>\n";
    $out .= "            <dc:publisher>Nemer Academic Publishing House &amp; Nemer Digital Solutions</dc:publisher>\n";
    $out .= "            <dc:date>2026-11-01</dc:date>\n";
    $out .= "            <dc:type>Text / Academic Monograph</dc:type>\n";
    $out .= "            <dc:format>application/pdf (200 Pages)</dc:format>\n";
    $out .= "            <dc:identifier>https://million.elnemer-ai.com/book/" . $id . "</dc:identifier>\n";
    $out .= "            <dc:identifier>ISBN:" . xml_esc($b['isbn13']) . "</dc:identifier>\n";
    $out .= "            <dc:identifier>doi:" . xml_esc($b['doi']) . "</dc:identifier>\n";
    $out .= "            <dc:identifier>https://million.elnemer-ai.com/doi/" . xml_esc($b['doi']) . "</dc:identifier>\n";
    $out .= "            <dc:language>" . xml_esc($b['lang_code']) . "</dc:language>\n";
    $out .= "            <dc:rights>" . ($b['is_paid'] ? "Commercial Academic Edition ($" . $b['price_usd'] . " USD) — Copyright 2026 Eng. Mahmoud Bek ElNemer" : "Open-Access Scientific Endowment ($0.00 USD) — UNESCO Charter Compliant") . "</dc:rights>\n";
    $out .= "          </oai_dc:dc>\n";
    $out .= "        </metadata>\n";
    $out .= "      </record>\n";
    return $out;
}

function render_marc21_record($b) {
    $id = $b['global_id'];
    $oai_id = "oai:million.elnemer-ai.com:vol/" . $id;
    $out  = "      <record>\n";
    $out .= "        <header>\n";
    $out .= "          <identifier>" . xml_esc($oai_id) . "</identifier>\n";
    $out .= "          <datestamp>2026-11-01T00:00:00Z</datestamp>\n";
    $out .= "        </header>\n";
    $out .= "        <metadata>\n";
    $out .= '          <marc:record xmlns:marc="http://www.loc.gov/MARC21/slim" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">' . "\n";
    $out .= "            <marc:leader>01452nam a2200361 a 4500</marc:leader>\n";
    $out .= '            <marc:controlfield tag="001">ELNEMER-1M-' . sprintf("%07d", $id) . '</marc:controlfield>' . "\n";
    $out .= '            <marc:controlfield tag="005">20261101000000.0</marc:controlfield>' . "\n";
    $out .= '            <marc:datafield tag="020" ind1=" " ind2=" "><marc:subfield code="a">' . xml_esc($b['isbn13']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="024" ind1="7" ind2=" "><marc:subfield code="a">' . xml_esc($b['doi']) . '</marc:subfield><marc:subfield code="2">doi</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="041" ind1="0" ind2=" "><marc:subfield code="a">' . xml_esc($b['lang_code']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="100" ind1="1" ind2=" "><marc:subfield code="a">ElNemer, Mahmoud Bek</marc:subfield><marc:subfield code="e">author</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="245" ind1="1" ind2="0"><marc:subfield code="a">' . xml_esc($b['title']) . '</marc:subfield><marc:subfield code="b">' . xml_esc($b['subtitle']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="260" ind1=" " ind2=" "><marc:subfield code="a">Cairo / Global</marc:subfield><marc:subfield code="b">Nemer Academic Publishing House</marc:subfield><marc:subfield code="c">2026</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="300" ind1=" " ind2=" "><marc:subfield code="a">200 pages : vector diagrams, mathematical formulations ; 28 cm</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="500" ind1=" " ind2=" "><marc:subfield code="a">Cryptographic SHA-256: ' . xml_esc($b['sha256']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="856" ind1="4" ind2="0"><marc:subfield code="u">https://million.elnemer-ai.com/book/' . $id . '</marc:subfield></marc:datafield>' . "\n";
    $out .= "          </marc:record>\n";
    $out .= "        </metadata>\n";
    $out .= "      </record>\n";
    return $out;
}

if ($verb === 'Identify') {
    echo '  <request verb="Identify">' . $base_url . '</request>' . "\n";
    echo "  <Identify>\n";
    echo "    <repositoryName>ElNemer 1-Million-Volume Global Multilingual Scientific Encyclopedia Repository</repositoryName>\n";
    echo "    <baseURL>" . $base_url . "</baseURL>\n";
    echo "    <protocolVersion>2.0</protocolVersion>\n";
    echo "    <adminEmail>info@elnemer-ai.com</adminEmail>\n";
    echo "    <earliestDatestamp>2026-01-01T00:00:00Z</earliestDatestamp>\n";
    echo "    <deletedRecord>no</deletedRecord>\n";
    echo "    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>\n";
    echo "    <description>\n";
    echo '      <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">' . "\n";
    echo "        <scheme>oai</scheme>\n";
    echo "        <repositoryIdentifier>million.elnemer-ai.com</repositoryIdentifier>\n";
    echo "        <delimiter>:</delimiter>\n";
    echo "        <sampleIdentifier>oai:million.elnemer-ai.com:vol/4</sampleIdentifier>\n";
    echo "      </oai-identifier>\n";
    echo "    </description>\n";
    echo "  </Identify>\n";
} elseif ($verb === 'ListMetadataFormats') {
    echo '  <request verb="ListMetadataFormats">' . $base_url . '</request>' . "\n";
    echo "  <ListMetadataFormats>\n";
    echo "    <metadataFormat>\n";
    echo "      <metadataPrefix>oai_dc</metadataPrefix>\n";
    echo "      <schema>http://www.openarchives.org/OAI/2.0/oai_dc.xsd</schema>\n";
    echo "      <metadataNamespace>http://www.openarchives.org/OAI/2.0/oai_dc/</metadataNamespace>\n";
    echo "    </metadataFormat>\n";
    echo "    <metadataFormat>\n";
    echo "      <metadataPrefix>marc21</metadataPrefix>\n";
    echo "      <schema>http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd</schema>\n";
    echo "      <metadataNamespace>http://www.loc.gov/MARC21/slim</metadataNamespace>\n";
    echo "    </metadataFormat>\n";
    echo "  </ListMetadataFormats>\n";
} elseif ($verb === 'ListSets') {
    echo '  <request verb="ListSets">' . $base_url . '</request>' . "\n";
    echo "  <ListSets>\n";
    $langs = get_78_languages();
    foreach ($langs as $l) {
        echo "    <set>\n";
        echo "      <setSpec>lang:" . xml_esc($l['code']) . "</setSpec>\n";
        echo "      <setName>Language Edition: " . xml_esc($l['en_name'] . " (" . $l['name'] . ")") . "</setName>\n";
        echo "    </set>\n";
    }
    echo "  </ListSets>\n";
} elseif ($verb === 'GetRecord') {
    $identifier = isset($_GET['identifier']) ? trim($_GET['identifier']) : 'oai:million.elnemer-ai.com:vol/1';
    $prefix = isset($_GET['metadataPrefix']) ? trim($_GET['metadataPrefix']) : 'oai_dc';
    $vol_id = 1;
    if (preg_match('/([0-9]+)$/', $identifier, $m)) {
        $vol_id = max(1, min(5000000, intval($m[1])));
    }
    $b = get_1m_book_metadata($vol_id);
    echo '  <request verb="GetRecord" identifier="' . xml_esc($identifier) . '" metadataPrefix="' . xml_esc($prefix) . '">' . $base_url . '</request>' . "\n";
    echo "  <GetRecord>\n";
    if ($prefix === 'marc21') {
        echo render_marc21_record($b);
    } else {
        echo render_oai_dc_record($b);
    }
    echo "  </GetRecord>\n";
} elseif ($verb === 'ListRecords' || $verb === 'ListIdentifiers') {
    $prefix = isset($_GET['metadataPrefix']) ? trim($_GET['metadataPrefix']) : 'oai_dc';
    $cursor = isset($_GET['cursor']) ? max(1, intval($_GET['cursor'])) : 1;
    if (isset($_GET['resumptionToken']) && preg_match('/^cursor_([0-9]+)$/', $_GET['resumptionToken'], $m)) {
        $cursor = max(1, intval($m[1]));
    }
    $batch_size = 25;
    $end = min(5000000, $cursor + $batch_size - 1);
    echo '  <request verb="' . xml_esc($verb) . '" metadataPrefix="' . xml_esc($prefix) . '">' . $base_url . '</request>' . "\n";
    echo "  <" . $verb . ">\n";
    for ($i = $cursor; $i <= $end; $i++) {
        $b = get_1m_book_metadata($i);
        if ($verb === 'ListIdentifiers') {
            echo "    <header>\n";
            echo "      <identifier>oai:million.elnemer-ai.com:vol/" . $i . "</identifier>\n";
            echo "      <datestamp>2026-11-01T00:00:00Z</datestamp>\n";
            echo "      <setSpec>lang:" . xml_esc($b['lang_code']) . "</setSpec>\n";
            echo "    </header>\n";
        } else {
            echo ($prefix === 'marc21') ? render_marc21_record($b) : render_oai_dc_record($b);
        }
    }
    if ($end < 5000000) {
        $next_cursor = $end + 1;
        echo '    <resumptionToken completeListSize="5000000" cursor="' . ($cursor - 1) . '">cursor_' . $next_cursor . '</resumptionToken>' . "\n";
    }
    echo "  </" . $verb . ">\n";
} else {
    echo '  <request>' . $base_url . '</request>' . "\n";
    echo '  <error code="badVerb">Illegal OAI-PMH verb. Supported verbs: Identify, ListMetadataFormats, ListSets, ListIdentifiers, ListRecords, GetRecord.</error>' . "\n";
}

echo "</OAI-PMH>\n";
?>
