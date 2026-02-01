<?php

namespace App\Http\Controllers;

//use App\BotRoute;
//use App\BotUsersPost;
use App\Models\BotCart;
use App\Models\BotOrder;
use App\Models\BotRaffleUsers;
use App\Models\BotUser;
use App\Http\Controllers\Telegram\BotPresentController;
use App\Http\Controllers\Telegram\FeedBackCommandController;
use App\Models\LongmanBotUser;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;

use Longman\TelegramBot\Telegram;

use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;

use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;

use App\Models\BotEcoUser;
//use App\BotAgrUsers;
//use App\BotCicadaUsers;

class PostsController extends Controller
{

    public function execute(LRequest $request) {

        if ($request->isMethod('post')) {

            $input = $request->except('_token');

//            dd($input);

            if ($input['action'] == 'new_post') {

                // $users = [522750680, 156668971];
                $users = [522750680];
//                $users = [522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680, 522750680];

//                $users = BotAgrUsers::where('is_bot', '0')->skip(2500)->take(500)->orderBy('id', 'asc')->get();
//                dd($users);

//                 $users = [];
//                 $users_cicada = BotCicadaUsers::where('is_bot', '0')->get();
//                 foreach ($users_cicada as $user_cicada) {
//
//                     $users[] = $user_cicada->id;
//
//                 }
//                 dd($users);


                if ($input['bot'] !== null && $input['bot'] > 0) {

                    $bot_id = $input['bot'];

                }
                else {

                    return redirect()->route('posts')->with('status', 'Пост не может быть отправлен без выбора бота!');

                }

                if ($request->hasFile('image')) {

                    $files = $request->file('image');
                    $images = '';
                    $i = 0;
                    foreach ($files as $file) {

                        $i++;
                        $file_name = md5(uniqid());
                        $ext = $file->getClientOriginalExtension();
                        $image = $file_name . '.' . $ext;
                        $images .= env('PHP_TELEGRAM_BOT_URL') . 'assets/img/posts/' . $image . ';;;';
                        $file->move(public_path() . '/assets/img/posts', $image);

                        $photo = env('PHP_TELEGRAM_BOT_URL').'assets/img/posts/'.$image;

                    }

                }
                else {

                    return redirect()->route('posts')->with('status', 'Пост не может быть отправылен без картинки!');

                }

                if ($input['text'] !== '' && $input['text'] !== null) {

                    $text = $input['text'];

                }
                else {

                    return redirect()->route('posts')->with('status', 'Пост не может быть отправлен без текста!');

                }

                $telegram_bot = BotRoute::find($bot_id);

                config(['database.connections.mysql_post.host' => $telegram_bot->mysql_host]);
                config(['database.connections.mysql_post.database' => $telegram_bot->mysql_db]);
                config(['database.connections.mysql_post.username' => $telegram_bot->mysql_user]);
                config(['database.connections.mysql_post.password' => $telegram_bot->mysql_password]);

                $inline_keyboard = new InlineKeyboard([]);

                if (isset($input['check_reserve']) && $input['check_reserve'] !== null && $input['check_reserve'] == 'check') {

                    if ($input['button_reserve_text'] !== null && $input['button_reserve_text'] !== '') {

                        $callback_data = $telegram_bot->agr == 1 ? 'reserve_start___' : 'resto___';
                        $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $input['button_reserve_text'], 'callback_data' => $callback_data]));

                    }
                    else {

                        return redirect()->route('posts')->with('status', 'Текст кнопки бронирования не может быть пустым!');

                    }

                }

                if (isset($input['check_menu']) && $input['check_menu'] !== null && $input['check_menu'] == 'check') {

                    if ($input['button_menu_text'] !== null && $input['button_menu_text'] !== '') {

                        if ($telegram_bot->agr !== 1) {

                            $callback_data = 'contacts___';
                            $inline_keyboard->addRow(new InlineKeyboardButton(['text' => $input['button_menu_text'], 'callback_data' => $callback_data]));

                        }

                    }
                    else {

                        return redirect()->route('posts')->with('status', 'Текст кнопки меню не может быть пустым!');

                    }

                }




