<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        <table class="table table-striped data-table" id="orders_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Доставка</th>
                <th>Адрес</th>
                <th>Цена</th>
                <th>КБ-</th>
                <th>КБ+</th>
                <th>Оплата</th>
                <th>Комментарий</th>
                <th>Дата</th>
                <th>Удалить</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

        var table = $("#orders_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('orders_list') }}",
            columns: [
                { data: 'order_id', name: 'order_id' },
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'delivery_name', name: 'delivery_name' },
                { data: 'address', name: 'address' },
                { data: 'price_with_discount', name: 'price_with_discount' },
                { data: 'cb_out', name: 'cb_out' },
                { data: 'cb_in', name: 'cb_in' },
                { data: 'pay', name: 'pay' },
                { data: 'comment', name: 'comment' },
                { data: 'created_at', name: 'created_at' },
                { data: 'order_delete', name: 'order_delete' }
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
//             "order": [[ 10, "desc" ]]
            "order": [[ 0, "desc" ]]
        });

        table.on( 'draw', function () {

            $('.order').click(function(e) {
                e.preventDefault();
                var order_id = this.id;
                $("#large_modal_title").html("Заказ №"+order_id);
                $("#large_modal_footer").html("");
                $.ajax({
                    type: "POST",
                    url: "{{ route('order') }}",
                    data: "_token={{ csrf_token() }}&order_id="+order_id,
                    cache: false
                }).done(function(modaldata) {
                    $("#large_modal_body").html(modaldata);
                    $("#largeModalCenter").modal("show");
                    // $("#modal_dialog").html(modaldata);
                }).fail(function() {
                    $("#large_modal_body").html("Произошла ошибка");
                    $("#largeModalCenter").modal("show");
                });
            });

            $( ".order_delete" ).click(function() {
                var id = this.id;
                $("#modal_header").html("Удаление заказа");
                $.ajax({
                    type: "POST",
                    url: "{{ route('order_delete') }}",
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
