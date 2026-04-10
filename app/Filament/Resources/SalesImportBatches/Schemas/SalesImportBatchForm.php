<?php

namespace App\Filament\Resources\SalesImportBatches\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesImportBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Upload daily sales file')
                    ->description('Upload the completed spreadsheet exported from the Daily Sales Export page. The system will validate each row, save valid sales, deduct stock safely, and show failures clearly.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Sales file')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'text/csv',
                                'text/plain',
                            ])
                            ->maxSize(10240)
                            ->storeFiles(false)
                            ->required()
                            ->helperText('Allowed files: .xlsx or .csv, up to 10 MB. Keep the exported columns unchanged for the cleanest import.')
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull()
                            ->helperText('Optional context, such as the branch note, cashier shift, or anything worth remembering about this upload.'),
                    ]),
            ]);
    }
}
