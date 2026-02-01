<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotCart extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_cart';
    public $timestamps = false;
}
