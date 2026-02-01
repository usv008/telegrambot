<?php

namespace App\Http\Controllers;

use App\Models\BotChatMessages;
use App\Models\BotGameSeaBattleGames;
use App\Models\BotGameSeaBattleRates;
use App\Models\BotGameSeaBattleUsers;
use App\Models\BotOrder;
use App\Models\BotRaffle;
use App\Models\BotRaffleUsers;
use App\Models\BotReviews;
use App\Models\BotUser;
use App\Http\Controllers\Telegram\BotGameSeaBattleController;
use App\Models\Simpla_Products;
use Illuminate\Http\Request as LRequest;
use Illuminate\Support\Facades\Input;

use Longman\TelegramBot\Request;

use Illuminate\Support\Facades\DB;
use Longman\TelegramBot\Telegram;

use DataTables;

use App\Models\BotFeedBack;

class BotSeaBattleController extends Controller
{

    public static function show(LRequest $request)
    {

        if (view()->exists('admin.bot')) {

            $input = $request->except('_token');

            $users = BotGameSeaBattleUsers::all();
            $games = BotGameSeaBattleGames::where('finished', 1)->get();

            $games_bot = $games->where('pwb', 1);
            $bot_cashback_plus = $games_bot->where('win_user_id', BotGameSeaBattleController::$bot_user_id)->sum('bet');
            $bot_cashback_minus = $games_bot->where('win_user_id', '!=', BotGameSeaBattleController::$bot_user_id)->where('win_user_id', '!=', null)->sum('bet');

            $data = [
                'title' => 'Морской бой',
                'page' => 'sea_battle',
                'games' => $games,
                'bot_cashback_plus' => $bot_cashback_plus,
                'bot_cashback_minus' => $bot_cashback_minus,
            ];

            return view('admin.bot', $data);

        }

    }

    public static function list()
    {

        $users = BotGameSeaBattleUsers::join('bot_user', 'bot_user.user_id', 'bot_game_sea_battle_users.user_id')
            ->select([
                'bot_game_sea_battle_users.user_id as user_id',
                'bot_user.username as username',
                'bot_user.first_name as first_name',
                'bot_user.last_name as last_name',
                'bot_game_sea_battle_users.updated_at as updated_at'
            ])
            ->get();

        foreach ($users as $user) {
            $user_id = $user->user_id;
            $user_games = BotGameSeaBattleGames::where('finished', 1)
                ->where(function ($query) use ($user_id) {
                    $query->where('player1_user_id', '=', $user_id)
                        ->orWhere('player2_user_id', '=', $user_id);
                })->get();
            $user->games = $user_games->count();
            $user->wins = $user_games->where('win_user_id', $user_id)->count();
            $user->cashback_plus = $user_games->where('win_user_id', $user_id)->sum('bet');
            $user->cashback_minus = $user_games->where('win_user_id', '!=', null)->where('win_user_id', '!=', $user_id)->sum('bet');
        }
//        dd($users);
        return Datatables::of($users)
            ->addIndexColumn()
            ->addColumn('user_id', function($row){
                $result = $row->user_id;
                return $result;
            })
            ->addColumn('fio', function($row){
                $username = $row->username !== null && $row->username !== '' ? '<br />('.$row->username.')' : '';
                $result = '<a data-num="'.$row->first_name . ' ' . $row->last_name . $username.'" href="'.route('user', array('user'=>$row->user_id)).'">'.$row->first_name . ' ' . $row->last_name . $username.'</a>';
                return $result;
            })
            ->addColumn('games', function($row){
                $result = '<a href="#games" class="games" data-num="'.$row->games.'" id="'.$row->user_id.'">'.$row->games.'</a>';
                return $result;
            })
            ->addColumn('wins', function($row){
                $result = $row->wins;
                return $result;
            })
            ->addColumn('cashback_plus', function($row){
                $result = $row->cashback_plus;
                return $result;
            })
            ->addColumn('cashback_minus', function($row){
                $result = $row->cashback_minus;
                return $result;
            })
            ->addColumn('profit', function($row){
                $result = $row->cashback_plus - $row->cashback_minus;
                return $result;
            })
            ->addColumn('updated_at', function($row){
                $result = $row->updated_at;
                return $result;
            })
            ->rawColumns(['user_id', 'fio', 'games'])
            ->make(true);
    }