                $telegram = new Telegram($telegram_bot->token, $telegram_bot->name);

//                $users = BotUsersPost::where('is_bot', 0)->orderBy('id')->get();
//                $users = BotUsersPost::where('is_bot', 0)->orderBy('id')->limit(10)->get();
//                dd($users);

                self::send($users, $images, $photo, $text, $inline_keyboard, $telegram_bot);
                return redirect()->route('posts')->with('status', 'Пост отправлен!');


//                foreach ($users as $user) {
//
////                    $user_id = $user->id;
//                    $user_id = $user;
//
//                    $data = ['chat_id' => $user_id];
//                    $data['photo'] = $photo;
//                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
//                    Request::sendPhoto($data);
//
//                    $data = ['chat_id' => $user_id];
//                    $data['text'] = $text;
//                    $data['reply_markup'] = $inline_keyboard;
//                    Request::sendMessage($data);
//
//                }
//
//                dd($users);

//                $image = 'photo_2019-10-22.jpg';

                $sticker = 'https://telegramadminbot.estmesta.com/assets/img/stickers/3_62c5703a8f08bcaed28e8b507f2ac3c2.webp';
                    $photo = env('PHP_TELEGRAM_BOT_URL').'assets/img/posts/'.$image;
                    // $text = $input['text'] !== '' && $input['text'] !== null ? $input['text'] : '';
                    // $text = 'На прошлой неделе сотни людей забронировали столик с помощью Bot •Есть Места• и сэкономили кучу времени! ⏱'.PHP_EOL.''.PHP_EOL.'➖ Бронь столов за 10 сек. 🍲'.PHP_EOL.'➖ 150 заведений Днепра!'.PHP_EOL.'➖ Никаких звонков! ❌📲'.PHP_EOL.''.PHP_EOL.'Жми на кнопку '.PHP_EOL.'*🔔Бронировать столик🔔* и вступай в ряды нашей желтой 🐥армии🐥'.PHP_EOL.''.PHP_EOL.'👇🏿TAP👇🏿';
                    // $text = 'Хай 👋'.PHP_EOL.'Наконец рабочая неделя подходит к концу! А это значит, что пора бронировать столик на выходные🍲🍹'.PHP_EOL.''.PHP_EOL.'На прошлой неделе сотни людей забронировали столик с помощью Bot •Есть Места• и сэкономили кучу времени! ⏱'.PHP_EOL.''.PHP_EOL.'➖ Бронь столов за 10 сек. 🍲'.PHP_EOL.'➖ 150 заведений Днепра!'.PHP_EOL.'➖ Никаких звонков! ❌📲'.PHP_EOL.''.PHP_EOL.'Жми на кнопку '.PHP_EOL.'💃Выбрать Место🕺и вступай в ряды счастливчиков! 💥'.PHP_EOL.''.PHP_EOL.'👇🏿TAP👇🏿';
                    // $text = 'На прошлой неделе сотни людей забронировали столик с помощью Bot •Есть Места• и сэкономили кучу времени! ⏱'.PHP_EOL.''.PHP_EOL.'➖ Бронь столов за 10 сек. 🍲'.PHP_EOL.'➖ 150 заведений Днепра!'.PHP_EOL.'➖ Никаких звонков! ❌📲'.PHP_EOL.''.PHP_EOL.'Жми на кнопку '.PHP_EOL.'💃Выбрать Место🕺и вступай в ряды счастливчиков! 💥'.PHP_EOL.''.PHP_EOL.'👇🏿TAP👇🏿';
//                    $text = 'На повестке дня – новое ланч-меню в CICADA, которое просто необходимо попробовать в перерыве между работой 🚀'.PHP_EOL.''.PHP_EOL.'Еще один повод увидеться в будние дни с 12 до 16, чтобы оценить новые супы, сендвичи и салаты 🥗'.PHP_EOL.''.PHP_EOL.'Смотрите меню, бронируйте столик и приходите к нам на обед 😉';
//                    $text = 'It’s Friday❗️⚡️'.PHP_EOL.PHP_EOL.'Желания как следует оторваться на выходных всё больше, как и шансов попасть в любимый бар.'.PHP_EOL.'Жми заветное *Забронировать* и не благодари. 👌😏';
//                $text_video = 'Guys, нас уже 2️⃣0️⃣0️⃣0️⃣❗️🎉'.PHP_EOL.PHP_EOL.'Мы хотим ближе познакомить тебя с самыми крутыми фичами бота ЕСТЬ МЕСТА:'.PHP_EOL.PHP_EOL.'• Умный поиск по 1500 заведениям всей Украины.'.PHP_EOL.'• Экономия времени.'.PHP_EOL.'• Больше нет необходимости звонить админу и бронировать столик.'.PHP_EOL.'• Бронируй даже когда ты: на переговорах/ не хочешь будить ребёнка/ в очень шумном месте.'.PHP_EOL.'• Вызывай Uber с помощью бота.'.PHP_EOL.'• Находи новые крутые заведения со знаком «☘️».'.PHP_EOL.'• Лёгкая отмена брони в любое время.'.PHP_EOL.PHP_EOL.'А вообще, бронируй столик уже сейчас 👇';
//                    $text = 'Под этой картинкой должен был быть текст от нашего копирайтера Ани.'.PHP_EOL.PHP_EOL.'Но она уже уехала отмечать выходные.'.PHP_EOL.'Надо было не показывать ей этот бот.'.PHP_EOL.PHP_EOL.'Хотя... Делай как Аня! Бронируй столик сейчас и едь за своим любимым коктейлем!'.PHP_EOL.PHP_EOL.'P.S. И как завещал наш копирайтер - в каждом хорошем тексте должно быть несколько emoji. Вот они:'.PHP_EOL.'🤡👨‍🦱🍔';
//                    $text = 'Новое меню в *CICADA Kitchen&Wine Bar*! Томатный орзо с бурратой, ВАО с угрём, пеппер стейк с печёным картофелем.'.PHP_EOL.'Идеально с бокалом вина! До встречи!';

//                    $inline_keyboard = new InlineKeyboard([]);
//                    $inline_keyboard->addRow(new InlineKeyboardButton([
//                        'text'          => '💃🏼 Выбрать место 🕺',
//                        'switch_inline_query_current_chat' => ''
//                    ]));

