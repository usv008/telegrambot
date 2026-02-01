<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSendingMessagesHistory extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_sending_messages_history';
    public $timestamps = false;
}
