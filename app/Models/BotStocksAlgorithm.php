<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotStocksAlgorithm extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_stocks_algorithm';
    public $timestamps = false;
}
