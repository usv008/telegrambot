<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaPurchasesComplects extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_purchases_complects';
    public $timestamps = false;
}
