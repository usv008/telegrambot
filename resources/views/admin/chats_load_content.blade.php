@foreach($users_messages as $user)
    <a href="{{ route('chat-user', ['user_id'=>$user->user_id]) }}">
        <div class="chat-users-and-messages border-bottom" id="{{ $user->user_id }}">
            <div class="chat-users p-3 border-right">
                <div>{!! $user->readed == 0 ? '<b>' : '' !!}{{ $user->first_name }} {{ $user->last_name }}{{ isset($user->username) && $user->username !== '' ? ' ('.$user->username.')' : '' }}{{ isset($user->lang) &&$user->lang !== '' ? ' ['.$user->lang.']' : '' }}{!! $user->readed == 0 ? '</b>' : '' !!}</div>
                <div class="chat-new-message">{!! $user->readed == 0 ? '🔵' : '' !!}</div>
                <div class="chat-updated-at">{{ date("d.m.Y H:i:s", strtotime($user->updated_at)) }}</div>
            </div>
            <div class="chat-users-messages p-3">
                @if ($messages->where('user_id', $user->user_id)->count() > 0)
                    @if ($messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['text'] !== null && $messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['text'] !== '')
                        {{ mb_strimwidth($messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['text'], 0, 80, "...") }}
                    @elseif ($messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['photo'] !== null && $messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['photo'] !== '')
                        <img src="{{ 'https://api.telegram.org/file/bot' . env('PHP_TELEGRAM_BOT_API_KEY') . '/' . $messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['photo'] }}" />
                    @endif
                @endif
            </div>
        </div>
    </a>
@endforeach
