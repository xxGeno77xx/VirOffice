<?php

namespace App\Filament\Imports;

use App\Models\Equipement;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class EquipementImporter extends Importer
{
    protected static ?string $model = Equipement::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
            ->requiredMapping()
            ->rules(['required', 'max:255']),
            
        ImportColumn::make('date')
            ->label('date')
            ->requiredMapping()
            ->rules(['required', 'max:32']),
        ];
    }

    public function resolveRecord(): ?Equipement
    {
        // return Equipement::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Equipement();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your equipement import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
