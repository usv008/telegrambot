<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoPizzaTimeNotAcceptHistrory extends Model
{
    protected $connection = 'mysql_ecopizza_vps';
    protected $table = 'time_not_accept_history';
    public $timestamps = false;
}
