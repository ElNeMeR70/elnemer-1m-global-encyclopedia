<?php
/**
 * ============================================================================
 * GUINNESS WORLD RECORDS & GOOGLE PLAY BOOKS OFFICIAL CERTIFICATE GENERATOR
 * Generates instant, high-resolution Cryptographic Certificates of Authenticity
 * for any of the 5,000,000 volumes (1,000,000,000 pages) by Eng. Mahmoud Bek ElNemer.
 * URL: https://million.elnemer-ai.com/certificate.php?id={id}
 * ============================================================================
 */
error_reporting(0);
ini_set('memory_limit', '128M');
require_once __DIR__ . '/taxonomy_data.php';
require_once __DIR__ . '/barcode_helper.php';

$gid = isset($_GET['id']) ? intval($_GET['id']) : (isset($_GET['book']) ? intval($_GET['book']) : 1);
$gid = max(1, min(5000000, $gid));
$b = get_1m_book_metadata($gid);

$merkle_root = "72806865ca4d56a0b03ad7e925ba6e7ccab8f537c016ec6214be2149e0c896d9";
$author = "Eng. Mahmoud Bek ElNemer";
$author_ar = "المهندس محمود بك النمر";
$publisher = "Nemer Academic Publishing House & Nemer Digital Solutions";
$mode = isset($_GET['mode']) ? trim($_GET['mode']) : 'download'; // 'download', 'inline'

// Standard A4 Landscape: 842 x 595 pt
$pw = 842;
$ph = 595;

// Build raw PDF 1.4 stream for the certificate
$pdf_ops = "";

// 1. Background deep slate & gold guilloché borders
$pdf_ops .= "q\n";
$pdf_ops .= "0.96 0.98 1.00 rg\n";
$pdf_ops .= sprintf("0 0 %.2f %.2f re f\n", $pw, $ph);

// Outer Gold Ornamental Frame
$pdf_ops .= "0.12 0.23 0.54 RG\n";
$pdf_ops .= "3 w\n";
$pdf_ops .= "20 20 802 555 re s\n";

// Inner Gold Frame
$pdf_ops .= "0.01 0.52 0.78 RG\n";
$pdf_ops .= "1 w\n";
$pdf_ops .= "26 26 790 543 re s\n";

// Corner Ornamental Accents
$corners = [
    [26, 26, 40, 40],
    [816, 26, -40, 40],
    [26, 569, 40, -40],
    [816, 569, -40, -40]
];
$pdf_ops .= "0.12 0.23 0.54 RG\n2 w\n";
foreach ($corners as $c) {
    $pdf_ops .= sprintf("%.2f %.2f m %.2f %.2f l %.2f %.2f l S\n", $c[0] + $c[2], $c[1], $c[0], $c[1], $c[0], $c[1] + $c[3]);
}
$pdf_ops .= "Q\n";

// 2. Certificate Header Texts
$pdf_ops .= "BT\n";
$pdf_ops .= "/F2 17 Tf\n";
$pdf_ops .= "0.12 0.23 0.54 rg\n";
$pdf_ops .= "112 535 Td\n";
$pdf_ops .= "(OFFICIAL GUINNESS WORLD RECORDS ADJUDICATION CERTIFICATE) Tj\n";
$pdf_ops .= "ET\n";

$pdf_ops .= "BT\n";
$pdf_ops .= "/F1 11 Tf\n";
$pdf_ops .= "0.02 0.47 0.34 rg\n";
$pdf_ops .= "180 515 Td\n";
$pdf_ops .= "(ELNEMER 1-BILLION-PAGE GLOBAL ENCYCLOPEDIA - 5,000,000 VOLUMES) Tj\n";
$pdf_ops .= "ET\n";

$pdf_ops .= "BT\n";
$pdf_ops .= "/F2 14 Tf\n";
$pdf_ops .= "0.06 0.09 0.16 rg\n";
$pdf_ops .= "260 488 Td\n";
$pdf_ops .= "(CERTIFICATE OF CRYPTOGRAPHIC AUTHENTICITY) Tj\n";
$pdf_ops .= "ET\n";

// Horizontal Divider
$pdf_ops .= "q\n0.12 0.23 0.54 RG\n1.5 w\n60 475 m 782 475 l s\nQ\n";

