<?php
/**
 * ============================================================================
 * ELNEMER 1-BILLION-PAGE OFFICIAL GS1 / EAN-13 / ISBN-13 BARCODE VECTOR GENERATOR
 * Generates mathematically authentic, GS1/ISO/IEC 15420 compliant EAN-13 barcodes
 * in pure SVG (with shape-rendering="crispEdges" & merged contiguous runs so ZERO
 * sub-pixel seams ever appear) and raw PDF vector stream operators.
 * Scannable 100% by any commercial laser/CCD barcode gun, iPhone Camera, or Google Lens.
 * Author: Eng. Mahmoud Bek ElNemer (info@elnemer-ai.com)
 * ============================================================================
 */

function normalize_ean13_isbn($isbn) {
    $digits = preg_replace('/\D/', '', (string)$isbn);
    if (strlen($digits) < 12) {
        $digits = str_pad('978971' . $digits, 12, '0', STR_PAD_RIGHT);
    }
    $first12 = substr($digits, 0, 12);
    // Always enforce mathematically valid GS1 Modulo-10 13th check digit
    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $d = intval($first12[$i]);
        $sum += ($i % 2 === 0) ? $d : ($d * 3);
    }
    $check = (10 - ($sum % 10)) % 10;
    return $first12 . (string)$check;
}

function get_ean13_binary_bits($isbn) {
    $digits = normalize_ean13_isbn($isbn);
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
    return $bits; // Exactly 95 optical modules
}

function render_ean13_svg($isbn, $width = 240, $height = 92) {
    $digits = normalize_ean13_isbn($isbn);
    $bits = get_ean13_binary_bits($digits);
    $total_modules = strlen($bits); // 95

    // Use an integer coordinate grid (115 total units: 11 left quiet zone + 95 modules + 9 right quiet zone)
    // and merge adjacent '1' modules of the same guard type so there are NEVER anti-aliasing gaps!
    $vw = 230; // 2 units per module (95 * 2 = 190 + 24 left margin + 16 right margin)
    $vh = 92;
    $mod_u = 2;
    $start_x = 24;

    $formatted_top = 'ISBN ' . substr($digits, 0, 3) . '-' . substr($digits, 3, 2) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $vw . ' ' . $vh . '" width="' . intval($width) . '" height="' . intval($height) . '" shape-rendering="crispEdges" role="img" aria-label="' . htmlspecialchars($formatted_top) . '" style="background:#FFFFFF;padding:6px 10px;border-radius:10px;border:1.5px solid #93C5FD;box-shadow:0 4px 14px rgba(30,58,138,0.08);display:inline-block;max-width:100%;height:auto;">';
    $svg .= '<rect x="0" y="0" width="' . $vw . '" height="' . $vh . '" fill="#FFFFFF"/>';
    $svg .= '<text x="' . ($vw / 2) . '" y="13" text-anchor="middle" font-family="monospace, Courier" font-size="9.5" font-weight="bold" fill="#0F172A" shape-rendering="geometricPrecision">' . htmlspecialchars($formatted_top) . '</text>';

    $i = 0;
    while ($i < $total_modules) {
        if ($bits[$i] === '1') {
            $is_guard = ($i < 3 || ($i >= 45 && $i < 50) || $i >= 92);
            $run_len = 1;
            while (($i + $run_len) < $total_modules && $bits[$i + $run_len] === '1') {
                $next_guard = (($i + $run_len) < 3 || (($i + $run_len) >= 45 && ($i + $run_len) < 50) || ($i + $run_len) >= 92);
                if ($next_guard !== $is_guard) break;
                $run_len++;
            }
            $x = $start_x + ($i * $mod_u);
            $w = $run_len * $mod_u;
            $bar_h = $is_guard ? 58 : 50;
            $svg .= '<rect x="' . $x . '" y="18" width="' . $w . '" height="' . $bar_h . '" fill="#0F172A"/>';
            $i += $run_len;
        } else {
            $i++;
        }
    }

    // Standard EAN-13 Human-Readable Digits Below Bars (1st digit left of guard, 6 left digits, 6 right digits)
    $d0 = $digits[0];
    $left6 = substr($digits, 1, 6);
    $right6 = substr($digits, 7, 6);
    $svg .= '<text x="12" y="84" text-anchor="middle" font-family="monospace, Courier" font-size="11.5" font-weight="bold" fill="#0F172A" shape-rendering="geometricPrecision">' . $d0 . '</text>';
    $svg .= '<text x="72" y="84" text-anchor="middle" font-family="monospace, Courier" font-size="11.5" font-weight="bold" fill="#0F172A" letter-spacing="3" shape-rendering="geometricPrecision">' . $left6 . '</text>';
    $svg .= '<text x="166" y="84" text-anchor="middle" font-family="monospace, Courier" font-size="11.5" font-weight="bold" fill="#0F172A" letter-spacing="3" shape-rendering="geometricPrecision">' . $right6 . '</text>';
    $svg .= '</svg>';
    return $svg;
}

function render_ean13_pdf_operators($isbn, $orig_x, $orig_y, $target_w = 160, $target_h = 50) {
    $digits = normalize_ean13_isbn($isbn);
    $bits = get_ean13_binary_bits($digits);
    $total_modules = strlen($bits);
    $mod_w = $target_w / $total_modules;

    $ops = "q\n";
    // White quiet-zone background
    $ops .= "1 1 1 rg\n";
    $ops .= sprintf("%.2f %.2f %.2f %.2f re f\n", $orig_x - 8, $orig_y - 14, $target_w + 16, $target_h + 22);
    // Crisp merged dark bars
    $ops .= "0.06 0.09 0.16 rg\n";
    $i = 0;
    while ($i < $total_modules) {
        if ($bits[$i] === '1') {
            $is_guard = ($i < 3 || ($i >= 45 && $i < 50) || $i >= 92);
            $run_len = 1;
            while (($i + $run_len) < $total_modules && $bits[$i + $run_len] === '1') {
                $next_guard = (($i + $run_len) < 3 || (($i + $run_len) >= 45 && ($i + $run_len) < 50) || ($i + $run_len) >= 92);
                if ($next_guard !== $is_guard) break;
                $run_len++;
            }
            $cur_x = $orig_x + ($i * $mod_w);
            $run_w = $run_len * $mod_w;
            $bar_h = $is_guard ? $target_h : ($target_h - 6);
            $bar_y = $is_guard ? $orig_y : ($orig_y + 6);
            $ops .= sprintf("%.3f %.2f %.3f %.2f re f\n", $cur_x, $bar_y, $run_w, $bar_h);
            $i += $run_len;
        } else {
            $i++;
        }
    }
    $formatted = substr($digits, 0, 3) . '-' . substr($digits, 3, 2) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
    $ops .= "BT\n/F1 8 Tf\n";
    $ops .= sprintf("%.2f %.2f Td\n", $orig_x + 6, $orig_y - 10);
    $ops .= "(ISBN " . $formatted . ") Tj\nET\n";
    $ops .= "Q\n";
    return $ops;
}
?>
