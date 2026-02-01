<div class="container" style="min-width: 380px;">

    <h3>{{ $title }}</h3>

    {{--    @php--}}
    {{--    $point_id = isset($input['point']) && $input['point'] !== null && ($input['point'] == 5 || $input['point'] == 10) ? $input['point'] : 5;--}}
    {{--    $points = [5 => 'Комса', 10 => 'Палермо'];--}}
    {{--    @endphp--}}

{{--        <form action="{{ route('archi') }}" method="get">--}}
{{--            <select name="point" class="form-control" onchange="this.form.submit()">--}}
{{--                    <option disabled>Выберите точку</option>--}}
{{--                @foreach ($points as $key => $value)--}}
{{--                    @php--}}
{{--                    $selected = $key == $point_id ? ' selected' : '';--}}
{{--                    @endphp--}}
{{--                    <option{{ $selected }} value="{{ $key }}">{{ $value }}</option>--}}
{{--                @endforeach--}}
{{--            </select>--}}
{{--        </form>--}}

    {{--    <p>{{ json_encode($input) }}</p>--}}
    <div class="container" style="width: 100%; margin: 0 auto; text-align: center;">

        <div class="row">

            <div class="col border-right border-bottom"><h4>Комса</h4></div>
            <div class="col border-bottom"><h4>Палермо</h4></div>


            <div class="w-100"></div>

            {{--        <div class="row">--}}

            @foreach($hours_table as $key => $hour_table)


                <div class="col border-right border-bottom d-inline">

                    <div class="row d-flex align-content-center flex-wrap">

                        <div class="col-sm-1 rounded text-center p-1 m-1 align-self-center font-weight-bold" id="hour_5_{{ $key }}" style="min-width: 70px;">{{ $hour_table }}</div>

                        <div class="col-sm-auto text-left align-self-center">

                            @php

                                $count_orders_all = 0;
                                $count_pizza_all = 0;

                                list($count_orders, $count_pizza) = \App\Http\Controllers\ArchiController::get_orders_table($res5_7, $hour_table, 7);
                                $count_orders_all += $count_orders;
                                $count_pizza_all += $count_pizza;

                                list($count_orders, $count_pizza) = \App\Http\Controllers\ArchiController::get_orders_table($res5_6, $hour_table, 6);
                                $count_orders_all += $count_orders;
                                $count_pizza_all += $count_pizza;

                                list($count_orders, $count_pizza) = \App\Http\Controllers\ArchiController::get_orders_table($res5_4, $hour_table, 4);
                                $count_orders_all += $count_orders;
                                $count_pizza_all += $count_pizza;

                                $bg_orders_ins = $count_orders_all >= 6 ? ' bg-danger text-white' : '';
                                $bg_pizza_ins = $count_pizza_all >= 15 ? ' bg-danger text-white' : '';

                                $bg_hours_ins = '';
                                $bg_hours_ins = $bg_orders_ins !== '' ? $bg_orders_ins : $bg_hours_ins;
                                $bg_hours_ins = $bg_pizza_ins !== '' ? $bg_pizza_ins : $bg_hours_ins;

                            @endphp

                        </div>

                        <div class="col align-self-center">

                            <div class="float-right" style="min-width: 140px;">

                                <div class="orders d-inline rounded text-center pl-0 pt-1 pr-0 pb-1 ml-1 mt-1 mr-0 mb-1 align-self-center{{ $bg_orders_ins }}" id="orders{{ $key }}" style="min-width: 60px;">{{ $count_orders_all }} 📦</div>
                                <div class="pizza d-inline rounded text-center pl-1 pt-1 pr-0 pb-1  ml-1 mt-1 mr-0 mb-1 align-self-center{{ $bg_pizza_ins }}"  id="pizza{{ $key }}" style="min-width: 60px;">{{ $count_pizza_all }} 🍕</div>

                            </div>
                            <script>
                                $("#hour_5_{{ $key }}").addClass("{{ $bg_hours_ins }}");
                                {{--$("#hour{{ $key }}").removeClass("border");--}}
                            </script>

                        </div>

                    </div>

                </div>

                <div class="col border-bottom d-inline">

                    <div class="row d-flex align-content-center flex-wrap">

                        <div class="col-sm-1 rounded text-center p-1 m-1 align-self-center font-weight-bold" id="hour_10_{{ $key }}" style="min-width: 70px;">{{ $hour_table }}</div>

                        <div class="col-sm-auto text-left align-self-center">

                            @php

                                $count_orders_all = 0;
                                $count_pizza_all = 0;

                                list($count_orders, $count_pizza) = \App\Http\Controllers\ArchiController::get_orders_table($res10_7, $hour_table, 7);
                                $count_orders_all += $count_orders;
                                $count_pizza_all += $count_pizza;

                                list($count_orders, $count_pizza) = \App\Http\Controllers\ArchiController::get_orders_table($res10_6, $hour_table, 6);
                                $count_orders_all += $count_orders;
                                $count_pizza_all += $count_pizza;

                                list($count_orders, $count_pizza) = \App\Http\Controllers\ArchiController::get_orders_table($res10_4, $hour_table, 4);
                                $count_orders_all += $count_orders;
                                $count_pizza_all += $count_pizza;

                                $bg_orders_ins = $count_orders_all >= 6 ? ' bg-danger text-white' : '';
                                $bg_pizza_ins = $count_pizza_all >= 15 ? ' bg-danger text-white' : '';

                                $bg_hours_ins = '';
                                $bg_hours_ins = $bg_orders_ins !== '' ? $bg_orders_ins : $bg_hours_ins;
                                $bg_hours_ins = $bg_pizza_ins !== '' ? $bg_pizza_ins : $bg_hours_ins;

                            @endphp

                        </div>

                        <div class="col align-self-center">

                            <div class="float-right" style="min-width: 140px;">

                                <div class="orders d-inline rounded text-right pl-0 pt-1 pr-0 pb-1 ml-1 mt-1 mr-0 mb-1 align-self-center{{ $bg_orders_ins }}" id="orders{{ $key }}" style="min-width: 50px;">{{ $count_orders_all }} 📦</div>
                                <div class="pizza d-inline rounded text-right pl-1 pt-1 pr-0 pb-1  ml-1 mt-1 mr-0 mb-1 align-self-center{{ $bg_pizza_ins }}"  id="pizza{{ $key }}" style="min-width: 50px;">{{ $count_pizza_all }} 🍕</div>

                            </div>
                            <script>
                                $("#hour_10_{{ $key }}").addClass("{{ $bg_hours_ins }}");
                                {{--$("#hour{{ $key }}").removeClass("border");--}}
                            </script>

                        </div>

                    </div>

                </div>

                {{--                    </div>--}}

                <div class="w-100"></div>

            @endforeach


        </div>


    </div>

</div>


<script type="text/javascript">

    $(document).ready(function(){

        $(".order_button").click(function(e) {

            $("#modal_title").html("Заказ №"+$(this).attr("id"));
            $("#modal_body").html("");
            $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');
            $("#exampleModalCenter").modal("show");

//            $.ajax({
//                type: "POST",
//                url: "https://telegramadminbotdebug.estmesta.com/admin/admins/showmodaldialog",
//                data: "_token=uqe5idwRAFqBZLPgnpYwF41ODl8LnzRnmMk7hYlE&action=admin_add",
//                cache: false
//            }).done(function(deldata) {
//                $("#modal_dialog").html(deldata);
//                $("#exampleModalCenter").modal("show");
//            }).fail(function() {
//                $("#modal_body").html("Произошла ошибка");
//                $("#exampleModalCenter").modal("show");
//            });
        });

    });
</script>
