<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Cashback_Categories extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'cashback_categories';
    public $timestamps = false;
}
