<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotReviews extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_sendreviews';
    public $timestamps = false;
}
