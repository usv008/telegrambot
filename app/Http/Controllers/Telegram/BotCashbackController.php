<?php

namespace App\Http\Controllers\Telegram;

use App\Models\BotCart;
use App\Models\BotCartNew;
use App\Models\BotCashbackHistory;
use App\Models\BotMenu;
use App\Models\BotSettings;
use App\Models\BotUser;
use App\Models\BotUsersNav;
use App\Http\Controllers\SimplaOrdersController;
use App\Models\PrestaShop_Cart_Rule;
use App\Models\PrestaShop_Cart_Rule_Lang;
use App\Models\Simpla_Categories;
use App\Models\Simpla_Complect_Products;
use App\Models\Simpla_Images;
use App\Models\Simpla_Options;
use App\Models\Simpla_Products;
use App\Models\Simpla_Variants;
use Illuminate\Http\Request as LRequest;
use Longman\TelegramBot\Request;
use App\Http\Controllers\Controller;
use App\Models\BotSettingsButtons;

use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use PHPUnit\ExampleExtension\Comparable;

class BotCashbackController extends Controller
{

    public static function getUserCashback($user_id)
    {
        $user = BotUser::where('user_id', $user_id)->first();
        return $user ? $user->cashback : 0;
    }

    public static function getUserCashbackAction($user_id)
    {
        $user = BotUser::where('user_id', $user_id)->first();
        return $user ? $user->cashback_action : 0;
    }

    public static function getUserCashbackAll($user_id)
    {
        $user = BotUser::getUser($user_id);
        return bcadd($user->cashback, $user->cashback_action, 2);
    }

    public static function getAllUserCashback($user_id)
    {
        return BotCashbackHistory::where('user_id', $user_id)->orderBy('id', 'asc')->get();
    }

    public static function clearCashback($user_id)
    {
        BotUsersNavController::updateValue($user_id, 'change_key', null);
        BotUsersController::updateUsers($user_id, 'cashback_pay', 0);
        BotUsersController::updateUsers($user_id, 'cashback_summa', 0);
    }

    public static function payCashback($user_id, $simpla_id)
    {
        $pay = BotUsersController::getValueFromUsers($user_id, 'cashback_pay');
        $cashback = BotUsersController::getValueFromUsers($user_id, 'cashback_summa');
        if ($pay == 1 && $cashback > 0) {

            $date_z = date("Y-m-d H:i:s");

            $user_cashback_old = self::getUserCashback($user_id);
            $user_cashback_action = self::getUserCashbackAction($user_id);

            $total = BotCartController::count_sum_total_without_cashback($user_id);
            if ($total >= 350) {
                $user_cashback = $user_cashback_action >= $cashback ? $user_cashback_action - $cashback : ($user_cashback_old + $user_cashback_action) - $cashback;
            }
            else {
                $user_cashback = $user_cashback_old - $cashback;
            }

            $order_id = BotOrderController::getOrderFromSimplaId($user_id, $simpla_id)['id'];

            $user_cashback_history = new BotCashbackHistory;
            $user_cashback_history->admin_login = 'BOT';
            $user_cashback_history->user_id = $user_id;
            $user_cashback_history->order_id = $order_id;
            $user_cashback_history->type = 'OUT';
            $user_cashback_history->summa = $cashback;
            $user_cashback_history->descr = 'Оплата заказа № '.$simpla_id;
            $user_cashback_history->balance_old = $user_cashback_old;
            $user_cashback_history->balance = $user_cashback;
            $user_cashback_history->ip = $_SERVER['REMOTE_ADDR'];
            $user_cashback_history->date_z = $date_z;
            $user_cashback_history->save();

            if ($total >= 350) {
                if ($user_cashback_action >= $cashback) {
                    BotUser::where('user_id', $user_id)->update(['cashback_action' => $user_cashback, 'updated_at' => $date_z]);
                }
                else {
                    BotUser::where('user_id', $user_id)->update(['cashback_action' => 0, 'updated_at' => $date_z]);
                    BotUser::where('user_id', $user_id)->update(['cashback' => $user_cashback, 'updated_at' => $date_z]);
                }

            }
            else {
                BotUser::where('user_id', $user_id)->update(['cashback' => $user_cashback, 'updated_at' => $date_z]);
            }

        }
        BotUsersController::updateUsers($user_id, 'cashback_pay', 0);
        BotUsersController::updateUsers($user_id, 'cashback_summa', 0);
    }