                    $keyboard = [
                        'inline_keyboard' => [
//                            [
//                                ['text' => '🔔 Забронировать столик', 'callback_data' => 'resto___']
//                            ]

                            [
                                ['text' => '🔔 Бронировать столик 🔔', 'callback_data' => 'resto___']
                            ],
                            [
                                ['text' => '🍽 Меню и 🏠 контакты', 'callback_data' => 'contacts___']
                            ]

//                            [
//                                ['text' => '🔔 Бронировать столик 🔔', 'callback_data' => 'reserve_start___']
//                            ]
//
//                            [
//                                ['text' => '🍽 Меню и 🏠 контакты', 'callback_data' => 'contacts___']
//                            ]
                        ]
                    ];
                    $inline_keyboard = json_encode($keyboard);

                    // dd($photo);
//                    $bot_api_key = '827766227:AAGItAJpbqCSNz4A89lKe8uEFqLYW99xur4';
                    $bot_api_key = '872032115:AAFDNAz1JbgQqlUN7x0xn7J6H-5wRnNWCSY';
                    $website="https://api.telegram.org/bot".$bot_api_key;

//                     $ch_video = curl_init($website . '/sendVideo');
//                     curl_setopt($ch_video, CURLOPT_HEADER, false);
//                     curl_setopt($ch_video, CURLOPT_RETURNTRANSFER, 1);
//                     curl_setopt($ch_video, CURLOPT_POST, 1);

                    $ch = curl_init($website . '/sendPhoto');
//                    $ch = curl_init($website . '/sendMessage');
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);

