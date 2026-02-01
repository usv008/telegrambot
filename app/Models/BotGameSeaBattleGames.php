<?php

namespace App\Models;

use App\Http\Controllers\Telegram\BotGameSeaBattleController;
use Illuminate\Database\Eloquent\Model;

class BotGameSeaBattleGames extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_game_sea_battle_games';
    public $timestamps = false;

    public static function getGame($id)
    {
        return self::find($id);
    }

    public static function getGames()
    {
        return self::where('finished', 1)->get();
    }

    public static function getGamesNotFinished()
    {
        return self::where('finished', 0)->get();
    }

    public static function getActiveGameByUserId($user_id)
    {
        return self::where(function ($query) use ($user_id) {
            $query->where('player1_user_id', $user_id)
                ->orWhere('player2_user_id', $user_id);
        })
            ->where('finished', 0)
            ->first();
    }

    public static function getLastGameByUserId($user_id)
    {
        return self::where(function ($query) use ($user_id) {
            $query->where('player1_user_id', $user_id)
                ->orWhere('player2_user_id', $user_id);
        })
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function updateValue($game_id, $key, $value)
    {
        return self::where('id', $game_id)->update([$key => $value]);
    }

    public static function createNewGame($playWithBot, $player1_user_id, $player2_user_id, $bet, $player1_field_id, $player2_field_id)
    {
        $new_game = new self;
        $new_game->pwb = $playWithBot;
        $new_game->player1_user_id = $player1_user_id;
        $new_game->player2_user_id = $player2_user_id;
        $new_game->bet = $bet;
        $new_game->player1_field_id = $player1_field_id;
        $new_game->player2_field_id = $player2_field_id;
        $new_game->save();
        return $new_game;
    }

    public static function checkPlayerStart($game, $player) {
        $player_start = $player.'_start';
        if ($game->$player_start == 1) {
            return true;
        }
        return null;
    }

    public static function updatePlayerStart($game_id, $player) {
        return self::where('id', $game_id)->update([$player.'_start' => 1]);
    }

    public static function updateStartMessageId($game_id, $player_n, $message_id) {
        return self::where('id', $game_id)->update(['player'.$player_n.'_start_message_id' => $message_id]);
    }

    public static function updateShotsMessageId($game_id, $player_n, $message_id) {
        return self::where('id', $game_id)->update([$player_n.'_shots_message_id' => $message_id]);
    }

    public static function checkGameOver($game_id)
    {
        $game = self::find($game_id);
        $player1_field = BotGameSeaBattleFields::find($game->player1_field_id);
        $player2_field = BotGameSeaBattleFields::find($game->player2_field_id);
        if ($player1_field->finished == 1 && $player2_field->finished == 1) {
            $generateGameResults = BotGameSeaBattleController::generateGameResults($game);
            return true;
        }
        else return null;
    }

    public static function getGamesNotStart()
    {
        return self::where('finished', 0)
            ->where(function ($query) {
                $query->where('player1_start', 0)
                    ->orWhere('player2_start', 0);
            })
            ->get();
    }

    public static function setGameLateFinished($game_id, $forced = null)
    {
        $array = [];
        $array['finished'] = 1;
        if ($forced == null) $array['late'] = 1;
        return self::where('id', $game_id)->update($array);
    }

    public static function updateUserWin($game_id, $user_id)
    {
        $won_plus = BotGameSeaBattleUsers::incrementUserWin($user_id);
        return self::where('id', $game_id)->update(['win_user_id' => $user_id]);
    }

}
