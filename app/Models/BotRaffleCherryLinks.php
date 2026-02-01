<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotRaffleCherryLinks extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_raffle_cherry_links';
    public $timestamps = false;

    public static function getLinkFromName($name)
    {
        return self::where('name', $name)->first();
    }

}
