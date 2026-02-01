@foreach($messages as $message)
    <div class="message-container row">
        <div class="messages-avatar col-1-10">
            @if ($message->operator == 1)
                <img src="{{ url('assets/img/logo_new.png') }}" />
            @else
                <a href="{{ route('user', ['user' => $message->user_id]) }}">
                    <img src="{{ isset($photo_small_arr[0]) ? $photo_small_arr[0] : url('assets/img/no_user.png') }}" />
                </a>
            @endif
        </div>
        <div class="message {{ $message->operator == 0 && $message->readed == 0 ? 'chat-message-new' : 'chat-message' }} col-9-10">
            @if ($message->text !== null && $message->text !== '' && $message->photo !== null && $message->photo !== '')
                <img src="{{ 'https://api.telegram.org/file/bot' . env('PHP_TELEGRAM_BOT_API_KEY') . '/' . $message->photo }}" /><br />
                {{ $message->text }}
            @elseif ($message->text !== null && $message->text !== '')
                {{ $message->text }}
            @elseif ($message->photo !== null && $message->photo !== '')
                <img src="{{ 'https://api.telegram.org/file/bot' . env('PHP_TELEGRAM_BOT_API_KEY') . '/' . $message->photo }}" />
            @endif
            <div class="chat-message-created-at">{{ date("d.m.Y H:i:s", strtotime($message->created_at)) }}</div>
            <div class="chat-message-author">{{ $message->name }}</div>
        </div>
    </div>
@endforeach