    public static function game(LRequest $request)
    {

        $input = $request->except('_token');
        $user_id = isset($input['user_id']) &&  $input['user_id'] !== null && $input['user_id'] > 0 ? $input['user_id'] : null;

        if ($user_id !== null) {

//            $bot_users = BotUser::getUsers();
            $games = BotGameSeaBattleGames::leftjoin('bot_user as bot_user1', 'bot_user1.user_id', 'bot_game_sea_battle_games.player1_user_id')
                ->leftJoin('bot_user as bot_user2', 'bot_user2.user_id', 'bot_game_sea_battle_games.player2_user_id')
                ->leftJoin('bot_cashback_history', function($join) use ($user_id) {
                    $join->on('bot_cashback_history.sea_battle_game_id', '=', 'bot_game_sea_battle_games.id');
                    $join->where('bot_cashback_history.user_id', '=', $user_id);
                })
                ->where('bot_game_sea_battle_games.finished', 1)
                ->where(function ($query) use ($user_id) {
                    $query->where('bot_game_sea_battle_games.player1_user_id', '=', $user_id)
                        ->orWhere('bot_game_sea_battle_games.player2_user_id', '=', $user_id);
                })
                ->orderBy('bot_game_sea_battle_games.id', 'desc')
                ->orderBy('bot_cashback_history.id', 'desc')
//                ->groupBy('bot_game_sea_battle_games.id')
                ->get([
                    'bot_game_sea_battle_games.id',
                    'bot_game_sea_battle_games.pwb',
                    'bot_game_sea_battle_games.player1_user_id',
                    'bot_game_sea_battle_games.player2_user_id',
                    'bot_game_sea_battle_games.bet',
                    'bot_game_sea_battle_games.player1_shots',
                    'bot_game_sea_battle_games.player2_shots',
                    'bot_game_sea_battle_games.win_user_id',
                    'bot_game_sea_battle_games.updated_at',
                    'bot_user1.username as player1_username',
                    'bot_user1.first_name as player1_first_name',
                    'bot_user1.last_name as player1_last_name',
                    'bot_user2.username as player2_username',
                    'bot_user2.first_name as player2_first_name',
                    'bot_user2.last_name as player2_last_name',
                    'bot_cashback_history.balance as cashback',
                ]);

            if ($user_id == 522750680) {
                $games = BotGameSeaBattleGames::leftjoin('bot_user as bot_user1', 'bot_user1.user_id', 'bot_game_sea_battle_games.player1_user_id')
                    ->leftJoin('bot_user as bot_user2', 'bot_user2.user_id', 'bot_game_sea_battle_games.player2_user_id')
                    ->leftJoin('bot_cashback_history', function($join) use ($user_id) {
                        $join->on('bot_cashback_history.sea_battle_game_id', '=', 'bot_game_sea_battle_games.id');
                        $join->where('bot_cashback_history.user_id', '=', $user_id);
                    })
                    ->where('bot_game_sea_battle_games.finished', 1)
                    ->where(function ($query) use ($user_id) {
                        $query->where('bot_game_sea_battle_games.player1_user_id', '=', $user_id)
                            ->orWhere('bot_game_sea_battle_games.player2_user_id', '=', $user_id);
                    })
                    ->groupBy('bot_game_sea_battle_games.id')
                    ->orderBy('bot_game_sea_battle_games.id', 'desc')
                    ->orderBy('bot_cashback_history.id', 'desc')
//                    ->whereRaw('id = (select max(`id`) from bot_cashback_history)')
//                    ->max('bot_cashback_history.id')
                    ->get([
                        'bot_game_sea_battle_games.id',
                        'bot_game_sea_battle_games.pwb',
                        'bot_game_sea_battle_games.player1_user_id',
                        'bot_game_sea_battle_games.player2_user_id',
                        'bot_game_sea_battle_games.bet',
                        'bot_game_sea_battle_games.player1_shots',
                        'bot_game_sea_battle_games.player2_shots',
                        'bot_game_sea_battle_games.win_user_id',
                        'bot_game_sea_battle_games.updated_at',
                        'bot_user1.username as player1_username',
                        'bot_user1.first_name as player1_first_name',
                        'bot_user1.last_name as player1_last_name',
                        'bot_user2.username as player2_username',
                        'bot_user2.first_name as player2_first_name',
                        'bot_user2.last_name as player2_last_name',
                        'bot_cashback_history.balance as cashback',
//                        'bot_game_sea_battle_games.id as cashback_history_id',
                    ]);
                $games = $games->take(10);
                dd($games);
            }

            foreach ($games as $game) {
                $player = 'player1';
                $opponent = 'player2';
                $player_user_id = $game->player1_user_id;
                $opponent_user_id = $game->player2_user_id;
                if ($game->player2_user_id == $user_id) {
                    $player = 'player2';
                    $opponent = 'player1';
                    $player_user_id = $game->player2_user_id;
                    $opponent_user_id = $game->player1_user_id;
                }
                $player_shots = $player.'_shots';
                $opponent_shots = $opponent.'_shots';
                $player_first_name = $player.'_first_name';
                $player_last_name = $player.'_last_name';
                $opponent_first_name = $opponent.'_first_name';
                $opponent_last_name = $opponent.'_last_name';
                $game->player = $player_user_id;
                $game->opponent = $opponent_user_id;
                $game->player_shots = $game->$player_shots;
                $game->opponent_shots = $game->$opponent_shots;
                $player_win = 0;
                if ($game->win_user_id == $player_user_id)
                    $player_win = $game->bet;
                elseif ($game->win_user_id == $opponent_user_id)
                    $player_win = 0 - $game->bet;
                $game->player_win = $player_win;

                $opponent_name_ins = 'бот';
                if ($game->pwb == 0) {
                    $opponent_name_ins = $game->$opponent_first_name . ' ' . $game->$opponent_last_name;
                }
                $game->player_name = $game->$player_first_name . ' ' . $game->$player_last_name;
                $game->opponent_name = $opponent_name_ins;
            }

            $data = [
                'user_id' => $user_id,
                'games' => $games,
//                'games_all' => $games->count(),
//                'wins' => $games->where('win_user_id', $user_id)->count(),
//                'not_wins' => $games->where('win_user_id', '!=', $user_id)->where('win_user_id', '!=', null)->count(),
            ];
            return view('admin.sea_battle_user_game_content', $data);

        }
        else return '---';
    }

