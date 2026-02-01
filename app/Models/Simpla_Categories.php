<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpla_Categories extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_categories';
    public $timestamps = false;
}
