<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotReviews;
use App\Models\BotSettings;
use App\Models\BotSettingsCashback;
use App\Models\BotUserHistory;
use App\Models\BotUserHistoryOld;
use App\Models\PrestaShop_Cashback_Categories;
use App\Models\PrestaShop_Category;
use App\Models\PrestaShop_Lang;
use App\Models\Role;
use App\Models\User;
use App\Models\UsersPermissions;
use App\Models\UsersRoles;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use App\Models\BotFeedBack;

class BotSettingsPaymentsController extends Controller
{

    public static function execute(LRequest $request) {

        $user = Auth::user();

        if (Gate::allows('settings')) {

            $bot_settings = BotSettings::getAllSettings();
            $messages_unreaded = BotChatMessages::getMessagesUnreaded();
            $data = [
                'title' => 'Настройки платежных систем',
                'page' => 'settings_payments',
                'bot_settings' => $bot_settings,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);
        }
        else return redirect(404);

    }

    public static function settings_payments_save(LRequest $request)
    {

        $input = $request->except('_token');

        BotSettings::where('settings_name', 'liqpay_token')->update(['settings_value' => $input['liqpay_token']]);
        BotSettings::where('settings_name', 'liqpay_private_key')->update(['settings_value' => $input['liqpay_private_key']]);
        BotSettings::where('settings_name', 'liqpay_public_key')->update(['settings_value' => $input['liqpay_public_key']]);

        return redirect()->route('settings_payments');

    }

}

