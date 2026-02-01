<div id="chat-container">

    @include('admin.header_content')
    <div class="row">
        <div id="chatUsers" class="col-1-3">
            @include('admin.chat_users_content')
        </div>
        <div id="chatMessages" class="col-2-3">
            @include('admin.chat_messages_content')
        </div>
    </div>
    <div class="enter-message-container rounded">
        <div class="enter-message p-0">
            <label for="messageText">Отправить сообщение (Enter - отправить, Shift + Enter - перенос на следующую строку)</label>
            <textarea class="md-textarea form-control" id="messageText" placeholder="Введите текст" autofocus></textarea>
            <button id="buttonSendMessage" type="button" class="btn btn-primary">Отправить</button>
        </div>
    </div>

</div>

<script type="text/javascript">

    var update_message = 1;
    var click_protection = 0;

    function loadChatUsers(user_id) {
        $.ajax({
            type: "GET",
            url: "{{ route('load-chat-users') }}",
            data: "_token={{ csrf_token() }}&user_id="+user_id,
            cache: false
        }).done(function(data) {
            $("#chatUsers").html(data);
        }).fail(function() {
            console.log("Произошла ошибка!");
        });
    }

    function loadChatMessages(user_id) {
        $.ajax({
            type: "GET",
            url: "{{ route('load-chat-messages') }}",
            data: "_token={{ csrf_token() }}&user_id="+user_id,
            cache: false
        }).done(function(data) {
            $("#chatMessages").html(data);
            if (update_message === 1) {
                $("#chatMessages").scrollTop(99999999);
                setTimeout(function () {
                    $(".message").removeClass("chat-message-new");
                    $(".message").addClass("chat-message");
                }, 2000);
                setTimeout(function () {
                    readMessages({{ $user_id }});
                }, 1000);
            }
        }).fail(function() {
            console.log("Произошла ошибка!");
        });
    }

    function readMessages(user_id) {
        $.ajax({
            type: "POST",
            url: "{{ route('chat-read-messages') }}",
            data: "_token={{ csrf_token() }}&user_id="+user_id,
            cache: false
        }).done(function(data) {
            console.log(user_id+': сообщения прочитаны');
        }).fail(function() {
            console.log("Произошла ошибка!");
            console.log("_token={{ csrf_token() }}&user_id="+user_id);
        });
    }

    function sendMessageToChat(user_id, text) {
        if (click_protection === 0 && text !== '') {
            $.ajax({
                type: "POST",
                url: "{{ route('send-message-to-chat') }}",
                data: "_token={{ csrf_token() }}&user_id="+user_id+"&text="+text,
                cache: false,
                beforeSend: function() {
                    click_protection = 1;
                }
            }).done(function(data) {
                $("#messageText").val('');
                $("#chatMessages").html(data);
                $("#chatMessages").scrollTop(99999999);
                click_protection = 0;
                $("#buttonSendMessage").prop("disabled", false);
            }).fail(function() {
                alert("Произошла ошибка!");
                click_protection = 0;
                $("#buttonSendMessage").prop("disabled", false);
            });
        }
        else $("#buttonSendMessage").prop("disabled", false);
    }

    $(document).ready( function () {

        setTimeout(function () {
            readMessages({{ $user_id }});
            loadChatUsers({{ $user_id }});
            $(".message").removeClass("chat-message-new");
            $(".message").addClass("chat-message");
        }, 1000);

        $("#messageText").keydown(function(e) {
            if (e.keyCode === 13 && !e.shiftKey) {
                $("#buttonSendMessage").prop("disabled", true);
                var text = $("#messageText").val();
                sendMessageToChat({{ $user_id }}, text);
            }
        });

        $("#buttonSendMessage").click(function(e) {
            e.preventDefault();
            $("#buttonSendMessage").prop("disabled", true);
            var text = $("#messageText").val();
            sendMessageToChat({{ $user_id }}, text);
        });

        $("#chatMessages").scroll(function() {
            if($("#chatMessages").scrollTop() == 0 || ($("#chatMessages").scrollTop() + $('#chatMessages').height() >= $('#chatMessages').prop('scrollHeight') - 1)) {
                update_message = 1;
            }
        });

        setInterval(function(){
            if($("#chatMessages").scrollTop() == 0 || ($("#chatMessages").scrollTop() + $('#chatMessages').height() >= $('#chatMessages').prop('scrollHeight') - 1)) {
                update_message = 1;
            }
            else update_message = 0;
            loadChatMessages({{ $user_id }});
            loadChatUsers({{ $user_id }});
        }, 7000);

        $("#chatMessages").scrollTop(99999999);

    });

</script>
