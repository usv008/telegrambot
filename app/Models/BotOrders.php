<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotOrders extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_orders';
    public $timestamps = false;
}
