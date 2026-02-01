<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotCashbackHistoryNew extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_cashback_history_new';
    public $timestamps = false;
}
