<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as Request;

class GeocodeController extends Controller
{

    public static function geocode($lat, $lng) {

        $yandex_url = 'https://geocode-maps.yandex.ru/1.x/?apikey='.env('YANDEX_KEY').'&format=json&geocode='.$lng.','.$lat;

        $result = file_get_contents($yandex_url);
        if ($result) {

            $result = json_decode($result, true);

            $result_addr = $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['metaDataProperty']['GeocoderMetaData']['Address']['formatted'];
            $lat_lng_arr = explode(" ", $result['response']['GeoObjectCollection']['featureMember']['0']['GeoObject']['Point']['pos']);
            $lat = $lat_lng_arr[1];
            $lng = $lat_lng_arr[0];
            $arr = [$result_addr, $lat, $lng];

            return $result_addr;

        }
        else return 'Не получилось геокодировать';

    }

}

