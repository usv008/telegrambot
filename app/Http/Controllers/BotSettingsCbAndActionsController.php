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
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use App\Models\BotFeedBack;

class BotSettingsCbAndActionsController extends Controller
{

    public static function execute(LRequest $request) {

        $user = Auth::user();

        if (Gate::allows('settings')) {

            $categories = PrestaShop_Category::join('ps_category_lang', 'ps_category_lang.id_category', 'ps_category.id_category')
//                ->where('ps_category.active', 1)
                ->where('ps_category.level_depth', '>=', 2)
                ->where('ps_category_lang.id_lang', 2)
//                ->orderBy('ps_category_lang.name')
                ->get();

            $cashback_categories = PrestaShop_Cashback_Categories::all();
            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
                'title' => 'Настройки - КБ и акции',
                'page' => 'settings_cb_and_actions',
                'categories' => $categories,
                'cashback_categories' => $cashback_categories,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);
        }
        else return redirect(404);

    }

    public static function settings_cb_and_actions_save(LRequest $request)
    {

        $input = $request->except('_token');
        $categories = $input['categories'];
        $truncate = PrestaShop_Cashback_Categories::truncate();
        foreach ($categories as $key => $value) {
            if ($value == 1) {
                $new_category = new PrestaShop_Cashback_Categories;
                $new_category->category_id = $key;
                $new_category->save();
            }
        }
//        dd($input, $truncate);

        return redirect()->route('settings_cb_and_actions');

    }

}

