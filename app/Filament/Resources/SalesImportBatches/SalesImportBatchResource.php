<?php

namespace App\Filament\Resources\SalesImportBatches;

use App\Filament\Resources\SalesImportBatches\Pages\CreateSalesImportBatch;
use App\Filament\Resources\SalesImportBatches\Pages\ListSalesImportBatches;
use App\Filament\Resources\SalesImportBatches\Pages\ViewSalesImportBatch;
use App\Filament\Resources\SalesImportBatches\RelationManagers\FailuresRelationManager;
use App\Filament\Resources\SalesImportBatches\RelationManagers\SalesRecordsRelationManager;
use App\Filament\Resources\SalesImportBatches\Schemas\SalesImportBatchForm;
use App\Filament\Resources\SalesImportBatches\Schemas\SalesImportBatchInfolist;
use App\Filament\Resources\SalesImportBatches\Tables\SalesImportBatchesTable;
use App\Models\SalesImportBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesImportBatchResource extends Resource
{
    protected static ?string $model = SalesImportBatch::class;

    protected static ?string $recordTitleAttribute = 'batch_code';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static ?string $navigationLabel = 'Sales Imports';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    public static function form(Schema $schema): Schema
    {
        return SalesImportBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SalesImportBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesImportBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SalesRecordsRelationManager::class,
            FailuresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesImportBatches::route('/'),
            'create' => CreateSalesImportBatch::route('/upload'),
            'view' => ViewSalesImportBatch::route('/{record}'),
        ];
    }
}
