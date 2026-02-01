@if(isset($message) && $message != null)
    {{ $message }}
@endif

@if($products != null)
    @foreach($products as $product)
        <div class="product_item">
            <div class="product_item__img">
                <img src="{{ asset('/assets/img/thumb/'.$abra_kadabra.$product->id_product.'.webp') }}" alt="" class="img">
            </div>
            <div class="product_item__main">
                <div class="product_item__title">{{ $product->name }}</div>
                @if (isset($product->product_features) && isset($product->product_features->value))
                    <div class="product_item__desc">
                        {{ $product->product_features->value }}
                    </div>
                @endif
                @if (isset($product->product_attributes))
                    @if ($product->product_attributes->count() > 1 || $product->ingredients->count() > 0)
                        <div class="product_item__btn" id="product_{{ $product->id_product }}">
                            <button class="button">від {{ bcmul($product->product_attributes->min('price_new'), 1, 2) }} грн</button>
                        </div>
                    @else
                        <div class="product_btn">
                            <button id="addProduct{{ $product->id_product }}" class="ready addProductClass" data-product-id="{{ $product->id_product }}" data-combination-id="{{ $product->product_attributes->first()->id_product_attribute }}" data-price="{{ bcmul($product->product_attributes->first()->price_new, 1, 2) }}" data-price-all="{{ bcmul($product->product_attributes->first()->price_new, 1, 2) }}">

                                <div class="message submitMessage">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 12.2">
                                        <polyline stroke="currentColor" points="2,7.1 6.5,11.1 11,7.1 " />
                                        <line stroke="currentColor" x1="6.5" y1="1.2" x2="6.5" y2="10.3" />
                                    </svg>
                                    <span id="addProduct_price_text{{ $product->id_product }}" class="button-text addProduct_price_text">додати за {{ bcmul($product->product_attributes->first()->price_new, 1, 2) }} грн</span>
                                </div>

                                <div class="message loadingMessage">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 17">
                                        <circle class="loadingCircle" cx="2.2" cy="10" r="1.6" />
                                        <circle class="loadingCircle" cx="9.5" cy="10" r="1.6" />
                                        <circle class="loadingCircle" cx="16.8" cy="10" r="1.6" />
                                    </svg>
                                </div>

                                <div class="message successMessage">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 13 11">
                                        <polyline stroke="currentColor" points="1.4,5.8 5.1,9.5 11.6,2.1 " />
                                    </svg>
                                    <span class="button-text">додано!</span>
                                </div>
                            </button>
                            <canvas id="canvas{{ $product->id_product }}" class="convasClass"></canvas>
                        </div>
                    @endif
                    {{--                @foreach($product->product_combinations as $combination)--}}
                    {{--                    <small>{{ bcmul($combination->price, 1, 2) }}грн ({{ $combination->options->first()->name }})</small><br />--}}
                    {{--                @endforeach--}}
                @endif
            </div>
        </div>
        {{--    <div id="{{ $product->id }}" style="width: 150px; height: 150px; margin: 15px; display: inline-block; cursor: pointer;" data-toggle="modal" data-target="#exampleModal">--}}
        {{--        <img src="{{ asset('/assets/img/thumb/'.$product->id.'.webp') }}" /><br />--}}
        {{--        {{ $product->name }}<br />--}}
        {{--        <small>{{ $product->product_feature }}</small><br />--}}
        {{--        <small>від {{ bcmul($product->product_combinations->min('price'), 1, 2) }} грн</small><br />--}}
        {{--        @foreach($product->product_combinations as $combination)--}}
        {{--            <small>{{ bcmul($combination->price, 1, 2) }}грн ({{ $combination->options->first()->name }})</small><br />--}}
        {{--        @endforeach--}}
        {{--    </div>--}}
    @endforeach
@endif

<script>
    $(document).ready( function () {
        $('.product_item__btn').click(function(e) {
            e.preventDefault();
            var product_id = this.id;
            var product_id_arr = product_id.split("_");
            var id = product_id_arr[1];
            var button_close = '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

            $("#modal_body").html(button_close+'<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');
            $("#modal_footer").html("");
            $("#modalDialog").modal("show");

            $.ajax({
                type: "GET",
                url: "{{ route('get_product') }}",
                data: "id="+id,
                cache: false
            }).done(function(data) {
                $("#modal_body").html(button_close+data);

            }).fail(function() {
                $("#modal_body").html(button_close+"Щось пішло не так...");
                $("#modalDialog").modal("show");
            });
        });
    });
</script>
@include('telegram.confetti_button')
