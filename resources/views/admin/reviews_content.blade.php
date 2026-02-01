<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        <table class="table table-striped data-table" id="reviews_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th class="text-center align-middle" scope="col">Всего: {{ $num }}</th>
                <th class="text-center align-middle" scope="col"></th>
                <th class="text-center align-middle" scope="col"></th>
                <th class="text-center align-middle" scope="col"></th>
                <th class="text-center align-middle" scope="col"></th>
                <th class="text-center align-middle" scope="col"></th>
            </tr>
            <tr>
                <th class="text-center align-middle" scope="col">ID пользователя</th>
                <th class="text-center align-middle" scope="col">Имя</th>
                <th class="text-center align-middle" scope="col">Отзыв</th>
                <th class="text-center align-middle" scope="col">Статус</th>
                <th class="text-center align-middle" scope="col">Дата</th>
                <th class="text-center align-middle" scope="col">Удалить</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

        var table = $("#reviews_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('reviews_list') }}",
            columns: [
                { data: 'user', name: 'user_id' },
                { data: 'user_name', name: 'user_name' },
                { data: 'review', name: 'review' },
                { data: 'status_change', name: 'status_change' },
                { data: 'date_reg', name: 'date_reg' },
                { data: 'delete', name: 'delete' }
            ],
            // destroy: true,
            stateSave: true,
            "aLengthMenu": [[10, 25, 50, 75, -1], [10, 25, 50, 75, "Все"]],
            "iDisplayLength": 10,
            "language": {
                "processing": "🚀 Загружаю...",
                "lengthMenu": "Показывать по _MENU_ записей на странице",
                "zeroRecords": "Ничего не найдено",
                // "info": "Страница _PAGE_ из _PAGES_",
                "info": "Записи с _START_ по _END_ из _TOTAL_",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(фильтр: _TOTAL_ из _MAX_ записей)",
                "search": "Поиск",
                "paginate": {
                    "first":      "Первая",
                    "last":       "Последняя",
                    "next":       "Следующая",
                    "previous":   "Предыдущая"
                }
            },
//             "columnDefs": [
//                 { "targets": [ 3 ], "visible": false },
//                 { "type": "string", "targets": [3, 4, 5] },
//             ]//,
            "order": [[ 4, "desc" ]]
        });

        table.on( 'draw', function () {

            $('.order').click(function(e) {
                e.preventDefault();
                var order_id = this.id;
                $.ajax({
                    type: "POST",
                    url: "{{ route('order') }}",
                    data: "_token={{ csrf_token() }}&order_id="+order_id,
                    cache: false
                }).done(function(modaldata) {
                    $("#large_modal_title").html("Заказ №"+order_id);
                    $("#large_modal_body").html(modaldata);
                    $("#large_modal_footer").html("");

                    $("#largeModalCenter").modal("show");
                    // $("#modal_dialog").html(modaldata);
                }).fail(function() {
                    $("#modal_body").html("Произошла ошибка");
                    $("#largeModalCenter").modal("show");
                });
            });

            $( ".change_status" ).click(function() {
                var id = this.id;
                $.ajax({
                    type: "POST",
                    url: "{{ route('change_status') }}",
                    data: "_token={{ csrf_token() }}&id="+id,
                    cache: false
                }).done(function(changedata) {
                    $("#"+id).html(changedata);
                }).fail(function() {
                    $("#modal_header").html("Уууупс...");
                    $("#modal_body").html("Произошла ошибка");
                    $("#exampleModalCenter").modal("show");
                });
            });

            $( ".review_delete" ).click(function() {
                var id = this.id;
                $("#modal_header").html("Удаление отзыва");
                $.ajax({
                    type: "POST",
                    url: "{{ route('review_delete') }}",
                    data: "_token={{ csrf_token() }}&id="+id,
                    cache: false
                }).done(function(deletedata) {
                    $("#modal_body").html(deletedata);
                    $("#exampleModalCenter").modal("show");
                }).fail(function() {
                    $("#modal_body").html("Произошла ошибка");
                    $("#exampleModalCenter").modal("show");
                });
            });

        });

    });

</script>
