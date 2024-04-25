<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DbaUser extends Model
{
    use HasFactory;

    protected $table = 'DBA_USERS';
    protected $connection = 'oracle';

}
