
<section class="checkout">
    <div class="close_btn">
        <img class="img" src="{{ asset('/assets/icon/x.svg') }}" alt="">
    </div>
    {{--        <div class="checkout_back-btn">--}}
    {{--            <img src="{{ asset('/assets/icon/back.svg') }}" alt="" class="img">--}}
    {{--        </div>--}}
    <div class="checkout_block delivery">
        <div class="checkout_block__title">СПОСОБИ ДОСТАВКИ</div>
        <div class="checkout_block__main">
            <button class="checkout_btn button delivery_btn active" id="courier">Кур’єром</button>
            <button class="checkout_btn button delivery_btn" id="take_away">Самовивіз</button>
        </div>
    </div>
    <div class="checkout_block contact-info">
        <div class="checkout_block__title">КОНТАКТНА ІНФОРМАЦІЯ</div>
        <div class="checkout_block__main">
            <div class="checkout_input required">
                <input type="text" class="input{{ $order_last && $order_last->name ? '' : ' error' }}" id="name" placeholder="ваше ім’я" name="client-name" value="{{ $order_last ? $order_last->name : '' }}" required>
            </div>
            <div class="checkout_input required">
                <input type="tel" class="input{{ $order_last && $order_last->phone ? '' : ' error' }}" id="phone" placeholder="+38(0__)___-__-__" name="client-tel" value="{{ $order_last ? $order_last->phone : '' }}" required>
            </div>
            <div class="checkout_input">
                <input type="text" class="input" value="м. Дніпро" name="client-city" readonly>
            </div>

            <div class="address-selection">
                @if ($addresses->count() > 0)
                    <button class="button checkout_btn address-selection_btn active" id="haveAddress">
                        <span class="text">Мої адреси</span>
                        <span class="icon"><img src="{{ asset ('assets/icon/Vector_bottom.svg') }}" alt="" class="img"></span>
                    </button>
                @endif
                <button class="button checkout_btn address-selection_btn{{ $addresses->count() == 0 ? ' active' : '' }}" id="newAddress">
                    <span class="text">Нова адреса</span>
                    <span class="icon"><img src="{{ asset ('assets/icon/Vector_bottom.svg') }}" alt="" class="img"></span>
                </button>
            </div>

            @if ($addresses->count() > 0)
                <div class="address-selection_container haveAddress show">
                    <div class="checkout_input required">
                        <select name="client-address" class="select" id="client-address" required>
                            <option value="" disabled selected>Оберіть адресу</option>
                            @foreach($addresses as $addr)
                                <option value="{{ $addr->id_address }}" data-street="{{ $addr->address1 }}" data-build="{{ $addr->build }}" data-corps="{{ $addr->corps }}" data-flat="{{ $addr->flat }}" data-frontdoor="{{ $addr->frontdoor }}" data-floor="{{ $addr->floor }}">{{ $addr->alias }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            <div class="checkout_input street_take_away" style="display: none;">
                <input type="text" class="input" value="вул. Старокозацька 66А" name="client-take-away" readonly>
            </div>

            <div class="address-selection_container newAddress{{ $addresses->count() == 0 ? ' show' : '' }}">
                <div class="checkout_input street required">
                    {{--                <input name="client-street" type="text" id="address" class="input select_street" placeholder="Введіть адресу" required />--}}
                    <select name="client-street" id="select_street" class="select select_street" data-notice="Оберіть вулицю" required>
                        {{--                        {!! $address && $address->address1 !== null && $address->address1 !== '' ? '<option value="'.$address->address1.'" selected="selected">'.$address->address1.'</option>' : '<option value="" disabled>Оберіть вулицю</option>' !!}--}}
                        <option value="" disabled>Оберіть вулицю</option>
                        {{--                    @foreach($streets as $street)--}}
                        {{--                        <option id="{{ $street->id }}" value="{{ $street->name }}">{{ $street->name }}</option>--}}
                        {{--                    @endforeach--}}
                    </select>
                </div>
                <div class="checkout_input house required row">
                    <input type="text" id="house" class="input error" name="buid" placeholder="буд." required>
                    <input type="number" id="corps" class="input" name="" placeholder="корпус">
                    <input type="text" id="flat" class="input" name="" placeholder="кв./оф.">
                </div>
                <div id="address_not_in_delivery_area" class="error_notification" style="display: none;">
                    Ця адреса не входить у зону доставки!
                </div>
                <div class="checkout_input house row two-item">
                    <input type="number" id="front_door" class="input" name="" placeholder="під’їзд">
                    <input type="number" id="floor" class="input" name="" placeholder="поверх">
                </div>
                {{--            <div class="checkout_input comment_address">--}}
                {{--                --}}{{--                <input type="text" class="input" placeholder="Коментар до адреси:" name="client-comment">--}}
                {{--                <textarea type="text" class="textarea" id="address_comment" placeholder="Коментар до адреси:" name="client-comment-address"></textarea>--}}
                {{--            </div>--}}
            </div>

            <div class="checkout_input required row">
                <input type="date" id="date" class="input" name="client-date" placeholder="Дата" value="{{ date("Y-m-d") }}" required>
                <select id="time" name="client-time" class="select error" required>
                    <option value="" disabled selected>Час</option>
                </select>
            </div>
            <div class="checkout_input">
                <textarea type="text" id="comment" class="textarea" placeholder="Коментар до замовлення:" name="client-comment"></textarea>
            </div>

        </div>
    </div>
    <div class="checkout_block payment">
        <div class="checkout_block__title">СПОСОБИ ОПЛАТИ</div>
        <div class="checkout_block__main">
            {!! $payment_modules->where('id_module', 112)->first() || $payment_modules->where('id_module', 79)->first() ? '<button class="checkout_btn button pay_btn" id="card">Оплата картою</button>' : '' !!}
            <button class="checkout_btn button pay_btn active" id="cash">Готівка</button>
            {!! $payment_modules->where('id_module', 117)->first() ? '<button class="checkout_btn button pay_btn" id="terminal" style="display: none;">Терміналом на місці</button>' : '' !!}
        </div>
    </div>
    <div class="checkout_block total">
        <div class="checkout_block__title">ЗНИЖКИ</div>
        <div class="checkout_block__main">
            <div class="checkout_input without_call">
                <input type="checkbox" name="save_trees" value="1" id="save_trees" />
                <label for="save_trees">Знижка 5%. <span>Я поверну коробку з минулого замовлення кур’єру.<span></label>
            </div>
            <div class="total_row with-input"{{ $cashback > 0 ? '' : ' style="display: none;"' }}>
                <div class="total_row__title">Застосувати кешбек <br><small><div id="cashback_max_enter">(макс. <div class="d-inline" id="cashback_max">{{ $cashback_max_pay }}</div> грн):</div></small></div>
                <div class="total_row__val">
                    <input type="number" id="cashback" class="input" placeholder="0" min="1" max="{{ $cashback_max_pay }}"> грн
                </div>
            </div>
            {{--            <div class="total_row"{{ $cashback > 0 ? '' : ' style="display: none;"' }}>--}}
            {{--                <div class="total_row__title"><div class="d-inline" id="price_for_cashback">$price_for_cashback: {{ $price_for_cashback }}</div> грн</div>--}}
            {{--            </div>--}}
            <div class="total_row"{{ $cashback > 0 ? '' : ' style="display: none;"' }}>
                <div class="total_row__title">На вашому рахунку <br>кешбека:</div>
                <div class="total_row__val">{{ $cashback }} грн</div>
            </div>
            <div class="checkout_block-bg">
                <div class="total_row">
                    <div class="total_row__title">Сума замовлення:</div>
                    <div class="total_row__val" id="price_all">{{ $products->sum('price_all') }} грн</div>
                </div>
                <div class="total_row">
                    <div class="total_row__title">Доставка:</div>
                    <div class="total_row__val" id="delivery">{{ $delivery }} грн</div>
                </div>
                <div class="total_row">
                    <div class="total_row__title">Сума знижки:</div>
                    <div class="total_row__val" id="discount">0 грн</div>
                </div>
                <div class="total_row main_row">
                    <div class="total_row__title">Разом до сплати:</div>
                    <div class="total_row__val" id="price_with_discount">{{ $price_all }} грн</div>
                </div>
                <div class="total_row with-input" id="div_change_from">
                    <div class="total_row__title">Підготувати решту з:</div>
                    <div class="total_row__val">
                        <input type="number" id="change_from" class="input" placeholder="0" min="0"> грн
                    </div>
                </div>
            </div>
            <div id="error_required_fields" class="error_notification">
                Обов’язкові поля не заповнені!
            </div>
            <div class="checkout_input without_call">
                <input type="checkbox" name="without_call" value="1" id="without_call" />
                <label for="without_call">Не передзвонювати</label>
            </div>
        </div>
    </div>

</section>

{{--<form id="order_form" action="{{ route('add_order') }}" method="POST">--}}
{{--    <label for="name" class="control-label mt-2 mb-0">Ім'я</label>--}}
{{--    <input type="text" class="form-control text mt-0" id="name" name="name" placeholder="Ім'я" required />--}}

{{--    <label for="phone2" class="control-label mt-2 mb-0">Телефон</label>--}}
{{--    <input type="text" class="form-control text mt-0" id="phone2" name="phone" placeholder="Телефон" required />--}}

{{--    <label for="street" class="control-label mt-2 mb-0">Вулиця</label>--}}
{{--    <input type="text" class="form-control text mt-0" id="street" name="street" placeholder="Вулиця" required />--}}

{{--    <label for="house" class="control-label mt-2 mb-0">Будинок</label>--}}
{{--    <input type="text" class="form-control text mt-0" id="house" name="house" placeholder="Будинок" required />--}}

{{--    <p class="mt-3">Сума замовлення: {{ $products->sum('price_all') }} грн</p>--}}
{{--</form>--}}

<script src="{{ asset ('assets/js/maskinput.js') }}"></script>
<script>

    @if($order_last && $order_last->name && $order_last->phone)
        check['name'] = 1;
    check['phone'] = 1;
    customer_name = '{{ $order_last->name }}';
    customer_phone = '{{ $order_last->phone }}';
        @endif

    var pay = 'cash';
    cashback_max_pay = {{ $cashback_max_pay }};
    price_for_cashback = {{ $price_for_cashback }};
    var pos = customer_discount.indexOf(1);
    customer_discount.splice(pos, 1);

    function empty_time() {
        $('#time').empty();
        $('#time').append($('<option/>').text("Час").attr('value', ""));
        $("#time").trigger("chosen:updated");
        tg.MainButton.hideProgress();
        $('#time').addClass("error");
        check['time'] = 0;
        check_for_errors();
    }

    function address_and_time (date, street, house) {
        var address = street + ", " + house;
        if (street !== '' && street !== null && house !== '' && house !== null) {
            $.ajax({
                type: "GET",
                url: "{{ route('address_in_delivery_area') }}",
                data: "address="+address,
                cache: false,
                success: function(data) {
                    if (!data.success) {
                        alert(data+ "; " +data.message);
                    }
                    else {
                        if (!data.data.delivery) {
                            gogo = 0;
                            $("#address_not_in_delivery_area").show();
                            empty_time();
                        }
                        else {
                            gogo = 1;
                            $("#address_not_in_delivery_area").hide();
                            get_time(date, address, 0);
                        }
                    }
                },
                error: function(data) {
                    alert('error: '+data.message);
                }
            });
        }
        else {
            empty_time();
        }
    }

    function get_time(date, address, takeaway) {
        $.ajax({
            type: "GET",
            url: "{{ route('get_time_for_order') }}",
            data: "date=" + date + "&address=" + address + "&takeaway=" + takeaway,
            cache: false
        }).done(function(data_time) {
            if (data_time.success) {
                // var jsonData = $.parseJSON(data_time.data);
                // alert(data_time.data);
                $('#time').empty();
                if (data_time.data.length == 0) {
                    $('#time').append($('<option/>').text('---').attr('value', '---'));
                    check['time'] = 0;
                }
                else {
                    var n = 0;
                    $.each(data_time.data, function(i, item) {
                        n++;
                        if (n === 1) {
                            customer_time = item.time_interval;
                        }
                        $('#time').append($('<option/>').text(item.time_interval).attr('value', item.time_interval));
                    });
                    $('#time').removeClass("error");
                    check['time'] = 1;
                }
                $("#time").trigger("chosen:updated");
                check_for_errors();
                tg.MainButton.hideProgress();
            }
        }).fail(function(data) {
            alert("Error time");
        });
    }

    function validatePhone(phone){
        let regex = /^(\+38|38)?[\s\-]?\(?[0-9]{3}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/;
        return regex.test(phone);
    }

    function check_for_error(id) {
        if ($("#"+id).val() !== '') {
            if (id === 'phone') {
                var phone = $("#"+id).val();
                if (!validatePhone(phone)){
                    $("#"+id).addClass("error");
                    check[id] = 0;
                }else{
                    $("#"+id).removeClass("error");
                    check[id] = 1;
                }
            }
            else if (id === 'name') {
                if ($("#"+id).val().length >= 2) {
                    $("#"+id).removeClass("error");
                    check[id] = 1;
                }
                else {
                    $("#"+id).addClass("error");
                    check[id] = 0;
                }
            }
            else {
                $("#"+id).removeClass("error");
                check[id] = 1;
            }
        }
        else {
            $("#"+id).addClass("error");
            check[id] = 0;
        }
        check_for_errors();
    }

    function check_for_errors() {
        if (check['name'] === 1 && check['phone'] === 1 && check['date'] === 1 && check['time'] === 1) {
            if ((takeaway === 0 && gogo === 1) || (takeaway === 1)) {
                check_errors = 0;
            }
            else check_errors = 1;
        }
        else check_errors = 1;

        if (check_errors === 0) {
            tg.MainButton.setParams({"color": "#1c7430", "text_color" : "#ffffff", "is_active" : true});
            tg.MainButton.enable();
            $("#error_required_fields").hide();
        }
        else {
            tg.MainButton.setParams({"color": "#89a57f", "text_color" : "#666666", "is_active" : false});
            tg.MainButton.disable();
            $("#error_required_fields").show();
        }
    }

    function get_order_sum() {
        $.ajax({
            type: "GET",
            url: "{{ route('get_order_sum') }}",
            data: "user_id="+`${tg.initDataUnsafe.user.id}`+"&discounts="+JSON.stringify(customer_discount)+"&cashback="+$("#cashback").val(),
            cache: false,
            success: function(data) {
                if (data.success) {
                    var delivery = 0;
                    if (takeaway === 0) {
                        delivery = data.delivery;
                    }
                    cashback_max_pay = data.cashback_max_pay;
                    price_for_cashback = data.price_for_cashback;
                    var cashback = parseFloat($("#cashback").val());
                    $("#cashback_max").html(cashback_max_pay);
                    $("#price_for_cashback").html("$price_for_cashback: " + data.price_for_cashback);
                    $("#delivery").html(delivery + " грн");
                    $("#discount").html(data.discount_all + " грн");
                    $("#price_with_discount").html(data.price_with_discount + " грн");
                    if (parseFloat(cashback) > cashback_max_pay) {
                        $("#cashback").val('');
                        cashback_pay = 0;
                        $("#cashback_max_enter").css('color', '#ff0000');
                        $("#cashback_max_enter").css('font-weight', 'bold');
                    }
                    else {
                        $("#cashback_max_enter").css('color', '#000000');
                        $("#cashback_max_enter").css('font-weight', 'normal');
                        cashback_pay = cashback;
                    }
                    customer_cashback = cashback_pay;
                    // alert(data+ "; " +data.message + "; " + data.price_all + "; " + data.discount_all + "; " + data.price_with_discount);
                }
                else {
                    alert('error!!! :' + data.message);
                }
            },
            error: function(data) {
                alert('error: '+data.message);
            }
        });
    }

    $(document).ready(function() {

        customer_date = $("#date").val();
        // phone mask
        $("#phone").mask("+38(099)999-99-99");

        $('.select_street').select2({
            // language: 'ru',
            width: '100%',
            placeholder: 'Оберіть вулицю',
            ajax: {
                url: '{{ route('get_streets') }}',
                dataType: 'json'
            }
        });
        $('.address-selection_btn').on('click', function(){
            if($(this).hasClass('active')) return;

            var id =  $(this).attr('id');

            $('.address-selection_btn').removeClass('active');
            $(this).addClass('active');

            $('.address-selection_container').removeClass('show');
            $('.address-selection_container').each(function(){
                if($(this).hasClass(id)) $(this).addClass('show');
            });
            $("#client-address").prop('selectedIndex',0);
            customer_street = '';
            customer_build = '';
            customer_corps = '';
            customer_flat = '';
            customer_front_door = '';
            customer_floor = '';
            $('#select_street').val(null).trigger('change');
            $("#house").val('');
            $("#corps").val('');
            $("#flat").val('');
            $("#front_door").val('');
            $("#floor").val('');
            customer_date = "{{ date("Y-m-d") }}";
            $("#date").val(customer_date);
            address_and_time(customer_date, customer_street, customer_build);
        });

        {{--        @if ($address && $address->address1 && $address->address1 !== '' && $address->build && $address->build !== '')--}}
        {{--        tg.MainButton.showProgress(true);--}}
        {{--        customer_date = $("#date").val();--}}
        {{--        customer_street = $("#select_street").val();--}}
        {{--        customer_build = $("#house").val();--}}
        {{--        customer_corps = $("#corps").val();--}}
        {{--        customer_flat = $("#flat").val();--}}
        {{--        customer_front_door = $("#front_door").val();--}}
        {{--        customer_floor = $("#floor").val();--}}
        {{--        // check_for_error('select_street');--}}
        {{--        address_and_time(customer_date, customer_street, customer_build);--}}
        {{--        $('#select_street').removeClass("error");--}}
        {{--        $('#select_street').addClass("checked");--}}
        {{--        $('#select_street').prop("checked","checked");--}}
        {{--        $('#select_street').prop("selected","selected");--}}
        {{--        @else--}}
        $('#select_street').addClass("error");
        {{--        @endif--}}

        $('#select_street').on('select2:select', function (e) {
            tg.MainButton.showProgress(true);
            var date = $("#date").val();
            var street = $("#select_street").val();
            var house = $("#house").val();
            check_for_error(this.id);
            address_and_time(date, street, house);
            customer_date = date;
            customer_street = street;
            customer_build = house;
        });

        $('#time').on('change', function () {
            customer_time = $('#time').val();
        });

        $('#client-address').on('change', function () {
            customer_street = $(this).find(':selected').data('street');
            customer_build = $(this).find(':selected').data('build');
            customer_corps = $(this).find(':selected').data('corps');
            customer_flat = $(this).find(':selected').data('flat');
            customer_front_door = $(this).find(':selected').data('frontdoor');
            customer_floor = $(this).find(':selected').data('floor');
            address_and_time(customer_date, customer_street, customer_build);
            // alert('вул. '+customer_street+', буд. '+customer_build+', корп. '+customer_corps+', кв. '+customer_flat+', под. '+customer_front_door+', этаж '+customer_floor);
        });

        // $('#cashback').on('change', function () {
        //     get_order_sum();
        // });
        $('#cashback').on('input', function () {
            get_order_sum();
        });

        $("#house").on('change', function () {
            tg.MainButton.showProgress(true);
            var date = $("#date").val();
            var street = $("#select_street").val();
            var house = $("#house").val();
            address_and_time(date, street, house);
            check_for_error(this.id);
            customer_date = date;
            customer_street = street;
            customer_build = house;
        });

        $("#name").on('change', function () {
            check_for_error(this.id);
            customer_name = $("#name").val();
        });

        $("#phone").on('change', function () {
            check_for_error(this.id);
            customer_phone = $("#phone").val();
        });

        $("#date").on('change', function () {
            tg.MainButton.showProgress(true);
            customer_date = $("#date").val();
            if (takeaway === 1) {
                get_time(date, '', takeaway);
            }
            else {
                address_and_time(customer_date, customer_street, customer_build);
            }
            check_for_error(this.id);
        });

        $(".close_btn").on('click', function () {
            $.ajax({
                type: "GET",
                url: "{{ route('get_main') }}",
                data: "",
                cache: false
            }).done(function(data) {
                gogo = 0;
                $("#main").html(data);
            }).fail(function() {
                //
            });
        });

        $(".delivery_btn").on('click', function () {
            $('#select_street').val(null).trigger('change');
            $("#house").val('');
            $("#corps").val('');
            $("#flat").val('');
            $("#front_door").val('');
            $("#floor").val('');
            customer_date = "{{ date("Y-m-d") }}";
            $("#date").val(customer_date);
            $(".delivery_btn").removeClass("active");
            $("#"+this.id).addClass("active");
            var pos_delivery = customer_discount.indexOf(4);
            if (pos_delivery >= 0) customer_discount.splice(pos_delivery, 1);
            if (this.id === 'take_away') {
                takeaway = 1;
                $(".pay_btn").removeClass("active");
                $("#cash").addClass("active");
                $("#terminal").show();
                $(".street").hide();
                $(".house").hide();
                $(".comment_address").hide();
                $(".address-selection").hide();
                @if ($addresses->count() > 0)
                $('.address-selection_container').removeClass('show');
                @endif
                // $(".address-selection_container").hide();
                $(".street_take_away").show();
                get_time(customer_date, '', 1);
                customer_delivery_type = 8;
                customer_discount.push(4);
                get_order_sum();
                customer_street = customer_street_takeaway;
                customer_build = customer_build_takeaway;
                customer_corps = '';
                customer_flat = '';
                customer_front_door = '';
                customer_floor = '';
            }
            else {
                takeaway = 0;
                $(".pay_btn").removeClass("active");
                $("#cash").addClass("active");
                $("#terminal").hide();
                $(".street_take_away").hide();

                $(".address-selection").show();
                $('.address-selection_btn').removeClass('active');
                $('.address-selection_container').removeClass('show');
                @if ($addresses->count() > 0)
                $("#haveAddress").addClass("active");
                $('.haveAddress').addClass('show');
                @else
                $("#newAddress").addClass("active");
                $('.newAddress').addClass('show');
                @endif
                // $(".address-selection_container").show();
                $(".street").show();
                $(".house").show();
                $(".comment_address").show();
                customer_street = '';
                customer_build = '';
                customer_corps = '';
                customer_flat = '';
                customer_front_door = '';
                customer_floor = '';
                $("#client-address").prop('selectedIndex',0);
                address_and_time(customer_date, customer_street, customer_build);
                customer_delivery_type = 6;
                get_order_sum();
                // alert('вул. '+customer_street+', буд. '+customer_build+', корп. '+customer_corps+', кв. '+customer_flat+', под. '+customer_front_door+', этаж '+customer_floor);
            }
            check_for_errors();
        });

        $("#corps").on('change', function () {
            customer_corps = $("#corps").val();
        });

        $("#flat").on('change', function () {
            customer_flat = $("#flat").val();
        });

        $("#front_door").on('change', function () {
            customer_front_door = $("#front_door").val();
        });

        $("#floor").on('change', function () {
            customer_floor = $("#floor").val();
        });

        $("#address_comment").on('change', function () {
            customer_address_comment = $("#address_comment").val();
        });

        $("#comment").on('input', function () {
            customer_comment = $("#comment").val();
        });

        // $("#cashback").on('change', function () {
        //     customer_cashback = $("#cashback").val();
        // });

        $("#change_from").on('change', function () {
            customer_change_from = $("#change_from").val();
        });

        $("#without_call").on('change', function () {
            if ($('#without_call').is(':checked')) {
                customer_without_call = 1;
            }
            else {
                customer_without_call = 0;
            }
        });

        $("#save_trees").on('change', function () {
            if ($('#save_trees').is(':checked')) {
                customer_discount.push(1);
                get_order_sum();
            }
            else {
                var pos_safe_trees = customer_discount.indexOf(1);
                customer_discount.splice(pos_safe_trees, 1);
                get_order_sum();
            }
        });


        $(".pay_btn").on('click', function () {
            $(".pay_btn").removeClass("active");
            $("#"+this.id).addClass("active");
            customer_payment_type = this.id;
            if (this.id === 'cash') {
                $("#div_change_from").show();
            }
            else {
                $("#div_change_from").hide();
            }
        });

    });


    {{--        function add_order() {--}}
    {{--            // var form = $("#order_form");--}}
    {{--            // var url = form.attr('action');--}}
    {{--            var formData = {--}}
    {{--                _token: "{{ csrf_token() }}",--}}
    {{--                name: $("#name").val(),--}}
    {{--                phone: $("#phone").val(),--}}
    {{--                street: $("#street").val(),--}}
    {{--                house: $("#house").val(),--}}
    {{--            };--}}
    {{--            $.ajax({--}}
    {{--                type: "POST",--}}
    {{--                url: "{{ route('add_order') }}",--}}
    {{--                data: formData,--}}
    {{--                dataType: "json",--}}
    {{--                encode: true,--}}
    {{--                success: function(data) {--}}
    {{--                    if (!data.success) {--}}
    {{--                        if (data.errors.name) {--}}
    {{--                            alert(data.errors.name);--}}
    {{--                        }--}}
    {{--                        if (data.errors.phone) {--}}
    {{--                            alert(data.errors.phone);--}}
    {{--                        }--}}
    {{--                        if (data.errors.street) {--}}
    {{--                            alert(data.errors.street);--}}
    {{--                        }--}}
    {{--                        if (data.errors.house) {--}}
    {{--                            alert(data.errors.house);--}}
    {{--                        }--}}
    {{--                    }--}}
    {{--                    else {--}}
    {{--                        alert("Form Submited Successfully: "+data.message);--}}
    {{--                    }--}}
    {{--                    tg.MainButton.hideProgress();--}}
    {{--                },--}}
    {{--                error: function(data) {--}}
    {{--                    // Some error in ajax call--}}
    {{--                    alert("some Error");--}}
    {{--                    tg.MainButton.hideProgress();--}}
    {{--                }--}}
    {{--            });--}}
    {{--        }--}}

</script>
{{--<script async src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_KEY') }}&libraries=places,geometry,drawing&callback=initMap"></script>--}}
