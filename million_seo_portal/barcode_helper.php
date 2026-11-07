<?php
/**
 * ============================================================================
 * ELNEMER 1-MILLION-BOOK OFFICIAL EAN-13 / ISBN-13 BARCODE VECTOR GENERATOR
 * Generates mathematically authentic, GS1/ISO compliant EAN-13 barcodes
 * in pure SVG (for HTML display) and raw PDF vector stream operators.
 * Scannable by any commercial barcode gun, Apple Camera, or Google Lens.
 * Author: Eng. Mahmoud Bek ElNemer (info@elnemer-ai.com)
 * ============================================================================
 */

function get_ean13_binary_bits($isbn) {
    $digits = preg_replace('/\D/', '', (string)$isbn);
    if (strlen($digits) !== 13) {
        $digits = str_pad(substr($digits, 0, 13), 13, '0', STR_PAD_RIGHT);
    }
    
    $first = intval($digits[0]);
    $left = substr($digits, 1, 6);
    $right = substr($digits, 7, 6);
    
    $patterns = [
        'AAAAAA', 'AABABB', 'AABBAB', 'AABBBA', 'ABAABB',
        'ABBAAB', 'ABBBAA', 'ABABAB', 'ABABBA', 'ABBABA'
    ];
    $pattern = $patterns[$first];
    
    $table_A = ['0001101', '0011001', '0010011', '0111101', '0100011', '0110001', '0101111', '0111011', '0110111', '0001011'];
    $table_B = ['0100111', '0110011', '0011011', '0100001', '0011101', '0111001', '0000101', '0010001', '0001001', '0010111'];
    $table_C = ['1110010', '1100110', '1101100', '1000010', '1011100', '1001110', '1010000', '1000100', '1001000', '1110100'];
    
    // Left Guard (101)
    $bits = '101';
    for ($i = 0; $i < 6; $i++) {
        $d = intval($left[$i]);
        $bits .= ($pattern[$i] === 'A') ? $table_A[$d] : $table_B[$d];
    }
    // Center Guard (01010)
    $bits .= '01010';
    for ($i = 0; $i < 6; $i++) {
        $d = intval($right[$i]);
        $bits .= $table_C[$d];
    }
    // Right Guard (101)
    $bits .= '101';
    return $bits; // exactly 95 modules
}

function render_ean13_svg($isbn, $width = 250, $height = 86) {
    $bits = get_ean13_binary_bits($isbn);
    $total_modules = strlen($bits); // 95
    $margin = 15;
    $usable_w = $width - ($margin * 2);
    $mod_w = round($usable_w / $total_modules, 3);
    
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' ' . $height . '" width="' . $width . '" height="' . $height . '" style="background:#ffffff;padding:4px;border-radius:8px;box-shadow:0 4px 15px rgba(0,0,0,0.3);display:inline-block;">';
    
    $x = $margin;
    for ($i = 0; $i < $total_modules; $i++) {
        if ($bits[$i] === '1') {
            // Guard bars are slightly longer
            $is_guard = ($i < 3 || ($i >= 45 && $i < 50) || $i >= 92);
            $bar_h = $is_guard ? ($height - 20) : ($height - 28);
            $svg .= '<rect x="' . round($x, 2) . '" y="6" width="' . $mod_w . '" height="' . $bar_h . '" fill="#000000"/>';
        }
        $x += $mod_w;
    }
    
    // ISBN human readable digits
    $formatted_isbn = substr($isbn, 0, 3) . '-' . substr($isbn, 3, 3) . '-' . substr($isbn, 6, 6) . '-' . substr($isbn, 12, 1);
    $svg .= '<text x="' . ($width / 2) . '" y="' . ($height - 6) . '" text-anchor="middle" font-family="Courier, monospace" font-size="11" font-weight="900" fill="#000000" letter-spacing="1.5">ISBN ' . $formatted_isbn . '</text>';
    $svg .= '</svg>';
    return $svg;
}

function render_ean13_pdf_operators($isbn, $orig_x, $orig_y, $target_w = 160, $target_h = 50) {
    $bits = get_ean13_binary_bits($isbn);
    $total_modules = strlen($bits);
    $mod_w = $target_w / $total_modules;
    
    $ops = "q\n";
    // Draw white background
    $ops .= "1 1 1 rg\n";
    $ops .= sprintf("%.2f %.2f %.2f %.2f re f\n", $orig_x - 6, $orig_y - 12, $target_w + 12, $target_h + 18);
    // Draw black bars
    $ops .= "0 0 0 rg\n";
    $cur_x = $orig_x;
    for ($i = 0; $i < $total_modules; $i++) {
        if ($bits[$i] === '1') {
            $is_guard = ($i < 3 || ($i >= 45 && $i < 50) || $i >= 92);
            $bar_h = $is_guard ? $target_h : ($target_h - 6);
            $bar_y = $is_guard ? $orig_y : ($orig_y + 6);
            $ops .= sprintf("%.2f %.2f %.2f %.2f re f\n", $cur_x, $bar_y, $mod_w, $bar_h);
        }
        $cur_x += $mod_w;
    }
    // Draw text underneath
    $ops .= "BT\n/F1 8 Tf\n";
    $ops .= sprintf("%.2f %.2f Td\n", $orig_x + 10, $orig_y - 8);
    $ops .= "(ISBN " . $isbn . ") Tj\nET\n";
    $ops .= "Q\n";
    return $ops;
}
