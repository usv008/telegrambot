<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        <table class="table table-striped data-table" id="history_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th class="text-center align-middle" scope="col">ID</th>
                <th class="text-center align-middle" scope="col">ID пользователя</th>
                <th class="text-center align-middle" scope="col">Пользователь</th>
                <th class="text-center align-middle" scope="col">Тип</th>
                <th class="text-center align-middle" scope="col">Событие</th>
                <th class="text-center align-middle" scope="col">Дата</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>

    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

        var table = $("#history_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('users_history_list') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'user', name: 'user' },
                { data: 'fio', name: 'fio' },
                { data: 'type', name: 'type' },
                { data: 'user_event', name: 'user_event', className: 'text-left' },
                { data: 'date_z', name: 'date_z' }
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
            "order": [[ 5, "desc" ]]
        });

        table.on( 'draw', function () {
            //
        });

    });

</script>
