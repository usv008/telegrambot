<div class="container w-100 float-left col">
    @php $game_id = 0;
    $games_all = 0;
    $wins = 0;
    $not_wins = 0;
    @endphp
    @foreach($games as $game)
        @if ($game->id !== $game_id)
            @php $game_id = $game->id;
            $games_all++;
            $wins = $game->win_user_id == $user_id ? $wins + 1 : $wins;
            $not_wins = $game->win_user_id != $user_id && $game->win_user_id !== null ? $not_wins + 1 : $not_wins;
            @endphp
        @endif
    @endforeach
    <h4 class="mb-4 mt-2">
        <table align="center">
            <tr>
                <td>Всего игр: {{ $games_all }}</td>
                <td class="pl-5">Выигрышей: {{ $wins }}</td>
                <td class="pl-5">Проигрышей: <p style="color: #b91d19; padding: 0; margin: 0; display: inline-block;">{{ $not_wins }}</p></td>
            </tr>
        </table>
    </h4>

    <table class="table table-striped data-table" style="width: 100%;" align="center">
        <thead>
        <tr>
            <th scope="col" class="text-center align-middle">ID</th>
            <th scope="col" class="text-center align-middle">Дата</th>
            <th scope="col" class="text-center align-middle">Игрок</th>
            <th scope="col" class="text-center align-middle">Соперник</th>
            <th scope="col" class="text-center align-middle">Ходов игрока</th>
            <th scope="col" class="text-center align-middle">Ходов соперника</th>
            <th scope="col" class="text-center align-middle">Выигрыш</th>
            <th scope="col" class="text-center align-middle">Баланс КБ, грн</th>
        </tr>
        </thead>
        <tbody>
        @php $game_id = 0; @endphp
        @foreach($games as $game)
            @if ($game->id !== $game_id)
                @php $game_id = $game->id; @endphp
                <tr>
                    <td style="text-align: center;">{{ $game->id }}</td>
                    <td style="text-align: center;">{{ date("d.m.Y H:i:s", strtotime($game->updated_at)) }}</td>
                    <td style="text-align: center;">{!! $game->player_name !!}</td>
                    <td style="text-align: center;">{{ $game->opponent_name }}</td>
                    <td style="text-align: center;">{{ $game->player_shots }}</td>
                    <td style="text-align: center;">{{ $game->opponent_shots }}</td>
                    <td style="text-align: center; {{ $game->player_win < 0 ? 'color: #b91d19; ' : '' }}">{{ $game->player_win }}</td>
                    <td style="text-align: center;">{{ $game->cashback }}</td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

</div>
