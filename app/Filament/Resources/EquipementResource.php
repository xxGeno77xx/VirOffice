<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\TypeVo;
use Filament\Forms\Form;
use App\Models\Equipement;
use Filament\Tables\Table;
use App\Models\Periodicite;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EquipementResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\EquipementResource\RelationManagers;
use App\Filament\Resources\EquipementResource\Widgets\PeriodiciteWidget;

class EquipementResource extends Resource
{

    protected static ?string $model = Equipement::class;
    protected static ?string $label = 'Virements';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make("Débit")
                    ->schema([
                        Placeholder::make("client")
                            ->content(function ($state, $get) {

                                    $result = "";

                                    $client = DB::table("client")
                                        ->join("compte", "compte.numero_client", "=", "client.numero_client")
                                        ->whereRaw("numero_compte = ?", [$get("numero_compte")])
                                        ->first();

                                    if ($client) {
                                        $result = $client->nom_client . " " . $client->prenom_client;
                                    } else
                                        $result = "Client non trouvé";

                                    return $result;

                            }),

                        Grid::make(2)
                            ->schema([

                                TextInput::make("numero_compte")
                                    ->label("Numéro compte")
                                    ->debounce(500)
                                    ->rules([
                                        'digits:16',
                                        'numeric'
                                    ])
                                    ->live(),

                                TextInput::make("libelle"),

                                Select::make("code_type_vo")
                                    ->label("Type vo")
                                    ->native(false)
                                    ->options(TypeVo::pluck("libelle", "code_type_vo")),

                                Select::make("code_periodicite")
                                    ->label("Périodicité")
                                    ->native(false)
                                    ->options(Periodicite::pluck("libelle_periodicite", "code_periodicite")),

                                TextInput::make("montant_total")
                                    ->numeric(),

                                TextInput::make("nbre_traite")
                                    ->numeric(),

                                TextInput::make("montant_vo")
                                    ->numeric(),

                                TextInput::make("montant_vo_fin")
                                    ->numeric(),

                                DatePicker::make("periode_debut")
                                    ->label("Période début"),

                                DatePicker::make("periode_fin")
                                    ->label("Période fin")
                                    ->after('periode_debut'),

                                Toggle::make("taxe")
                                    ->afterStateHydrated(function (Toggle $component, $state) {
                                        $component->state($state === 'O');
                                    })
                                    ->dehydrateStateUsing(fn($state): string => $state ? 'O' : 'N'),

                                Select::make("etat_vo")
                                    ->native(false)
                                    ->options([
                                        0 => "Non validé",
                                        1 => "En cours",
                                        2 => "Suspendu",
                                        9 => "Annulé",
                                        3 => "Clôturé", 

                                    ])
                            ])

                    ]),

                Section::make("Crédit")
                    ->schema([

                        TextInput::make("com_numero_compte")
                            ->label("Numéro compte")
                            ->debounce(500)
                            ->live(),

                        Placeholder::make("client_com")
                            ->label("Bénéficiaire")
                            ->content(function ($state, $get) {

                                try {

                                    $result = "";
                                    $client = DB::table("client")
                                        ->join("compte", "compte.numero_client", "=", "client.numero_client")
                                        ->whereRaw("numero_compte = ?", [$get("com_numero_compte")])
                                        ->first();

                                    if ($client) {
                                        $result = $client->nom_client . " " . $client->prenom_client;
                                    } else
                                        $result = "Client non trouvé";

                                    return $result;
                                } catch (\Exception $e) {

                                }


                            }),
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("numero_vo")
                    ->searchable(),

                TextColumn::make("numero_compte")
                    ->label("Numéro compte (débit)"),

                TextColumn::make("libelle"),

                TextColumn::make("vo")
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label("Type Vo"),

                TextColumn::make("periodicite")
                    ->badge()

                    ->color(fn(string $state): string => match ($state) {
                        "MENSUELLE" => 'primary',
                        "ANNUELLE" => 'danger',
                        "BIMESTRIELLE" => 'success',
                        "SEMESTRIELLE" => 'danger',
                        "TRIMESTIELLE" => 'closed',
                    }),

                TextColumn::make("montant_total")
                    ->numeric(
                        decimalPlaces: 0,
                        thousandsSeparator: "."
                    ),

                TextColumn::make("nbre_traite"),

                TextColumn::make("montant_vo")
                    ->numeric(
                        decimalPlaces: 0,
                        thousandsSeparator: "."
                    ),

                TextColumn::make("montant_vo_fin")
                    ->label("Montant vo fin")
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make("periode_debut")
                    ->label("période début")
                    ->date("d-m-Y"),

                TextColumn::make("periode_fin")
                    ->label("période fin")
                    ->date("d-m-Y"),

                TextColumn::make("com_numero_compte")
                    ->label("Compte à créditer"),

                TextColumn::make("etat_vo")
                    ->label("Etat")
                    ->formatStateUsing(function ($state): string {

                        $result = "";

                        switch ($state) {
                            case 0:
                                $result = 'Non validé';
                                break;

                            case 1:
                                $result = 'En cours';
                                break;

                            case 2:
                                $result = 'Suspendu';
                                break;

                            case 9:
                                $result = 'Annulé';
                                break;

                            case 3:
                                $result = 'Clôturé';
                                break;
                        }

                        return $result;

                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        "0" => 'danger',
                        "1" => 'success',
                        "2" => 'warning',
                        "3" => 'danger',
                        "9" => 'closed',
                    }),


                TextColumn::make("numero_operation"),
            ])
            ->filters([
                SelectFilter::make('etat_vo')
                    ->native(false)
                    ->options([
                        "0" => 'Non validé',
                        "1" => 'En cours',
                        "2" => 'Suspendu',
                        "3" => 'Clôturé',
                        "9" => 'Annulé',
                    ])
            ])->deferFilters()
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->hidden(fn($record) => $record->etat_vo != 0),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([

                    ExportBulkAction::make()->color("primary"),

                    BulkAction::make("valider")
                    ->label("Valider")
                    ->requiresConfirmation()
                    ->icon("heroicon-o-check-circle")
                    ->color('success')
                    ->action(fn (Collection $records) => $records->each->update(["etat_vo" => 1]))
                    ->after(function(){
                        Notification::make()
                                ->title('Validé(s)')
                                ->success()
                                ->send();
                    }),

                    BulkAction::make("cancel")
                    ->requiresConfirmation()
                    ->label("annuler")
                    ->icon("heroicon-o-archive-box-x-mark")
                    ->color(Color::Gray)
                    ->action(fn (Collection $records) => $records->each->update(["etat_vo" => 9]))
                    ->after(function(){
                        Notification::make()
                                ->title('Annulé(s)')
                                ->color(Color::Gray)
                                ->icon("heroicon-o-archive-box-x-mark")
                                ->send();
                    })
                    
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $record->etat_vo == 0
            );

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
            'index' => Pages\ListEquipements::route('/'),
            'create' => Pages\CreateEquipement::route('/create'),
            // 'edit' => Pages\EditEquipement::route('/{record}/edit'),
        ];
    }

    
}
