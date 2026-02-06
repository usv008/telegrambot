<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotEcoUser;
use App\Models\BotOrder;
use App\Models\BotSendingMessages;
use App\Models\BotSendingMessagesHistory;
use App\Models\BotSendingMessagesReactions;
use App\Models\BotSendingMessagesReactionsAnswers;
use App\Models\BotSendingMessagesReactionsHistory;
use App\Models\BotSettingsCities;
use App\Models\BotSettingsSticker;
use App\Http\Controllers\Telegram\BotUserSettingsController;
use App\Jobs\ProcessSendingMessages;
use App\Models\LongmanBotUser;
use App\Models\PrestaShop_Product;
use App\Models\PrestaShop_Product_Attribute;
use App\Models\PrestaShop_Product_Attribute_Combination;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\Log;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Request;

use DataTables;
use App\Models\BotUser;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

class BotMailingController extends Controller
{

    public static function execute() {

        if (view()->exists('admin.bot')) {

            $stickers = BotSettingsSticker::orderBy('sticker_command', 'asc')->get();
            $cities = BotSettingsCities::all();
            $users = BotUser::all();

            $messages_unreaded = BotChatMessages::getMessagesUnreaded();

            $product_attributes = PrestaShop_Product_Attribute::all();
            $action_products = PrestaShop_Product::join('ps_product_lang', 'ps_product_lang.id_product', 'ps_product.id_product')
                ->where('ps_product.id_category_default', 41)
                ->where('ps_product_lang.id_lang', 2)
                ->get();
            foreach ($action_products as $product) {
                $product_attribute = $product_attributes->where('id_product', $product->id_product)->where('default_on', 1)->first();
                if (!$product_attribute)
                    $product_attribute = $product_attributes->where('id_product', $product->id_product)->first();
                $product->attribute = $product_attribute;
                $product->price = $product_attribute ? $product_attribute->price : 0;
                $product->variant = null;
                $id_product_attribute = null;
                if ($product_attribute) {
                    $id_product_attribute = $product_attribute->id_product_attribute;
                    $variant = PrestaShop_Product_Attribute_Combination::join('ps_attribute_lang', 'ps_attribute_lang.id_attribute', 'ps_product_attribute_combination.id_attribute')
                        ->where('ps_product_attribute_combination.id_product_attribute', $id_product_attribute)
                        ->where('ps_attribute_lang.id_lang', 2)
                        ->first()
                        ->name;
                    $product->variant = $variant;
                }
                $product->id_product_attribute = $id_product_attribute;
            }

            $data = [
//                'data' => $users,
                'title' => 'Рассылка',
                'page' => 'mailing',
                'stickers' => $stickers,
                'cities' => $cities,
                'users' => $users,
                'messages_unreaded' => $messages_unreaded,
                'action_products' => $action_products,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function posts_list() {

        $data = BotSendingMessages::all();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('photo', function($row){
                return $row->photo !== null && $row->photo !== '' ? '<img src="'.$row->photo.'" width="150" />' : '';
            })
            ->addColumn('sticker', function($row){
                return $row->sticker !== null && $row->sticker !== '' ? '<img src="'.$row->sticker.'" height="100" />' : '';
            })
            ->addColumn('reactions', function($row){
                $text_reactions = '';
                $reactions = BotSendingMessagesReactions::getReactionsByPost($row->id);
                foreach ($reactions as $reaction) {
                    $text_reactions .= $reaction->text_uk.' - '.$reaction->clicks.'<br />';
                }
                return (string)$text_reactions;
            })
            ->addColumn('button1', function($row){
                return $row->button_text.' ('.$row->button1.')';
            })
            ->addColumn('button2', function($row){
                return $row->button_text2.' ('.$row->button2.')';
            })
            ->addColumn('send_yes', function($row){
                return $row->total == 0 ? $row->send_yes.' (0%)' : $row->send_yes.' ('.bcmul(bcdiv($row->send_yes, $row->total, 10), 100, 4).'%)';
            })
            ->addColumn('send_no', function($row){
                return $row->total == 0 ? $row->send_no.' (0%)' : $row->send_no.' ('.bcmul(bcdiv($row->send_no, $row->total, 10), 100, 4).'%)';
            })
            ->addColumn('ctr', function($row){
                if ($row->send_yes !== 0) $result = bcmul(bcdiv(bcadd($row->button1, $row->button2, 10), $row->send_yes, 10), 100, 4).'%';
                else $result = '0%';
                return $result;
            })
            ->addColumn('repeat_post', function($row){
                return '<button class="btn btn-primary button_repeat" id="repeat_post___'.$row->id.'" data-text_uk="'.$row->text_uk.'">Повтор</button>';
            })
            ->addColumn('delete_post', function($row){
                return $row->only_us == 1 ? '<button class="btn btn-danger button_delete" id="delete_post___'.$row->id.'">Удалить</button>' : '';
//                return $row->sticker !== null && $row->sticker !== '' ? '<img src="'.$row->sticker.'" height="100" />' : '';
            })
            ->rawColumns(['photo', 'sticker', 'reactions', 'button1', 'button2', 'repeat_post', 'delete_post'])
            ->make(true);
    }

    public static function add_mailing(LRequest $request) {

        if ($request->isMethod('post')) {

            $only_us = 0;
            $url = null;
            if ($request->hasFile('image')) {

                $file = $request->file('image');
                $file_name = $file->getClientOriginalName();
                $m = $file->move(public_path().'/assets/img/posts', $file_name);
                if ($m) {
                    $url = url('/assets/img/posts/'.$m->getFilename());
                }

            }

            $input = $request->except('_token');

//            dd($input);

            if ($input['send_users_ids'] !== null && $input['send_users_ids'] !== '') {

                $only_us = 1;
                $arr = explode(PHP_EOL, $input['send_users_ids']);

                $users = [];
                // перебираем значения и добавляем только числовую часть в массив $users
                foreach ($arr as $key => $value) {
                    $users[] = (int)$value;
                }

            }
            elseif (isset($input['only_us']) && $input['only_us'] == 1) {

                $only_us = 1;
                $users = [
                    '522750680',    // Я
                    '190644023',    // Сережа
                    '329595353',    // Юра
                    '578694999',    // Лиля
//                    '365626203'     // Алиса
                ];

            }
            else {

                $users = [];

                // Если город не выбран идем в таблицу старого бота, берем оттуда пользователей, потом в таблицу нового бота, аналогично
                if ($input['city'] == 0) {
//                    $old_bot_users = BotEcoUser::where('is_bot', '0')->orderBy('id', 'asc')->get();
//                    foreach ($old_bot_users as $old_bot_user) {
//                        $users[] = $old_bot_user['id'];
//                    }
//                    $users_new = LongmanBotUser::where('is_bot', '0')->orderBy('id', 'asc')->get();
//                    foreach ($users_new as $user_new) {
//                        if (!in_array($user_new['id'], $users)) $users[] = $user_new['id'];
//                    }
                    $bot_users = BotUser::where('active', 1)->orderBy('id', 'asc')->get();
                    foreach ($bot_users as $bot_user) {
                        $users[] = $bot_user->user_id;
                    }
                }
                // Если выбран Днепр, берем всех пользователей из BotUser, где выбран Днепр или не выбран город вообще
                elseif ($input['city'] == 6) {
                    $bot_users = BotUser::where('city_id', $input['city'])->where('active', 1)->orWhere('city_id', null)->get();
                    foreach ($bot_users as $bot_user) {
                        $users[] = $bot_user->user_id;
                    }
                }
                // Если выбран другой город, то берем пользователей, которые выбрали именно этот город
                else {
                    $bot_users = BotUser::where('city_id', $input['city'])->where('active', 1)->get();
                    foreach ($bot_users as $bot_user) {
                        $users[] = $bot_user->user_id;
                    }
                }

            }

            $total = count($users);

            $post = new BotSendingMessages;
            $post->sticker = $input['sticker'];
            $post->text = $input['text_uk'];
            $post->text_ru = '';
            $post->text_uk = $input['text_uk'];
            $post->text_en = $input['text_en'];
            $post->photo = $url;
            $post->button_text = $input['button_text_uk'];
            $post->button_text_ru = '';
            $post->button_text_uk = $input['button_text_uk'];
            $post->button_text_en = $input['button_text_en'];
            $post->button_data = $input['button_data'];
            $post->button_text2 = $input['button_text2_uk'];
            $post->button_text2_ru = '';
            $post->button_text2_uk = $input['button_text2_uk'];
            $post->button_text2_en = $input['button_text2_en'];
            $post->button_data2 = $input['button_data2'];
            $post->only_us = $only_us;
            $post->total = $total;
            $post->date_z = date("Y-m-d H:i:s");
            $post->save();

            $post_id = $post->id;

            $reaction_max_symbols = 0;
            $reactions = [];
            if (isset($input['reaction_text_uk']) && is_array($input['reaction_text_uk']) && count($input['reaction_text_uk']) > 0) {
                foreach ($input['reaction_text_uk'] as $key => $reaction) {
                    if ($reaction !== null && $reaction !== '') {
//                        if ($reaction_max_symbols < mb_strlen($input['reaction_text_ru'][$key])) $reaction_max_symbols = mb_strlen($input['reaction_text_ru'][$key]);
                        if ($reaction_max_symbols < mb_strlen($input['reaction_text_uk'][$key])) $reaction_max_symbols = mb_strlen($input['reaction_text_uk'][$key]);
                        if ($reaction_max_symbols < mb_strlen($input['reaction_text_en'][$key])) $reaction_max_symbols = mb_strlen($input['reaction_text_en'][$key]);

                        $new_reaction = BotSendingMessagesReactions::addReaction($post_id, '', $input['reaction_text_uk'][$key], $input['reaction_text_en'][$key]);

//                        $reactions[$key]['reaction_text_ru'] = $input['reaction_text_ru'][$key];
                        $reactions[$key]['reaction_text_uk'] = $input['reaction_text_uk'][$key];
                        $reactions[$key]['reaction_text_en'] = $input['reaction_text_en'][$key];
                        $reactions[$key]['reaction_id'] = $new_reaction->id;
                    }
                }
                if (isset($input['reaction_answer_uk']) && $input['reaction_answer_uk'] !== '') {
                    $new_reaction_answer = BotSendingMessagesReactionsAnswers::addAnswer($post_id, '', $input['reaction_answer_uk'], $input['reaction_answer_en']);
                }
            }

            $settings_users = BotUserSettingsController::getUsers();

            $error = false;
            foreach ($users as $user) {

                $user_lang = $settings_users->where('user_id', $user)->first();
                $lang = isset($user_lang->lang) && $user_lang->lang !== null && $user_lang->lang !== '' ? $user_lang->lang : 'uk';
                if ($lang == 'ru') $lang = 'uk';
                $inline_keyboard = new InlineKeyboard([]);
                // реакции
                if (isset($input['reaction_text_'.$lang]) && is_array($input['reaction_text_'.$lang]) && count($input['reaction_text_'.$lang]) > 0 && $reaction_max_symbols > 0) {
                    $cols = 1;
                    if ($reaction_max_symbols == 1) $cols = 4;
                    if ($reaction_max_symbols > 1 && $reaction_max_symbols <= 5) $cols = 3;
                    if ($reaction_max_symbols > 5 && $reaction_max_symbols <= 10) $cols = 2;

                    $i = 0;
                    $buttons_arr = [];
                    foreach ($reactions as $reaction) {
                        $i++;
                        $buttons_arr[$i] = new InlineKeyboardButton(['text' => $reaction['reaction_text_'.$lang], 'callback_data' => 'sending_messages_reactions___'.$post_id.'___'.$reaction['reaction_id']]);
                        if ($i == $cols) {
                            if ($cols == 1) $inline_keyboard->addRow($buttons_arr[1]);
                            if ($cols == 2) $inline_keyboard->addRow($buttons_arr[1], $buttons_arr[2]);
                            if ($cols == 3) $inline_keyboard->addRow($buttons_arr[1], $buttons_arr[2], $buttons_arr[3]);
                            if ($cols == 4) $inline_keyboard->addRow($buttons_arr[1], $buttons_arr[2], $buttons_arr[3], $buttons_arr[4]);
                            $i = 0;
                            $buttons_arr = [];
                        }
                    }
                    if ($i > 0) {
                        if ($i == 1) $inline_keyboard->addRow($buttons_arr[1]);
                        if ($i == 2) $inline_keyboard->addRow($buttons_arr[1], $buttons_arr[2]);
                        if ($i == 3) $inline_keyboard->addRow($buttons_arr[1], $buttons_arr[2], $buttons_arr[3]);
                    }
                }
                // кнопки
                if ($input['button_text_'.$lang] !== null && $input['button_text_'.$lang] !== '') {
                    if ($input['button_data'] == 'addstat_addtocartproductfrommailing___') {
                        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $input['button_text_'.$lang], 'callback_data' => $input['button_data'].$input['variant_id'].'___1___'.$post_id]));
                    }
                    else {
                        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $input['button_text_'.$lang], 'callback_data' => $input['button_data'].'1___'.$post_id]));
                    }

                    if ($input['button_text2_'.$lang] !== null && $input['button_text2_'.$lang] !== '') {
                        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $input['button_text2_'.$lang], 'callback_data' => $input['button_data2'].'2___'.$post_id]));
                    }
                }

                $data = ['post_id' => $post_id, 'user_id' => $user, 'sticker' => $input['sticker'], 'text' => $input['text_'.$lang], 'image' => $url, 'inline_keyboard' => $inline_keyboard];
