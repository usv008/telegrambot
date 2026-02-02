<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHoursSettings extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_working_hours_settings';
    protected $fillable = ['setting_name', 'setting_value'];
}
