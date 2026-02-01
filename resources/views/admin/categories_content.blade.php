    <div class="w-100">

        @include('admin.header_content')

        <button id="categories_add" type="button" class="btn btn-success day-off-action-add mt-2 mb-3">Добавить</button>

        <div class="container" style="width: 100%; margin: 0 auto; text-align: center;">

        </div>

    </div>

    <script type="text/javascript">

        $(function () {

            // $('.btn').click(function(e) {
            //     $("#modal_title").html("Добавление категории");
            //     $("#modal_body").html("Добавить");
            //     $("#modal_footer").html("");
            //     $("#exampleModalCenter").modal("show");
            // });

            $('#categories_add').click(function(e) {

                $.ajax({
                    type: "POST",
                    url: "{{ route('showmodaldialog') }}",
                    data: "_token={{ csrf_token() }}&action=categories_add",
                    cache: false
                }).done(function(modaldata) {
                    $("#modal_dialog").html(modaldata);
                    $("#exampleModalCenter").modal("show");
                }).fail(function() {
                    $("#modal_body").html("Произошла ошибка");
                    $("#exampleModalCenter").modal("show");
                });
            });



        });

    </script>
