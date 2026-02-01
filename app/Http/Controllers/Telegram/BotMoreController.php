<?php

namespace App\Http\Controllers\Telegram;


use App\Models\BotReviews;
use App\Models\Simpla_Shares;

use App\Http\Controllers\Controller;


class BotMoreController extends Controller
{

    public static function getActions($user_id)
    {

        return Simpla_Shares::where('visible', 1)->orderBy('position', 'asc')->get();

    }

    public static function getReviews($user_id)
    {

        return BotReviews::where('status', 'ok')->orderBy('id', 'asc')->get();

    }

    public static function addReview($user_id, $firstname, $text)
    {

        $date_z = date("Y-m-d H:i:s");

        $review = new BotReviews;
        $review->user_id = $user_id;
        $review->user_name = $firstname;
        $review->review = $text;
        $review->date_reg = $date_z;
        $review->date_edit = $date_z;
        $review->save();

    }

}
