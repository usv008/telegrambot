<?php

namespace App\Http\Controllers;

use App\Models\BotGameSeaBattleFields;
use App\Models\BotGameSeaBattleGames;
use App\Models\BotGameSeaBattleShots;
use App\Http\Controllers\Telegram\BotCashbackController;
use App\Http\Controllers\Telegram\BotGameSeaBattleController;

class BotSeaBattleCronController extends Controller
{

    public static function execute()
    {
        $gamesNotStart = BotGameSeaBattleGames::getGamesNotStart();
        $result = '';
        foreach ($gamesNotStart as $game) {
            $time_stop = strtotime("+1 minutes", strtotime($game->updated_at));
            if (time() > $time_stop) {
                $gameLate = BotGameSeaBattleController::setGameLate($game);
            }
        }

        $gamesNotFinished = BotGameSeaBattleGames::getGamesNotFinished();
        foreach ($gamesNotFinished as $game) {
            $time_stop = strtotime("+2 minutes", strtotime($game->updated_at));
            if (time() > $time_stop) {
                $gameLate = BotGameSeaBattleController::setGameLate($game, 1);
            }
        }
        return $result;
    }

}