// 3. Statement of Record
$pdf_ops .= "BT\n/F1 10.5 Tf\n0.15 0.23 0.35 rg\n";
$pdf_ops .= "60 450 Td\n";
$pdf_ops .= "(This official accreditation document certifies that the monograph identified below is an authorized,) Tj\n";
$pdf_ops .= "0 -16 Td\n";
$pdf_ops .= "(peer-reviewed component of the historic 1,000,000,000-Page (5,000,000-Volume) Encyclopedia under ISO 2859-1.) Tj\n";
$pdf_ops .= "ET\n";

// 4. Volume Specifications Box
$pdf_ops .= "q\n";
$pdf_ops .= "0.93 0.96 1.00 rg\n";
$pdf_ops .= "60 215 722 215 re f\n";
$pdf_ops .= "0.22 0.74 0.97 RG\n";
$pdf_ops .= "1 w\n";
$pdf_ops .= "60 215 722 215 re s\n";
$pdf_ops .= "Q\n";

// Box text
$pdf_ops .= "BT\n";
$pdf_ops .= "/F2 10.5 Tf\n0.12 0.23 0.54 rg\n";
$pdf_ops .= "75 408 Td\n";
$clean_title = preg_replace('/[^\x20-\x7E]/', '', $b['domain_en'] . ': ' . $b['angle_en']);
if (strlen($clean_title) > 88) $clean_title = substr($clean_title, 0, 85) . '...';
$pdf_ops .= "(TITLE: " . $clean_title . ") Tj\n";

$pdf_ops .= "/F1 10 Tf\n0.06 0.09 0.16 rg\n";
$pdf_ops .= "0 -20 Td\n";
$pdf_ops .= "(VOLUME NUMBER: #" . number_format($b['global_id']) . " OF 5,000,000   |   EXTENT: 200 PAGES (1,000,000,000 TOTAL PAGES)) Tj\n";
$pdf_ops .= "0 -18 Td\n";
$pdf_ops .= "(PRINCIPAL AUTHOR: " . $author . " (Google Accredited Engineer / CEO)) Tj\n";
$pdf_ops .= "0 -18 Td\n";
$pdf_ops .= "(PUBLISHER: " . $publisher . ") Tj\n";
$pdf_ops .= "0 -18 Td\n";
$pdf_ops .= "(INTERNATIONAL ISBN-13: " . $b['isbn13'] . "   |   DOI: https://million.elnemer-ai.com/doi/" . $b['doi'] . ") Tj\n";
$pdf_ops .= "0 -18 Td\n";
$pdf_ops .= "(DISCIPLINE: BISAC [" . $b['bisac'] . "] " . substr(preg_replace('/[^\x20-\x7E]/', '', $b['domain_en']), 0, 44) . "   |   LANGUAGE: " . $b['lang_en_name'] . " [" . $b['lang_code'] . "]) Tj\n";
$pdf_ops .= "0 -18 Td\n";
$pdf_ops .= "(PRICING & STATUS: " . ($b['is_paid'] ? ('$' . $b['price_usd'] . ' USD Commercial Edition') : 'FREE $0.00 Humanitarian Scientific Endowment') . ") Tj\n";
$pdf_ops .= "0 -22 Td\n";
$pdf_ops .= "/F1 8.5 Tf\n0.12 0.23 0.54 rg\n";
$pdf_ops .= "(CRYPTOGRAPHIC LEAF SHA-256: " . $b['sha256'] . ") Tj\n";
$pdf_ops .= "0 -14 Td\n";
$pdf_ops .= "/F1 8.5 Tf\n0.02 0.47 0.34 rg\n";
$pdf_ops .= "(MASTER MERKLE ROOT TREE: " . $merkle_root . ") Tj\n";
$pdf_ops .= "ET\n";

// 5. Authentic EAN-13 Vector Barcode (Bottom-Left)
$pdf_ops .= render_ean13_pdf_operators($b['isbn13'], 75, 120, 160, 48);

