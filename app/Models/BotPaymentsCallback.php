<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotPaymentsCallback extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_payments_callback';
    public $timestamps = false;
}
