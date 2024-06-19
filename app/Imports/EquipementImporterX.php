<?php

namespace App\Imports;

use PDO;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Compte;
use App\Models\TypeVo;
use App\Models\Anomalie;
use App\Models\Operation;
use App\Models\Equipement;
use App\Models\Periodicite;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;

class EquipementImporterX implements ToModel, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    use RemembersRowNumber, Importable;
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $numeroCaisse = DB::table("caissier")->whereRaw("rtrim(nom_caissier) = rtrim(?)", [strtoupper(auth()->user()->name)])->first();

        $nextNumeroOperation = DB::getSequence()->nextValue('spt.numero_operation');

        $pdo = DB::getPdo();

        $lib_mvt = $row["libelle"];
        $taxe_total_taf = 0;  //static because  taxe = N
        $zero = 0; //static
        $sysDate = Carbon::parse(today())->format("Y/m/d"); //is date...doesn't have to be changed
        $sens = "D"; // static
        $codeOp = 501; //  static....for now
        $num_op = $nextNumeroOperation; // not static
        $null = null; //  static
        $code_guichet = $numeroCaisse->numero_caissier; // not static
        $code_guichet_second = $numeroCaisse->numero_caisse; // not static
        $bureau_poste = $numeroCaisse->code_agence; // not static
        $second_null = null; //  static
        $second_zero = 0; //  static
        $third_zero = 0; //  static
        //$num_mvt =946640; // output param
        $aOne = 1; //static   =  code application
        $thirdNull = null; //static
        $xof = 'XOF'; //static
        $numeroCompte = $row["numero_compte"];// non static
        $num_op = $nextNumeroOperation; //non static i guess
        $total_taf = 0; // static  because N = 0
        $taxe = 0; //  static because N = 0
        $lastZero = 0;
        $utilisateur = strtoupper(auth()->user()->name);



        $stmtInsertionMvtCaisseAG = $pdo->prepare("begin insertion_mvt_caisse_ag_filament(:lib_mvt, :taxe_total_taf, :zero, :sysDate, :sens, :codeOp, :num_op, :null,:code_guichet, :code_guichet_second, :bureau_poste, :second_null, :second_zero, :third_zero, :num_mvt, :aOne, :thirdNull , :xof, :numeroCompte, :num_op, :total_taf, :taxe, :lastZero, :utilisateur); end;");
        $stmtInsertionMvtCaisseAG->bindParam(':lib_mvt', $lib_mvt, PDO::PARAM_STR);
        $stmtInsertionMvtCaisseAG->bindParam(':taxe_total_taf', $taxe_total_taf, PDO::PARAM_INT);
        $stmtInsertionMvtCaisseAG->bindParam(':zero', $zero, PDO::PARAM_INT);
        $stmtInsertionMvtCaisseAG->bindParam(':sysDate', $sysDate, PDO::PARAM_STR);  //date
        $stmtInsertionMvtCaisseAG->bindParam(':sens', $sens, PDO::PARAM_STR);  //sens
        $stmtInsertionMvtCaisseAG->bindParam(':codeOp', $codeOp, PDO::PARAM_INT); //codeOp
        $stmtInsertionMvtCaisseAG->bindParam(':num_op', $num_op, PDO::PARAM_INT); //numOp
        $stmtInsertionMvtCaisseAG->bindParam(':null', $null, PDO::PARAM_NULL); //null value
        $stmtInsertionMvtCaisseAG->bindParam(':code_guichet', $code_guichet, PDO::PARAM_INT); //codeGuichet
        $stmtInsertionMvtCaisseAG->bindParam(':code_guichet_second', $code_guichet_second, PDO::PARAM_INT); //codeGuichet 2
        $stmtInsertionMvtCaisseAG->bindParam(':bureau_poste', $bureau_poste, PDO::PARAM_INT); //bureau poste 2
        $stmtInsertionMvtCaisseAG->bindParam(':second_null', $second_null, PDO::PARAM_NULL); //second_null 
        $stmtInsertionMvtCaisseAG->bindParam(':second_zero', $second_zero, PDO::PARAM_INT); //second_zero
        $stmtInsertionMvtCaisseAG->bindParam(':third_zero', $third_zero, PDO::PARAM_INT); //third_zero
        $stmtInsertionMvtCaisseAG->bindParam(':num_mvt', $num_mvt, PDO::PARAM_INT); //num_mvt         sortie|PDO::PARAM_INPUT_OUTPUT
        $stmtInsertionMvtCaisseAG->bindParam(':aOne', $aOne, PDO::PARAM_INT); //num_mvt
        $stmtInsertionMvtCaisseAG->bindParam(':thirdNull', $thirdNull, PDO::PARAM_NULL); //thirdNull 
        $stmtInsertionMvtCaisseAG->bindParam(':xof', $xof, PDO::PARAM_STR);  //XOF
        $stmtInsertionMvtCaisseAG->bindParam(':numeroCompte', $numeroCompte, PDO::PARAM_INT); //numeroCompte
        $stmtInsertionMvtCaisseAG->bindParam(':num_op', $num_op, PDO::PARAM_INT); //numeroOperation
        $stmtInsertionMvtCaisseAG->bindParam(':total_taf', $total_taf, PDO::PARAM_INT); //total_taf
        $stmtInsertionMvtCaisseAG->bindParam(':taxe', $taxe, PDO::PARAM_INT); //numeroOperation
        $stmtInsertionMvtCaisseAG->bindParam(':lastZero', $lastZero, PDO::PARAM_INT); //static  0
        $stmtInsertionMvtCaisseAG->bindParam(':utilisateur', $utilisateur, PDO::PARAM_STR); //static  0

        ////Second proccedure compta_mvt_prov


        $deux = 2; // static
        $fourthNull = null; //static
        $bureau_postea = $bureau_poste; //auth's bureau
        $bureau_posteb = $bureau_poste; //auth's bureau 2
        $fifthNull = null; //fifth null

        $pdo = DB::getPdo();

        $stmtComptaMvtProv = $pdo->prepare("begin compta_mvt_prov_filament(:num_mvt,
            :deux,
            :numeroCompte,
            :null,
            :second_null,
            :thirdNull,
            :fourthNull,
            :bureau_postea,
            :bureau_posteb,
            :xof,
            :fifthNull,
            :compta,
            :codeOp,
            :utilisateur); end;");

        $stmtComptaMvtProv->bindParam(':num_mvt', $num_mvt, PDO::PARAM_INT); //num_mvt
        $stmtComptaMvtProv->bindParam(':deux', $deux, PDO::PARAM_INT); //numeroCompte
        $stmtComptaMvtProv->bindParam(':numeroCompte', $numeroCompte, PDO::PARAM_INT); //numeroCompte
        $stmtComptaMvtProv->bindParam(':null', $null, PDO::PARAM_NULL); //null value
        $stmtComptaMvtProv->bindParam(':second_null', $second_null, PDO::PARAM_NULL); //second_null 
        $stmtComptaMvtProv->bindParam(':thirdNull', $thirdNull, PDO::PARAM_NULL); //thirdNull 
        $stmtComptaMvtProv->bindParam(':fourthNull', $fourthNull, PDO::PARAM_NULL); //fouthNull 
        $stmtComptaMvtProv->bindParam(':bureau_postea', $bureau_postea, PDO::PARAM_INT); //bureau poste 1
        $stmtComptaMvtProv->bindParam(':bureau_posteb', $bureau_posteb, PDO::PARAM_INT); //bureau poste 2
        $stmtComptaMvtProv->bindParam(':xof', $xof, PDO::PARAM_STR);  //XOF
        $stmtComptaMvtProv->bindParam(':fifthNull', $fifthNull, PDO::PARAM_NULL); //fifthNull 
        $stmtComptaMvtProv->bindParam(':compta', $compta, PDO::PARAM_INT); //fouthNull 
        $stmtComptaMvtProv->bindParam(':codeOp', $codeOp, PDO::PARAM_INT); //codeOp
        $stmtComptaMvtProv->bindParam(':utilisateur', $utilisateur, PDO::PARAM_STR); //static  0


        try {

            Equipement::create([

                'numero_vo' => Equipement::max("numero_vo") + 1,
                'numero_compte' => $row["numero_compte"],
                'libelle' => $row["libelle"],
                'code_type_vo' => $row['code_type_vo'],
                'code_periodicite' => $row['code_periodicite'],
                'montant_total' => $row['montant_total'],
                'nbre_traite' => $row['nbre_traite'],
                'montant_vo' => $row['montant_vo'],
                'montant_vo_fin' => $row['montant_vo_fin'],
                'periode_debut' => Carbon::parse($row['periode_debut'])->format("Y-m-d"),
                'periode_fin' => Carbon::parse($row['periode_fin'])->format("Y-m-d"),
                'com_numero_compte' => $row['com_numero_compte'],
                'date_creation' => today(),
                'code_utilisateur' => strtoupper(auth()->user()->name),
                'date_etat' => today(),
                'taxe' => 'N',                               //strtoupper($row['taxe']),
                'etat_vo' => 0,
                'numero_operation' => $nextNumeroOperation
            ]);

            $stmtInsertionMvtCaisseAG->execute();
            $stmtComptaMvtProv->execute();

            if ($compta == 2) {
                throw new Exception("Opération annulée");
            }

            DB::commit();

            Notification::make()
            ->title('Importé avec succès')
            ->success()
            ->send();


        } catch (Exception $e) {

            $message = $e->getMessage();

            Notification::make()
                ->title('Erreur : ' . $message)
                ->warning()
                ->send();

            DB::rollBack();

        }

    }


    public function rules(): array
    {

        $existingTypeVoArray = TypeVo::pluck("code_type_vo")->toArray();

        $existingPeriodiciteArray = Periodicite::pluck("code_periodicite")->toArray();


        return [

            '*.code_type_vo' => function ($attribute, $value, $onFailure, ) use ($existingTypeVoArray) {

                if (!in_array($value, $existingTypeVoArray)) {

                    Notification::make()
                        ->title($onFailure('Erreur code type vo'))
                        ->warning()
                        ->send();
                }
            },

            '*.code_periodicite' => function ($attribute, $value, $onFailure, ) use ($existingPeriodiciteArray) {

                if (!in_array($value, $existingPeriodiciteArray)) {

                    Notification::make()
                        ->title($onFailure('Erreur code periodicité'))
                        ->warning()
                        ->send();
                }
            },



            '*.taxe' => function ($attribute, $value, $onFailure) {

                if (!in_array($value, ["O", "N"])) {

                    $onFailure('Ligne ' . $this->getRowNumber() + 1 . ': La valeur de la taxe doit être soit "O", soit "N" (en majuscules)');

                    Notification::make()
                        ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': La valeur de la taxe doit être soit "O", soit "N" (en majuscules)'))
                        ->warning()
                        ->send();
                }
            },

            '*.numero_compte' => function ($attribute, $value, $onFailure) {

                if (!is_numeric($value)) {

                    Notification::make()
                        ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': Un numéro de compte ne doit contenir que des  chiffres (Débit)'))
                        ->warning()
                        ->send();

                } elseif (strlen($value) < 16) {

                    Notification::make()
                        ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': Un numéro de compte (débit) doit contenir 16 chiffres (Débit)'))
                        ->warning()
                        ->send();
                } else {

                    $existingAccounts = DB::table("compte")
                        ->selectRaw("numero_compte")
                        ->whereRaw("numero_compte = ?", $value)
                        ->whereRaw("code_etat_compte = 1")
                        ->first();

                    if (!$existingAccounts) {

                        Notification::make()
                            ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': Numéro de compte inexistant ( débit)'))
                            ->warning()
                            ->send();
                    }
                }
            },

            '*.com_numero_compte' => function ($attribute, $value, $onFailure) {

                if (!is_numeric($value)) {

                    Notification::make()
                        ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': Un numéro de compte ne doit contenir que des  chiffres (Débit)'))
                        ->warning()
                        ->send();

                } elseif (strlen($value) < 16) {

                    Notification::make()
                        ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': Un numéro de compte doit contenir 16 chiffres (Débit)'))
                        ->warning()
                        ->send();
                } else {

                    $existingAccounts = DB::table("compte")
                        ->selectRaw("numero_compte")
                        ->whereRaw("numero_compte = ?", $value)
                        ->whereRaw("code_etat_compte = 1")
                        ->first();

                    if (!$existingAccounts) {

                        Notification::make()
                            ->title($onFailure('Ligne ' . $this->getRowNumber() + 1 . ': Numéro de compte (Crédit) inexistant'))
                            ->warning()
                            ->send();
                    }
                }
            },

        ];
    }



    public function batchSize(): int
    {
        return 1000;


    }


}
