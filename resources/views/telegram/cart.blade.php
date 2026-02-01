<section class="shop-bascet" style="margin-top: 35px;">
    {{--    <div class="close_btn">--}}
    {{--        <img class="img" src="{{ asset('/assets/icon/x.svg') }}" alt="">--}}
    {{--    </div>--}}
    @foreach($products as $product)
        @if($product->parent_id == null)
            <div class="shop-bascet_list product_list">
                <div class="product_item">
                    <div class="product_item__img">
                        <img src="{{ asset('/assets/img/thumb/'.$abra_kadabra.$product->product_id.'.webp') }}" alt="" class="img">
                    </div>
                    <div class="product_item__main">
                        <div class="product_item__title">
                            <span class="text">{{ $product->product_name }}</span>
                            @if ($product->product_action == 0 && $product->product_cherry == 0)
                                <button class="button delete_btn delete_product" id="product_delete___{{ $product->id }}"></button>
                            @endif
                        </div>
                        <div class="product_item__size">
                            ({{ $product->combination_name }})
                        </div>
                        @if($products->where('parent_id', $product->parent_id)->count() > 0)
                            <div class="product_item__ingredients">
                                @foreach($products->where('parent_id', $product->id) as $ingredient)
                                    <span class="item">{{ $ingredient->product_name }}</span>
                                @endforeach
                            </div>
                        @endif
                        <div class="product_item__btn">
                            {{--                                <div class="item__count">--}}
                            {{--                                    <div class="count_min{{ $product->product_action == 0 && $product->product_present == 0 ? ' remove_product' : '' }}" id="product_remove___{{ $product->id }}" data-id="{{ $product->id }}">-</div>--}}
                            {{--                                    <input type="number" name="count_input" readonly max="4" value="{{ $product->quantity }}" id="" data-id="01" data-price=69>--}}
                            {{--                                    <div class="count_plus{{ $product->product_action == 0 && $product->product_present == 0 ? ' add_product' : '' }}" id="product_add___{{ $product->id }}" data-id="{{ $product->id }}" data-product-id="{{ $product->product_id }}" data-combination-id="{{ $product->combination_id }}" data-price="{{ $product->price }}">+</div>--}}
                            {{--                                </div>--}}
                            <div class="item__count">
                                <div class="count_min{{ $product->product_action == 0 && $product->product_cherry == 0 ? ' remove_product' : '' }}" id="product_remove___{{ $product->id }}" data-id="{{ $product->id }}">-</div>
                                <input type="number" name="count_input" readonly max="4" value="{{ $product->quantity }}" id="" data-id="01" data-price=69>
                                <div class="count_plus{{ $product->product_action == 0 && $product->product_cherry == 0 ? ' add_product' : '' }}" id="product_add___{{ $product->id }}" data-id="{{ $product->id }}" data-product-id="{{ $product->product_id }}" data-combination-id="{{ $product->combination_id }}" data-price="{{ $product->price }}">+</div>
                            </div>
                            <div class="product_item__price">{{ $product->price_with_ingredients }} грн</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{--        <div class="product_item" id="product_{{ $product->id }}">--}}
        {{--            <div class="product_item__img">--}}
        {{--                <img src="{{ asset('/assets/img/thumb/medium_'.$product->product_id.'.webp') }}" alt="" class="img">--}}
        {{--            </div>--}}
        {{--            <div class="product_item__main">--}}
        {{--                <div class="product_item__title">{{ $product->product_name }} - {{ $product->combination_name }}</div>--}}
        {{--                <div class="product_item__desc">--}}
        {{--                    <button type="button" class="btn btn-secondary add_product" id="product_add___{{ $product->id }}" data-product-id="{{ $product->product_id }}" data-combination-id="{{ $product->combination_id }}" data-price="{{ $product->price }}">+</button>--}}
        {{--                    {{ $product->quantity }}--}}
        {{--                    <button type="button" class="btn btn-secondary remove_product" id="product_remove___{{ $product->id }}">-</button>--}}
        {{--                    <button type="button" class="btn btn-danger delete_product" id="product_delete___{{ $product->id }}">x</button>--}}
        {{--                    {{ $product->price_all }} грн--}}
        {{--                </div>--}}
        {{--            </div>--}}
        {{--        </div>--}}
    @endforeach

</section>




