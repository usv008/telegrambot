@foreach($users_messages as $user)
    <a href="{{ route('chat-user', ['user_id'=>$user->user_id]) }}">
        <div class="chat-user d-block p-3 border-right border-bottom{{ $user->user_id == $user_id ? ' bg-success text-white' : '' }}">
            <div class="">{!! $user->readed == 0 ? '<b>' : '' !!}{{ $user->first_name }} {{ $user->last_name }}{{ isset($user->username) && $user->username !== '' ? ' ('.$user->username.')' : '' }}{{ isset($user->lang) &&$user->lang !== '' ? ' ['.$user->lang.']' : '' }}{!! $user->readed == 0 ? '</b>' : '' !!}</div>
            <div class="chat-new-message">{!! $user->readed == 0 ? '🔵' : '' !!}</div>
            <div class="{{ $user->user_id == $user_id ? 'chat-updated-at-selected' : 'chat-updated-at' }}">{{ date("d.m.Y H:i:s", strtotime($user->updated_at)) }}</div>
            {{--                    <div class="p-3" style="cursor: pointer; display: inline-block; font-size: 14px;">{{ $messages->where('user_id', $user->user_id)->count() > 0 ? $messages->where('user_id', $user->user_id)->sortByDesc('id')->first()['text'] : '' }}</div>--}}
        </div>
    </a>
@endforeach
