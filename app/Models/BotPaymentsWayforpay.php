<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotPaymentsWayforpay extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_payments_wayforpay';
    public $timestamps = false;
    protected $fillable = ['order_id', 'status', 'url', 'date_reg', 'result1', 'date_result1', 'result2', 'date_result2'];
}
