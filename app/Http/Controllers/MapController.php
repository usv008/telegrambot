<?php

namespace App\Http\Controllers;

use App\Models\LogisticsOrders;
use Illuminate\Http\Request as LRequest;

use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class MapController extends Controller
{

    const h_start = 10;
    const h_end = 22;

    public static function execute () {

//        $input = $request->except('_token');

        if (view()->exists('admin.map')) {

            $point_id = 5;
            $res5_2 = ArchiController::get_orders(2, $point_id);
            $res5_4 = ArchiController::get_orders(4, $point_id);
//            $res5_6 = ArchiController::get_orders(6, $point_id);
//            $res5_7 = ArchiController::get_orders(7, $point_id);

            $point_id = 10;
            $res10_2 = ArchiController::get_orders(2, $point_id);
            $res10_4 = ArchiController::get_orders(4, $point_id);
//            dd($res10_4);
//            $res10_6 = ArchiController::get_orders(6, $point_id);
//            $res10_7 = ArchiController::get_orders(7, $point_id);

            $gps5_2 = [];
            foreach ($res5_2 as $value) {
                if ($value['ORDERTYPE'] == 3) {

                    $yandex_key = '9b22e87b-0778-44ff-803d-9e5d7beda8dd';
                    $yandex_url = 'https://geocode-maps.yandex.ru/1.x/?apikey='.$yandex_key.'&format=json&geocode='.urlencode($value['AddressForYaMap']);

                    $result = file_get_contents($yandex_url);
                    $result = json_decode($result, true);
//                    $result = json_encode($result);

                    $addr = $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['formatted'];
                    $lat_lng_arr = explode(" ", $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos']);
                    $lat = $lat_lng_arr[1];
                    $lng = $lat_lng_arr[0];

                    $result = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?language=ru&mode=driving&units=metric&origins=48.468538,35.0277714&destinations='.$lat.','.$lng.'&key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8');
                    $result = json_decode($result, true);
                    $distance = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['distance']['text'] : '-';
                    $duration = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['duration']['text'] : '-';
//                    dd($result);

                    $id = $value['ID'];
                    $gps5_2[$id] = ['addr' => $addr, 'lat' => $lat, 'lng' => $lng, 'distance' => $distance, 'duration' => $duration];

//                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($value['AddressForYaMap']).'&key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8&region=ua';
//
//                    if( $curl = curl_init($url) ) {
//                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//                        $result = curl_exec($curl);
//                        curl_close($curl);
//
//                        $result = json_decode($result, true);
//                        $addr = $result['results']['0']['formatted_address'];
//                        $lat = $result['results']['0']['geometry']['location']['lat'];
//                        $lng = $result['results']['0']['geometry']['location']['lng'];
//
//                        $id = $value['ID'];
//                        $gps10_7[$id] = ['addr' => $addr, 'lat' => $lat, 'lng' => $lng];
//
////                        echo $lat.', '.$lng.'<br />'.$addr;
////                        exit;
////                        dd($result);

//                    }
//                    else $res = null;

//                    $result = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($value['AddressForYaMap']).'&key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8&region=ua');

//                    dd($result);

                }
            }

            $gps5_4 = [];
            foreach ($res5_4 as $value) {
                if ($value['ORDERTYPE'] == 3) {

                    $yandex_key = '9b22e87b-0778-44ff-803d-9e5d7beda8dd';
                    $yandex_url = 'https://geocode-maps.yandex.ru/1.x/?apikey='.$yandex_key.'&format=json&geocode='.urlencode($value['AddressForYaMap']);

                    $result = file_get_contents($yandex_url);
                    $result = json_decode($result, true);

                    $addr = $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['formatted'];
                    $lat_lng_arr = explode(" ", $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos']);
                    $lat = $lat_lng_arr[1];
                    $lng = $lat_lng_arr[0];

                    $result = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?language=ru&mode=driving&units=metric&origins=48.468538,35.0277714&destinations='.$lat.','.$lng.'&key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8');
                    $result = json_decode($result, true);
                    $distance = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['distance']['text'] : '-';
                    $duration = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['duration']['text'] : '-';
//                    dd($result);

                    $id = $value['ID'];
                    $gps5_4[$id] = ['addr' => $addr, 'lat' => $lat, 'lng' => $lng, 'distance' => $distance, 'duration' => $duration];

                }
            }

            $gps10_2 = [];
            foreach ($res10_2 as $value) {
                if ($value['ORDERTYPE'] == 3) {

                    $yandex_key = '9b22e87b-0778-44ff-803d-9e5d7beda8dd';
                    $yandex_url = 'https://geocode-maps.yandex.ru/1.x/?apikey='.$yandex_key.'&format=json&geocode='.urlencode($value['AddressForYaMap']);

                    $result = file_get_contents($yandex_url);
                    $result = json_decode($result, true);

                    $addr = $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['formatted'];
                    $lat_lng_arr = explode(" ", $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos']);
                    $lat = $lat_lng_arr[1];
                    $lng = $lat_lng_arr[0];

                    $result = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?language=ru&mode=driving&units=metric&origins=48.4914503,35.0677432&destinations='.$lat.','.$lng.'&key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8');
                    $result = json_decode($result, true);
                    $distance = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['distance']['text'] : '-';
                    $duration = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['duration']['text'] : '-';
//                    dd($result);

                    $id = $value['ID'];
                    $gps10_2[$id] = ['addr' => $addr, 'lat' => $lat, 'lng' => $lng, 'distance' => $distance, 'duration' => $duration];

                }
            }

            $gps10_4 = [];
            foreach ($res10_4 as $value) {
                if ($value['ORDERTYPE'] == 3) {

                    $yandex_key = '9b22e87b-0778-44ff-803d-9e5d7beda8dd';
                    $yandex_url = 'https://geocode-maps.yandex.ru/1.x/?apikey='.$yandex_key.'&format=json&geocode='.urlencode($value['AddressForYaMap']);

                    $result = file_get_contents($yandex_url);
                    $result = json_decode($result, true);

                    $addr = $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['formatted'];
                    $lat_lng_arr = explode(" ", $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos']);
                    $lat = $lat_lng_arr[1];
                    $lng = $lat_lng_arr[0];

                    $result = file_get_contents('https://maps.googleapis.com/maps/api/distancematrix/json?language=ru&mode=driving&units=metric&origins=48.4914503,35.0677432&destinations='.$lat.','.$lng.'&key=AIzaSyDOLTf3ayZ0yZ22j76f067GvUZq7wglRV8');
                    $result = json_decode($result, true);
                    $distance = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['distance']['text'] : '-';
                    $duration = $result['status'] == 'OK' ? $result['rows']['0']['elements']['0']['duration']['text'] : '-';
//                    dd($result);

                    $id = $value['ID'];
                    $gps10_4[$id] = ['addr' => $addr, 'lat' => $lat, 'lng' => $lng, 'distance' => $distance, 'duration' => $duration];

                }
            }


            //            dd($res10_7);
            $data = [
                'title' => 'Map',
                'res5_2' => $res5_2,
                'res5_4' => $res5_4,
//                'res5_6' => $res5_6,
//                'res5_7' => $res5_7,
                'res10_2' => $res10_2,
                'res10_4' => $res10_4,
//                'res10_6' => $res10_6,
//                'res10_7' => $res10_7,
                'gps5_2' => $gps5_2,
                'gps5_4' => $gps5_4,
                'gps10_2' => $gps10_2,
                'gps10_4' => $gps10_4,
//                'input' => $input,
            ];

            return view('admin.map', $data);

        }

    }

    public static function contentStringAdd ($res, $gps) {

        $result = '';
        foreach ($res as $value) {

            if ($value['ORDERTYPE'] == 3) {

                $id = $value['ID'];
                $order_datetime = explode(" ", $value['FormatORDERDATETIME']);
                $order_time = $order_datetime[1];
                $addr = isset($gps[$id]['addr']) && $gps[$id]['addr'] !== null ? $gps[$id]['addr'] : 'Не распознал';
                $result .= ','.$value['ID'].': { content: \'<h4>Заказ №'.$value['ID'].'</h4><p>Время на которое доставить: '.$order_time.'</p><p>Из Арчи: '.$value['AddressForYaMap'].'</p><p>Распознал: '.str_replace("'", "", $addr ).'</p><p>Расстояние: '.$gps[$id]['distance'].'<br />Время доставки: '.$gps[$id]['duration'].'</p>\' }';

            }

        }
        echo $result;

    }

    public static function featuresAdd ($point, $status, $res, $gps) {

        $result = '';

        foreach ($res as $value) {

            if ($value['ORDERTYPE'] == 3) {

                $id = $value['ID'];
                $lat = isset($gps[$id]['lat']) && $gps[$id]['lat'] !== null ? $gps[$id]['lat'] : '0';
                $lng = isset($gps[$id]['lng']) && $gps[$id]['lng'] !== null ? $gps[$id]['lng'] : '0';
                $order_datetime = explode(" ", $value['FormatORDERDATETIME']);
                $order_time = $order_datetime[1];
                $result .= ',{ position: new google.maps.LatLng('.$lat.', '.$lng.'), id: \''.$value['ID'].'\', type: \''.$point.'_'.$status.'\', title: \''.$order_time.'\' }';

            }

        }
        echo $result;

    }

    public static function map_test ()
    {

        if (view()->exists('admin.map_test')) {

//            $orders = LogisticsOrders::where('id_delivery', 3)->where('lat', '!=', '')->where('lon', '!=', '')->orderBy('date_order', 'desc')->skip(0)->take(10)->get();
            $orders = LogisticsOrders::where('id_delivery', 3)->where('lat', '!=', '')->where('lon', '!=', '')->orderBy('date_order', 'desc')->get();
            $data = [
                'orders' => $orders,
                'title' => 'Map',
            ];

            return view('admin.map_test', $data);
        }

    }

}
