<?php

namespace App\Http\Controllers;

use App\Models\BotAdvertisingChannel;
use App\Models\BotAdvertisingChannelHistory;
use App\Models\BotAdvertisingChannelTexts;
use App\Models\BotChatMessages;
use App\Models\BotOrder;
use App\Models\BotReviews;
use App\Models\BotSettingsTexts;
use App\Models\BotUserHistory;
use App\Models\BotUserHistoryOld;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;

use Illuminate\Support\Str;
use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;

use App\Models\BotFeedBack;

class BotAdvertisingStatController extends Controller
{

    public static function show_stat(LRequest $request) {

        if (view()->exists('admin.bot')) {

            $input = $request->except('_token');

            $last_channel_id = BotAdvertisingChannel::orderBy('id', 'desc')->first()['id'];

            $date_start = isset($input['date_start']) && $input['date_start'] !== null ? date("Y-m-d", strtotime($input['date_start'])) : date("Y-m-d", strtotime("-1 month", time()));
            $date_end = isset($input['date_end']) && $input['date_end'] !== null ? date("Y-m-d", strtotime($input['date_end'])) : date("Y-m-d", strtotime("+1 day", time()));
            $channel_id = isset($input['channel']) && $input['channel'] !== null ? $input['channel'] : $last_channel_id;

            $days_week = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'];
            $days = '';
            $users = '';
            $orders = '';

            $stats = BotAdvertisingChannelHistory::selectRaw('date(date_z) as days, count(distinct(bot_advertising_channel_history.user_id)) as users')
                ->where('bot_advertising_channel_history.date_z', '>=', $date_start)
                ->where('bot_advertising_channel_history.date_z', '<=', $date_end)
                ->where('channel_id', $channel_id)
                ->distinct('days')
                ->groupBy('days')
                ->get();

            $i = 0;
            $orders_text = [];
            foreach ($stats as $stat) {

                $i++;
                $day_week = date("N",  strtotime($stat->days));
                $day_count = '"' . date("d.m", strtotime($stat->days)) . ' (' . $days_week[$day_week-1] . ')"';
                $days .= $i == 1 ? $day_count : ', '.$day_count;
                $users .= $i == 1 ? $stat->users : ', '.$stat->users;

                $day_users = BotAdvertisingChannelHistory::where('date_z', 'like', $stat->days.'%')->where('channel_id', $channel_id)->distinct('user_id')->groupBy('user_id')->get();
                $users_arr = [];
                foreach ($day_users as $day_user) {
                    $users_arr[] = $day_user->user_id;
                }
                $orders_count = BotOrder::where('created_at', 'like', $stat->days.'%')->whereIn('user_id', $users_arr)->count();
                $t_orders = BotOrder::where('created_at', 'like', $stat->days.'%')->whereIn('user_id', $users_arr)->get();
                foreach ($t_orders as $t_order) $orders_text[] = $t_order['id'];

                $orders .= $i == 1 ? $orders_count : ', '.$orders_count;

            }

            $channel = BotAdvertisingChannel::where('id', $channel_id)->first();
            $text = BotAdvertisingChannelTexts::find($channel_id) !== null ? BotAdvertisingChannelTexts::find($channel_id)->get_text()->first() : null;
            $channels = BotAdvertisingChannel::orderBy('id', 'desc')->get();

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $data = [
                'title' => 'Статистика по рекламе',
                'page' => 'stat_advertising',
                'date_start' => $date_start,
                'date_end' => $date_end,
                'days' => $days,
                'users' => $users,
                'orders' => $orders,
                'orders_text' => $orders_text,
                'channel' => $channel,
                'text' => $text,
                'channels' => $channels,
                'messages_unreaded' => $messages_unreaded,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function show_form_add () {

        $url = Str::random(16);
        if (BotAdvertisingChannel::where('url', $url)->count() > 0)
            $url = Str::random(16);

        $data = [
            'url' => $url,
        ];

        return view('admin.modaldialog_advertising_add_content', $data);

    }

    public static function show_form_edit (LRequest $request) {

        $input = $request->except('_token');

        $channel = BotAdvertisingChannel::where('id', $input['channel_id'])->first();
        $text = BotAdvertisingChannelTexts::find($input['channel_id']) !== null ? BotAdvertisingChannelTexts::find($input['channel_id'])->get_text()->first() : null;

        $data = [
            'channel' => $channel,
            'text' => $text,
        ];

        return view('admin.modaldialog_advertising_edit_content', $data);

    }

    public static function show_form_delete (LRequest $request) {

        $input = $request->except('_token');

        $channel = BotAdvertisingChannel::where('id', $input['channel_id'])->first();

        $data = [
            'channel_id' => $input['channel_id'],
            'name' => $channel['name'],
            'url' => $channel['url'],
        ];

        return view('admin.modaldialog_advertising_delete_content', $data);

    }

    public static function stat_advertising_add (LRequest $request) {

        $input = $request->except('_token');

        if ($input['name'] !== null && $input['url'] !== null) {

            $bonus = $input['bonus_value'] !== '' && $input['bonus_value'] > 0 ? $input['bonus_value'] : 0;
            $only_new_users = isset($input['bonus_only_new_users']) && $input['bonus_only_new_users'] !== null ? 1 : 0;
            $only_exists_users = isset($input['bonus_only_exists_users']) && $input['bonus_only_exists_users'] !== null ? 1 : 0;
            $limit_in = isset($input['limit_in']) && $input['limit_in'] !== null ? 1 : 0;
            $product_present = isset($input['product_present']) && $input['product_present'] !== null ? 1 : 0;
            $product_present_variant_id = isset($input['product_present_variant_id']) && $input['product_present_variant_id'] !== null && (int)$input['product_present_variant_id'] > 0 ? $input['product_present_variant_id'] : null;

            $channel = new BotAdvertisingChannel;
            $channel->name = $input['name'];
            $channel->url = $input['url'];
            $channel->bonus = $bonus;
            $channel->product_present = $product_present;
            $channel->product_present_variant_id = $product_present_variant_id;
            $channel->limit_in = $limit_in;
            $channel->limit_in_value = $input['limit_in_value'];
            $channel->only_new_users = $only_new_users;
            $channel->only_exists_users = $only_exists_users;
            $channel->date_z = date("Y-m-d H:i:s");
            $channel->save();

            if ($bonus > 0 || $product_present > 0) {

                $text = new BotSettingsTexts;
                $text->text_command = 'Advertising';
                $text->text_name = $input['url'];
                $text->text_value_ru = $input['text_ru'];
                $text->text_value_uk = $input['text_uk'];
                $text->text_value_en = $input['text_en'];
                $text->manual = 1;
                $text->date_z = date("Y-m-d H:i:s");
                $text->save();

                if ($text) {
                    $channel_text = new BotAdvertisingChannelTexts;
                    $channel_text->id = $channel->id;
                    $channel_text->text_id = $text->id;
                    $channel_text->save();
                }

            }

            return redirect()->route('stat_advertising')->with('status', 'Рекламный канал успешно создан!');

        }
        else return redirect()->route('stat_advertising')->with('status', 'Произошла ошибка!');


    }

    public static function stat_advertising_edit (LRequest $request) {

        $input = $request->except('_token');

        if ($input['name'] !== null && $input['url'] !== null) {

            $only_new_users = isset($input['bonus_only_new_users']) && $input['bonus_only_new_users'] !== null ? 1 : 0;
            $only_exists_users = isset($input['bonus_only_exists_users']) && $input['bonus_only_exists_users'] !== null ? 1 : 0;
            $limit_in = isset($input['limit_in']) && $input['limit_in'] !== null ? 1 : 0;
            $product_present = isset($input['product_present']) && $input['product_present'] !== null ? 1 : 0;
            $product_present_variant_id = isset($input['product_present_variant_id']) && $input['product_present_variant_id'] !== null && (int)$input['product_present_variant_id'] > 0 ? $input['product_present_variant_id'] : null;

            BotAdvertisingChannel::where('id', $input['channel_id'])->
            update([
                'name' => $input['name'],
                'url' => $input['url'],
                'bonus' => $input['bonus_value'],
                'product_present' => $product_present,
                'product_present_variant_id' => $product_present_variant_id,
                'limit_in' => $limit_in,
                'limit_in_value' => $input['limit_in_value'],
                'only_new_users' => $only_new_users,
                'only_exists_users' => $only_exists_users
            ]);

            $channel = BotAdvertisingChannelTexts::find((int)$input['channel_id']);
            $text_id = $channel !== null ? $channel->text_id : null;
            if ($text_id !== null && $text_id > 0) {
                BotSettingsTexts::where('id', $text_id)
                    ->where('manual', 1)
                    ->update([
                            'text_value_ru' => $input['text_ru'],
                            'text_value_uk' => $input['text_uk'],
                            'text_value_en' => $input['text_en']
                    ]);
            }
            elseif (
                isset($input['text_ru'])
                && $input['text_ru'] !== null
                && isset($input['text_uk'])
                && $input['text_uk'] !== null
                && isset($input['text_en'])
                && $input['text_en'] !== null
            ) {
                $text = new BotSettingsTexts;
                $text->text_command = 'Advertising';
                $text->text_name = $input['url'];
                $text->text_value_ru = $input['text_ru'];
                $text->text_value_uk = $input['text_uk'];
                $text->text_value_en = $input['text_en'];
                $text->manual = 1;
                $text->date_z = date("Y-m-d H:i:s");
                $text->save();

                $channel_text = new BotAdvertisingChannelTexts;
                $channel_text->id = $input['channel_id'];
                $channel_text->text_id = $text->id;
                $channel_text->save();
            }

            return redirect()->route('stat_advertising', ['channel' => $input['channel_id']])->with('status', 'Рекламный канал '.$input['name'].' успешно изменен!');

        }
        else return redirect()->route('stat_advertising')->with('status', 'Произошла ошибка!');


    }

    public static function stat_advertising_delete (LRequest $request) {

        $input = $request->except('_token');

        if ($input['channel_id'] !== null && $input['channel_id'] > 0) {

            $channel = BotAdvertisingChannelTexts::find((int)$input['channel_id']);
            $text_id = $channel !== null ? $channel->text_id : null;
            if ($text_id !== null && $text_id > 0) {
                BotAdvertisingChannelTexts::where('id', $input['channel_id'])->delete();
                BotSettingsTexts::where('id', $text_id)->where('manual', 1)->delete();
            }
            BotAdvertisingChannel::where('id', $input['channel_id'])->delete();

            return redirect()->route('stat_advertising')->with('status', 'Рекламный канал успешно удален!');

        }
        else return redirect()->route('stat_advertising')->with('status', 'Произошла ошибка!');


    }

}
