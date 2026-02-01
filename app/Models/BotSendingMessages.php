<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSendingMessages extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_sending_messages';
    protected $fillable = ['button1', 'button2'];
    public $timestamps = false;
}
