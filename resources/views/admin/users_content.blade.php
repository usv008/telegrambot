<div style="width: 99%;">

        @include('admin.header_content')
    <form>
        <div class="d-inline">
        <input id="noactive_users" name="noactive_users" type="checkbox" value="1" onchange="this.form.submit()"{{ $noactive_users == 1 ? " checked" : "" }}>
        <label for="noactive_users">неактивные пользователи</label>
        </div>
        <div class="d-inline ml-3">
        <input id="all_users" name="all_users" type="checkbox" value="1" onchange="this.form.submit()"{{ $all_users == 1 ? " checked" : "" }}>
        <label for="all_users">все пользователи</label>
        </div>
    </form>
        <div class="w-100" style="width: 100%; margin: 0 auto; text-align: center;">

            <table class="table table-striped data-table" id="users_datatable" style="width: 100%;">
                <thead>
                <tr>
                    <th>User ID</th>
                    <th>Город</th>
                    <th>Имя</th>
                    <th>Заказов</th>
                    <th>На сумму</th>
                    <th>Ср. чек</th>
                    <th>Кешбэк</th>
                    <th>Телефон</th>
                    <th>Активный</th>
                    <th>Дата создания</th>
                    <th>Дата обновления</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </div>

    </div>

    <script type="text/javascript">

        $(document).ready( function () {

            var table = $("#users_datatable").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    'url' : "{{ route('users_list') }}",
                    'data' : {
                        'noactive_users' : {{ $noactive_users }},
                        'all_users' : {{ $all_users }}
                    }
                },
                columns: [
                    { data: 'user_id', name: 'user_id' },
                    { data: 'city', name: 'city' },
                    { data: 'fio', name: 'fio' },
                    { data: 'num_orders', name: 'num_orders' },
                    { data: 'sum_price', name: 'sum_price' },
                    { data: 'avg_price', name: 'avg_price' },
                    { data: 'cashback', name: 'cashback' },
                    { data: 'phone', name: 'phone' },
                    { data: 'active', name: 'active' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'updated_at', name: 'updated_at' }
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
                //"order": [[ 5, "desc" ], [ 2, "asc" ]]
                "order": [[ 10, "desc" ]]
            });

            table.on( 'draw', function () {
                // alert('draw');
            });

        });

    </script>
