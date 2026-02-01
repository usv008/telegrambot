<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsDelivery extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_delivery';
    public $timestamps = false;
}
