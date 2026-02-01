<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotStocksProducts extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_stocks_products';
    public $timestamps = false;
}
