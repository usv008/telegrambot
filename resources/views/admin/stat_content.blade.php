<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        <form class="map_filter_form" action="{{ route('stat') }}" method="get">
            <div class="form-group text-center" style="width:100%; height: 60px;">

                <div class="align-top text-center ml-0 d-inline-block" style="width:290px;">

                    с <input type="text" id="date_start" name="date_start" class="map_filter form-control datepicker d-inline-block p-1 text-center" style="width:125px;" value="{{ date("d.m.Y", strtotime($date_start)) }}" />
                    по <input type="text" id="date_end" name="date_end" class="map_filter form-control datepicker d-inline-block p-1 text-center" style="width:125px;" value="{{ date("d.m.Y", strtotime($date_end)) }}" />

                </div>

                <div class="text-left ml-0 d-inline-block align-middle" style="width:150px;">

                    <div class="form-check align-middle">
                        <button type="submit" class="btn btn-primary">Применить</button>
                    </div>

                </div>

            </div>

        </form>

        {{--        <div class="p-4 shadow bg-white rounded">--}}
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

</script>
