<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSettingsPayments extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_settings_payments';
    public $timestamps = false;
}
