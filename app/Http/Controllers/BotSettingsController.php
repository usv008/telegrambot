<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotReviews;
use App\Models\BotSettings;
use App\Models\BotSettingsCashback;
use App\Models\BotUserHistory;
use App\Models\BotUserHistoryOld;
use App\Models\Role;
use App\Models\User;
use App\Models\UsersPermissions;
use App\Models\UsersRoles;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use App\Models\BotFeedBack;

class BotSettingsController extends Controller
{

    public static function execute(LRequest $request) {

        $user = Auth::user();

        if (Gate::allows('settings')) {

            $bot_settings = BotSettings::getAllSettings();
            $cashback_settings = BotSettingsCashback::get_all_settings();
            $users = User::orderBy('id', 'asc')->get();
            $roles = Role::all();
            $users_roles = UsersRoles::all();

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
                'title' => 'Настройки',
                'page' => 'settings',
                'bot_settings' => $bot_settings,
                'cashback_settings' => $cashback_settings,
                'users' => $users,
                'roles' => $roles,
                'users_roles' => $users_roles,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);
        }
        else return redirect(404);

    }

    public static function settings_save(LRequest $request)
    {

        $input = $request->except('_token');

        BotSettings::where('settings_name', 'time_open')->update(['settings_value' => $input['time_open']]);
        BotSettings::where('settings_name', 'time_close')->update(['settings_value' => $input['time_close']]);
        BotSettings::where('settings_name', 'min_sum_order')->update(['settings_value' => $input['min_sum_order']]);
        BotSettings::where('settings_name', 'sum_delivery')->update(['settings_value' => $input['sum_delivery']]);
        BotSettings::where('settings_name', 'max_sum_order')->update(['settings_value' => $input['max_sum_order']]);
        BotSettings::where('settings_name', 'youtube_link')->update(['settings_value' => $input['youtube_link']]);
        BotSettings::where('settings_name', 'product_present_quantity_max')->update(['settings_value' => $input['product_present_quantity_max']]);

        return redirect()->route('settings');

    }

    public static function cashback_save(LRequest $request)
    {

        $input = $request->except('_token');
        $cashback_percent = $input['cashback_percent'];
        $cashback_add_user = $input['cashback_add_user'];
        $cashback_add_referal = $input['cashback_add_referal'];
        $min_order_sum = $input['min_order_sum'];

        BotSettingsCashback::where('settings_name', 'cashback_percent')->update(['settings_value' => $cashback_percent]);
        BotSettingsCashback::where('settings_name', 'cashback_add_user')->update(['settings_value' => $cashback_add_user]);
        BotSettingsCashback::where('settings_name', 'cashback_add_referal')->update(['settings_value' => $cashback_add_referal]);
        BotSettingsCashback::where('settings_name', 'min_order_sum')->update(['settings_value' => $min_order_sum]);

        return redirect()->route('settings');

    }

    public static function user_role_save(LRequest $request)
    {

        $input = $request->except('_token');
        $users_role = $input['users_role'];
//        dd($input);
        if (is_array($users_role)) {
            foreach ($users_role as $user_id => $role_id) {
                if (UsersRoles::where('user_id', $user_id)->count() == 0 && $role_id !== null && $role_id > 0) {
                    $new_user_role = new UsersRoles();
                    $new_user_role->user_id = $user_id;
                    $new_user_role->role_id = $role_id;
                    $new_user_role->save();
                }
                else {
                    if ($role_id !== null && $role_id > 0) {
                        UsersRoles::where('user_id', $user_id)->update(['role_id' => $role_id]);
                    }
                    else {
                        UsersRoles::where('user_id', $user_id)->delete();
                    }
                }
            }
        }

        return redirect()->route('settings');

    }

    public static function user_delete(LRequest $request) {

        $input = $request->except('_token');
        $id = isset($input['id']) ? $input['id'] : null;

        if (is_numeric($id) && $id > 0) {

            $data = [
                'id' => $id,
            ];
            return view('admin.settings_user_delete', $data);

        }
        else return '---';

    }


    public static function user_delete_yes(LRequest $request) {

        $input = $request->except('_token');

        if (isset($input['id']) && is_numeric($input['id']) && $input['id'] > 0 && isset($input['action']) && $input['action'] == 'user_delete') {

            UsersRoles::where('user_id', $input['id'])->delete();
            UsersPermissions::where('user_id', $input['id'])->delete();
            User::where('id', $input['id'])->delete();

        }

        return redirect()->route('settings');

    }

}

