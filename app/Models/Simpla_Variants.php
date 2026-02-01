<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpla_Variants extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_variants';
    public $timestamps = false;
}
