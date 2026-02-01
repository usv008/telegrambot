<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUsers extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_users';
    public $timestamps = false;
}
