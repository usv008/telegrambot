<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotAdvertisingChannelHistory extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_advertising_channel_history';
    public $timestamps = false;
}