//                    $ch = curl_init($website . '/sendPhoto');
                    $ch2 = curl_init($website . '/sendMessage');
                    curl_setopt($ch2, CURLOPT_HEADER, false);
                    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch2, CURLOPT_POST, 1);

                    $send = 0;
                    $send_no = 0;

//                    dd($users);

                    foreach ($users as $user) {

//                        $user_id = $user->id;
                        $user_id = $user;

//                         $data_video=[
//                             'chat_id'=>$user_id,
//                             'video' => $photo,
////                             'caption'=>$text_video,
////                             'parse_mode'=>'markdown',
////                             'reply_markup'=>$inline_keyboard
//                             'reply_markup'=>Keyboard::remove(['selective' => true])
//                         ];
//                         curl_setopt($ch_video, CURLOPT_POSTFIELDS, ($data_video));
//                         curl_setopt($ch_video, CURLOPT_SSL_VERIFYPEER, false);
//                         $result_video = curl_exec($ch_video);
//
//                         $result_video = json_decode($result_video);
//                         if ($result_video->ok == 1) {
//                             $send++;
//                             echo 'video_good - '.$user_id.'; '.'<br />';
//                         }
//                         else {
//                             $send_no++;
//                             echo 'video_error - '.$user_id.'; '.'<br />';
//                         }

                        $data=[
                            'chat_id'=>$user_id,
                            'photo' => $photo,
//                            'text'=>$text,
                            'parse_mode'=>'markdown',
                            'reply_markup'=>Keyboard::remove(['selective' => true])
                        ];

                        $data2=[
                            'chat_id'=>$user_id,
                            'text'=>$text,
                            'parse_mode'=>'markdown',
                            'reply_markup'=>$inline_keyboard
                        ];

                        curl_setopt($ch, CURLOPT_POSTFIELDS, ($data));
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $result = curl_exec($ch);

                        curl_setopt($ch2, CURLOPT_POSTFIELDS, ($data2));
                        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                        $result2 = curl_exec($ch2);

                        $result = json_decode($result);
                        // dd($result);
                        if ($result->ok == 1) {
                            $send++;
                            echo 'text_good - '.$user_id.'<br />';
                        }
                        else {
                            $send_no++;
                            echo 'text_error - '.$user_id.'<br />';
                        }

                    }

//                    curl_close($ch_video);
                    curl_close($ch);
                    curl_close($ch2);

                    echo 'Отправлено: '.$send.'<br />';
                    echo 'Не отправлено: '.$send_no.'<br />';

                    exit();
                    dd($result);

                    $date_z = date("Y-m-d H:i:s");
                    $id = BotPosts::insertGetId(['image'=>$images, 'text'=>$input['text'], 'date_z'=>$date_z]);

                    if ($i > 1) {

                        $j = 0;
                        $arr_photo = ['chat_id' => $user_id];

                        $image_arr = explode(";;;", $images);
                        $media_arr = [];
                        foreach ($image_arr as $image) {
                            if ($image !== '' && $image !== null) {
                                $j++;
                                $arr_photo['photo_'.$j] = Request::encodeFile($image);
                                $text = $input['text'] !== '' && $input['text'] !== null ? $input['text'] : '';
                                $media_arr[] = new InputMediaPhoto(['media' => 'attach://photo_'.$j, 'caption' => $text]);
                            }
                        }
                        $arr_photo['media'] = $media_arr;
                        Request::sendMediaGroup($arr_photo);

                        if ($text !== '') {
                            $data = ['chat_id' => $user_id];
                            $data['text'] = $input['text'];
                            $send = Request::sendMessage($data);
                        }

                    }
                    else {

                        $image_arr = explode(";;;", $images);
                        $media_arr = [];
                        foreach ($image_arr as $image) {
                            if ($image !== '' && $image !== null) {
                                $data = ['chat_id' => $user_id];
                                $data['photo'] = $image;
                                $data['caption'] = $input['text'];
                                Request::sendPhoto($data);
                            }
                        }
                        // $data['parse_mode'] = 'markdown';
                        // $data_parent['reply_markup'] = $inline_keyboard;
                    }

                    // $users = BotUsers::all();
                    // foreach ($users as $user) {
                    //
                    //     $user_id = $user->id;
                    //
                    //     $data = ['chat_id' => $user_id];
                    //     // $data['photo'] = $image_addr;
                    //     $data['caption'] = $input['text'];
                    //     // $data['parse_mode'] = 'markdown';
                    //     // $data_parent['reply_markup'] = $inline_keyboard;
                    //
                    //     // Request::sendPhoto($data);
                    //
                    //     // $data_t = ['chat_id' => $user_id];
                    //     // $data_t['text'] = 'debug: '.$image_addr;
                    //     // Request::sendMessage($data_t);
                    //
                    // }

                    return redirect()->route('posts')->with('status', 'Пост отправлен!');

