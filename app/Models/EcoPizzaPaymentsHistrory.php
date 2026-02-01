<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoPizzaPaymentsHistrory extends Model
{
    protected $connection = 'mysql_ecopizza_vps';
    protected $table = 'payments_history';
    public $timestamps = false;
}
