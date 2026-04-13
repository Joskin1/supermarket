<?php

namespace App\Filament\Resources\BackupRuns;

use App\Filament\Resources\BackupRuns\Pages\ListBackupRuns;
use App\Filament\Resources\BackupRuns\Tables\BackupRunsTable;
use App\Models\BackupRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BackupRunResource extends Resource
{
    protected static ?string $model = BackupRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static ?string $navigationLabel = 'Backups';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    public static function table(Table $table): Table
    {
        return BackupRunsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBackupRuns::route('/'),
        ];
    }
}