<script>
    var button_close = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
    $(document).ready( function () {
        $('.add_product').on('click', function () {
            tg.MainButton.showProgress(true);
            $.ajax({
                type: "POST",
                url: "{{ route('add_product_to_cart') }}",
                data: "_token={{ csrf_token() }}&user_id="+`${tg.initDataUnsafe.user.id}`+"&id="+$("#"+this.id).attr('data-id')+"&product_id="+$("#"+this.id).attr('data-product-id')+"&combination_id="+$("#"+this.id).attr('data-combination-id')+"&price="+$("#"+this.id).attr('data-price'),
                cache: false
            }).done(function(data) {
                if (data) {
                    $("#cart_body").html(button_close+data['view']);
                    if (data['products_sum'] > 0) {
                        products_sum = data['products_sum'];
                        tg.MainButton.text = "✅ Оформити замовлення ("+products_sum+" грн)";
                        tg.MainButton.hideProgress();
                    }
                    else {
                        tg.MainButton.hide();
                    }
                }
                else {
                    $("#cart_body").html(button_close+"Щось пішло не так...");
                }
            }).fail(function() {
                // $("#cart_body").html(button_close+"Щось пішло не так...");
            });
        });
        $('.remove_product').on('click', function () {
            tg.MainButton.showProgress(true);
            var product_id = this.id;
            var product_id_arr = product_id.split("___");
            var id = product_id_arr[1];
            $.ajax({
                type: "POST",
                url: "{{ route('remove_product_in_cart') }}",
                data: "_token={{ csrf_token() }}&user_id="+`${tg.initDataUnsafe.user.id}`+"&id="+id,
                cache: false
            }).done(function(data) {
                if (data) {
                    $("#cart_body").html(button_close+data['view']);
                    if (data['products_sum'] > 0) {
                        products_sum = data['products_sum'];
                        tg.MainButton.text = "✅ Оформити замовлення ("+products_sum+" грн)";
                        tg.MainButton.hideProgress();
                        tg.MainButton.show();
                    }
                    else {
                        tg.MainButton.hide();
                        $('.modal').modal('hide');
                    }
                }
                else {
                    $("#cart_body").html(button_close+"Щось пішло не так...");
                }
            }).fail(function() {
                // $("#cart_body").html(button_close+"Щось пішло не так...");
            });
        });
        $('.delete_product').on('click', function () {
            tg.MainButton.showProgress(true);
            var product_id = this.id;
            var product_id_arr = product_id.split("___");
            var id = product_id_arr[1];
            $.ajax({
                type: "POST",
                url: "{{ route('delete_product_in_cart') }}",
                data: "_token={{ csrf_token() }}&user_id="+`${tg.initDataUnsafe.user.id}`+"&id="+id,
                cache: false
            }).done(function(data) {
                if (data) {
                    $("#cart_body").html(button_close+data['view']);
                    if (data['products_sum'] > 0) {
                        products_sum = data['products_sum'];
                        tg.MainButton.text = "✅ Оформити замовлення ("+products_sum+" грн)";
                        tg.MainButton.hideProgress();
                        tg.MainButton.show();
                    }
                    else {
                        tg.MainButton.hide();
                        $('.modal').modal('hide');
                    }
                }
                else {
                    $("#cart_body").html(button_close+"Щось пішло не так...");
                }
            }).fail(function() {
                // $("#cart_body").html(button_close+"Щось пішло не так...");
            });
        });
        {{--$('.product_item').click(function(e) {--}}
        {{--    e.preventDefault();--}}
        {{--    var product_id = this.id;--}}
        {{--    var product_id_arr = product_id.split("_");--}}
        {{--    var id = product_id_arr[1];--}}
        {{--    var button_close = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';--}}

        {{--    $("#modal_body").html(button_close+'<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');--}}
        {{--    $("#modal_footer").html("");--}}
        {{--    $("#modalDialog").modal("show");--}}

        {{--    $.ajax({--}}
        {{--        type: "GET",--}}
        {{--        url: "{{ route('get_product') }}",--}}
        {{--        data: "id="+id,--}}
        {{--        cache: false--}}
        {{--    }).done(function(data) {--}}
        {{--        $("#modal_body").html(button_close+data);--}}

        {{--    }).fail(function() {--}}
        {{--        $("#modal_body").html(button_close+"Щось пішло не так...");--}}
        {{--        $("#modalDialog").modal("show");--}}
        {{--    });--}}
        {{--});--}}
    });
</script>
