<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsRaffleImages extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_raffle_images';
    public $timestamps = false;
}
