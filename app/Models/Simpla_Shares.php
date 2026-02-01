<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpla_Shares extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_shares';
    public $timestamps = false;
}
