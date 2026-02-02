<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHoursClosure extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_working_hours_closures';
    protected $fillable = ['start_datetime', 'end_datetime', 'reason', 'active'];
}
