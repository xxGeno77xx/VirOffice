<?php

namespace App\Filament\Resources\EquipementResource\Pages;

use Filament\Actions;
use App\Models\Equipement;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\EquipementResource;

class EditEquipement extends EditRecord
{
    protected static string $resource = EquipementResource::class;

    protected function authorizeAccess(): void
    {
        $record = $this->record;

        abort_if($record->etat_vo != 0, 403, __("Ce virement en en cours. Vous ne pouvez pas le modifier"));
    }


    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),

            Action::make("Virement précédent")
            ->icon("heroicon-o-arrow-left-circle")
            ->url(fn($record) => route("filament.admin.resources.equipements.edit",["record" => $record->numero_vo - 1]))
            ->visible(fn($record)=> $record->numero_vo == Equipement::min("numero_vo")? false : true),


            Action::make("Virement suivant")
            ->icon("heroicon-o-arrow-right-circle")
            ->iconPosition('after') 
            ->url(fn($record) => route("filament.admin.resources.equipements.edit",["record" => $record->numero_vo + 1]))
            ->visible(fn($record)=> $record->numero_vo == Equipement::max("numero_vo")? false : true)
            
        ];
    }
}
