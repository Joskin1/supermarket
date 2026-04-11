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
}
