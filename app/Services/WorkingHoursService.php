<?php

namespace App\Services;

use App\Models\WorkingHoursClosure;
use App\Models\WorkingHoursSchedule;
use App\Models\WorkingHoursSettings;
use DateTime;

class WorkingHoursService
{
    /**
     * Get a setting value by key.
     */
    public static function getSetting($key, $default = null)
    {
        $setting = WorkingHoursSettings::where('setting_name', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setSetting($key, $value)
    {
        WorkingHoursSettings::updateOrCreate(
            ['setting_name' => $key],
            ['setting_value' => $value]
        );
    }

    /**
     * Check if the bot is currently open.
     *
     * Returns: ['is_open' => bool, 'reason' => string|null, 'next_open' => string|null, 'closure_type' => string|null]
     */
    public static function isCurrentlyOpen()
    {
        // 1. If module is disabled — always open
        if (self::getSetting('enabled', '1') != '1') {
            return ['is_open' => true, 'reason' => null, 'next_open' => null, 'closure_type' => null];
        }

        $now = new DateTime();

        // 2. Quick pause (highest priority)
        if (self::getSetting('quick_pause_active') == '1') {
            $pauseEnd = self::getSetting('quick_pause_end');
            if ($pauseEnd && strtotime($pauseEnd) > time()) {
                return [
                    'is_open' => false,
                    'reason' => self::getSetting('quick_pause_reason'),
                    'next_open' => $pauseEnd,
                    'closure_type' => 'quick_pause',
                ];
            }
            // Pause expired — auto-deactivate
            self::setSetting('quick_pause_active', '0');
        }

        // 3. Temporary closures
        $closure = WorkingHoursClosure::where('active', 1)
            ->where('start_datetime', '<=', $now->format('Y-m-d H:i:s'))
            ->where('end_datetime', '>', $now->format('Y-m-d H:i:s'))
            ->orderBy('start_datetime')
            ->first();

        if ($closure) {
            return [
                'is_open' => false,
                'reason' => $closure->reason,
                'next_open' => $closure->end_datetime,
                'closure_type' => 'scheduled',
            ];
        }

        // 4. Weekly schedule
        // PHP date('N') returns 1=Monday..7=Sunday; our day_of_week is 0=Monday..6=Sunday
        $dayOfWeek = (int) $now->format('N') - 1;
        $currentTime = $now->format('H:i:s');

        $schedule = WorkingHoursSchedule::where('day_of_week', $dayOfWeek)->first();

        if (!$schedule) {
            // No schedule entry — assume open
            return ['is_open' => true, 'reason' => null, 'next_open' => null, 'closure_type' => null];
        }

        if (!$schedule->is_working_day) {
            return [
                'is_open' => false,
                'reason' => null,
                'next_open' => self::findNextOpenTime($now),
                'closure_type' => 'schedule_day_off',
            ];
        }

        if ($currentTime < $schedule->open_time) {
            $nextOpen = $now->format('Y-m-d') . ' ' . $schedule->open_time;
            return [
                'is_open' => false,
                'reason' => null,
                'next_open' => $nextOpen,
                'closure_type' => 'schedule_before_open',
            ];
        }

        if ($currentTime >= $schedule->close_time) {
            return [
                'is_open' => false,
                'reason' => null,
                'next_open' => self::findNextOpenTime($now),
                'closure_type' => 'schedule_after_close',
            ];
        }

        // Within working hours
        return ['is_open' => true, 'reason' => null, 'next_open' => null, 'closure_type' => null];
    }

    /**
     * Find next opening time, scanning up to 7 days ahead.
     */
    public static function findNextOpenTime(DateTime $fromDate)
    {
        $checkDate = clone $fromDate;

        for ($i = 0; $i < 7; $i++) {
            $checkDate->modify('+1 day');
            $dayOfWeek = (int) $checkDate->format('N') - 1;

            $schedule = WorkingHoursSchedule::where('day_of_week', $dayOfWeek)->first();

            if ($schedule && $schedule->is_working_day) {
                return $checkDate->format('Y-m-d') . ' ' . $schedule->open_time;
            }
        }

        return null;
    }

    /**
     * Get the full weekly schedule (all 7 days).
     */
    public static function getFullSchedule()
    {
        return WorkingHoursSchedule::orderBy('day_of_week')->get();
    }

    /**
     * Get upcoming closures (active, ending in the future).
     */
    public static function getUpcomingClosures($limit = 20)
    {
        return WorkingHoursClosure::where('end_datetime', '>', now()->format('Y-m-d H:i:s'))
            ->orderBy('start_datetime')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all closures for admin display.
     */
    public static function getAllClosures()
    {
        return WorkingHoursClosure::orderBy('start_datetime', 'desc')->get();
    }

    /**
     * Get the blocking mode setting.
     */
    public static function getBlockingMode()
    {
        return self::getSetting('blocking_mode', 'orders_only');
    }

    /**
     * Build a formatted message for the user about non-working hours.
     */
    public static function getClosedMessage()
    {
        $status = self::isCurrentlyOpen();
        if ($status['is_open']) {
            return null;
        }

        $title = self::getSetting('message_title', 'Ми зараз не працюємо');
        $text = self::getSetting('message_text', 'На жаль, зараз неробочий час. Будь ласка, спробуйте пізніше.');

        $message = "🕐 <b>{$title}</b>\n\n{$text}";

        if ($status['reason']) {
            $message .= "\n\n📋 <i>{$status['reason']}</i>";
        }

        if ($status['next_open']) {
            $nextOpen = date('d.m.Y H:i', strtotime($status['next_open']));
            $message .= "\n\n⏰ Наступне відкриття: {$nextOpen}";
        }

        return $message;
    }
}
