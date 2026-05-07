<?php

namespace App\Filament\Resources\SalesRecords\Pages;

use App\Actions\Sales\CreateQuickSalesRecordAction;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Filament\Resources\SalesRecords\SalesRecordResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListSalesRecords extends ListRecords
{
    protected static string $resource = SalesRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('record_sale')
                ->label('Record Sale')
                ->icon('heroicon-o-qr-code')
                ->form([
                    TextInput::make('barcode')
                        ->label('Barcode')
                        ->maxLength(255)
                        ->helperText('Scan a barcode here, or select a product below.'),
                    Select::make('product_id')
                        ->label('Product')
                        ->getSearchResultsUsing(fn (string $search): array => Product::query()
                            ->where(function (Builder $query) use ($search): void {
                                $query
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('barcode', 'like', "%{$search}%")
                                    ->orWhere('sku', 'like', "%{$search}%")
                                    ->orWhere('brand', 'like', "%{$search}%")
                                    ->orWhere('product_group', 'like', "%{$search}%");
                            })
                            ->orderBy('name')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn (Product $product): array => [
                                $product->id => trim($product->name.' ('.collect([$product->barcode, $product->sku])->filter()->implode(' / ').')'),
                            ])
                            ->all())
                        ->getOptionLabelUsing(function (mixed $value): ?string {
                            $product = Product::query()->find($value);

                            return $product
                                ? trim($product->name.' ('.collect([$product->barcode, $product->sku])->filter()->implode(' / ').')')
                                : null;
                        })
                        ->searchable()
                        ->searchDebounce(300)
                        ->optionsLimit(50),
                    TextInput::make('quantity_sold')
                        ->label('Quantity sold')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(),
                    DatePicker::make('sales_date')
                        ->default(now()->toDateString())
                        ->native(false)
                        ->required(),
                    TextInput::make('sales_time')
                        ->label('Sales time')
                        ->type('time'),
                    Textarea::make('note')
                        ->rows(3),
                ])
                ->action(fn (array $data) => app(CreateQuickSalesRecordAction::class)->execute(array_merge($data, [
                    'created_by' => auth()->id(),
                ]))),
            Action::make('sales_imports')
                ->label('Sales Imports')
                ->icon('heroicon-o-document-arrow-up')
                ->url(SalesImportBatchResource::getUrl('index')),
        ];
    }
}
