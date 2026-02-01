<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotEcoUser extends Model
{
    protected $connection = 'mysql_ecopizza_bot';
    protected $table = 'user';
    public $timestamps = false;
}
