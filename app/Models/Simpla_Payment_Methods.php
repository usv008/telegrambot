<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpla_Payment_Methods extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_payment_methods';
    public $timestamps = false;
}
