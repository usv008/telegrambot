<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\WorkingHoursClosure;
use App\Models\WorkingHoursSchedule;
use App\Services\WorkingHoursService;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BotSettingsWorkingHoursController extends Controller
{
    public static function execute(LRequest $request)
    {
        $user = Auth::user();

        if (Gate::allows('settings')) {

            $schedule = WorkingHoursService::getFullSchedule();
            $closures = WorkingHoursService::getAllClosures();
            $status = WorkingHoursService::isCurrentlyOpen();

            $settings = [
                'enabled' => WorkingHoursService::getSetting('enabled', '1'),
                'blocking_mode' => WorkingHoursService::getSetting('blocking_mode', 'orders_only'),
                'message_title' => WorkingHoursService::getSetting('message_title', ''),
                'message_text' => WorkingHoursService::getSetting('message_text', ''),
                'quick_pause_active' => WorkingHoursService::getSetting('quick_pause_active', '0'),
                'quick_pause_end' => WorkingHoursService::getSetting('quick_pause_end'),
                'quick_pause_reason' => WorkingHoursService::getSetting('quick_pause_reason'),
                'allow_future_orders' => WorkingHoursService::getSetting('allow_future_orders', '1'),
            ];

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $dayNames = ['Понеділок', 'Вівторок', 'Середа', 'Четвер', "П'ятниця", 'Субота', 'Неділя'];

            $data = [
                'title' => 'Робочий час',
                'page' => 'settings_working_hours',
                'schedule' => $schedule,
                'closures' => $closures,
                'status' => $status,
                'wh_settings' => $settings,
                'day_names' => $dayNames,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);
        }

        return redirect(404);
    }

    public static function saveSchedule(LRequest $request)
    {
        $input = $request->except('_token');

        for ($day = 0; $day <= 6; $day++) {
            WorkingHoursSchedule::updateOrCreate(
                ['day_of_week' => $day],
                [
                    'is_working_day' => isset($input['is_working_day'][$day]) ? 1 : 0,
                    'open_time' => $input['open_time'][$day] ?? '09:00',
                    'close_time' => $input['close_time'][$day] ?? '22:00',
                ]
            );
        }

        return redirect()->route('settings_working_hours');
    }

    public static function saveClosure(LRequest $request)
    {
        $input = $request->except('_token');

        $data = [
            'start_datetime' => $input['start_datetime'],
            'end_datetime' => $input['end_datetime'],
            'reason' => $input['reason'] ?? null,
            'active' => isset($input['active']) ? (int) $input['active'] : 1,
        ];

        if (!empty($input['closure_id'])) {
            WorkingHoursClosure::where('id', $input['closure_id'])->update($data);
        } else {
            WorkingHoursClosure::create($data);
        }

        return redirect()->route('settings_working_hours');
    }

    public static function deleteClosure(LRequest $request)
    {
        $input = $request->except('_token');

        if (!empty($input['closure_id'])) {
            WorkingHoursClosure::where('id', $input['closure_id'])->delete();
        }

        return redirect()->route('settings_working_hours');
    }

    public static function saveSettings(LRequest $request)
    {
        $input = $request->except('_token');

        WorkingHoursService::setSetting('enabled', isset($input['enabled']) ? '1' : '0');
        WorkingHoursService::setSetting('blocking_mode', $input['blocking_mode'] ?? 'orders_only');
        WorkingHoursService::setSetting('allow_future_orders', isset($input['allow_future_orders']) ? '1' : '0');
        WorkingHoursService::setSetting('message_title', $input['message_title'] ?? '');
        WorkingHoursService::setSetting('message_text', $input['message_text'] ?? '');

        return redirect()->route('settings_working_hours');
    }

    public static function quickPause(LRequest $request)
    {
        $input = $request->except('_token');

        $minutes = (int) ($input['pause_duration'] ?? 30);
        if (!empty($input['custom_duration'])) {
            $minutes = (int) $input['custom_duration'];
        }

        $endTime = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

        WorkingHoursService::setSetting('quick_pause_active', '1');
        WorkingHoursService::setSetting('quick_pause_end', $endTime);
        WorkingHoursService::setSetting('quick_pause_reason', $input['pause_reason'] ?? null);

        return redirect()->route('settings_working_hours');
    }

    public static function resumePause(LRequest $request)
    {
        WorkingHoursService::setSetting('quick_pause_active', '0');
        WorkingHoursService::setSetting('quick_pause_end', null);
        WorkingHoursService::setSetting('quick_pause_reason', null);

        return redirect()->route('settings_working_hours');
    }

    public static function getStatus(LRequest $request)
    {
        $status = WorkingHoursService::isCurrentlyOpen();
        return response()->json([
            'success' => true,
            'is_open' => $status['is_open'],
            'reason' => $status['reason'],
            'next_open' => $status['next_open'],
            'closure_type' => $status['closure_type'],
        ]);
    }
}
