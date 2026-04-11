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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesEntryLogSheetExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected CarbonInterface $salesDate,
    ) {}

    public function array(): array
    {
        $rows = [];

        for ($rowNumber = 2; $rowNumber <= DailySalesTemplateColumns::ENTRY_TEMPLATE_ROWS + 1; $rowNumber++) {
            $rows[] = [
                'date' => $this->salesDate->toDateString(),
                'time' => null,
                'product_code' => null,
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
                $sheet->setAutoFilter("A1:H{$highestRow}");
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getStyle("A2:A{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD2);
                $sheet->getStyle("B2:B{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode('hh:mm');
                $sheet->getStyle("E2:G{$highestRow}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

                // Keep reference-driven columns visually distinct while leaving the row editable.
                $sheet->getStyle("D2:D{$highestRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('F3F4F6');
                $sheet->getStyle("G2:G{$highestRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('ECFDF5');

                $this->applyDateValidation($sheet, $highestRow);
                $this->applyTimeValidation($sheet, $highestRow);
                $this->applyTimeColumnComment($sheet);
                $this->applyWorksheetGuidance($sheet);
            },
        ];
    }

    protected function productNameFormula(int $rowNumber): string
    {
        return sprintf(
            '=IF($C%d="","",IFERROR(VLOOKUP($C%d,\'%s\'!$A:$D,3,FALSE),""))',
            $rowNumber,
            $rowNumber,
            DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET,
        );
    }

    protected function unitPriceFormula(int $rowNumber): string
    {
        return sprintf(
            '=IF($C%d="","",IFERROR(VLOOKUP($C%d,\'%s\'!$A:$D,4,FALSE),""))',
            $rowNumber,
            $rowNumber,
            DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET,
        );
    }

    protected function totalAmountFormula(int $rowNumber): string
    {
        return sprintf(
            '=IF(OR($E%d="",$F%d=""),"",ROUND($E%d*$F%d,2))',
            $rowNumber,
            $rowNumber,
            $rowNumber,
            $rowNumber,
        );
    }

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

    protected function applyTimeColumnComment(Worksheet $sheet): void
    {
        $comment = $sheet->getComment('B1');
        $comment->setAuthor((string) config('app.name', 'Supermarket'));
        $comment->getText()->createTextRun('Enter the sale time manually as a fixed hh:mm value.');
    }

    protected function applyWorksheetGuidance(Worksheet $sheet): void
    {
        $sheet->mergeCells('J1:L1');
        $sheet->mergeCells('J2:L4');
        $sheet->setCellValue('J1', 'Quick guide');
        $sheet->setCellValue(
            'J2',
            "Enter one sale per row.\nFor time, press Ctrl+Shift+: in Excel to insert the current time as a fixed value.",
        );

        foreach (['J', 'K', 'L'] as $column) {
            $sheet->getColumnDimension($column)->setWidth(18);
        }

        $sheet->getStyle('J1:L4')->applyFromArray([
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

        $sheet->getStyle('J2:L4')->applyFromArray([
            'alignment' => [
                'wrapText' => true,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);
    }
}
