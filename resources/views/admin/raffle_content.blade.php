<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">
{{--        <button id="rafflePizzas" class="btn btn-primary">Пиццы для выигрыша</button>--}}
        <h4 class="mb-4 mt-2">Побед: {{ $wins }}, победителей: {{ $users_win }}, попыток: {{ $attempts }}, процент: {{ $procent }}%</h4>

        <table class="table table-striped data-table" id="raffle_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th scope="col" class="text-center align-middle">ID</th>
                <th scope="col" class="text-center align-middle">Имя, Фамилия</th>
                <th scope="col" class="text-center align-middle">Потрачено попыток</th>
                <th scope="col" class="text-center align-middle">Выиграл</th>
                <th scope="col" class="text-center align-middle">Пригласил</th>
                <th scope="col" class="text-center align-middle">Осталось попыток</th>
                <th scope="col" class="text-center align-middle">Числится</th>
                <th scope="col" class="text-center align-middle">В корзине</th>
                <th scope="col" class="text-center align-middle">Регистрация</th>
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

        $('#rafflePizzas').click(function(e) {
            e.preventDefault();
            $("#large_modal_title").html("Пиццы для выигрыша");
            $("#large_modal_footer").html("");
            // var user_id = this.id;
            $.ajax({
                type: "GET",
                url: "{{ route('raffle_pizzas') }}",
                data: "_token={{ csrf_token() }}",
                cache: false
            }).done(function(modaldata) {
                $("#large_modal_body").html(modaldata);
                $("#largeModalCenter").modal("show");
            }).fail(function() {
                $("#large_modal_body").html("Произошла ошибка");
                $("#largeModalCenter").modal("show");
            });
        });

        var table = $("#raffle_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('raffle_list') }}",
            columns: [
                { data: 'user', name: 'user' },
                { data: 'fio', name: 'fio' },
                { data: 'attempts', name: 'attempts' },
                { data: 'wins', name: 'wins' },
                { data: 'guests', name: 'guests' },
                { data: 'raffle_try', name: 'raffle_try' },
                { data: 'win', name: 'win' },
                { data: 'cart', name: 'cart' },
                { data: 'date_reg', name: 'date_reg' },
                { data: 'date_edit', name: 'date_edit' }
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
                { "type": "html-num", "targets": [0, 1, 2] },
            ],
            "order": [ 9, "desc" ]
        });

        table.on( 'draw', function () {

            $('.attempts').click(function(e) {
                e.preventDefault();
                $("#large_modal_title").html("Потраченные попытки");
                $("#large_modal_footer").html("");
                var user_id = this.id;
                $.ajax({
                    type: "POST",
                    url: "{{ route('raffle_attempts') }}",
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
