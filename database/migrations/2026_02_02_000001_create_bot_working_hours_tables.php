<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateBotWorkingHoursTables extends Migration
{
    protected $connection = 'mysql_ecopizza';

    public function up()
    {
        Schema::connection($this->connection)->create('bot_working_hours_schedule', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('day_of_week')->unsigned()->comment('0=Monday, 6=Sunday');
            $table->boolean('is_working_day')->default(1);
            $table->time('open_time')->default('09:00:00');
            $table->time('close_time')->default('22:00:00');
            $table->timestamps();
            $table->unique('day_of_week');
        });

        // Default schedule: Mon-Fri 09:00-22:00, Sat-Sun 10:00-21:00
        $days = [
            ['day_of_week' => 0, 'is_working_day' => 1, 'open_time' => '09:00:00', 'close_time' => '22:00:00'],
            ['day_of_week' => 1, 'is_working_day' => 1, 'open_time' => '09:00:00', 'close_time' => '22:00:00'],
            ['day_of_week' => 2, 'is_working_day' => 1, 'open_time' => '09:00:00', 'close_time' => '22:00:00'],
            ['day_of_week' => 3, 'is_working_day' => 1, 'open_time' => '09:00:00', 'close_time' => '22:00:00'],
            ['day_of_week' => 4, 'is_working_day' => 1, 'open_time' => '09:00:00', 'close_time' => '22:00:00'],
            ['day_of_week' => 5, 'is_working_day' => 1, 'open_time' => '10:00:00', 'close_time' => '21:00:00'],
            ['day_of_week' => 6, 'is_working_day' => 1, 'open_time' => '10:00:00', 'close_time' => '21:00:00'],
        ];
        $now = now();
        foreach ($days as $day) {
            $day['created_at'] = $now;
            $day['updated_at'] = $now;
            DB::connection($this->connection)->table('bot_working_hours_schedule')->insert($day);
        }

        Schema::connection($this->connection)->create('bot_working_hours_closures', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('reason', 500)->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
            $table->index(['active', 'start_datetime', 'end_datetime'], 'active_dates');
        });

        Schema::connection($this->connection)->create('bot_working_hours_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_name', 100)->unique();
            $table->text('setting_value')->nullable();
            $table->timestamps();
        });

        // Default settings
        $settings = [
            ['setting_name' => 'enabled', 'setting_value' => '1'],
            ['setting_name' => 'blocking_mode', 'setting_value' => 'orders_only'],
            ['setting_name' => 'quick_pause_active', 'setting_value' => '0'],
            ['setting_name' => 'quick_pause_end', 'setting_value' => null],
            ['setting_name' => 'quick_pause_reason', 'setting_value' => null],
            ['setting_name' => 'message_title', 'setting_value' => 'Ми зараз не працюємо'],
            ['setting_name' => 'message_text', 'setting_value' => 'На жаль, зараз неробочий час. Будь ласка, спробуйте пізніше.'],
        ];
        foreach ($settings as $setting) {
            $setting['created_at'] = $now;
            $setting['updated_at'] = $now;
            DB::connection($this->connection)->table('bot_working_hours_settings')->insert($setting);
        }
    }

    public function down()
    {
        Schema::connection($this->connection)->dropIfExists('bot_working_hours_settings');
        Schema::connection($this->connection)->dropIfExists('bot_working_hours_closures');
        Schema::connection($this->connection)->dropIfExists('bot_working_hours_schedule');
    }
}
