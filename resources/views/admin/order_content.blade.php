<div class="container w-50 float-left col miniTableOrder">

{{--    <div class="row">--}}
{{--        @foreach($photos as $key => $photo)--}}
{{--            <div class="d-inline p-1"><a href="{{ $photo }}" data-toggle="lightbox"><img class="rounded shadow-sm user_image" id="user_image" style="cursor:pointer;" src="{{ $photos_small[$key] }}" width="150" class="img-fluid" /></a></div>--}}
{{--        @endforeach--}}
{{--    </div>--}}

    <div class="row">
        <div class="col-md-12"><b>CMS id</b>: {{ $data_order->external_id }}</div>
    </div>
{{--    <div class="row">--}}
{{--        <div class="col-md-12"><b>Archi ID</b>: {{ $data_order->archi_id }}</div>--}}
{{--    </div>--}}
    <div class="row">
        <div class="col-md-12"><b>Имя</b>: {{ $data_order->name }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Телефон</b>: {{ $data_order->phone }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Доставка</b>: {{ $data_order->delivery_name }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Тип оплаты</b>: {{ $data_order->pay }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Адрес</b>: {{ $data_order->address }}</div>
    </div>
    <div class="row">
{{--        @php--}}
{{--            $re="/\d{1,2}\.\d{1,2}\.\d{2,4}/";--}}
{{--            preg_match($re, $data_order->delivery_date, $arr_date_delivery);--}}
{{--        @endphp--}}
        <div class="col-md-12"><b>Дата доставки</b>: {{ date("d.m.Y", strtotime($data_order->delivery_date)) }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Время доставки</b>: {{ $data_order->delivery_time }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Дата создания</b>: {{ date("d.m.Y H:i:s", strtotime($data_order->created_at)) }}</div>
    </div>
    <div class="row">
        <div class="col-md-12"><b>Комментарий</b>: {{ $data_order->comment }}</div>
    </div>

</div>
<div class="container w-50 float-left col miniTableOrders">

    <div class="row">
        <div class="col-md-6 orders_content_left_th">Наименование</div>
        <div class="col-md-2 orders_content_center_th">Ед</div>
        <div class="col-md-2 orders_content_center_th">Цена</div>
        <div class="col-md-2 orders_content_center_th">Всего</div>
    </div>

    @foreach($data_order_content as $content)
        <div class="row">
                <div class="col-md-6 orders_content_left">{{ $content->product_name }}</div>
                <div class="col-md-2 orders_content_center">{{ $content->quantity }}шт</div>
                <div class="col-md-2 orders_content_center">{{ $content->price }}</div>
                <div class="col-md-2 orders_content_center">{{ $content->price_all }}</div>
        </div>
    @endforeach

    @if ($data_order->cashback_out !== null && $data_order->cashback_out > 0)
        <div class="row mt-2" style="color: blue">
            <div class="col-md-6 orders_content_left_th">Оплачено кешбэком</div>
            <div class="col-md-6 orders_content_center_th">{{ $data_order->cashback_out }} грн</div>
        </div>
    @endif

    <div class="row mt-2">
        <div class="col-md-6 orders_content_left">Доставка</div>
        <div class="col-md-6 orders_content_center">{{ $data_order->delivery_sum }} грн</div>
    </div>

    <div class="row mt-2">
        <div class="col-md-6 orders_content_left_th">Итого</div>
        <div class="col-md-6 orders_content_center_th">{{ $data_order->price_with_discount }} грн</div>
    </div>

    @if ($data_order->cashback_in !== null && $data_order->cashback_in > 0)
        <div class="row mt-2" style="color: blue">
            <div class="col-md-6 orders_content_left_th">Начислено кешбэк</div>
            <div class="col-md-6 orders_content_center_th">{{ $data_order->cashback_in }} грн</div>
        </div>
    @endif

</div>
