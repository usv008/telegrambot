<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotIngredients extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_ingredients';
    public $timestamps = false;
}
