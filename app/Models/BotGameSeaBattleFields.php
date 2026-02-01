<?php

namespace App\Models;

use App\Http\Controllers\Telegram\BotGameSeaBattleController;
use Illuminate\Database\Eloquent\Model;
use Longman\TelegramBot\Request;

class BotGameSeaBattleFields extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_fields';
    public $timestamps = false;

    public static $icons = 0;
    public static $ships = 0;
    public static $bombs = 0;

    public static function getField($id) {
        return self::find($id);
    }

    public static function getUserFields($user_id) {
        return self::where('user_id', $user_id)->get();
    }

    public static function getUserNotFinishedFields($user_id) {
        return self::where('user_id', $user_id)->where('finished', 0)->get();
    }

    public static function getUserFieldForGame($user_id) {
        return self::where('user_id', $user_id)->where('enabled', 1)->where('finished', 0)->first();
    }

    public static function updateField($field_id, $number, $value) {
        return self::where('id', $field_id)->update(['f'.$number => $value]);
    }

    public static function updateFieldMessageId($field_id, $message_id) {
        return self::where('id', $field_id)->update(['message_id' => $message_id]);
    }

    public static function countIconsInFieldAndGetField($field_id) {
        self::$icons = 0;
        self::$ships = 0;
        self::$bombs = 0;
        $field = self::getField($field_id);
        for ($n = 1; $n <= 16; $n++) {
            $field_n = 'f'.$n;
            if ($field->$field_n == BotGameSeaBattleIcons::$ship_id) {
                self::$icons++;
                self::$ships++;
            }
            elseif ($field->$field_n == BotGameSeaBattleIcons::$bomb_id) {
                self::$icons++;
                self::$bombs++;
            }
        }
        return ['field' => $field, 'icons' => self::$icons, 'ships' => self::$ships, 'bombs' => self::$bombs];
    }

    public static function clearField($field_id) {
        self::$icons = 0;
        self::$ships = 0;
        self::$bombs = 0;
        return self::where('id', $field_id)->update([
            'f1' => null,
            'f2' => null,
            'f3' => null,
            'f4' => null,
            'f5' => null,
            'f6' => null,
            'f7' => null,
            'f8' => null,
            'f9' => null,
            'f10' => null,
            'f11' => null,
            'f12' => null,
            'f13' => null,
            'f14' => null,
            'f15' => null,
            'f16' => null
        ]);
    }

    public static function enableField($field_id)
    {
        return self::where('id', $field_id)->update(['enabled' => 1]);
    }

    public static function disableField($field_id)
    {
        return self::where('id', $field_id)->update(['enabled' => 0]);
    }

    public static function deleteUserFields($user_id)
    {
        return self::where('user_id', $user_id)->delete();
    }

    public static function deleteDisabledUserFields($user_id)
    {
        return self::where('user_id', $user_id)->where('enabled', 0)->delete();
    }

    public static function deleteUserField($user_id, $field_id)
    {
        return self::where('id', $field_id)
            ->where('user_id', $user_id)
            ->where(function ($query) {
                $query->where('finished', 0)
                    ->orWhere('enabled', 0);
            })
            ->delete();
    }

    public static function createNewField($user_id, $is_bot = 0, $enabled = 1)
    {

        // Случайным образом формируем массив с кораблями и бомбой для поля бота
        $arr_icons = [];
        $i = 1;
        while ($i <= BotGameSeaBattleController::$ships_need + BotGameSeaBattleController::$bombs_need) {
            $rand = rand(1,16);
            if (!array_key_exists($rand, $arr_icons)) {
                if ($i <= BotGameSeaBattleController::$ships_need) {
                    $arr_icons[$rand] = BotGameSeaBattleIcons::$ship_id;
                }
                elseif ($i > BotGameSeaBattleController::$ships_need) {
                    $arr_icons[$rand] = BotGameSeaBattleIcons::$bomb_id;
                }
                $i++;
            }
        }

        $new_field = new self;
        $new_field->user_id = $user_id;
        $new_field->is_bot = $is_bot;
        $new_field->enabled = $enabled;
        for ($i = 1; $i <= 16; $i++) {
            if (isset($arr_icons[$i]) && $arr_icons[$i] !== null && ($arr_icons[$i] === BotGameSeaBattleIcons::$ship_id || $arr_icons[$i] === BotGameSeaBattleIcons::$bomb_id))
            {
                $f = 'f'.$i;
                $new_field->$f = $arr_icons[$i];
            }
        }
        $new_field->updated_at = date("Y-m-d H:i:s");
        $new_field->save();
        return $new_field;
    }

    public static function updateFieldSearchMessageId($field_id, $message_id)
    {
        return self::where('id', $field_id)->update(['search_message_id' => $message_id]);
    }

    public static function setFieldFinished($field_id)
    {
        return self::where('id', $field_id)->update(['finished' => 1]);
    }

    public static function checkFieldFinished($field_id)
    {
        return self::find($field_id)->finished;
    }

}
