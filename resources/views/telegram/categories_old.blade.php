@foreach($menus as $menu)
    <div class="categories" id="{{ $menu->category_id }}" style="margin: 5px; padding: 10px; min-width: 150px; cursor: pointer; border: 1px solid; border-radius: 10px;{{ $menu->category_id == $category_select ? ' background: #1c7430; color: #ffffff;' : '' }}">
        {{ $menu->menu_value_uk }}
    </div>
@endforeach

<script>
    $(document).ready( function () {
        $('.categories').click(function(e) {
            e.preventDefault();
            var id = this.id;
            $.ajax({
                type: "GET",
                url: "{{ route('get_products') }}",
                data: "category_select="+id,
                cache: false
            }).done(function(data) {
                $(".categories").css("background-color", "#ffffff");
                $(".categories").css("color", "#222222");
                $("#"+id).css("background-color", "#1c7430");
                $("#"+id).css("color", "#ffffff");
                $("#products").html(data);
            }).fail(function() {
                $("#products").html("Произошла ошибка");
            });
        });
    });
</script>