//                }
//                else {
//
//                    if ($input['text'] !== '') {
//
//                        $date_z = date("Y-m-d H:i:s");
//                        // $id = BotPosts::insertGetId(['text'=>$input['text'], 'date_z'=>$date_z]);
//
//                        $bot_api_key = '827766227:AAGItAJpbqCSNz4A89lKe8uEFqLYW99xur4';
//                        $website="https://api.telegram.org/bot".$bot_api_key;
//
//                        $ch = curl_init($website . '/sendMessage');
//                        curl_setopt($ch, CURLOPT_HEADER, false);
//                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//                        curl_setopt($ch, CURLOPT_POST, 1);
//
//                        foreach ($users as $user_id) {
//
//                            $data = ['chat_id' => $user_id];
//                            $data['text'] = $input['text'];
//
//                            curl_setopt($ch, CURLOPT_POSTFIELDS, ($data));
//                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//                            $result = curl_exec($ch);
//
//                            // $send = Request::sendMessage($data);
//
//                        }
//                        curl_close($ch);
//
//                        return redirect()->route('posts')->with('status', 'Пост отправлен!');
//
//                    }
//                    else {
//
//                        return redirect()->route('posts')->withErrors('Текст сообщения не может быть пустым!');
//
//                    }
//
//                }
//
//                dd($request);

            }

            dd($input);

        }
        else {

            if (view()->exists('admin.posts')) {

                $posts = BotPosts::all();
                $data = ['title' => 'Пост', 'posts' => $posts];

                return view('admin.posts', $data);

            }

        }

    }

    public static function test_send() {

        $date_z = date("Y-m-d H:i:s");

//        $telegram = new Telegram(env('PHP_TELEGRAM_BOT_API_KEY'), env('PHP_TELEGRAM_BOT_NAME'));

        $users = [522750680];
//        $users = [190644023];
//        $users = [522750680, 48102095];
        $users = [522750680, 190644023];

        //        $users = BotRaffleUsers::where('win', '1')->orderBy('id', 'asc')->get();
//        $users = BotCart::where('action_pizza', '1')->orderBy('id', 'asc')->get();
//        dd($users);

//        $users = BotEcoUser::where('is_bot', '0')->skip(4500)->take(500)->orderBy('id', 'asc')->get();

//        $users = BotUser::skip(5000)->take(500)->orderBy('id', 'asc')->get();

//        $users_old = BotEcoUser::where('is_bot', '0')->orderBy('id', 'asc')->get();
//        $users_new = LongmanBotUser::where('is_bot', '0')->orderBy('id', 'asc')->get();
//
//        $arr_old = [];
//        foreach ($users_old as $user_old) {
//            $arr_old[] = $user_old['id'];
//        }
//
//        $arr_new = [];
//        foreach ($users_new as $user_new) {
//            if (!in_array($user_new['id'], $arr_old)) $arr_new[] = $user_new['id'];
//        }
//
//        $users = $arr_new;
//        dd($users);


//        $users = BotEcoUser::where('is_bot', '0')->orderBy('id', 'asc')->get();

        $count = count($users);

        $send = 0;
        $send_no = 0;

        echo 'Старт: '.$date_z.'<br />';
        foreach ($users as $user) {

            $user_id = $user;
//            $user_id = $user->user_id;
//            $user_id = $user->id;
//            $user_id = $user->id_user;

//             $sticker = 'https://telegrambot.ecopizza.com.ua/assets/img/stickers/boy_conv19.webp';
//             $data_sticker = ['chat_id' => $user_id];
//             $data_sticker['sticker'] = $sticker;
//             $data_sticker['reply_markup'] = Keyboard::remove(['selective' => true]);
//             $send_sticker = Request::sendSticker($data_sticker);

//             $result = json_decode($send_sticker);
// //            dd($result);
//             if ($result->ok == 1) {
//                 $send++;
//                 echo $user_id.' - стикер отправлено!<br />';
//             }
//             else {
//                 $send_no++;
//                 echo $user_id.' - стикер не отправлено!<br />';
//             }

            $text = 'Привет!
Сделал небольшой отчет за май по работе с 21 инфекционной больницей г. Днепр. 😷
Всего, вместе с вами, мы сделали 415 пицц для медиков!!! Из них 112 куплены лично клиентами и доставлены нами в больницу врачам. 🚑
Мы получили массу благодарностей от докторов. Нам звонили, писали, передавали привет все доктора и медперсонал с выражением огромной благодарности за нашу с вами заботу! Это так приятно!!! 🤪
Всё это стало возможным благодаря вашим заказам. 🥰 Ведь с каждого заказа мы направляем  10% от прибыли в наш фонд GreenCheckin, с которого потом выделяем деньги на подобные программы. Спасибо ВАМ!!! 👍';

            $data = ['chat_id' => $user_id];

//            $img = '20200213.mp4';
//            $img = '20200303.jpg';
//            $img = '2020-05-04.jpg';
//            $photo = env('PHP_TELEGRAM_BOT_URL').'assets/img/posts/'.$img;
            $photo = 'AgACAgIAAxkBAAEXnCRe36EDmBxgFb8fbj7h8a_2YYgyKQACWK4xG_h5-UomP9fC1vg0yVrh5pIuAAMBAAMCAANtAAPjbQIAARoE';

             $data['caption'] = $text;
//            $data['text'] = $text;
            // $photo = env('PHP_TELEGRAM_BOT_URL').'assets/img/posts/20191219.gif';
            // echo $photo.'<br />';
//             $data['video'] = $photo;
             $data['photo'] = $photo;
            $data['parse_mode'] = 'html';

             $inline_keyboard = new InlineKeyboard([]);

//            $inline_keyboard->addRow(
//                new InlineKeyboardButton(['text' => '👀 Веб-камера 🎥', 'url' => 'https://open.ivideon.com/embed/v2/?server=100-vwRX5fdHzO04nKn5YQX2XJ&camera=262144&width=1920&height=1080&lang=ru&ap=&fs=&noibw='])
//            );

//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                 'text'          => '🏁 СТАРТ',
//                 'callback_data' => 'gotostart___'
//             ]));

//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                 'text'          => '🤑‍ Кешбэк',
//                 'callback_data' => 'gocashback___'
//             ]));

//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                 'text'          => '🍕 ЗАКАЗАТЬ 🍕',
//                 'callback_data' => 'gotostart___'
//             ]));

            $inline_keyboard->addRow(new InlineKeyboardButton([
                 'text'          => 'На здоровье!',
                 'callback_data' => 'gotostart___'
             ]));

//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                 'text'          => '⚙️ Выбрать язык',
//                 'callback_data' => 'change_lang___'
//             ]));

//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                 'text'          => '🥖 Продукты',
//                 'callback_data' => 'category___produkty'
//             ]));

//            $inline_keyboard->addRow(new InlineKeyboardButton([
//                 'text'          => 'Перейти',
//                 'url' => 'https://t.me/ecopizzabetabot'
//             ]));

             $data['reply_markup'] = $inline_keyboard;

//             $result_photo = Request::sendVideo($data);
            $result_photo = Request::sendPhoto($data);
//            $result_photo = Request::sendMessage($data);

            $result = json_decode($result_photo);
//            echo $photo;
//            dd($result);
            if ($result->ok == 1) {
                $send++;
                echo $user_id.' - отправлено!<br />';
//                BotPresentController::addPresentToCart($user_id);
            }
            else {
                $send_no++;
                echo $user_id.' - не отправлено!<br />';
            }

//            $result_text = Request::sendMessage($data);

        }

        $date_z = date("Y-m-d H:i:s");
        echo 'Стоп: '.$date_z;
        echo '<br />Всего пользователей в очереди: '.$count.'<br />Отправлено: '.$send.'<br />Не отправлено: '.$send_no;

    }

