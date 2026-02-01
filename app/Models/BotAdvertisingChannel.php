<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotAdvertisingChannel extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_advertising_channel';
    public $timestamps = false;
}
