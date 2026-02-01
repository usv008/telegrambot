<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaUsers extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_users';
    public $timestamps = false;
}
