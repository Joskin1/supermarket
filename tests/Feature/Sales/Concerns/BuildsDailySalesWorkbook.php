<?php

namespace Tests\Feature\Sales\Concerns;

use App\Models\Product;
use App\Support\SalesImport\DailySalesTemplateColumns;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

trait BuildsDailySalesWorkbook
{
    /**
     * @param  array<int, array<string, mixed>>  $salesRows
     * @param  array<int, array<string, mixed>>  $referenceRows
     */
    protected function makeSalesWorkbookUpload(
        array $salesRows,
        array $referenceRows = [],
        string $fileName = 'daily-sales.xlsx',
    ): UploadedFile {
        return UploadedFile::fake()->createWithContent(
            $fileName,
            $this->buildSalesWorkbookBinary($salesRows, $referenceRows),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $salesRows
     * @param  array<int, array<string, mixed>>  $referenceRows
     */
    protected function buildSalesWorkbookBinary(array $salesRows, array $referenceRows = []): string
    {
        $spreadsheet = new Spreadsheet;

        $referenceSheet = $spreadsheet->getActiveSheet();
        $referenceSheet->setTitle(DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET);
        $this->writeSheetRows(
            $referenceSheet,
            DailySalesTemplateColumns::productReference(),
            $referenceRows,
        );

        $salesSheet = $spreadsheet->createSheet();
        $salesSheet->setTitle(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);
        $this->writeSheetRows(
            $salesSheet,
            DailySalesTemplateColumns::salesEntryLog(),
            $salesRows,
        );

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return is_string($binary) ? $binary : '';
    }

    protected function loadSpreadsheetFromBinary(string $binary): Spreadsheet
    {
        $basePath = tempnam(sys_get_temp_dir(), 'sales-workbook-');

        if ($basePath === false) {
            throw new RuntimeException('Could not create a temporary workbook path.');
        }

        $path = $basePath.'.xlsx';
        rename($basePath, $path);
        file_put_contents($path, $binary);

        $spreadsheet = IOFactory::load($path);

        unlink($path);

        return $spreadsheet;
    }

    protected function referenceRowForProduct(Product $product): array
    {
        return [
            'product_code' => $product->sku,
            'category' => $product->category?->name,
            'product_name' => $product->name,
            'unit_price' => $product->selling_price,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function salesEntryRow(array $overrides = []): array
    {
        $row = array_merge([
            'date' => now()->toDateString(),
            'time' => '09:30',
            'product_code' => 'SKU-001',
            'product_name' => 'Coca-Cola Classic Soft Drink',
            'unit_price' => 500,
            'quantity_sold' => 1,
            'total_amount' => 500,
            'note' => '',
        ], $overrides);

        if (
            (array_key_exists('unit_price', $overrides) || array_key_exists('quantity_sold', $overrides))
            && ! array_key_exists('total_amount', $overrides)
        ) {
            $row['total_amount'] = round((float) $row['unit_price'] * (int) $row['quantity_sold'], 2);
        }

        return $row;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function writeSheetRows(
        Worksheet $sheet,
        array $columns,
        array $rows,
    ): void {
        $sheet->fromArray($columns, null, 'A1');

        $rowIndex = 2;

        foreach ($rows as $row) {
            $sheet->fromArray(
                array_map(
                    static fn (string $column): mixed => $row[$column] ?? null,
                    $columns,
                ),
                null,
                "A{$rowIndex}",
            );

            $rowIndex++;
        }
    }
}
