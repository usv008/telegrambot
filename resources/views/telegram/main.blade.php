@if (!$wh_is_open)
<div id="working_hours_banner" style="padding: 30px 20px; text-align: center;">
    <div style="font-size: 48px; margin-bottom: 15px;">🕐</div>
    <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333;">
        {{ \App\Services\WorkingHoursService::getSetting('message_title', 'Ми зараз не працюємо') }}
    </div>
    <div style="font-size: 14px; color: #666; margin-bottom: 15px; line-height: 1.5;">
        {{ \App\Services\WorkingHoursService::getSetting('message_text', 'На жаль, зараз неробочий час.') }}
    </div>
    @if ($wh_next_open)
    <div style="font-size: 14px; color: #1c7430; font-weight: 500; margin-bottom: 15px;">
        ⏰ Наступне відкриття: {{ date('d.m.Y H:i', strtotime($wh_next_open)) }}
    </div>
    @endif
    @if (\App\Services\WorkingHoursService::getSetting('allow_future_orders', '1') == '1')
    <div style="font-size: 13px; color: #555; margin-top: 10px;">
        Ви можете зробити замовлення на інший час
    </div>
    @endif
</div>
@endif

<div class="categories" id="categories"></div>
<div class="product_list" id="products">
    <div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;">
        <div class="loader"></div>
    </div>
</div>

<script>
    var wh_is_open = {{ $wh_is_open ? 'true' : 'false' }};

    $(document).ready( function () {
        $.ajax({
            type: "GET",
            url: "{{ route('get_categories') }}",
            data: "category_select={{ $category_select }}",
            cache: false
        }).done(function (categories_data) {
            $("#categories").html(categories_data);
            $("#products").html('<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');
            $.ajax({
                type: "GET",
                url: "{{ route('get_products') }}",
                data: "category_select={{ $category_select }}",
                cache: false
            }).done(function (products_data) {
                $("#products").html(products_data);
            }).fail(function () {
                $("#products").html("Произошла ошибка");
            });
        }).fail(function () {
            $("#categories").html("Произошла ошибка");
        });

        $.ajax({
            type: "GET",
            url: "{{ route('count_products_in_cart') }}",
            data: "user_id=" + `${tg.initDataUnsafe.user.id}`,
            cache: false
        }).done(function (data) {
            if (data) {
                if (data['products_count'] > 0) {
                    order_open = 0;
                    products_sum = data['products_sum'];
                    tg.MainButton.textColor = "#ffffff";
                    tg.MainButton.color = "#1c7430";
                    tg.MainButton.text = "👀 Моє замовлення (" + products_sum + " грн)";
                    tg.MainButton.show();
                } else {
                    tg.MainButton.hide();
                }
                tg.MainButton.hideProgress();
            }
        }).fail(function () {
            //
        });
    });
</script>
