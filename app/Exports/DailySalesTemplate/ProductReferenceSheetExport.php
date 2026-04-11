<?php

namespace App\Exports\DailySalesTemplate;

use App\Models\Product;
use App\Support\SalesImport\DailySalesTemplateColumns;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductReferenceSheetExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function collection(): Collection
    {
        return Product::query()
            ->with('category:id,name')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'product_code' => $product->sku,
                'category' => $product->category?->name,
                'product_name' => $product->name,
                'unit_price' => $product->selling_price,
            ]);
    }

    public function headings(): array
    {
        return DailySalesTemplateColumns::productReference();
    }

    public function title(): string
    {
        return DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D1FAE5'],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestRow = max($sheet->getHighestDataRow(), 1);

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:D{$highestRow}");
                $sheet->getProtection()->setSheet(true);
            },
        ];
    }
}
