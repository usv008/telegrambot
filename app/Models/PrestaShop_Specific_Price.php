<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Specific_Price extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_specific_price';
    public $timestamps = false;

    public static function getPriceByProductId($id)
    {
        return self::where('id_product', $id)
            ->where(function ($query) {
                $query->where(function ($query_from_to1) {
                    $query_from_to1->where('from', '>=', date("Y-m-d H:i:s"))->where('to', '<=', date("Y-m-d H:i:s"));
                })
                ->orWhere(function ($query_from_to2) {
                    $query_from_to2->where('from', '=', '0000-00-00 00:00:00')->where('to', '=', '0000-00-00 00:00:00');
                });
            })
            ->get();
//        if ($prices->where('to', '>', date("Y-m-d H:i:s"))->count() > 0) {
//            $result = $prices->where('to', '>', date("Y-m-d H:i:s"));
//        }
//        else $result = $prices;
    }

}
