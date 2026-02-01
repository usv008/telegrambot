<div id="chat-container">

    @include('admin.header_content')
    <div id="chats" class="border rounded">
        @include('admin.chats_load_content')
    </div>
</div>

<script type="text/javascript">

    function loadChats() {
        $.ajax({
            type: "GET",
            url: "{{ route('load-chats') }}",
            data: "_token={{ csrf_token() }}",
            cache: false
        }).done(function(data) {
            $("#chats").html(data);
        }).fail(function() {
            console.log("Произошла ошибка!");
        });
    }

    $(document).ready( function () {

        setInterval(function(){
            loadChats();
        }, 5000);

    });

</script>