    public static function payCashbackNew($user_id, $cashback, $order_id, $external_order_id)
    {

        $date_z = date("Y-m-d H:i:s");

        $user_cashback_old = self::getUserCashback($user_id);
        $user_cashback_action = self::getUserCashbackAction($user_id);

        $cart_products = BotCartNew::getProductsByUserId($user_id);
        $total = $cart_products->sum('price_all');

        if ($total >= 350) {
            $user_cashback = $user_cashback_action >= $cashback ? $user_cashback_action - $cashback : ($user_cashback_old + $user_cashback_action) - $cashback;
        }
        else {
            $user_cashback = $user_cashback_old - $cashback;
        }

        $user_cashback_history = new BotCashbackHistory;
        $user_cashback_history->admin_login = 'BOT';
        $user_cashback_history->user_id = $user_id;
        $user_cashback_history->order_id = $order_id;
        $user_cashback_history->type = 'OUT';
        $user_cashback_history->summa = $cashback;
        $user_cashback_history->descr = 'Оплата заказа № '.$external_order_id;
        $user_cashback_history->balance_old = $user_cashback_old;
        $user_cashback_history->balance = $user_cashback;
        $user_cashback_history->ip = $_SERVER['REMOTE_ADDR'];
        $user_cashback_history->date_z = $date_z;
        $user_cashback_history->save();

        if ($total >= 350) {
            if ($user_cashback_action >= $cashback) {
                BotUser::where('user_id', $user_id)->update(['cashback_action' => $user_cashback, 'updated_at' => $date_z]);
            }
            else {
                BotUser::where('user_id', $user_id)->update(['cashback_action' => 0, 'updated_at' => $date_z]);
                BotUser::where('user_id', $user_id)->update(['cashback' => $user_cashback, 'updated_at' => $date_z]);
            }
        }
        else {
            BotUser::where('user_id', $user_id)->update(['cashback' => $user_cashback, 'updated_at' => $date_z]);
        }
    }

    public static function cashbackMinus($user_id, $summa)
    {
        $user = BotUser::getUser($user_id);
        $cashback = $user->cashback;
        $cashback_action = $user->cashback_action;
        $cashback_all = bcadd($cashback, $cashback_action, 2);

        if ($cashback_all >= $summa) {
            if ($user->cashback_action >= $summa) {
                $cashback_action = bcsub($user->cashback_action, $summa, 2);
            }
            elseif($user->cashback_action > 0 && $user->cashback_action < $summa) {
                $summa = bcsub($summa, $user->cashback_action, 2);
                $cashback_action = 0;
                $cashback = bcsub($user->cashback, $summa, 2);
            }
            else {
                $cashback = bcsub($user->cashback, $summa, 2);
            }
            $update_cashback = BotUser::setValues($user_id, ['cashback' => $cashback, 'cashback_action' => $cashback_action]);
            return $update_cashback;
        }
        return null;
    }

    public static function cashbackPlus($user_id, $summa)
    {
        $user = BotUser::getUser($user_id);
        $cashback = bcadd($user->cashback, $summa, 2);
        $update_cashback = BotUser::setValues($user_id, ['cashback' => $cashback]);
        return $update_cashback;
    }

    public static function cashbackMinusForSeaBattle($user_id, $game_id = null)
    {
        $summa = BotGameSeaBattleController::$cashback_bet;
        $writeToHistory = self::writeToCashbackHistory($user_id, 'minus', $summa, $game_id);
        return self::cashbackMinus($user_id, $summa);
    }

    public static function cashbackPlusForSeaBattle($user_id, $game_id = null)
    {
        $summa = BotGameSeaBattleController::$cashback_bet;
        $writeToHistory = self::writeToCashbackHistory($user_id, 'plus', $summa, $game_id);
        return self::cashbackPlus($user_id, $summa);
    }

    public static function cashbackPlusForSeaBattleForWin($user_id, $game_id)
    {
        $summa = bcmul(BotGameSeaBattleController::$cashback_bet, 2, 2);
        $writeToHistory = self::writeToCashbackHistory($user_id, 'win', $summa, $game_id);
        return self::cashbackPlus($user_id, $summa);
    }

    public static function writeToCashbackHistory($user_id, $what, $summa, $game_id = null)
    {
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '' && $_SERVER['REMOTE_ADDR'] !== null) $ip = $_SERVER['REMOTE_ADDR'];
        else $ip = '';
        $balance_old = BotCashbackController::getUserCashback($user_id);

        $descr = 'нет описания';
        $type = 'NO';
        if ($what == 'plus') {
            $type = 'IN';
            $descr = 'Начисление бонусов за игру '.$game_id;
            $balance_new = bcadd($balance_old, $summa, 2);
        }
        elseif ($what == 'minus') {
            $type = 'OUT';
            $descr = 'Списание бонусов за игру '.$game_id;
            $balance_new = bcsub($balance_old, $summa, 2);
        }

        elseif ($what == 'win') {
            $type = 'IN';
            $descr = 'Начисление выигрышных бонусов за игру '.$game_id;
            $balance_new = bcadd($balance_old, $summa, 2);
        }

        $user_cashback_history = new BotCashbackHistory;
        $user_cashback_history->admin_login = 'BOT';
        $user_cashback_history->user_id = $user_id;
        $user_cashback_history->type = $type;
        $user_cashback_history->summa = $summa;
        $user_cashback_history->descr = $descr;
        $user_cashback_history->balance_old = $balance_old;
        $user_cashback_history->balance = $balance_new;
        $user_cashback_history->sea_battle_game_id = $game_id;
        $user_cashback_history->ip = $ip;
        $user_cashback_history->date_z = date("Y-m-d H:i:s");
        $user_cashback_history->save();
    }

    public static function deleteCartRulesByCron()
    {
        $cart_rules = PrestaShop_Cart_Rule::where('description', 'like', 'CashBack Pay, cart:%')->get();
        foreach ($cart_rules as $cart_rule) {
            $delete_rule_lang = PrestaShop_Cart_Rule_Lang::where('id_cart_rule', $cart_rule->id_cart_rule)->delete();
            $delete_rule = PrestaShop_Cart_Rule::where('id_cart_rule', $cart_rule->id_cart_rule)->delete();
        }
        return true;
    }

}