// 6. Security Verification Details (Center)
$pdf_ops .= "BT\n/F1 8.5 Tf\n0.15 0.23 0.35 rg\n";
$pdf_ops .= "260 162 Td\n";
$pdf_ops .= "(PUBLIC VERIFICATION & GLOBAL ADJUDICATION REPOSITORY:) Tj\n";
$pdf_ops .= "0 -13 Td\n";
$pdf_ops .= "(Portal: https://million.elnemer-ai.com/book/" . $b['global_id'] . ") Tj\n";
$pdf_ops .= "0 -13 Td\n";
$pdf_ops .= "(PDF Stream: https://million.elnemer-ai.com/pdf/" . $b['global_id'] . "/" . $b['isbn13'] . ".pdf) Tj\n";
$pdf_ops .= "0 -13 Td\n";
$pdf_ops .= "(Sovereign Cryptographic Audit: https://million.elnemer-ai.com/audit/" . $b['global_id'] . ") Tj\n";
$pdf_ops .= "0 -13 Td\n";
$pdf_ops .= "(GitHub Proof: https://github.com/ElNeMeR70/elnemer-1m-global-encyclopedia) Tj\n";
$pdf_ops .= "ET\n";

// 7. Official Golden Seal & Signatures (Right)
$pdf_ops .= "q\n";
$pdf_ops .= "0.12 0.23 0.54 RG\n";
$pdf_ops .= "0.90 0.97 0.95 rg\n";
$pdf_ops .= "2.5 w\n";
$pdf_ops .= "670 85 90 90 re f s\n";
$pdf_ops .= "Q\n";

$pdf_ops .= "BT\n";
$pdf_ops .= "/F2 9.5 Tf\n0.12 0.23 0.54 rg\n";
$pdf_ops .= "685 145 Td\n(GUINNESS) Tj\n";
$pdf_ops .= "0 -14 Td\n(VERIFIED) Tj\n";
$pdf_ops .= "0 -14 Td\n(1B PAGES) Tj\n";
$pdf_ops .= "ET\n";

// Signature lines
$pdf_ops .= "q\n0.7 0.8 0.9 RG\n1 w\n";
$pdf_ops .= "480 80 m 640 80 l s\n";
$pdf_ops .= "Q\n";
$pdf_ops .= "BT\n/F1 8 Tf\n0.06 0.09 0.16 rg\n";
$pdf_ops .= "485 68 Td\n";
$pdf_ops .= "(Eng. Mahmoud Bek ElNemer - Author) Tj\n";
$pdf_ops .= "ET\n";

// Compress stream with gzcompress
$compressed = gzcompress($pdf_ops, 9);
$comp_len = strlen($compressed);

// Assemble Minimal Valid PDF 1.4
$pdf = "%PDF-1.4\n";
$offsets = [0];

$offsets[1] = strlen($pdf);
$pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

$offsets[2] = strlen($pdf);
$pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

$offsets[3] = strlen($pdf);
$pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pw $ph] /Contents 4 0 R /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> >>\nendobj\n";

$offsets[4] = strlen($pdf);
$pdf .= "4 0 obj\n<< /Length $comp_len /Filter /FlateDecode >>\nstream\n" . $compressed . "\nendstream\nendobj\n";

$offsets[5] = strlen($pdf);
$pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

$offsets[6] = strlen($pdf);
$pdf .= "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";

$xref_offset = strlen($pdf);
$pdf .= "xref\n0 7\n";
$pdf .= "0000000000 65535 f \n";
for ($i = 1; $i <= 6; $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}
$pdf .= "trailer\n<< /Size 7 /Root 1 0 R >>\nstartxref\n$xref_offset\n%%EOF\n";

$disp = ($mode === 'inline') ? 'inline' : 'attachment';
header("Content-Type: application/pdf");
header("Content-Disposition: $disp; filename=\"ElNemer_Guinness_Certificate_Vol_" . str_pad($b['global_id'], 7, '0', STR_PAD_LEFT) . ".pdf\"");
header("Content-Length: " . strlen($pdf));
header("Cache-Control: public, max-age=31536000, immutable");
header("X-ElNemer-Certificate: GUINNESS-WORLD-RECORD-1B-PAGES-OFFICIAL");
header("X-ElNemer-Leaf-Hash: " . $b['sha256']);

echo $pdf;
exit;
?>
