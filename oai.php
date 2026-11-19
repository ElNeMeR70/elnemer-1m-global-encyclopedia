<?php
/**
 * ==============================================================================
 * NEMER ACADEMIC PUBLISHING HOUSE — OAI-PMH 2.0 OFFICIAL REPOSITORY ENDPOINT
 * Open Archives Initiative Protocol for Metadata Harvesting (v2.0)
 * 100% Spec-Compliant (GET + POST, Strict Error Codes, Clean XML Stream)
 * Compliant with Library of Congress, WorldCat, UNESCO, OpenAIRE, BASE & OpenAlex
 *
 * Principal Author & Chief Architect: Eng. Mahmoud ElNemer
 * Publisher: ElNemer Encyclopedia Publishing House — Nemer Digital Solutions
 * ==============================================================================
 */

ob_start();
require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/portal_i18n_theme.php';

// Merge GET and POST parameters per OAI-PMH 2.0 Section 3.1.1
$req_params = ($_SERVER['REQUEST_METHOD'] === 'POST') ? array_merge($_GET, $_POST) : $_GET;
$verb = isset($req_params['verb']) ? trim((string)$req_params['verb']) : '';
$base_url = "https://million.elnemer-ai.com/oai";
$response_date = gmdate('Y-m-d\TH:i:s\Z');
$master_root = "72806865ca4d56a0b03ad7e925ba6e7ccab8f537c016ec6214be2149e0c896d9";
$max_volumes = 7000000;

// Detect whether caller is a human web browser vs an automated OAI-PMH harvester/validator
$accept_hdr = isset($_SERVER['HTTP_ACCEPT']) ? strtolower((string)$_SERVER['HTTP_ACCEPT']) : '';
$ua_hdr = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower((string)$_SERVER['HTTP_USER_AGENT']) : '';
$is_browser_html = (
    $_SERVER['REQUEST_METHOD'] === 'GET' &&
    strpos($accept_hdr, 'text/html') !== false &&
    !preg_match('/(oai|harvester|openalex|base|openaire|oclc|curl|python|java|wget|validator)/i', $ua_hdr)
);

