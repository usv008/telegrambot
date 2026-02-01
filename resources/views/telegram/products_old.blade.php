@foreach($products as $product)
    <div id="{{ $product->id }}" style="width: 150px; height: 150px; margin: 15px; display: inline-block; cursor: pointer;" data-toggle="modal" data-target="#exampleModal">
        <img src="{{ asset('/assets/img/thumb/'.$product->id.'.webp') }}" /><br />
        {{ $product->name }}<br />
        <small>{{ $product->product_feature }}</small><br />
        <small>від {{ bcmul($product->product_combinations->min('price'), 1, 2) }} грн</small><br />
        @foreach($product->product_combinations as $combination)
            <small>{{ bcmul($combination->price, 1, 2) }}грн ({{ $combination->options->first()->name }})</small><br />
        @endforeach
    </div>
@endforeach
