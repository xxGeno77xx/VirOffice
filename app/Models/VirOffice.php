<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirOffice extends Model
{
    use HasFactory;

    
    public $timestamps = false;

    protected $table = "gepar.vir_office_clone";

    protected $primaryKey = "numero_vo";
}
