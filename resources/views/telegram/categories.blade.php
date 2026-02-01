{{--<div class="logo">--}}
{{--    <img src="{{ asset ('assets/img/ECO-pizza-Logo.png') }}" alt="" class="img">--}}
{{--</div>--}}
@foreach($menus as $menu)
    @if ($menu->category_id == $category_select)
        <a href="#" class="categories_item active" id="{{ $menu->category_id }}">{{ $menu->menu_value_uk }}</a>
    @else
        <a href="#" class="categories_item" id="{{ $menu->category_id }}">{{ $menu->menu_value_uk }}</a>
    @endif
@endforeach

<script>
$(document).ready( function () {
        $('.categories_item').click(function(e) {
            e.preventDefault();
            var id = this.id;
            $(".categories_item").removeClass("active");
            $("#"+id).addClass("active");
            $("#products").html('<div class="container-fluid w-100 h-100 p-0 m-0 bg_tr_5 pt-0 pl-3 pr-3 text-center" style="position: relative; display: flex; align-items: center; justify-content: center;"><div class="loader"></div></div>');
            $.ajax({
                type: "GET",
                url: "{{ route('get_products') }}",
                data: "category_select="+id,
                cache: false
            }).done(function(data) {
                $("#products").html(data);
            }).fail(function() {
                $("#products").html("Произошла ошибка");
            });
        });
    });
</script>
