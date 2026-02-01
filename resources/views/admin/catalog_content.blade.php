<div class="main">

    @if ($cat_prod == 'categories')
        @include('admin.categories_content')
    @elseif ($cat_prod == 'products')
        @include('admin.products_content')
    @elseif ($cat_prod == 'sizes')
        @include('admin.sizes_content')
    @endif

</div>


