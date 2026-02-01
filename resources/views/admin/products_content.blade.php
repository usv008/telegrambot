    <div class="w-100">

        @include('admin.header_content')

        <button type="button" class="btn btn-success day-off-action-add mt-2 mb-3">Добавить</button>

        <div class="container" style="width: 100%; margin: 0 auto; text-align: center;">



        </div>

    </div>

    <script>

        $(function () {

            $('.btn').click(function(e) {
                $("#modal_title").html("Добавление товара");
                $("#modal_body").html("Добавить");
                $("#modal_footer").html("111");
                $("#exampleModalCenter").modal("show");
            });

        });

    </script>
