<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>bot</title>

    {{--    <script src="https://telegram.org/js/telegram-web-app.js"></script>--}}
    <script src="{{ asset ('assets/js/telegram-web-app.js') }}"></script>
    <link rel="stylesheet" href="{{ asset ('assets/css/jquery-ui.css') }}">
    <script src="{{ asset ('assets/js/jquery-1.12.4.js') }}"></script>
    <script src="{{ asset ('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset ('assets/js/popper.min.js') }}" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="{{ asset ('assets/js/bootstrap.min.js') }}" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/dataTables.bootstrap4.min.css') }}">
    {{--    <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/jquery.dataTables.js') }}"></script>--}}
    {{--    <script type="text/javascript" charset="utf8" src="{{ asset ('assets/js/dataTables.bootstrap4.min.js') }}"></script>--}}

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" type="text/css" href="{{ asset ('assets/css/style.css?').time() }}">

    {{--    <link rel="stylesheet" href="{{ asset ('assets/docsupport/style.css') }}">--}}
    {{--    <link rel="stylesheet" href="{{ asset ('assets/docsupport/prism.css') }}">--}}
    {{--    <link rel="stylesheet" href="{{ asset ('assets/css/chosen.css') }}">--}}

    {{--    <script src="{{ asset ('assets/js/chosen.jquery.js') }}" type="text/javascript"></script>--}}
    {{--    <script src="{{ asset ('assets/docsupport/prism.js') }}" type="text/javascript" charset="utf-8"></script>--}}
    {{--    <script src="{{ asset ('assets/docsupport/init.js') }}" type="text/javascript" charset="utf-8"></script>--}}

    <style>
        /*LOADER*/
        .loader {
            border: 10px solid #f3f3f3;
            border-radius: 50%;
            border-top: 10px solid #628c6f;
            border-bottom: 10px solid #628c6f;
            width: 50px;
            height: 50px;
            -webkit-animation: spin 2s linear infinite;
            animation: spin 2s linear infinite;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /*MODAL DIALOG*/
        .modal-dialog {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .modal-content {
            height: auto;
            min-height: 100%;
            border-radius: 0;
        }

    </style>

</head>
<body>
<main class="main" id="main">
    @include('telegram/main')
</main>

<!-- Modal Menu -->
<div class="modal fade" id="modalDialog" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            {{--            <div class="modal-header">--}}
            {{--                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>--}}
            {{--                <button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
            {{--                    <span aria-hidden="true">&times;</span>--}}
            {{--                </button>--}}
            {{--            </div>--}}

            <div class="modal-body" id="modal_body">
                ...
            </div>
            <div class="modal-footer" id="modal_footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cart -->
<div class="modal fade" id="modalCart" tabindex="-1" role="dialog" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" id="cart_body"></div>
            <div class="modal-footer" id="cart_footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Order -->
<div class="modal fade" id="modalOrder" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body" id="order_body"></div>
            <div class="modal-footer" id="order_footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    let tg = window.Telegram.WebApp;
    tg.expand();
    var products_sum = 0;
    var cart_open = 0;
    var order_open = 0;
    var takeaway = 0;
    var gogo = 0;
    var check = [];
    check['name'] = 0;
    check['phone'] = 0;
    check['street'] = 0;
    check['home'] = 0;
    check['date'] = 1;
    check['time'] = 0;
    var check_errors = 1;
    var customer_delivery_type = 6;
    var customer_name = '';
    var customer_phone = '';
    var customer_city = 'Дніпро';
    var customer_street = 'вул. Старокозацька';
    var customer_build = '66А';
    var customer_street_takeaway = customer_street;
    var customer_build_takeaway = customer_build;
    var customer_corps = '';
    var customer_flat = '';
    var customer_front_door = '';
    var customer_floor = '';
    var customer_address_comment = '';
    var customer_date = '';
    var customer_time = '';
    var customer_comment = '';
    var customer_payment_type = 'cash';
    var customer_cashback = 0;
    var customer_change_from = 0;
    var customer_without_call = 0;
    var customer_discount = [];
    var cashback_pay = 0;
    var price_for_cashback = 0;
    var cashback_max_pay = 0;
    var button_click_send_order = 0;


    // tg.MainButton.text = "Changed Text"; //изменяем текст кнопки
    // tg.MainButton.setText("Changed Text1"); //изменяем текст кнопки иначе
    tg.MainButton.textColor = "#ffffff"; //изменяем цвет текста кнопки
    tg.MainButton.color = "#1c7430"; //изменяем цвет бэкграунда кнопки
    tg.MainButton.setParams({"color": "#1c7430"}); //так изменяются все параметры
    var button_close = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

    tg.onEvent('mainButtonClicked', function () {
        tg.MainButton.showProgress(true);
        if (cart_open === 0 && order_open === 0) {
            // tg.sendData("some string that we need to send");
            $("#cart_body").html(button_close+'<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');
            $("#cart_footer").html("");
            $("#modalCart").modal("show");
            $.ajax({
                type: "GET",
                url: "{{ route('show_cart') }}",
                data: "user_id="+`${tg.initDataUnsafe.user.id}`,
                cache: false
            }).done(function(data) {
                if (data) {
                    cart_open = 1;
                    $("#cart_body").html(button_close+data['view']);
                    products_sum = data['products_sum'];
                    tg.MainButton.text = "✅ Оформити замовлення ("+products_sum+" грн)";
                    tg.MainButton.hideProgress();
                }
            }).fail(function() {
                $("#cart_body").html(button_close+"Щось пішло не так...");
            });
        }
        else if (cart_open === 1 && order_open === 0 && products_sum > 0) {
            // $("#order_body").html(button_close+'<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');
            // $("#order_footer").html("");
            // $("#modalOrder").modal("show");
            order_open = 1;
            $("#modalDialog").modal("hide");
            $("#modalCart").modal("hide");
            $("#main").html('<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');
            $.ajax({
                type: "GET",
                url: "{{ route('show_order') }}",
                data: "user_id="+`${tg.initDataUnsafe.user.id}`,
                cache: false
            }).done(function(data) {
                if (data) {
                    $("#main").html(data['view']);
                    products_sum = data['products_sum'];
                    tg.MainButton.text = "Підтвердити";
                    tg.MainButton.setParams({"color": "#89a57f", "text_color" : "#666666", "is_active" : false});
                    tg.MainButton.disable();
                    tg.MainButton.hideProgress();
                }
            }).fail(function() {
                $("#order_body").html(button_close+"Щось пішло не так...");
            });
        }
        else if (order_open === 1) {
            if (check['name'] === 1 && check['phone'] === 1 && check['date'] === 1 && check['time'] === 1) {
                if (((takeaway === 0 && gogo === 1) || (takeaway === 1)) && button_click_send_order === 0) {
                    button_click_send_order = 1;
                    $.ajax({
                        type: "POST",
                        url: "{{ route('testCreateOrder') }}",
                        data: "_token={{ csrf_token() }}&user_id="
                            +`${tg.initDataUnsafe.user.id}`
                            +"&name="+customer_name
                            +"&phone="+customer_phone
                            +"&city="+customer_city
                            +"&street="+customer_street
                            +"&build="+customer_build
                            +"&corps="+customer_corps
                            +"&flat="+customer_flat
                            +"&front_door="+customer_front_door
                            +"&floor="+customer_floor
                            +"&address_comment="+customer_address_comment
                            +"&date="+customer_date
                            +"&time="+customer_time
                            +"&takeaway="+takeaway
                            +"&comment="+customer_comment
                            +"&payment_type="+customer_payment_type
                            +"&price_for_cashback="+price_for_cashback
                            +"&cashback="+customer_cashback
                            +"&change_from="+customer_change_from
                            +"&without_call="+customer_without_call
                            +"&delivery_type="+customer_delivery_type
                            +"&discounts="+JSON.stringify(customer_discount)
                            +"&cashback_pay="+cashback_pay,
                        cache: false
                    }).done(function(data) {
                        tg.MainButton.hideProgress();
                        tg.MainButton.hide();
                        tg.close();
                        $("#main").html(data);
                    }).fail(function(data) {
                        tg.MainButton.hideProgress();
                        $("#main").html(data);
                        // $("#main").html("Щось пішло не так...");
                    });

                    // alert(JSON.stringify(customer_discount));

                    // alert('no errors: '+check_errors
                    //     +'gogo = '+gogo
                    //     +'; name: '+check['name']
                    //     +'; phone: '+check['phone']
                    //     +'; date: '+check['date']
                    //     +'; time: '+check['time']
                    //     +';customer_name: '+customer_name
                    //     +';customer_phone: '+customer_phone
                    //     +';customer_city: '+customer_city
                    //     +';customer_street: '+customer_street
                    //     +';customer_build: '+customer_build
                    //     +';customer_corps: '+customer_corps
                    //     +';customer_flat: '+customer_flat
                    //     +';customer_front_door: '+customer_front_door
                    //     +';customer_floor: '+customer_floor
                    //     +';customer_address_comment: '+customer_address_comment
                    //     +';customer_date: '+customer_date
                    //     +';customer_time: '+customer_time
                    //     +';customer_comment: '+customer_comment
                    //     +';customer_payment_type: '+customer_payment_type
                    //     +';customer_cashback: '+customer_cashback
                    //     +';customer_change_from: '+customer_change_from
                    //     +';customer_not_call: '+customer_without_call
                    // );
                }
            }
            else {
                tg.MainButton.hideProgress();
                // alert('!errors: '+check_errors
                //     +'gogo = '+gogo
                //     +'; name: '+ check['name']
                //     +'; phone: '+check['phone']
                //     +'; date: '+check['date']
                //     +'; time: '+check['time']
                //     +';customer_name: '+customer_name
                //     +';customer_phone: '+customer_phone
                //     +';customer_city: '+customer_city
                //     +';customer_street: '+customer_street
                //     +';customer_build: '+customer_build
                //     +';customer_corps: '+customer_corps
                //     +';customer_flat: '+customer_flat
                //     +';customer_front_door: '+customer_front_door
                //     +';customer_floor: '+customer_floor
                //     +';customer_address_comment: '+customer_address_comment
                //     +';customer_date: '+customer_date
                //     +';customer_time: '+customer_time
                //     +';customer_comment: '+customer_comment
                //     +';customer_payment_type: '+customer_payment_type
                //     +';customer_cashback: '+customer_cashback
                //     +';customer_change_from: '+customer_change_from
                //     +';customer_not_call: '+customer_without_call
                // );
            }
        }
        else {
            tg.MainButton.hideProgress();
            return;
        }
    });
    // btn.addEventListener('click', function(){ //вешаем событие на нажатие html-кнопки
    //     if (tg.MainButton.isVisible){ //если кнопка показана
    //         tg.MainButton.hide() //скрываем кнопку
    //     }
    //     else{ //иначе
    //         tg.MainButton.show() //показываем
    //     }
    // });
    //
    // let btnED = document.getElementById("btnED"); //получаем кнопку активировать/деактивировать
    // btnED.addEventListener('click', function(){ //вешаем событие на нажатие html-кнопки
    //     if (tg.MainButton.isActive){ //если кнопка показана
    //         tg.MainButton.setParams({"color": "#89a57f"}); //меняем цвет
    //         tg.MainButton.textColor = "#666666"
    //         tg.MainButton.disable() //скрываем кнопку
    //     }
    //     else{ //иначе
    //         tg.MainButton.setParams({"color": "#1c7430"}); //меняем цвет
    //         tg.MainButton.textColor = "#ffffff"
    //         tg.MainButton.enable() //показываем
    //     }
    // });

    let usercard = document.getElementById("usercard"); //получаем блок usercard

    // let profName = document.createElement('p'); //создаем параграф
    // profName.innerText = `${tg.initDataUnsafe.user.first_name}
    // ${tg.initDataUnsafe.user.last_name}
    // ${tg.initDataUnsafe.user.username} (${tg.initDataUnsafe.user.language_code})`;
    //выдем имя, "фамилию", через тире username и код языка
    // usercard.appendChild(profName); //добавляем

    // let userid = document.createElement('p'); //создаем еще параграф
    // userid.innerText = `${tg.initDataUnsafe.user.id}`; //показываем user_id
    // usercard.appendChild(userid); //добавляем

    $(document).ready( function () {

        $('#modalCart').on('shown.bs.modal', function () {
            cart_open = 1;
        })

        $('#modalCart').on('hidden.bs.modal', function () {
            cart_open = 0;
            if (order_open === 0) {
                tg.MainButton.text = "👀 Моє замовлення ("+products_sum+" грн)";
            }
        })

        $('#modalOrder').on('shown.bs.modal', function () {
            order_open = 1;
        })

        $('#modalOrder').on('hidden.bs.modal', function () {
            order_open = 0;
            tg.MainButton.text = "✅ Оформити замовлення ("+products_sum+" грн)";
        })

    });

</script>

<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_KEY') }}&libraries=places,geometry,drawing&callback=initMap"></script>

</body>
</html>
