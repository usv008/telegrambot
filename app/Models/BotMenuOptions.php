<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMenuOptions extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_menu_options';
    public $timestamps = false;
}
