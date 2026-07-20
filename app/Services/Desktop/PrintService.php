<?php

namespace App\Services\Desktop;

use App\Support\BusinessProfile;

/**
 * Wrapper around NativePHP's printing capabilities.
 *
 * Provides methods for printing receipts and reports
 * to local printers via the NativePHP System facade.
 */
class PrintService
{
    /**
     * Print HTML content to the default system printer.
     *
     * @param  string  $html  The HTML content to print.
     * @param  array<string, mixed>  $options  Printer options (margins, copies, etc.)
     */
    public function printHtml(string $html, array $options = []): void
    {
        if (! class_exists(\Native\Desktop\Facades\System::class)) {
            throw new \RuntimeException('Printing is only available in the NativePHP desktop environment.');
        }

        $defaults = [
            'silent' => true,
            'printBackground' => true,
        ];

        \Native\Desktop\Facades\System::print($html, array_merge($defaults, $options));
    }

    /**
     * Print content to a PDF file.
     *
     * @param  string  $html  The HTML content to render.
     * @param  string  $outputPath  Where to save the PDF.
     * @param  array<string, mixed>  $options  PDF options (margins, landscape, etc.)
     */
    public function printToPdf(string $html, string $outputPath, array $options = []): void
    {
        if (! class_exists(\Native\Desktop\Facades\System::class)) {
            throw new \RuntimeException('PDF printing is only available in the NativePHP desktop environment.');
        }

        $defaults = [
            'printBackground' => true,
            'marginsType' => 0,
        ];

        \Native\Desktop\Facades\System::printToPDF($html, $outputPath, array_merge($defaults, $options));
    }

    /**
     * Format a receipt for thermal printer output (80mm width).
     *
     * @param  array<string, mixed>  $receiptData
     */
    public function formatReceipt(array $receiptData): string
    {
        $businessName = $receiptData['business_name'] ?? BusinessProfile::name();
        $date = $receiptData['date'] ?? now()->format('Y-m-d H:i:s');
        $items = $receiptData['items'] ?? [];
        $total = $receiptData['total'] ?? 0;
        $footer = $receiptData['footer'] ?? 'Thank you for your patronage!';

        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Courier New', monospace;
                    font-size: 12px;
                    width: 80mm;
                    padding: 4mm;
                }
                .center { text-align: center; }
                .bold { font-weight: bold; }
                .divider { border-top: 1px dashed #000; margin: 4px 0; }
                .item-row { display: flex; justify-content: space-between; }
                .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 4px; }
                .footer { text-align: center; margin-top: 8px; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class="center bold" style="font-size: 16px;">{$businessName}</div>
            <div class="center" style="font-size: 10px;">{$date}</div>
            <div class="divider"></div>
        HTML;

        foreach ($items as $item) {
            $name = htmlspecialchars($item['name'] ?? '');
            $qty = $item['qty'] ?? 1;
            $price = number_format((float) ($item['price'] ?? 0), 2);
            $lineTotal = number_format((float) ($item['total'] ?? 0), 2);

            $html .= <<<HTML
                <div class="item-row">
                    <span>{$name} x{$qty}</span>
                    <span>{$lineTotal}</span>
                </div>
            HTML;
        }

        $formattedTotal = number_format((float) $total, 2);
        $footerEscaped = htmlspecialchars($footer);

        $html .= <<<HTML
                <div class="divider"></div>
                <div class="total-row">
                    <span>TOTAL</span>
                    <span>{$formattedTotal}</span>
                </div>
                <div class="divider"></div>
                <div class="footer">{$footerEscaped}</div>
            </body>
            </html>
        HTML;

        return $html;
    }
}