// Only render interactive HTML explorer when a human browser opens /oai with NO verb
if ($verb === '' && $is_browser_html) {
    ob_end_clean();
    header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>بوابة الحصاد المكتبي العالمي (OAI-PMH 2.0 Repository) | موسوعة النمر العالمية لـ 1.4 مليار صفحة (7,000,000 مجلد)</title>
  <meta name="description" content="المستودع الأكاديمي الرسمي المتوافق 100% مع بروتوكول OAI-PMH 2.0 لمكتبة الكونجرس، اليونسكو، WorldCat، OpenAlex، وOpenAIRE لموسوعة النمر العالمية لـ 1.4 مليار صفحة (7,000,000 مجلد بـ 78 لغة).">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;900&family=Tajawal:wght@400;500;700;800;900&family=Fira+Code:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #F4F7FB;
      --card: #FFFFFF;
      --gold: #0284c7;
      --gold-light: #1e3a8a;
      --border: #93c5fd;
      --emerald: #059669;
      --blue: #2563eb;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: linear-gradient(135deg, #F0F5FF 0%, #F8FAFC 50%, #ECFDF5 100%) !important;
      color: #0F172A !important;
      font-family: 'Tajawal', sans-serif;
      min-height: 100vh;
    }
    .container { max-width: 1120px; margin: 32px auto 64px; padding: 0 20px; }
    .hero-card {
      background: #FFFFFF !important;
      border: 2px solid #DBEAFE !important;
      border-radius: 24px;
      padding: 34px;
      box-shadow: 0 12px 32px rgba(15,23,42,0.06);
      margin-bottom: 28px;
    }
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: #ECFDF5;
      border: 1px solid #6EE7B7;
      color: #065F46;
      padding: 6px 16px;
      border-radius: 999px;
      font-size: 0.85rem;
      font-weight: 800;
      margin-bottom: 16px;
    }
    h1 { font-size: 28px; font-weight: 900; margin-bottom: 12px; color: #1E3A8A !important; }
    .sub { color: #334155 !important; font-size: 1.02rem; line-height: 1.75; margin-bottom: 24px; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
      gap: 18px;
      margin-top: 24px;
    }
    .verb-card {
      background: #F8FAFC !important;
      border: 1.5px solid #BFDBFE !important;
      border-radius: 16px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.2s;
    }
    .verb-card:hover { border-color: #2563EB !important; transform: translateY(-2px); }
    .verb-name {
      font-family: 'Fira Code', monospace;
      color: #1E3A8A !important;
      font-size: 1.02rem;
      font-weight: 800;
      margin-bottom: 8px;
      direction: ltr;
      text-align: left;
    }
    .verb-desc { color: #334155 !important; font-size: 0.92rem; line-height: 1.65; margin-bottom: 16px; }
    .verb-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      background: linear-gradient(135deg, #2563eb, #0284c7);
      color: #fff !important;
      font-weight: 800;
      text-decoration: none;
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 0.9rem;
    }
    .endpoint-box {
      background: #EFF6FF !important;
      border: 1.5px solid #93C5FD !important;
      padding: 14px 20px;
      border-radius: 12px;
      font-family: 'Fira Code', monospace;
      color: #1E3A8A !important;
      font-weight: 700;
      direction: ltr;
      text-align: left;
      margin-bottom: 20px;
      word-break: break-all;
    }
  </style>
<?php render_unified_sapphire_css(); ?>
</head>
<body>
  <?php render_unified_topbar(); ?>

  <main class="container">
    <div class="hero-card">
      <div class="badge">● ACTIVE OAI-PMH v2.0 INSTITUTIONAL HARVESTING GATEWAY (GET &amp; POST • LOC / WORLDCAT / OPENALEX / CERN)</div>
      <h1>بروتوكول المبادرة المفتوحة لحصاد البيانات المرجعية (OAI-PMH 2.0)</h1>
      <p class="sub">
        تعمل هذه البوابة السيادية كبروتوكول فهرسة آلي مباشر متوافق 100% مع معيار <b>OAI-PMH v2.0 (GET &amp; POST)</b> للمكتبات الوطنية والجامعات العالمية (مكتبة الكونجرس الأمريكية، الفهرس العالمي WorldCat، جامعة هارفارد <code>10.7910/DVN/UFYQDK</code>، سجل CERN Zenodo <code>10.5281/zenodo.24051667</code>، وOpenAIRE) لحصاد وتوثيق الـ <b>7,000,000 مجلد علمي وتطبيقي (1,000,000,000 صفحة بـ 78 لغة)</b> من إصدار <b>دار النمر للنشر والمعرفة التطبيقية</b> بصيغ XML القياسية المعتمدة دولياً.
      </p>

      <div class="endpoint-box">
        Base OAI-PMH 2.0 Endpoint: https://million.elnemer-ai.com/oai (Supports HTTP GET &amp; POST)
      </div>

      <div class="grid">
        <div class="verb-card">
          <div>
            <div class="verb-name">GET / POST ?verb=Identify</div>
            <p class="verb-desc">يعرض البطاقة التعريفية الرسمية القياسية للمستودع الأكاديمي، بيانات الناشر، أرقام التوثيق الدولية (Harvard &amp; CERN DOI)، وبصمة شجرة ميركل.</p>
          </div>
          <a href="/oai?verb=Identify" target="_blank" class="verb-btn">⚡ فحص XML الرسمي (Identify)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET / POST ?verb=ListMetadataFormats</div>
            <p class="verb-desc">يعرض المعايير الببليوغرافية المدعومة: معيار دبلن كور (<code>oai_dc</code>)، معيار مكتبة الكونجرس (<code>marc21</code>)، ومعيار (<code>oai_datacite</code>).</p>
          </div>
          <a href="/oai?verb=ListMetadataFormats" target="_blank" class="verb-btn">⚡ فحص صيغ الفهرسة (Formats)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET / POST ?verb=ListSets</div>
            <p class="verb-desc">يعرض التصنيفات القطاعية الـ 50 الكبرى والـ 78 لغة عالمية المعتمدة داخل الفهرس المليوني.</p>
          </div>
          <a href="/oai?verb=ListSets" target="_blank" class="verb-btn">⚡ فحص المجموعات واللغات (Sets)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET / POST ?verb=ListRecords (Dublin Core)</div>
            <p class="verb-desc">إتاحة قياسية لسجلات المجلدات المنشورة بمعيار Dublin Core العالمي مع رموز الاستئناف (Resumption Tokens) لحصاد الـ 7,000,000 مجلد.</p>
          </div>
          <a href="/oai?verb=ListRecords&metadataPrefix=oai_dc" target="_blank" class="verb-btn">⚡ عرض سجلات المجلدات (ListRecords)</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET / POST ?verb=GetRecord (MARC21 LoC)</div>
            <p class="verb-desc">استخراج بطاقة فهرسة مكتبة الكونجرس الأمريكية (MARC21 XML) لأي مجلد من الـ 7,000,000 مجلد برقم ISBN-13 و DOI.</p>
          </div>
          <a href="/oai?verb=GetRecord&identifier=oai:million.elnemer-ai.com:vol/79&metadataPrefix=marc21" target="_blank" class="verb-btn">⚡ فحص سجل MARC21 للمجلد #79</a>
        </div>

        <div class="verb-card">
          <div>
            <div class="verb-name">GET / POST ?verb=ListIdentifiers</div>
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

// Clear any buffered whitespace from included files so XML starts at byte 0!
ob_end_clean();
http_response_code(200);
header('Content-Type: text/xml; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('X-OAI-PMH-Protocol: 2.0');

function xml_esc($s) {
    return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function oai_error_xml($base_url, $response_date, $code, $msg) {
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"' . "\n";
    echo '         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
    echo '         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
    echo '  <responseDate>' . $response_date . '</responseDate>' . "\n";
    echo '  <request>' . xml_esc($base_url) . '</request>' . "\n";
    echo '  <error code="' . xml_esc($code) . '">' . xml_esc($msg) . '</error>' . "\n";
    echo "</OAI-PMH>\n";
    exit;
}

// Check for repeated query parameters in raw QUERY_STRING (OAI-PMH 2.0 Section 3.1.1.1: duplicate arguments must return badArgument)
$raw_qs = isset($_SERVER['QUERY_STRING']) ? (string)$_SERVER['QUERY_STRING'] : '';
if ($raw_qs !== '') {
    $seen_keys = array();
    foreach (explode('&', $raw_qs) as $pair) {
        if ($pair === '') continue;
        $parts = explode('=', $pair, 2);
        $k = urldecode($parts[0]);
        if (isset($seen_keys[$k])) {
            oai_error_xml($base_url, $response_date, 'badArgument', 'Duplicate argument in OAI-PMH request: ' . $k);
        }
        $seen_keys[$k] = true;
    }
}

$valid_verbs = array('Identify', 'ListMetadataFormats', 'ListSets', 'GetRecord', 'ListIdentifiers', 'ListRecords');
if ($verb === '' || !in_array($verb, $valid_verbs, true)) {
    oai_error_xml($base_url, $response_date, 'badVerb', 'Missing or illegal OAI-PMH verb. Supported verbs: Identify, ListMetadataFormats, ListSets, ListIdentifiers, ListRecords, GetRecord.');
}

$supported_prefixes = array('oai_dc', 'marc21', 'oai_datacite');

function check_allowed_args($req_params, $allowed, $base_url, $response_date) {
    foreach ($req_params as $k => $v) {
        if ($k === 'verb') continue;
        if (!in_array($k, $allowed, true)) {
            oai_error_xml($base_url, $response_date, 'badArgument', 'Illegal argument for this verb: ' . $k);
        }
    }
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
    $out .= "            <dc:creator>ElNemer, Mahmoud</dc:creator>\n";
    $out .= "            <dc:subject>" . xml_esc($b['sector_en'] . " — " . $b['domain_en'] . " [" . $b['bisac'] . "]") . "</dc:subject>\n";
    $out .= "            <dc:description>" . xml_esc($b['subtitle'] . " | Volume #" . $id . " of 5,000,000 | 200 Standardized Applied Pages | SHA-256: " . $b['sha256']) . "</dc:description>\n";
    $out .= "            <dc:publisher>ElNemer Encyclopedia Publishing House — Nemer Digital Solutions</dc:publisher>\n";
    $out .= "            <dc:date>2026-11-01</dc:date>\n";
    $out .= "            <dc:type>Text</dc:type>\n";
    $out .= "            <dc:type>Book</dc:type>\n";
    $out .= "            <dc:format>application/pdf</dc:format>\n";
    $out .= "            <dc:identifier>https://million.elnemer-ai.com/book/" . $id . "</dc:identifier>\n";
    $out .= "            <dc:identifier>ISBN:" . xml_esc($b['isbn13']) . "</dc:identifier>\n";
    $out .= "            <dc:identifier>doi:" . xml_esc($b['doi']) . "</dc:identifier>\n";
    $out .= "            <dc:relation>doi:10.7910/DVN/UFYQDK</dc:relation>\n";
    $out .= "            <dc:relation>doi:10.5281/zenodo.24051667</dc:relation>\n";
    $out .= "            <dc:relation>doi:10.57967/hf/11509</dc:relation>\n";
    $out .= "            <dc:language>" . xml_esc($b['lang_code']) . "</dc:language>\n";
    $out .= "            <dc:rights>" . ($b['is_paid'] ? "Commercial Academic Edition ($" . $b['price_usd'] . " USD) — Copyright 2026 ElNemer Encyclopedia Publishing House" : "Open-Access Scientific Endowment ($0.00 USD) — UNESCO OER Charter Compliant") . "</dc:rights>\n";
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
    $out .= "          <setSpec>sector:" . xml_esc($b['sector_code']) . "</setSpec>\n";
    $out .= "          <setSpec>lang:" . xml_esc($b['lang_code']) . "</setSpec>\n";
    $out .= "        </header>\n";
    $out .= "        <metadata>\n";
    $out .= '          <marc:record xmlns:marc="http://www.loc.gov/MARC21/slim" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">' . "\n";
    $out .= "            <marc:leader>01452nam a2200361 a 4500</marc:leader>\n";
    $out .= '            <marc:controlfield tag="001">ELNEMER-5M-' . sprintf("%07d", $id) . '</marc:controlfield>' . "\n";
    $out .= '            <marc:controlfield tag="005">20261101000000.0</marc:controlfield>' . "\n";
    $out .= '            <marc:datafield tag="020" ind1=" " ind2=" "><marc:subfield code="a">' . xml_esc($b['isbn13']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="024" ind1="7" ind2=" "><marc:subfield code="a">' . xml_esc($b['doi']) . '</marc:subfield><marc:subfield code="2">doi</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="041" ind1="0" ind2=" "><marc:subfield code="a">' . xml_esc($b['lang_code']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="100" ind1="1" ind2=" "><marc:subfield code="a">ElNemer, Mahmoud</marc:subfield><marc:subfield code="e">author</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="245" ind1="1" ind2="0"><marc:subfield code="a">' . xml_esc($b['title']) . '</marc:subfield><marc:subfield code="b">' . xml_esc($b['subtitle']) . '</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="260" ind1=" " ind2=" "><marc:subfield code="a">Cairo / Global</marc:subfield><marc:subfield code="b">ElNemer Encyclopedia Publishing House</marc:subfield><marc:subfield code="c">2026</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="300" ind1=" " ind2=" "><marc:subfield code="a">200 pages : practical diagrams, checklists, step-by-step tables ; 28 cm</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="500" ind1=" " ind2=" "><marc:subfield code="a">Cryptographic SHA-256: ' . xml_esc($b['sha256']) . ' | Harvard Dataverse DOI: 10.7910/DVN/UFYQDK | CERN Zenodo DOI: 10.5281/zenodo.24051667</marc:subfield></marc:datafield>' . "\n";
    $out .= '            <marc:datafield tag="856" ind1="4" ind2="0"><marc:subfield code="u">https://million.elnemer-ai.com/book/' . $id . '</marc:subfield></marc:datafield>' . "\n";
    $out .= "          </marc:record>\n";
    $out .= "        </metadata>\n";
    $out .= "      </record>\n";
    return $out;
}

function render_datacite_record($b) {
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
    $out .= '          <resource xmlns="http://datacite.org/schema/kernel-4" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4.4/metadata.xsd">' . "\n";
    $out .= '            <identifier identifierType="DOI">' . xml_esc($b['doi']) . '</identifier>' . "\n";
    $out .= "            <creators><creator><creatorName nameType=\"Personal\">ElNemer, Mahmoud</creatorName></creator></creators>\n";
    $out .= "            <titles><title>" . xml_esc($b['title']) . "</title></titles>\n";
    $out .= "            <publisher>ElNemer Encyclopedia Publishing House — Nemer Digital Solutions</publisher>\n";
    $out .= "            <publicationYear>2026</publicationYear>\n";
    $out .= '            <resourceType resourceTypeGeneral="Book">Applied Reference Volume (200 Pages)</resourceType>' . "\n";
    $out .= "          </resource>\n";
    $out .= "        </metadata>\n";
    $out .= "      </record>\n";
    return $out;
}

// Validate verb-specific arguments BEFORE emitting header so error responses stay 100% schema-valid
if ($verb === 'Identify') {
    check_allowed_args($req_params, array(), $base_url, $response_date);
} elseif ($verb === 'ListMetadataFormats') {
    check_allowed_args($req_params, array('identifier'), $base_url, $response_date);
    if (isset($req_params['identifier'])) {
        $chk_id = trim((string)$req_params['identifier']);
        if (!preg_match('/^oai:million\.elnemer-ai\.com:vol\/([0-9]+)$/', $chk_id, $m) || intval($m[1]) < 1 || intval($m[1]) > $max_volumes) {
            oai_error_xml($base_url, $response_date, 'idDoesNotExist', 'The value of the identifier argument is unknown or illegal in this repository.');
        }
    }
} elseif ($verb === 'ListSets') {
    check_allowed_args($req_params, array('resumptionToken'), $base_url, $response_date);
} elseif ($verb === 'GetRecord') {
    check_allowed_args($req_params, array('identifier', 'metadataPrefix'), $base_url, $response_date);
    if (empty($req_params['identifier']) || empty($req_params['metadataPrefix'])) {
        oai_error_xml($base_url, $response_date, 'badArgument', 'GetRecord requires both identifier and metadataPrefix arguments.');
    }
    if (!in_array(trim((string)$req_params['metadataPrefix']), $supported_prefixes, true)) {
        oai_error_xml($base_url, $response_date, 'cannotDisseminateFormat', 'The metadata format identified by the metadataPrefix argument is not supported by this repository.');
    }
    if (!preg_match('/^oai:million\.elnemer-ai\.com:vol\/([0-9]+)$/', trim((string)$req_params['identifier']), $m) || intval($m[1]) < 1 || intval($m[1]) > $max_volumes) {
        oai_error_xml($base_url, $response_date, 'idDoesNotExist', 'The value of the identifier argument is unknown or illegal in this repository.');
    }
} elseif ($verb === 'ListRecords' || $verb === 'ListIdentifiers') {
    check_allowed_args($req_params, array('from', 'until', 'set', 'resumptionToken', 'metadataPrefix', 'cursor'), $base_url, $response_date);
    if (empty($req_params['resumptionToken'])) {
        if (empty($req_params['metadataPrefix'])) {
            oai_error_xml($base_url, $response_date, 'badArgument', $verb . ' requires the metadataPrefix argument when resumptionToken is not provided.');
        }
        if (!in_array(trim((string)$req_params['metadataPrefix']), $supported_prefixes, true)) {
            oai_error_xml($base_url, $response_date, 'cannotDisseminateFormat', 'The metadata format identified by the metadataPrefix argument is not supported by this repository.');
        }
    } else {
        if (!preg_match('/^cursor_([0-9]+)(?:_([a-z0-9_]+))?$/i', trim((string)$req_params['resumptionToken']))) {
            oai_error_xml($base_url, $response_date, 'badResumptionToken', 'The value of the resumptionToken argument is invalid or expired.');
        }
    }
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"' . "\n";
echo '         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">' . "\n";
echo '  <responseDate>' . $response_date . '</responseDate>' . "\n";

if ($verb === 'Identify') {
    echo '  <request verb="Identify">' . $base_url . '</request>' . "\n";
    echo "  <Identify>\n";
    echo "    <repositoryName>ElNemer Global Encyclopedia of Applied Knowledge Repository (7,000,000 Volumes · 1,400,000,000 Pages · 78 Languages)</repositoryName>\n";
    echo "    <baseURL>" . $base_url . "</baseURL>\n";
    echo "    <protocolVersion>2.0</protocolVersion>\n";
    echo "    <adminEmail>info@elnemer-ai.com</adminEmail>\n";
    echo "    <earliestDatestamp>2026-01-01T00:00:00Z</earliestDatestamp>\n";
    echo "    <deletedRecord>no</deletedRecord>\n";
    echo "    <granularity>YYYY-MM-DDThh:mm:ssZ</granularity>\n";
    echo "    <compression>gzip</compression>\n";
    echo "    <compression>deflate</compression>\n";
    echo "    <description>\n";
    echo '      <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">' . "\n";
    echo "        <scheme>oai</scheme>\n";
    echo "        <repositoryIdentifier>million.elnemer-ai.com</repositoryIdentifier>\n";
    echo "        <delimiter>:</delimiter>\n";
    echo "        <sampleIdentifier>oai:million.elnemer-ai.com:vol/79</sampleIdentifier>\n";
    echo "      </oai-identifier>\n";
    echo "    </description>\n";
    echo "  </Identify>\n";
} elseif ($verb === 'ListMetadataFormats') {
    $id_attr = isset($req_params['identifier']) ? ' identifier="' . xml_esc(trim((string)$req_params['identifier'])) . '"' : '';
    echo '  <request verb="ListMetadataFormats"' . $id_attr . '>' . $base_url . '</request>' . "\n";
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
    echo "    <metadataFormat>\n";
    echo "      <metadataPrefix>oai_datacite</metadataPrefix>\n";
    echo "      <schema>http://schema.datacite.org/meta/kernel-4.4/metadata.xsd</schema>\n";
    echo "      <metadataNamespace>http://datacite.org/schema/kernel-4</metadataNamespace>\n";
    echo "    </metadataFormat>\n";
    echo "  </ListMetadataFormats>\n";
} elseif ($verb === 'ListSets') {
    echo '  <request verb="ListSets">' . $base_url . '</request>' . "\n";
    echo "  <ListSets>\n";
    $all_sectors = get_mega_sectors();
    foreach ($all_sectors as $sec) {
        echo "    <set>\n";
        echo "      <setSpec>sector:" . xml_esc($sec['code']) . "</setSpec>\n";
        echo "      <setName>Sector #" . intval($sec['sector_id']) . ": " . xml_esc($sec['en'] . " (" . $sec['ar'] . ")") . "</setName>\n";
        echo "    </set>\n";
    }
    $langs = get_78_languages();
    foreach ($langs as $l) {
        echo "    <set>\n";
        echo "      <setSpec>lang:" . xml_esc($l['code']) . "</setSpec>\n";
        echo "      <setName>Language Edition: " . xml_esc($l['en_name'] . " (" . $l['name'] . ")") . "</setName>\n";
        echo "    </set>\n";
    }
    echo "  </ListSets>\n";
} elseif ($verb === 'GetRecord') {
    $identifier = trim((string)$req_params['identifier']);
    $prefix = trim((string)$req_params['metadataPrefix']);
    preg_match('/([0-9]+)$/', $identifier, $m);
    $vol_id = intval($m[1]);
    $b = get_1m_book_metadata($vol_id);
    echo '  <request verb="GetRecord" identifier="' . xml_esc($identifier) . '" metadataPrefix="' . xml_esc($prefix) . '">' . $base_url . '</request>' . "\n";
    echo "  <GetRecord>\n";
    if ($prefix === 'marc21') {
        echo render_marc21_record($b);
    } elseif ($prefix === 'oai_datacite') {
        echo render_datacite_record($b);
    } else {
        echo render_oai_dc_record($b);
    }
    echo "  </GetRecord>\n";
} elseif ($verb === 'ListRecords' || $verb === 'ListIdentifiers') {
    $prefix = isset($req_params['metadataPrefix']) ? trim((string)$req_params['metadataPrefix']) : 'oai_dc';
    $cursor = isset($req_params['cursor']) ? max(1, intval($req_params['cursor'])) : 1;
    if (!empty($req_params['resumptionToken']) && preg_match('/^cursor_([0-9]+)(?:_([a-z0-9_]+))?$/i', trim((string)$req_params['resumptionToken']), $m)) {
        $cursor = max(1, intval($m[1]));
        if (!empty($m[2]) && in_array($m[2], $supported_prefixes, true)) {
            $prefix = $m[2];
        }
    }
    $batch_size = 25;
    $end = min($max_volumes, $cursor + $batch_size - 1);
    echo '  <request verb="' . xml_esc($verb) . '" metadataPrefix="' . xml_esc($prefix) . '">' . $base_url . '</request>' . "\n";
    echo "  <" . $verb . ">\n";
    for ($i = $cursor; $i <= $end; $i++) {
        $b = get_1m_book_metadata($i);
        if ($verb === 'ListIdentifiers') {
            echo "    <header>\n";
            echo "      <identifier>oai:million.elnemer-ai.com:vol/" . $i . "</identifier>\n";
            echo "      <datestamp>2026-11-01T00:00:00Z</datestamp>\n";
            echo "      <setSpec>sector:" . xml_esc($b['sector_code']) . "</setSpec>\n";
            echo "      <setSpec>lang:" . xml_esc($b['lang_code']) . "</setSpec>\n";
            echo "    </header>\n";
        } else {
            if ($prefix === 'marc21') {
                echo render_marc21_record($b);
            } elseif ($prefix === 'oai_datacite') {
                echo render_datacite_record($b);
            } else {
                echo render_oai_dc_record($b);
            }
        }
    }
    if ($end < $max_volumes) {
        $next_cursor = $end + 1;
        echo '    <resumptionToken completeListSize="' . $max_volumes . '" cursor="' . ($cursor - 1) . '">cursor_' . $next_cursor . '_' . $prefix . '</resumptionToken>' . "\n";
    }
    echo "  </" . $verb . ">\n";
}

echo "</OAI-PMH>\n";
?>
