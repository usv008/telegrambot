<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">
        <h4 class="mb-4 mt-2">
            <table align="center">
                <tr>
                    <td>Всего игр:<br />{{ $games->count() }}</td>
                    <td class="pl-5">Выигрышей:<br />{{ $games->where('win_user_id', '!=', null)->count() }}</td>
                    <td class="pl-5">Ничья:<br />{{ $games->where('win_user_id', null)->where('late', 0)->count() }}</td>
                    <td class="pl-5">Отменено:<br />{{ $games->where('late', 1)->count() }}</td>
                    <td class="pl-5">КБ бота:<br />КБ+ {{ $bot_cashback_plus }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;КБ- {{ $bot_cashback_minus }}</td>
                </tr>
            </table>
        </h4>
        <table class="table table-striped data-table" id="sea_battle_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th scope="col" class="text-center align-middle">USER ID</th>
                <th scope="col" class="text-center align-middle">Имя, Фамилия</th>
                <th scope="col" class="text-center align-middle">Игры</th>
                <th scope="col" class="text-center align-middle">Побед</th>
                <th scope="col" class="text-center align-middle">КБ+ (грн)</th>
                <th scope="col" class="text-center align-middle">КБ- (грн)</th>
                <th scope="col" class="text-center align-middle">Профит</th>
                <th scope="col" class="text-center align-middle">Обновлено</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

        var table = $("#sea_battle_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('sea-battle-list') }}",
            columns: [
                { data: 'user_id', name: 'user_id' },
                { data: 'fio', name: 'fio' },
                { data: 'games', name: 'games' },
                { data: 'wins', name: 'wins' },
                { data: 'cashback_plus', name: 'cashback_plus' },
                { data: 'cashback_minus', name: 'cashback_minus' },
                { data: 'profit', name: 'profit' },
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
            "columnDefs": [
                // { "targets": [ 3 ], "visible": false },
                // { "type": "html-num", "targets": [0, 1, 2] },
                { "type": "html-num", "targets": 0 },
            ],
            "order": [ 7, "desc" ]
        });

        table.on( 'draw', function () {

            $('.games').click(function(e) {
                e.preventDefault();
                $("#large_modal_title").html("Игры");
                $("#large_modal_footer").html("");
                var user_id = this.id;
                $.ajax({
                    type: "POST",
                    url: "{{ route('sea-battle-game') }}",
                    data: "_token={{ csrf_token() }}&user_id="+user_id,
                    cache: false
                }).done(function(modaldata) {
                    $("#large_modal_body").html(modaldata);
                    $("#largeModalCenter").modal("show");
                }).fail(function() {
                    $("#large_modal_body").html("Произошла ошибка");
                    $("#largeModalCenter").modal("show");
                });
            });

        });

    });

</script>
