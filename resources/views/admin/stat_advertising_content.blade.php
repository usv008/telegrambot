<div style="width: 99%;">

    @include('admin.header_content')
    @if (count($orders_text) > 0)
        <b>Заказы:</b>
        @foreach($orders_text as $order_text)
            {{ $order_text }};
        @endforeach
    @endif
    <button type="button" class="button_create btn btn-success">Создать</button>
    <button type="button" class="button_edit btn btn-primary" id="post_"{{ $channel->id }}>Редактировать</button>
    <button type="button" class="button_delete btn btn-danger" id="post_"{{ $channel->id }}>Удалить</button>
    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        <div class="d-inline-block align-text-top mb-4{{ $channel->bonus > 0 ? ' pr-5 m-3' : ''}}">
            Ссылка: <b>https://t.me/{{ env('PHP_TELEGRAM_BOT_NAME') }}?start={{ $channel->url }}</b><br />
            <img src="https://api.qrserver.com/v1/create-qr-code/?data=https://t.me/{{ env('PHP_TELEGRAM_BOT_NAME') }}?start={{ $channel->url }}&size=200x200" alt="" title="" />
        </div>
        <div class="d-inline-block align-text-top mb-4{{ $channel->bonus > 0 ? ' pr-5 m-3' : ''}}">
            @if ($channel->bonus > 0 || $channel->product_present > 0)
                @if ($channel->product_present > 0)
                    Товар в подарок (ID варианта): {{ $channel->product_present_variant_id }}<br />
                @else
                    Начислить бонусов: {{ $channel->bonus }}<br />
                @endif
                {!! $channel->limit_in > 0 ? 'Лимит: '.$channel->limit_in_value.' переходов<br />' : '' !!}
                @if ($text !== null)
                    Текст на русском: {{ $text->text_value_ru }}<br />
                    Текст на украинском: {{ $text->text_value_uk }}<br />
                    Текст на английском: {{ $text->text_value_en }}<br />
                @endif
            @endif
        </div>
        <br />
        <form class="map_filter_form" action="{{ route('stat_advertising') }}" method="get">
            <div class="form-group text-center" style="width:100%; height: 60px;">

                <div class="align-top text-center ml-0 d-inline-block" style="width:290px;">

                    с <input type="text" id="date_start" name="date_start" class="map_filter form-control datepicker d-inline-block p-1 text-center" style="width:125px;" value="{{ date("d.m.Y", strtotime($date_start)) }}" />
                    по <input type="text" id="date_end" name="date_end" class="map_filter form-control datepicker d-inline-block p-1 text-center" style="width:125px;" value="{{ date("d.m.Y", strtotime($date_end)) }}" />
                    <select name="channel" class="browser-default custom-select form-control p-1 m-2">
                        @foreach($channels as $value)
                            <option value="{{ $value['id'] }}" {{ $value['id'] == $channel->id ? ' selected' : ''}}>{{ $value['name'] }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="form-control btn btn-primary">Показать</button>
                </div>

            </div>

        </form>

        {{--        <div class="p-4 shadow bg-white rounded">--}}
        <br />
        <br />
        <br />
        <canvas id="myChart"></canvas>
        {{--        </div>--}}

    </div>

</div>

<script type="text/javascript">

    var ctx = document.getElementById('myChart').getContext('2d');
    var chart = new Chart(ctx, {
        // The type of chart we want to create
        type: 'bar',

        // The data for our dataset
        data: {
            labels: [{!! $days !!}],
            datasets: [{
                label: "Пользователи",
                backgroundColor: 'rgb(66,139,202)',
                borderColor: 'rgb(66,139,202)',
                data: [{!! $users !!}],
            },
                {
                    label: "Заказы",
                    backgroundColor: 'rgb(92,184,92)',
                    borderColor: 'rgb(92,184,92)',
                    data: [{!! $orders !!}],
                }
            ]
        },
        options: {
            legend: {
                display: true,
                labels: {
                    fontColor: 'rgb(66,139,202)'
                }
            }
        }

    });

    // $(document).ready( function () {
    //
    //
    // });

    $(function(){
        $("#date_start").datepicker();
        $("#date_end").datepicker();
        $("form :input").attr("autocomplete", "off");
    });

    /* Локализация datepicker */
    $.datepicker.regional['ru'] = {
        closeText: 'Закрыть',
        prevText: 'Предыдущий',
        nextText: 'Следующий',
        currentText: 'Сегодня',
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Не',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['ru']);

    $('.button_create').click(function(e) {
        e.preventDefault();
        var order_id = this.id;

        $("#modal_title_h").html("Создание рекламного канала");
        $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');

        $.ajax({
            type: "POST",
            url: "{{ route('show_advertising_form_add') }}",
            data: "_token={{ csrf_token() }}",
            cache: false
        }).done(function(modaldata) {
            $("#modal_body").html(modaldata);
        }).fail(function() {
            $("#modal_body").html("Произошла ошибка");
        });

        $('#exampleModalCenter').modal({
            backdrop: 'static',
            keyboard: false
        });
        $("#exampleModalCenter").modal("show");

    });

    $('.button_edit').click(function(e) {
        e.preventDefault();
        var order_id = this.id;

        $("#modal_title_h").html("Изменение рекламного канала");
        $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');

        $.ajax({
            type: "POST",
            url: "{{ route('show_advertising_form_edit') }}",
            data: "_token={{ csrf_token() }}&channel_id={{ $channel->id }}",
            cache: false
        }).done(function(modaldata) {
            $("#modal_body").html(modaldata);
        }).fail(function() {
            $("#modal_body").html("Произошла ошибка");
        });

        $('#exampleModalCenter').modal({
            backdrop: 'static',
            keyboard: false
        });
        $("#exampleModalCenter").modal("show");

    });

    $('.button_delete').click(function(e) {
        e.preventDefault();
        var order_id = this.id;

        $("#modal_title_h").html("Удаление рекламного канала");
        $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');

        $.ajax({
            type: "POST",
            url: "{{ route('show_advertising_form_delete') }}",
            data: "_token={{ csrf_token() }}&channel_id={{ $channel->id }}",
            cache: false
        }).done(function(modaldata) {
            $("#modal_body").html(modaldata);
        }).fail(function() {
            $("#modal_body").html("Произошла ошибка");
        });

        $("#exampleModalCenter").modal("show");

    });

</script>
