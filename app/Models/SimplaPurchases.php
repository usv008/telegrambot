<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaPurchases extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_purchases';
    public $timestamps = false;
}
