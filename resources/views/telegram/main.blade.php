<div class="categories" id="categories"></div>
<div class="product_list" id="products">
    <div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;">
        <div class="loader"></div>
    </div>
</div>

<script>
    $(document).ready( function () {
        $.ajax({
            type: "GET",
            url: "{{ route('get_categories') }}",
            data: "category_select={{ $category_select }}",
            cache: false
        }).done(function (categories_data) {
            $("#categories").html(categories_data);
            // $("#products").html('<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><canvas id="pizza" style="width: 120px; height: 120px;"></canvas></div>');
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
                    tg.MainButton.textColor = "#ffffff"; //изменяем цвет текста кнопки
                    tg.MainButton.color = "#1c7430"; //изменяем цвет бэкграунда кнопки
                    tg.MainButton.text = "👀 Моє замовлення (" + products_sum + " грн)";
                    tg.MainButton.show();
                } else {
                    tg.MainButton.hide();
                }
                tg.MainButton.hideProgress();
            }
            alert(data_count);
        }).fail(function () {
            //
        });
    });
</script>
