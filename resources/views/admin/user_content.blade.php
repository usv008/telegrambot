<div class="main" style="margin-left: 200px;">
    <div style="width: 99%;">
        @include('admin.header_content')

        Пригласил: {!! $share_user !== null ? '<a href="'.route('user', ['user'=>$share_user->user_id]).'">'.$share_user->user_id.'</a>' : '' !!}
        <div class="divTable">
            <div class="divTableBody">

                <div class="divTableRow">

                    <div class="divTableCellNo">

                        <div class="row">
                            @foreach($photos as $key => $photo)
                                <div class="d-inline p-1"><a href="{{ $photo }}" data-toggle="lightbox"><img class="rounded shadow-sm user_image" id="user_image" style="cursor:pointer;" src="{{ $photos_small[$key] }}" width="150" class="img-fluid" /></a></div>
                            @endforeach
                        </div>

                        <div class="row pt-2">
                            <div class="col-md-3">Имя:</div>
                            <div class="col-md-8">{{ isset($data->first_name) && $data->first_name !== null ? $data->first_name : '' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Фамилия:</div>
                            <div class="col-md-8">{{ isset($data->last_name) && $data->last_name !== null ? $data->last_name : '' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">Ник:</div>
                            <div class="col-md-8">{{ isset($data->username) && $data->username !== null ? $data->username : '' }}</div>
                        </div>

{{--                        <div class="row">--}}
{{--                            <div class="col-md-3">Регистрация:</div>--}}
{{--                            <div class="col-md-8">{{ isset($data->created_at) && $data->created_at !== null ? date("d.m.Y H:i:s", strtotime($data->created_at)) : '' }}</div>--}}
{{--                        </div>--}}
{{--                        <div class="row">--}}
{{--                            <div class="col-md-3">Посещение:</div>--}}
{{--                            <div class="col-md-8">{{ isset($data->updated_at) && $data->updated_at !== null ? date("d.m.Y H:i:s", strtotime($data->updated_at)) : '' }}</div>--}}
{{--                        </div>--}}

                    </div>

                    <div class="divTableCellNoCenter">
                        <a href="{{ route('chat-user', ['user_id' => $data->user_id]) }}" class="btn btn-primary">Отправить сообщение</a>
{{--                        <form action="test.php" method="post" style="text-align: center;">--}}
{{--                            <textarea class="md-textarea form-control" rows="5" cols="40" name="text" id="message_text" style="font-size: 13px; resize: none;" placeholder="Текст сообщения"></textarea>--}}
{{--                            <button type="button" class="btn btn-primary mt-1 w-100 send_message_button">Отправить</button>--}}
{{--                        </form>--}}
                    </div>

                </div>
            </div>

        </div>

        <table class="table mt-4 border table-bordered">
            <thead>
            <tr>
                <th scope="col" class="text-center">Регистрация</th>
                <th scope="col" class="text-center">Посещение</th>
                <th scope="col" class="text-center">Заказов</th>
                <th scope="col" class="text-center">Сумма</th>
                <th scope="col" class="text-center">КБ баланс</th>
                <th scope="col" class="text-center">Пригласил</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                {{--                                <th scope="row">1</th>--}}
                <td class="text-center">{{ isset($data->created_at) && $data->created_at !== null ? date("d.m.Y H:i:s", strtotime($data->created_at)) : '' }}</td>
                <td class="text-center">{{ isset($data->updated_at) && $data->updated_at !== null ? date("d.m.Y H:i:s", strtotime($data->updated_at)) : '' }}</td>
                <td class="text-center">{{ $orders_count }}</td>
                <td class="text-center">{{ $orders->sum('order_price') }}</td>
                <td class="text-center">{{ $data->cashback }}</td>
                <td class="text-center">{{ $referrals->count() }}</td>
            </tr>
            </tbody>
        </table>

        <div class="w-100 mt-4" style="width: 98%; margin: 0 auto; text-align: center;">

            {{--            <h3>Заказы ({{ count($orders) }} на сумму {{ $orders->sum('order_price') }}грн)</h3>--}}
            <table class="table table-striped data-table" id="user_orders_datatable" style="width: 100%;">
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
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>

        </div>


    </div>
</div>

<script type="text/javascript">

    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });

    $(document).ready( function () {

        $('.send_message_button').click(function(e) {
            e.preventDefault();
            var text = $("#message_text").val();
            $.ajax({
                type: "POST",
                url: "{{ route('send_message_to_user') }}",
                data: "_token={{ csrf_token() }}&user_id={{ $user_id }}&text="+text,
                cache: false
            }).done(function(data) {
                alert(data);
            }).fail(function() {
                alert("Произошла ошибка!");
            });
        });

            @php $data_user_id = ['user_id' => $user_id]; @endphp

        var table = $("#user_orders_datatable").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_orders_list', $data_user_id) }}",
                columns: [
                    { data: 'id', name: 'order_id' },
                    { data: 'name', name: 'order_name' },
                    { data: 'phone', name: 'order_phone' },
                    { data: 'delivery_name', name: 'order_delivery' },
                    { data: 'address', name: 'order_addr' },
                    { data: 'price_with_discount', name: 'order_price' },
                    { data: 'order_cb_out', name: 'order_cb_out' },
                    { data: 'order_cb_in', name: 'order_cb_in' },
                    { data: 'pay', name: 'order_oplata' },
                    { data: 'comment', name: 'order_comment' },
                    { data: 'created_at', name: 'order_date' },
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
                    $("#modal_body").html("Произошла ошибка");
                    $("#largeModalCenter").modal("show");
                });
            });

        });



    });

</script>

