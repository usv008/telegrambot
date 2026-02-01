<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Module_Currency extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_module_currency';
    public $timestamps = false;

    public static function getModulesByCurrency($currency_id = 1)
    {
        return self::where('id_currency', $currency_id)->get();
    }

}
