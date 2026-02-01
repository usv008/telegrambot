<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaOrdersLabels extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_orders_labels';
    public $timestamps = false;
}
