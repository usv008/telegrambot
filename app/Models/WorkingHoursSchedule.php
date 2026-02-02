<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHoursSchedule extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_working_hours_schedule';
    protected $fillable = ['day_of_week', 'is_working_day', 'open_time', 'close_time'];
}
