<?php

namespace App\Imports;

use App\Models\Equipement;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class EquipementImport implements ToModel , WithBatchInserts
{
    public function model(array $row)
    {
        
        if (!isset($row[0])) {
        return null;
    }



        return new Equipement([
            'compte_a_debiter' => $row[0],
            'libelle' => $row[1],
            'type_vo' => $row[2],
            'periodicite' => $row[3],
            'montant_total' => $row[4],
            'nombre_traite' =>  $row[5],
            'montant_vo' => $row[6],
            'montant_fin' => $row[7],
            'date_debut' => $row[8],
            'date_fin' => $row[9],
            'compte_a_crediter' => $row[10],

        ]);

        
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