    public static function rates()
    {
        $data = [
            'title' => 'Морской бой - отзывы',
            'page' => 'sea_battle_rates',
        ];

        return view('admin.bot', $data);
    }

    public static function rates_list()
    {

        $rates = BotGameSeaBattleRates::getRates();

//        dd($rates);
        return Datatables::of($rates)
            ->addIndexColumn()
            ->addColumn('user_id', function($row){
                $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->user_id.'</a>';
                return $result;
            })
//            ->addColumn('fio', function($row){
//                $username = $row->username !== null && $row->username !== '' ? '<br />('.$row->username.')' : '';
//                $result = '<a href="'.route('user', array('user'=>$row->user_id)).'">'.$row->first_name . ' ' . $row->last_name . $username.'</a>';
//                return $result;
//            })
            ->addColumn('game_id', function($row){
//                $result = '<a href="#games" class="games" id="'.$row->user_id.'" data-num="'.$row->games.'">'.$row->games.'</a>';
                $result = $row->game_id;
                return $result;
            })
            ->addColumn('rate', function($row){
                $result = $row->rate;
                return $result;
            })
            ->addColumn('comment', function($row){
                $result = $row->comment;
                return $result;
            })
            ->addColumn('updated_at', function($row){
                $result = $row->updated_at;
                return $result;
            })
            ->rawColumns(['user_id', 'game_id'])
            ->make(true);
    }

}
