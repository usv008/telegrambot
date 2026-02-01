<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class ArchiController extends Controller
{

    const h_start = 10;
    const h_end = 22;

    public static function get_all_point() {

        $website = 'http://643106e9883f.sn.mynetname.net:5858';
        $command = '/getallpoint';
        $data = '';

        if( $curl = curl_init($website.$command.$data) ) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);

            if (stripos($result, 'RESULT=OK') !== false) {
                $res = str_replace('RESULT=OK', '', $result);
                if (stripos($res, 'DATA=') !== false) {
                    $res = str_replace('DATA=', '', $res);
                    $res = trim($res);
                }

            } else $res = null;
            $res = json_decode($res, true);

        }
        else $res = null;

        return $res;


    }

    public static function get_order_data($id) {

        $website = 'http://643106e9883f.sn.mynetname.net:5858';
        $command = '/getordercontentcur';
        $command = '/getorderdata';
        $data = '?OrderId='.$id;

        if( $curl = curl_init($website.$command.$data) ) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);

            if (stripos($result, 'RESULT=OK') !== false) {
                $res = str_replace('RESULT=OK', '', $result);
                if (stripos($res, 'DATA=') !== false) {
                    $res = str_replace('DATA=', '', $res);
                    $res = trim($res);
                }

            } else $res = null;
            $res = json_decode($res, true);

        }
        else $res = null;

        return $res;

    }

    public static function get_order_content($id) {

        $website = 'http://643106e9883f.sn.mynetname.net:5858';
        $command = '/getordercontent';
        $data = '?OrderId='.$id;

        if( $curl = curl_init($website.$command.$data) ) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);

            if (stripos($result, 'RESULT=OK') !== false) {
                $res = str_replace('RESULT=OK', '', $result);
                if (stripos($res, 'DATA=') !== false) {
                    $res = str_replace('DATA=', '', $res);
                    $res = trim($res);
                }

            } else $res = null;
            $res = json_decode($res, true);

        }
        else $res = null;

        return $res;

    }

    public static function get_orders($status, $point_id) {

        $website = 'http://643106e9883f.sn.mynetname.net:5858';
        $command = '/getorderbypoint';
        $data = '?pointid='.$point_id.'&statusid='.$status;

        if( $curl = curl_init($website.$command.$data) ) {
            //curl_setopt($curl, CURLOPT_URL, 'http://193.19.242.38:5858/getordercontent?orderid=75058');
            // curl_setopt($curl, CURLOPT_HEADER,true);
            // curl_setopt($curl, CURLOPT_PORT, 5858);
            // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Accept: application/json'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // curl_setopt($curl, CURLOPT_BINARYTRANSFER,true);
            // curl_setopt($curl, CURLINFO_HEADER_OUT,true);
            // curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36');
            $result = curl_exec($curl);
            curl_close($curl);

            // $res = json_encode($result);
            if (stripos($result, 'RESULT=OK') !== false) {
                $res = str_replace('RESULT=OK', '', $result);
                //echo 'Delete RESULT=OK<br />';
                if (stripos($res, 'DATA=') !== false) {
                    $res = str_replace('DATA=', '', $res);
                    //echo 'Delete DATA=<br />';
                    $res = trim($res);
                }

            } else $res = null;
            // $res = json_encode($res);
            $res = json_decode($res, true);

        }
        else $res = null;

        return $res;

    }

    public static function count_pizza($order_id) {

        $order_data = self::get_order_content($order_id);
        $pizza_count = 0;
        foreach ($order_data as $data) {
            if (stripos($data['NAME'], 'Еко&Піца') !== false) $pizza_count += $data['QUANT'];
            elseif ($data['NAME'] == 'Набір "Вечеринка". Флоренція, Гавайска, Фелічіта, Неаполь, Вегетарианськая') $pizza_count += 5;
            elseif ($data['NAME'] == 'Набір "Пiд футбол". 4 м"яса, Селянська, Баварiя') $pizza_count += 3;
            elseif ($data['NAME'] == 'Набір "Набір "Девичник". 4 сыра, Афіна, Бьянка, Гавайська') $pizza_count += 4;
            elseif ($data['NAME'] == 'Набір "Дитяче свято". Віденська, Гавайска , Парма.') $pizza_count += 3;
        }

        return $pizza_count;

    }

    public static function get_count_mktime($i, $h, $min1, $min2, $res, $status_ins) {

        $count = 0;

        foreach ($res as $r) {
            $order_datetime = $r['FormatORDERDATETIME'];
            $h_ins = $min2 == 0 ? $h + 1 : $h;
            $mktime1 = mktime($h, $min1, 0, date('m'), date('d'), date('Y'));
            $mktime2 = mktime($h_ins, $min2, 0, date('m'), date('d'), date('Y'));
            if (
                strtotime($order_datetime) >= $mktime1 &&
                strtotime($order_datetime) < $mktime2 &&
                date('Y-m-d', strtotime($order_datetime)) == date('Y-m-d')
            ) {
                $count += self::count_pizza($r['ID']);
            }
        }
        $status_ins .= $i == 1 ? ''.$count.'' : ', '.$count.'';
        $i++;

        return [$i, $status_ins];

    }

    public static function get_status_arr($res) {

        $status_ins = '[';
        $i = 0;
        for ($h = self::h_start; $h <= self::h_end; $h++) {

            $i++;

            list($i, $status_ins) = self::get_count_mktime($i, $h, 0, 15, $res, $status_ins);
            list($i, $status_ins) = self::get_count_mktime($i, $h, 15, 30, $res, $status_ins);
            list($i, $status_ins) = self::get_count_mktime($i, $h, 30, 45, $res, $status_ins);
            list($i, $status_ins) = self::get_count_mktime($i, $h, 45, 0, $res, $status_ins);

        }
        $status_ins .= ']';

        return $status_ins;

    }

    public static function get_hours() {


        $hours = [];
        for ($h = self::h_start; $h <= self::h_end; $h++) {

            $hours[] = $h.':00';
            $hours[] = $h.':15';
            $hours[] = $h.':30';
            $hours[] = $h.':45';

        }

        return $hours;

    }


    public static function test2() {

        $telegram = new Telegram(env('PHP_TELEGRAM_BOT_API_KEY'), env('PHP_TELEGRAM_BOT_NAME'));

        $user_id = 749088898;
        $message_id = 714886;

//        $user_id = 522750680;
//        $message_id = 715671;

        $data = ['chat_id' => $user_id];
        $data ['message_id'] = $message_id;
        $result = Request::deleteMessage($data);

        $result = json_decode($result);

        dd($result);

    }

    public static function execute(LRequest $request) {

        $input = $request->except('_token');
        $point_id = isset($input['point']) && $input['point'] !== null && ($input['point'] == 5 || $input['point'] == 10) ? $input['point'] : 5;
//        dd($input);

        if (view()->exists('admin.archi')) {

            $hours_table = self::get_hours();

            $point_id = 5;
            $res5_4 = self::get_orders(4, $point_id);
            $res5_6 = self::get_orders(6, $point_id);
            $res5_7 = self::get_orders(7, $point_id);
            $res5_3 = self::get_orders(3, $point_id);

            $point_id = 10;
            $res10_4 = self::get_orders(4, $point_id);
            $res10_6 = self::get_orders(6, $point_id);
            $res10_7 = self::get_orders(7, $point_id);
            $res10_3 = self::get_orders(3, $point_id);

            $data = [
                'title' => 'ArchiDelivery',
                'hours_table' => $hours_table,
                'res5_4' => $res5_4,
                'res5_6' => $res5_6,
                'res5_7' => $res5_7,
                'res5_3' => $res5_3,
                'res10_4' => $res10_4,
                'res10_6' => $res10_6,
                'res10_7' => $res10_7,
                'res10_3' => $res10_3,
                'input' => $input,
            ];

            return view('admin.archi', $data);

        }

    }

    public static function test() {

//        dd(self::get_all_point());

//        $res2 = self::get_orders(2);
        $res4 = self::get_orders(4, 5);
        $res6 = self::get_orders(6, 5 );
        $res7 = self::get_orders(7, 5 );
        $res3 = self::get_orders(3, 5);

        $hours_table = self::get_hours();



//        $hours = '[';
//        $hours_arr = self::get_hours();
//        $i = 0;
//        foreach ($hours_arr as $hour) {
//            $hours .= $i == 1 ? '"'.$hour.'"' : ', "'.$hour.'"';
//        }
//        $hours .= ']';

//        $status2_ins = self::get_status_arr($res2);
//        $status4_ins = self::get_status_arr($res4);
//        $status6_ins = self::get_status_arr($res6);
//        $status7_ins = self::get_status_arr($res7);

        echo '
        <!doctype html>
        <html lang="en">
          <head>
            <meta charset="utf-8">
            <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no, user-scalable=no"> -->
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

            <meta name="description" content="">
            <meta name="author" content="">
            <link rel="icon" href="view/img/favicon.png">

            <title>stat</title>

            <!-- Bootstrap core CSS -->
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

            <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

            <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>


          </head>
          <body style="width:99%;">

          ';

//        echo '
//        <div style="width: 85%; margin: 0 auto; text-align: center;"><canvas id="myChart" width="800" height="400"></canvas></div>
//
//        <script>
//        var ctx = document.getElementById(\'myChart\').getContext(\'2d\');
//        var chart = new Chart(ctx, {
//            // The type of chart we want to create
//            type: \'bar\',
//
//            // The data for our dataset
//            data: {
//                labels: '.$hours.',
//                datasets: [
////                {
////                    label: "принятые",
////                    backgroundColor: \'rgb(66,139,202)\',
////                    borderColor: \'rgb(66,139,202)\',
////                    data: '.$status2_ins.',
////                },
//                {
//                    label: "в работе",
//                    backgroundColor: \'rgb(255, 99, 71)\',
//                    borderColor: \'rgb(255, 99, 71)\',
//                    data: '.$status4_ins.',
//                },
//                {
//                    label: "доставляются",
//                    backgroundColor: \'rgb(106, 90, 205)\',
//                    borderColor: \'rgb(106, 90, 205)\',
//                    data: '.$status6_ins.',
//                },
//                {
//                    label: "исполненные",
//                    backgroundColor: \'rgb(92,184,92)\',
//                    borderColor: \'rgb(92,184,92)\',
//                    data: '.$status7_ins.',
//                }
//            ]
//            },
//            options: {
//                legend: {
//                    display: true,
//                    labels: {
//                        fontColor: \'rgb(66,139,202)\'
//                    }
//                }
//            }
//
//        });
//
//        </script>
//
//        ';


//        dd($res4);

        echo '<div class="container" style="width: 100%; margin: 0 auto; text-align: center;">';

        foreach ($hours_table as $hour_table) {

            echo '<div class="row border-bottom">';
            echo '<div class="col-sm-2 border border-dark rounded text-center m-1 p-1 align-self-center">'.$hour_table.'</div>';

            echo '<div class="col-sm-auto text-left">';

            $count_orders_all = 0;
            $count_pizza_all = 0;

            list($count_orders, $count_pizza) = self::get_orders_table($res7, $hour_table, 7);
            $count_orders_all += $count_orders;
            $count_pizza_all += $count_pizza;

            list($count_orders, $count_pizza) = self::get_orders_table($res6, $hour_table, 6);
            $count_orders_all += $count_orders;
            $count_pizza_all += $count_pizza;

            list($count_orders, $count_pizza) = self::get_orders_table($res4, $hour_table, 4);
            $count_orders_all += $count_orders;
            $count_pizza_all += $count_pizza;

            echo '</div>';

            $bg_orders_ins = $count_orders_all >= 6 ? ' bg-danger text-white' : '';
            $bg_pizza_ins = $count_pizza_all >= 15 ? ' bg-danger text-white' : '';
            echo '<div class="col">';
            echo '<div class="float-right">';
            echo '<div class="d-inline-block rounded text-center m-1 p-1 align-self-center font-weight-bold'.$bg_orders_ins.'" style="width: 70px;">'.$count_orders_all.' 📦</div>';
            echo '<div class="d-inline-block rounded text-center m-1 p-1 align-self-center font-weight-bold'.$bg_pizza_ins.'" style="width: 70px;">'.$count_pizza_all.' 🍕</div>';
            echo '</div>';
            echo '</div>';

            echo '</div>';

//            echo $hour_table.': доставка заказы '.$count_orders.'; (пицца '.$count_pizza.'шт)'.'; самовывоз заказов '.$count_orders2.'; (пицц '.$count_pizza2.'шт)<br />';

        }
        echo '</div>';



        echo '


<!-- Large modal
<button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg">Large modal</button>
<button type="button" class="order_button btn btn-primary" id="222" data-toggle="modal" data-target=".bd-example-modal-lg">Test</button>
-->

<div class="modal fade bd-example-modal-lg" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" id="modal_header">
        <h5 class="modal-title" id="modal_title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="modal_body">
        <p>Modal body text goes here.</p>
      </div>
      <div class="modal-footer" id="modal_footer">
        <button type="button" class="btn btn-primary">Save changes</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

        <script type="text/javascript">

$(document).ready(function(){

        $(".order_button").click(function(e) {

            $("#modal_title").html("Заказ №"+$(this).attr("id"));
            $("#modal_body").html("");
            $("#modal_footer").html(\'<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>\');
            $("#exampleModalCenter").modal("show");

//            $.ajax({
//                type: "POST",
//                url: "https://telegramadminbotdebug.estmesta.com/admin/admins/showmodaldialog",
//                data: "_token=uqe5idwRAFqBZLPgnpYwF41ODl8LnzRnmMk7hYlE&action=admin_add",
//                cache: false
//            }).done(function(deldata) {
//                $("#modal_dialog").html(deldata);
//                $("#exampleModalCenter").modal("show");
//            }).fail(function() {
//                $("#modal_body").html("Произошла ошибка");
//                $("#exampleModalCenter").modal("show");
//            });
        });

});
</script>

        </body>
        </html>
        ';

//        $points = self::get_all_point();
//        dd($points);
//        dd($res4);
//        dd($res3);

//        $order_data = self::get_order_content(158194);
//        $order_data = self::get_order_data(158194);
//        dd($order_data);
//        $pizza_count = 0;
//        foreach ($order_data as $data) {
//            if (stripos($data['NAME'], 'Еко&Піца') !== false) $pizza_count += $data['QUANT'];
//        }
//        $order_data = self::get_order_data(157507);
//        $order_data = self::get_order_data(157596);
//        dd($order_data);

//        dd($res7);

    }

    public static function get_orders_table ($orders, $hour_table, $type) {

        $count_orders = 0;
        $count_pizza = 0;
        $count_orders2 = 0;
        $count_pizza2 = 0;
        foreach ($orders as $order) {

            $order_datetime = $order['FormatORDERDATETIME'];
            $mktime1 = mktime(date('H', strtotime($hour_table)), date('i', strtotime($hour_table)), 0, date('m'), date('d'), date('Y'));
            $mktime2 = mktime(date('H', strtotime($hour_table)), date('i', strtotime($hour_table))+15, 0, date('m'), date('d'), date('Y'));
            if (
                strtotime($order_datetime) >= $mktime1 &&
                strtotime($order_datetime) < $mktime2 &&
                date('Y-m-d', strtotime($order_datetime)) == date('Y-m-d')
            ) {

                $ins = '';
                if ($order['ORDERTYPE'] == 2) {

//                    $ins = '🏃‍♂️ ';
                    $ins = '⛄️ ';
                    $count_orders2++;
                    $count_pizza2 += self::count_pizza($order['ID']);

                }
                else {

                    $count_orders++;
                    $count_pizza += self::count_pizza($order['ID']);

                }

                $border_ins = '';
                $delivery_ins = '';

                $bg_ins = '';
                if ($type == 7) { $border_ins = 'border-success'; $bg_ins = ' btn-success text-white'; }
                elseif ($type == 6) { $border_ins = 'border-warning'; $delivery_ins = '🚗 '; $bg_ins = ' btn-warning text-black'; }
                elseif ($type == 4) { $border_ins = 'border-primary'; $bg_ins = ' btn-primary text-white'; }
                else $border_ins = 'border';
//                echo '<div class="col-sm-1 border '.$border_ins.' rounded text-center m-1 p-1 align-self-center">'.$delivery_ins.$ins.self::count_pizza($order['ID']).'</div>';
//                echo '<div class="d-inline-block border '.$border_ins.$bg_ins.' rounded text-center m-1 p-1 align-self-center" style="width: 60px; text-shadow: 1px 0 0 #222, -1px 0 0 #222, 0 1px 0 #222, 0 -1px 0 #222, 1px 1px #222, -1px -1px 0 #222, 1px -1px 0 #222, -1px 1px 0 #222;">'.$delivery_ins.$ins.self::count_pizza($order['ID']).'</div>';
//                echo '<div id="'.$order['ID'].'" class="order_button d-inline-block border '.$border_ins.$bg_ins.' rounded text-center m-1 p-1 align-self-center" style="width: 60px; cursor: pointer;">'.$delivery_ins.$ins.self::count_pizza($order['ID']).'</div>';
                echo '<button id="'.$order['ID'].'" type="button" class="order_button btn'.$bg_ins.' p-0 ml-1 mt-0 mr-0 mb-0" style="width: 40px;"><small>'.$delivery_ins.$ins.self::count_pizza($order['ID']).'</small></button>';

            }

        }

        $count_orders_all = $count_orders + $count_orders2;
        $count_pizza_all = $count_pizza + $count_pizza2;

        return [$count_orders_all, $count_pizza_all];

    }

}
