<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotRaffle extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_raffle';
    public $timestamps = false;
}
