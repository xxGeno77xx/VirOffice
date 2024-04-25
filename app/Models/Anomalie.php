<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anomalie extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "gepar.mouvement_anomalie_clone";

    protected $primaryKey ="REFERENCE_MVT";

    // protected $primaryKey = "numero_vo";
}
