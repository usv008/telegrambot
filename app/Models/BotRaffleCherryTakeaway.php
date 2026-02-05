<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class BotRaffleCherryTakeaway extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_raffle_cherry_takeaway';
    public $timestamps = false;

    public static function getUserByUserIdAndId($user_id, $id)
    {
        return self::where('user_id', $user_id)->where('id', $id)->first();
    }

    public static function countUnReceivedByUserId($user_id)
    {
        if (self::where('user_id', $user_id)->where('received', 0)->count()) {
            return (int)self::where('user_id', $user_id)->where('received', 0)->count();
        }
        return 0;
    }

    public static function countUnReceivedAndWinByUserId($user_id)
    {
        $count_takeaway = (int)self::where('user_id', $user_id)->where('received', 0)->count();
        $count_cherry_win = (int)BotRaffleUsers::where('user_id', $user_id)->where('win_cherry', 1)->count();
        Log::debug('COUNT WIN - caout_takeaway: '.$count_takeaway.'; count win cherry: '.$count_cherry_win);
        return (int)$count_takeaway + (int)$count_cherry_win;
    }

    public static function getCherryTakeawayByUserId($user_id)
    {
        return self::where('user_id', $user_id)->where('received', 0)->first();
    }

    public static function removeUnReceivedByUserId($user_id)
    {
        return self::where('user_id', $user_id)->where('received', 0)->where('waiting_for_receipt', 0)->delete();
    }

    public static function updateNameByUserIdAndId($user_id, $id, $name)
    {
        return self::where('user_id', $user_id)->where('id', $id)->update(['name' => $name]);
    }

    public static function updatePhoneByUserIdAndId($user_id, $id, $phone)
    {
        return self::where('user_id', $user_id)->where('id', $id)->update(['phone' => $phone]);
    }

    public static function updateWaitingForReceiptAndMessageIdsByUserIdAndId($user_id, $id, $message_ids)
    {
        return self::where('user_id', $user_id)->where('id', $id)->update(['waiting_for_receipt' => 1, 'message_ids' => $message_ids]);
    }

    public static function updateCodeByTekeawayIdAndUserId($user_id, $id, $code)
    {
        return self::where('user_id', $user_id)->where('id', $id)->update(['code' => $code]);
    }

    public static function setTakeawayByUserIdAndTakewayId($user_id, $takeaway_id)
    {
        return self::where('user_id', $user_id)->where('id', $takeaway_id)->update(['takeaway' => 1]);
    }

}
