<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Stock_Available extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_stock_available';
    public $timestamps = false;
}
