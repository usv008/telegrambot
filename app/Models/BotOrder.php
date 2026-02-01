<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotOrder extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_orders_new';
    public $timestamps = false;
}
