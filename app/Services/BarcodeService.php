<?php
namespace App\Services;

use App\Models\ProductVariation;
use App\Models\ProductCategory;

class BarcodeService
{
    // ── Generate SKU from category code + product id + sequence ──────────────
    public function generateSku(int $productId, int $categoryId = null, int $sequence = 1): string
    {
        $catCode = 'GEN';
        if ($categoryId) {
            $cat = ProductCategory::find($categoryId);
            if ($cat) $catCode = strtoupper(substr($cat->code, 0, 4));
        }
        return $catCode . '-' . str_pad($productId, 5, '0', STR_PAD_LEFT) . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    // ── Generate unique barcode (EAN-13 style numeric) ────────────────────────
    public function generateBarcode(string $sku): string
    {
        // Use hash of SKU to make numeric barcode, ensure uniqueness
        $numeric = substr(preg_replace('/[^0-9]/', '', hash('crc32b', $sku) . crc32($sku)), 0, 12);
        $numeric = str_pad($numeric, 12, '0', STR_PAD_RIGHT);
        $barcode = $numeric . $this->ean13CheckDigit($numeric);

        // Ensure uniqueness
        $attempt = 0;
        while (ProductVariation::where('barcode', $barcode)->exists()) {
            $attempt++;
            $barcode = substr($numeric, 0, 11) . $attempt . $this->ean13CheckDigit(substr($numeric, 0, 11) . $attempt);
        }

        return $barcode;
    }

    private function ean13CheckDigit(string $code): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        return (10 - ($sum % 10)) % 10;
    }

    // ── Generate SVG barcode (no external package needed) ─────────────────────
    public function barcodeHtml(string $code, int $width = 200, int $height = 60): string
    {
        // Simple Code128-like visual using CSS stripes for HTML rendering
        // In production: use picqer/php-barcode-generator
        return '<div class="barcode-container" style="text-align:center;">
            <div style="font-family:monospace;letter-spacing:-1px;font-size:18px;line-height:60px;background:#fff;padding:4px;border:1px solid #ddd;display:inline-block;width:'.$width.'px;height:'.($height+20).'px;overflow:hidden;">
                <div style="filter:url(#barcode);height:'.$height.'px;background:repeating-linear-gradient(90deg,#000 0px,#000 2px,#fff 2px,#fff 4px,#000 4px,#000 5px,#fff 5px,#fff 7px,#000 7px,#000 9px,#fff 9px,#fff 12px);background-size:'.((strlen($code)*3+40)).'px 100%;"></div>
                <div style="font-size:11px;text-align:center;margin-top:4px;font-family:monospace;">'.$code.'</div>
            </div>
        </div>';
    }

    // ── Generate SVG barcode (alias used by barcodePrint) ────────────────────
    public function generateSvg(string $code, int $width = 200, int $height = 60): string
    {
        // Build a simple Code39-style SVG barcode
        $bars   = $this->codeToStripes($code);
        $svgW   = max($width, count($bars) * 2 + 20);
        $x      = 10;
        $svgBars = '';

        foreach ($bars as $i => $bar) {
            $barWidth = $bar['wide'] ? 3 : 1.5;
            if ($bar['dark']) {
                $svgBars .= "<rect x=\"{$x}\" y=\"4\" width=\"{$barWidth}\" height=\"{$height}\" fill=\"#000\"/>";
            }
            $x += $barWidth + ($bar['wide'] ? 0 : 0.5);
        }

        $svgW = (int)($x + 10);
        $svgH = $height + 16;

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$svgW} {$svgH}\" width=\"{$svgW}\" height=\"{$svgH}\">
            <rect width=\"{$svgW}\" height=\"{$svgH}\" fill=\"#fff\"/>
            {$svgBars}
            <text x=\"" . ($svgW/2) . "\" y=\"" . ($svgH - 2) . "\" text-anchor=\"middle\" font-family=\"monospace\" font-size=\"9\" fill=\"#000\">{$code}</text>
        </svg>";
    }

    private function codeToStripes(string $code): array
    {
        // Simple repeating pattern based on character values — visually distinct, not spec-correct
        $bars = [];
        // Start guard
        $bars[] = ['dark'=>true,  'wide'=>false];
        $bars[] = ['dark'=>false, 'wide'=>false];
        $bars[] = ['dark'=>true,  'wide'=>true];
        $bars[] = ['dark'=>false, 'wide'=>false];

        foreach (str_split($code) as $char) {
            $val = ord($char);
            for ($i = 0; $i < 5; $i++) {
                $bars[] = ['dark' => ($val >> $i) & 1, 'wide' => ($val >> ($i+1)) & 1];
                $bars[] = ['dark' => false, 'wide' => false];
            }
        }

        // End guard
        $bars[] = ['dark'=>true,  'wide'=>true];
        $bars[] = ['dark'=>false, 'wide'=>false];
        $bars[] = ['dark'=>true,  'wide'=>false];

        return $bars;
    }

    // ── Print-ready barcode label HTML ────────────────────────────────────────
    public function labelHtml(string $productName, string $sku, string $barcode, float $price): string
    {
        return '<div class="label" style="width:180px;border:1px solid #999;padding:4px;display:inline-block;margin:3px;vertical-align:top;font-family:Arial,sans-serif;font-size:10px;text-align:center;">
            <div style="font-size:9px;font-weight:bold;overflow:hidden;white-space:nowrap;">'.htmlspecialchars($productName).'</div>
            <div style="font-size:8px;color:#555;">'.htmlspecialchars($sku).'</div>
            <div style="margin:3px 0;">'.$this->barcodeHtml($barcode, 160, 40).'</div>
            <div style="font-size:11px;font-weight:bold;">PKR '.number_format($price,2).'</div>
        </div>';
    }
}