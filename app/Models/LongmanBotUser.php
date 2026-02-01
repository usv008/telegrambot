<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongmanBotUser extends Model
{
    protected $connection = 'mysql_bot';
    protected $table = 'bot_user';
    public $timestamps = false;
}
