<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotOrderOld extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_order';
    public $timestamps = false;
}
