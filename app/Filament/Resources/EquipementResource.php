<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Tables;
use App\Models\TypeVo;
use Filament\Forms\Form;
use App\Models\Equipement;
use Filament\Tables\Table;
use App\Models\Periodicite;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EquipementResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use App\Filament\Resources\EquipementResource\RelationManagers;

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

                                try{

                                    $result = "";
                                    $client = DB::table("client")
                                        ->join("compte", "compte.numero_client", "=", "client.numero_client")
                                        ->whereRaw("numero_compte = ?", [$get("numero_compte")])
                                        ->first();

                                        if($client)
                                        {
                                            $result =   $client->nom_client . " " . $client->prenom_client;
                                        }

                                        else $result = "Client non trouvé";

                                        return $result;
                                }
                                catch(\Exception $e)
                                {

                                }
                              
                                    
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
                                        ->live()
                                    ,

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
    
                                    try{
    
                                        $result = "";
                                        $client = DB::table("client")
                                            ->join("compte", "compte.numero_client", "=", "client.numero_client")
                                            ->whereRaw("numero_compte = ?", [$get("com_numero_compte")])
                                            ->first();
    
                                            if($client)
                                            {
                                                $result =   $client->nom_client . " " . $client->prenom_client;
                                            }
    
                                            else $result = "Client non trouvé";
    
                                            return $result;
                                    }
                                    catch(\Exception $e)
                                    {
                                        
                                    }
                                  
                                        
                                }),
                        ])





            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make("numero_vo"),

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
                            "TRIMESTRIELLE" => 'closed',
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
                        ->date("d-m-y"),

                    TextColumn::make("periode_fin")
                        ->label("période fin")
                        ->date("d-m-y"),

                    TextColumn::make("com_numero_compte")
                        ->label("Compte à créditer"),

                    TextColumn::make("etat_vo")
                        ->label("Etat")
                        ->formatStateUsing(function ($state): string {

                            $result = "";

                            switch ($state) {
                                case 0:
                                    $result = 'Non valide';
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
                    //
                ])
            ->actions([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make()
                        ->hidden(fn($record) => $record->etat_vo != 0),
                ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        // Tables\Actions\DeleteBulkAction::make(),
                        ExportBulkAction::make()
                    ]),
                ]);

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
            'edit' => Pages\EditEquipement::route('/{record}/edit'),
        ];
    }
}
