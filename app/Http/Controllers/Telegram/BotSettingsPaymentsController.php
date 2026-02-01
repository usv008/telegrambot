<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotSettings;
use App\Models\BotSettingsPayments;
use App\Http\Controllers\SimplaPaymentController;
use Illuminate\Http\Request as LRequest;
use App\Http\Controllers\Controller;

class BotSettingsPaymentsController extends Controller
{

    public static function getPayments($user_id) {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = 'text_value_'.$lang;

        $arr = [];
        $delivery = BotUsersNavController::getDeliveryYesOrNo($user_id);
        $payments = $delivery == 0 ? BotSettingsPayments::orderBy('id')->get() : BotSettingsPayments::where('takeaway', 0)->orderBy('id')->get();
        foreach ($payments as $payment) {
            if ($payment['type'] == 'card') {
                if (SimplaPaymentController::check_pay() == 1) $arr[$payment['id']] = $payment['emoji'].$payment[$text_lang];
            }
            else $arr[$payment['id']] = $payment['emoji'].$payment[$text_lang];
        }

        return $arr;

    }

    public static function getPaymentMethodId($payment_id) {

        $payment = BotSettingsPayments::find($payment_id);

        return $payment['payment_method_id'];

    }

    public static function getPaymentType($payment_id) {

        $payment = BotSettingsPayments::find($payment_id);

        return $payment['type'];

    }

    public static function getPaymentText($user_id, $payment_id) {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = 'text_value_'.$lang;

        $payment = BotSettingsPayments::find($payment_id);

        return $payment[$text_lang];

    }

    public static function getPaymentTextDefault($payment_id) {

        $lang = 'ru';
        $text_lang = 'text_value_'.$lang;

        $payment = BotSettingsPayments::find($payment_id);

        return $payment[$text_lang];

    }

    public static function getPaymentEmoji($user_id, $payment_id) {

        $lang = BotUserSettingsController::getLang($user_id);
        $text_lang = 'text_value_'.$lang;

        $payment = BotSettingsPayments::find($payment_id);

        return $payment['emoji'];

    }

}
