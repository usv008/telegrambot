<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Addresses extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_address';
    public $timestamps = false;
}