//    public function send(LRequest $request, PhpTelegramBotContract $telegram_bot) {
    public function send($users, $images, $photo, $text, $inline_keyboard, $telegram_bot) {

        $users_num = count($users);

        $date_z = date("Y-m-d H:i:s");
        $id = BotPosts::insertGetId(['image'=>$images, 'text'=>$text, 'num' => $users_num, 'bot_id'=>$telegram_bot->id, 'date_go'=>$date_z, 'date_start'=>$date_z]);

        $users_arr = '';
        $send = 0;
        $send_no = 0;
        foreach ($users as $user) {

//            $user_id = $user->id;
            $user_id = $user;

            $data = ['chat_id' => $user_id];
            $data['photo'] = $photo;
            $data['reply_markup'] = Keyboard::remove(['selective' => true]);
            $result_photo = Request::sendPhoto($data);

            $send_check = false;
            if ($result_photo->ok == true) {

                $send_check = true;
                $users_arr .= $user_id.':photo:'.$result_photo->result->message_id;
                $data = ['chat_id' => $user_id];
                $data['text'] = $text;
                $data['reply_markup'] = $inline_keyboard;
                $result_text = Request::sendMessage($data);

                if ($result_text->ok == true) {
                    $users_arr .= ':text:'.$result_text->result->message_id.';;;';
                }
                else {
                    $send_check = false;
                    $users_arr .= ':text:null;;;';
                }

            }
            else {
                $users_arr .= $user_id.':photo:null:text:null;;;';
            }

            if ($send_check == true) $send++;
            else $send_no++;

            $date_z = date("Y-m-d H:i:s");
            BotPosts::where('id', $id)->update(['users'=>$users_arr,'send'=>$send,'send_no'=>$send_no,'date_end'=>$date_z]);

        }

//        dd($users_arr);

    }

    public static function test_feedback() {

        $day = date("Y-m-d", mktime(0,0,0, date("m"),date("d")-1, date("Y")));

        echo $day.'<br />';
        $users = [522750680];
        $users = BotOrder::where('created_at', 'like', $day.'%')->groupBy('user_id')->get(['user_id']);
        dd($users);

        $date_z = date("Y-m-d H:i:s");
        $count = count($users);

        $send = 0;
        $send_no = 0;

        echo 'Старт: '.$date_z.'<br />';
        echo 'Пользователей в очереди: '.$count.'<br /><br />';
        $i = 0;
        foreach ($users as $user) {

            $i++;
            $user_id = $user->user_id;
            $send = FeedBackCommandController::execute($user_id);
            if ($send == true) echo $i.') '.$user_id.' - ok<br />';
            else echo $i.') '.$user_id.' - error<br />';

        }
        echo '<br />Стоп: '.date("Y-m-d H:i:s").'<br />';

    }

    public static function sendMessageToUser(LRequest $request) {

        if ($request->isMethod('post')) {

            $input = $request->except('_token');

            $data = ['chat_id' => $input['user_id']];
            $data['text'] = $input['text'];
            $data['parse_mode'] = 'html';
//        $data['reply_markup'] = $inline_keyboard;
            $result = Request::sendMessage($data);
            return $result->getOk() == true ? 'Сообщение отправлено' : 'Сообщение не отправлено!';

        }

    }

}
