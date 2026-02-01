<div class="container w-100 float-left col">

{{--    <div class="row">--}}
{{--        <div class="col-md-6 orders_content_left_th">Наименование</div>--}}
{{--        <div class="col-md-2 orders_content_center_th">Ед</div>--}}
{{--        <div class="col-md-2 orders_content_center_th">Цена</div>--}}
{{--        <div class="col-md-2 orders_content_center_th">Всего</div>--}}
{{--    </div>--}}

    @foreach($raffles as $raffle)
        <div class="row">
            <div class="col-md-3 orders_content_left">{{ $raffle->date_reg }}</div>
            <div class="col-md-3 orders_content_center">{{ $raffle->date_edit }}</div>
        </div>
        <div class="row border-bottom">
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p1) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p2) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p3) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p4) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p5) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p6) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p7) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p8) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p9) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p10) }}</div>
                <div class="orders_content_center border-right" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p11) }}</div>
                <div class="orders_content_centert" style="width:60px;">{{ str_replace("___", "✔️", $raffle->p12) }}</div>
        </div>
    @endforeach

</div>
