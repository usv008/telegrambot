<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotPizzaNoBortiks extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_pizza_no_bortiks';
    public $timestamps = false;
}
