<div style="width: 99%; margin-bottom: 20px;">

    @include('admin.header_content')

    <div class="w-100" style="width: 100%; margin: 0 auto; text-align: left;" id="settings_div">
        <form action="{{ route('settings_cb_and_actions_save') }}" method="post">
        <fieldset>
            <legend>Категории, которые учавствуют в начислении / списании КБ:</legend>
            @csrf
            @foreach($categories as $category)
                <div>
                    <input type="checkbox" id="category_{{ $category->id_category }}" name="categories[{{ $category->id_category }}]" value="1"{{ $cashback_categories->where('category_id', $category->id_category)->count() > 0 ? ' checked' : '' }}>
                    <label for="category_{{ $category->id_category }}">{{ $category->name }}</label>
                </div>
            @endforeach
        </fieldset>
            <div class="settings_save">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>

    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

    });

</script>
