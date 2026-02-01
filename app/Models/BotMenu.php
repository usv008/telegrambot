<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMenu extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_menu';
    public $timestamps = false;
}
