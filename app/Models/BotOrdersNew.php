<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotOrdersNew extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_orders_new';
    public $timestamps = false;

    public static function getOrdersForCashBack() {

        return self::where('cashback_cron', 0)->get();

    }

}
