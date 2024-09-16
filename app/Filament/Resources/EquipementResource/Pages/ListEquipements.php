<?php

namespace App\Filament\Resources\EquipementResource\Pages;

use Filament\Actions;
use App\Models\Operation;

use App\Models\Equipement;
use App\Imports\EquipementImport;
use Filament\Actions\ImportAction;
use Filament\Pages\Actions\Action;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use App\Imports\EquipementImporterX;
use Filament\Livewire\Notifications;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Imports\EquipementImporter;
use EightyNine\ExcelImport\ExcelImportAction;
use App\Filament\Resources\EquipementResource;
use App\Filament\Resources\EquipementResource\Widgets\PeriodiciteWidget;

class ListEquipements extends ListRecords
{
    protected static string $resource = EquipementResource::class;

    protected function getHeaderActions(): array
    {
        return [

            ExcelImportAction::make("upload")
            ->label("Importer")
                ->color(Color::Cyan)
                ->use(EquipementImporterX::class)
                ->after(function(){
                    
                    Notification::make()
                    ->title('Importé avec succès')
                    ->success()
                    ->send();
                }),

                Action::make("report")
                ->label("Rapport d'erreurs")
                ->color(Color::Red)
                    ->action(fn()=> Storage::download('errors.txt')),

                    Action::make("numero")
                    ->label("Numéro VO suivant")
                    ->color(Color::Red)
                    ->url(route('increment')),
                

        ];
    }

    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery()
            ->join("type_vo", "type_vo.code_type_vo", "=", "vir_office.code_type_vo")
            ->join("periodicite", "periodicite.code_periodicite", "=", "vir_office.code_periodicite")
            ->select("vir_office.*", "type_vo.libelle as vo", "libelle_periodicite as periodicite")
            ->orderBy("date_creation", "asc");
            // ->where("date_creation", today());
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PeriodiciteWidget::class
        ];
    }
}
