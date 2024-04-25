<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeVo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = "spt.type_vo";

    protected $primaryKey = "code_type_vo";
}
