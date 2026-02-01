<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        <table class="table table-striped data-table" id="feedback_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th class="text-center align-middle" scope="col"></th>
                <th class="text-center align-middle" scope="col"></th>
                <th class="text-center align-middle" scope="col">Всего: {{ $num_users }}</th>
                <th class="text-center align-middle" scope="col">{{ $nps0 }} %</th>
                <th class="text-center align-middle" scope="col">{{ $nps1 }} %</th>
                <th class="text-center align-middle" scope="col">{{ $nps2 }} %</th>
                <th class="text-center align-middle" scope="col">{{ $nps3 }} %</th>
                <th class="text-center align-middle" scope="col"></th>
            </tr>
            <tr>
                <th class="text-center align-middle" scope="col">Дата</th>
                <th class="text-center align-middle" scope="col">ID заказа</th>
                <th class="text-center align-middle" scope="col">ID пользователя</th>
                <th class="text-center align-middle" scope="col">Суши</th>
                <th class="text-center align-middle" scope="col">Пицца</th>
                <th class="text-center align-middle" scope="col">Доставка</th>
                <th class="text-center align-middle" scope="col">Рекомендация</th>
                <th class="text-center align-middle" scope="col">Комментарий</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

        var table = $("#feedback_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('feedback_list') }}",
            columns: [
                { data: 'date_reg', name: 'date_reg' },
                { data: 'order', name: 'order' },
                { data: 'user', name: 'user' },
                { data: 'o0', name: 'o0' },
                { data: 'o1', name: 'o1' },
                { data: 'o2', name: 'o2' },
                { data: 'o3', name: 'o3' },
                { data: 'comment', name: 'comment' }
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
            "order": [[ 0, "desc" ]]
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
                    $("#large_modal_body").html("Произошла ошибка");
                    $("#largeModalCenter").modal("show");
                });
            });

        });

    });

</script>


