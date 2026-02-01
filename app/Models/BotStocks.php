<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotStocks extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_stocks';
    public $timestamps = false;
}
