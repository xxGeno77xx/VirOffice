<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodicite extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "spt.periodicite";

    protected $primaryKey = "code_periodicite";
}
