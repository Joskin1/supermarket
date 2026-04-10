<?php

namespace App\Filament\Resources\SalesRecords;

use App\Filament\Resources\SalesRecords\Pages\ListSalesRecords;
use App\Filament\Resources\SalesRecords\Pages\ViewSalesRecord;
use App\Filament\Resources\SalesRecords\Schemas\SalesRecordInfolist;
use App\Filament\Resources\SalesRecords\Tables\SalesRecordsTable;
use App\Models\SalesRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesRecordResource extends Resource
{
    protected static ?string $model = SalesRecord::class;

    protected static ?string $recordTitleAttribute = 'product_name_snapshot';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Sales Records';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    public static function infolist(Schema $schema): Schema
    {
        return SalesRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesRecords::route('/'),
            'view' => ViewSalesRecord::route('/{record}'),
        ];
    }
}
