<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcoPizzaTimeNotAccept extends Model
{
    protected $connection = 'mysql_ecopizza_vps';
    protected $table = 'time_not_accept';
    public $timestamps = false;
}
