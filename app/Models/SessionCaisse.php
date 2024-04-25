<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionCaisse extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "gpost.session_caisse";

    protected $primaryKey = "numero_caisse";
}
