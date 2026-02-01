<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotStocksProductsPresent extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_stocks_products_present';
    public $timestamps = false;
}
