<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotOrderContent extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_order_content';
    public $timestamps = false;
}
