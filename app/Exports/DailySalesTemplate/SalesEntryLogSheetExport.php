<?php

namespace App\Exports\DailySalesTemplate;

use App\Support\SalesImport\DailySalesTemplateColumns;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesEntryLogSheetExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected CarbonInterface $salesDate,
    ) {}

    /**
     * Column layout:
     *   A = date         (pre-filled, locked)
     *   B = time         (editable)
     *   C = barcode      (editable — scan or type)
     *   D = sku          (editable — type manually when scanner fails)
     *   E = product_name (formula — auto-filled from barcode OR sku lookup)
     *   F = unit_price   (formula — auto-filled from barcode OR sku lookup)
     *   G = quantity_sold (editable)
     *   H = total_amount (formula — unit_price × quantity_sold)
     *   I = note         (editable)
     */
    public function array(): array
    {
        $rows = [];

        for ($rowNumber = 2; $rowNumber <= DailySalesTemplateColumns::ENTRY_TEMPLATE_ROWS + 1; $rowNumber++) {
            $rows[] = [
                'date' => $this->salesDate->toDateString(),
                'time' => null,
                'barcode' => null,
                'sku' => null,
                'product_name' => $this->productNameFormula($rowNumber),
                'unit_price' => $this->unitPriceFormula($rowNumber),
                'quantity_sold' => null,
                'total_amount' => $this->totalAmountFormula($rowNumber),
                'note' => null,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        return DailySalesTemplateColumns::salesEntryLog();
    }

    public function title(): string
    {
        return DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = DailySalesTemplateColumns::ENTRY_TEMPLATE_ROWS + 1;

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:I{$highestRow}");

                // Column widths for scanner-friendly layout.
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(18);

                // Number formats.
                $sheet->getStyle("A2:A{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);
                $sheet->getStyle("B2:B{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('hh:mm');
                // Barcode as text to preserve leading zeros.
                $sheet->getStyle("C2:C{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
                // SKU as text.
                $sheet->getStyle("D2:D{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
                $sheet->getStyle("F2:H{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                // Visual styling: formula columns get gray background.
                $sheet->getStyle("E2:F{$highestRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F3F4F6');
                $sheet->getStyle("H2:H{$highestRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('ECFDF5');

                // Editable columns get a subtle highlight so cashiers know where to type.
                foreach (['C', 'D'] as $col) {
                    $sheet->getStyle("{$col}2:{$col}{$highestRow}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('FFFBEB');
                }

                $this->applyDateValidation($sheet, $highestRow);
                $this->applyTimeValidation($sheet, $highestRow);
                $this->applyProtectionRules($sheet, $highestRow);
                $this->applyTimeColumnComment($sheet);
                $this->applyWorksheetGuidance($sheet);
            },
        ];
    }

    // ─── Formula generators ──────────────────────────────────────────

    /**
     * Product name lookup: barcode first, then SKU fallback.
     *
     * IF barcode (C) is filled → INDEX/MATCH against reference barcode column (A)
     * ELSE IF sku (D) is filled → INDEX/MATCH against reference SKU column (B)
     * ELSE → empty
     */
    protected function productNameFormula(int $rowNumber): string
    {
        $ref = DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET;
        $max = DailySalesTemplateColumns::ENTRY_TEMPLATE_ROWS + 1;

        return sprintf(
            '=IF($C%d<>"",IFERROR(INDEX(\'%s\'!$D$2:$D$%d,MATCH($C%d,\'%s\'!$A$2:$A$%d,0)),""),IF($D%d<>"",IFERROR(INDEX(\'%s\'!$D$2:$D$%d,MATCH($D%d,\'%s\'!$B$2:$B$%d,0)),""),""))',
            $rowNumber, $ref, $max, $rowNumber, $ref, $max,
            $rowNumber, $ref, $max, $rowNumber, $ref, $max,
        );
    }

    /**
     * Unit price lookup: barcode first, then SKU fallback.
     */
    protected function unitPriceFormula(int $rowNumber): string
    {
        $ref = DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET;
        $max = DailySalesTemplateColumns::ENTRY_TEMPLATE_ROWS + 1;

        return sprintf(
            '=IF($C%d<>"",IFERROR(INDEX(\'%s\'!$E$2:$E$%d,MATCH($C%d,\'%s\'!$A$2:$A$%d,0)),""),IF($D%d<>"",IFERROR(INDEX(\'%s\'!$E$2:$E$%d,MATCH($D%d,\'%s\'!$B$2:$B$%d,0)),""),""))',
            $rowNumber, $ref, $max, $rowNumber, $ref, $max,
            $rowNumber, $ref, $max, $rowNumber, $ref, $max,
        );
    }

    /**
     * Total amount: unit_price × quantity_sold (only when both present).
     */
    protected function totalAmountFormula(int $rowNumber): string
    {
        return sprintf(
            '=IF(OR($F%d="",$G%d=""),"",ROUND($F%d*$G%d,2))',
            $rowNumber,
            $rowNumber,
            $rowNumber,
            $rowNumber,
        );
    }

    // ─── Validation & protection ─────────────────────────────────────

    protected function applyDateValidation(Worksheet $sheet, int $highestRow): void
    {
        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $validation = $sheet->getCell("A{$rowNumber}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_DATE);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Invalid date');
            $validation->setError('Enter a valid sale date.');
        }
    }

    protected function applyTimeValidation(Worksheet $sheet, int $highestRow): void
    {
        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $validation = $sheet->getCell("B{$rowNumber}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_TIME);
            $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setPromptTitle('Enter sale time');
            $validation->setPrompt('Enter the sale time manually as a fixed value in hh:mm format.');
            $validation->setErrorTitle('Invalid time');
            $validation->setError('Enter a valid fixed sale time in hh:mm format.');
            $validation->setFormula1('TIME(0,0,0)');
            $validation->setFormula2('TIME(23,59,59)');
        }
    }

    /**
     * Protection rules:
     *
     * EDITABLE (unlocked):  B (time), C (barcode), D (sku), G (quantity_sold), I (note)
     * PROTECTED (locked):   A (date — pre-filled), E (product_name), F (unit_price), H (total_amount)
     */
    protected function applyProtectionRules(Worksheet $sheet, int $highestRow): void
    {
        $sheet->getProtection()->setSheet(true);

        // Explicitly lock pre-filled/formula columns.
        foreach (['A', 'E', 'F', 'H'] as $column) {
            $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                ->getProtection()
                ->setLocked(Protection::PROTECTION_PROTECTED);
        }

        // Unlock editable columns: time, barcode, sku, quantity_sold, note.
        foreach (['B', 'C', 'D', 'G', 'I'] as $column) {
            $sheet->getStyle("{$column}2:{$column}{$highestRow}")
                ->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }
    }

    protected function applyTimeColumnComment(Worksheet $sheet): void
    {
        $comment = $sheet->getComment('B1');
        $comment->setAuthor((string) config('app.name', 'Supermarket'));
        $comment->getText()->createTextRun('Enter the sale time manually as a fixed hh:mm value.');
    }

    protected function applyWorksheetGuidance(Worksheet $sheet): void
    {
        $sheet->mergeCells('J1:L1');
        $sheet->mergeCells('J2:L6');
        $sheet->setCellValue('J1', 'Quick guide');
        $sheet->setCellValue(
            'J2',
            implode("\n", [
                '• Scan barcode into column C',
                '  OR type SKU into column D.',
                '• Product name and price auto-fill.',
                '• Enter quantity sold in column G.',
                '• Total calculates automatically.',
                '• For time: Ctrl+Shift+; in Excel.',
            ]),
        );

        foreach (['J', 'K', 'L'] as $column) {
            $sheet->getColumnDimension($column)->setWidth(18);
        }

        $sheet->getStyle('J1:L6')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'],
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'F59E0B'],
                ],
            ],
        ]);

        $sheet->getStyle('J1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);

        $sheet->getStyle('J2:L6')->applyFromArray([
            'alignment' => [
                'wrapText' => true,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);
    }
}
