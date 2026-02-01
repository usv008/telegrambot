<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotFeedBack extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_feedback';
    public $timestamps = false;
}
