<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Customer extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_customer';
    public $timestamps = false;
}