//                dd($data);
                $job = ProcessSendingMessages::dispatch($data);
                if (!$job) $error = true;

            }

//            Log::info("!!!BotMailingController!!! user_id: $user_id; text: ".$input['text']."; image: $url; ");

//            $job = ProcessSendingMessages::dispatch($data);

//            if ($job) return redirect()->route('mailing')->with('status', 'Пост поставлен в очередь отправки!');
//            else return redirect()->route('mailing')->with('error', 'Произошла ошибка');

            if ($error == false) return 'Пост поставлен в очередь отправки!';
            else return 'Произошла ошибка';

        }

    }

    public static function show_modal_order_delete(LRequest $request) {

        $input = $request->except('_token');

        $delete_and_id = explode("___", $input['id']) ? explode("___", $input['id']) : null;

        $id = isset($delete_and_id[1]) ? $delete_and_id[1] : null;

        if (is_numeric($id) && $id > 0) {

            $data = [
                'id' => $id,
            ];
            return view('admin.post_delete', $data);

        }
        else return '---';

    }

    public static function post_delete_yes(LRequest $request) {

        $input = $request->except('_token');

        if (is_numeric($input['id']) && $input['id'] > 0) {
            BotSendingMessagesReactionsHistory::where('post_id', $input['id'])->delete();
            BotSendingMessagesReactionsAnswers::where('post_id', $input['id'])->delete();
            BotSendingMessagesReactions::where('post_id', $input['id'])->delete();
            BotSendingMessagesHistory::where('post_id', $input['id'])->delete();
            BotSendingMessages::where('id', $input['id'])->delete();
        }

        return redirect()->route('mailing');

    }

}
