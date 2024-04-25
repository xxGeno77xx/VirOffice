<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipement extends Model
{
    use HasFactory;

    
    public $timestamps = false;

    protected $table = "spt.vir_office";

    protected $primaryKey = "numero_vo";
}
