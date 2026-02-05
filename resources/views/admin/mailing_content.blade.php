<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 98%; margin: 0 auto; text-align: center;">

        {!! Form::open(['url'=>route('add_mailing'), 'id' =>'form_send', 'class'=>'form-horizontal', 'method'=>'POST', 'enctype'=>'multipart/form-data']) !!}

        <div class="form-group text-left">
            {!! Form::label('only_us', 'Только своим', ['class'=>'control-label']) !!}
            {!! Form::checkbox('only_us', '1', true, ['class'=>'control-label', 'id'=>'only_us']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('send_users_ids', 'Список ID пользователей для отправки тестового сообщения', ['id' => 'message_send_users_ids','class'=>'col-xs-2 control-label']) !!}
            {!! Form::textarea('send_users_ids', old('send_users_ids') !== null ? old('send_users_ids') : '', ['rows'=>'3','class'=>'form-control text_area', 'placeholder'=>'Список ID пользователей (каждый с новой строки)']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('sticker', 'Город', ['class'=>'col-xs-2 control-label']) !!}
            <select name="city" id="city" class="browser-default custom-select w-100">
                <option value="0" selected>Не выбран</option>
                @foreach($cities as $city)
                    @if ($city->simpla_region_id == 6)
                        <option value="{{ $city->simpla_region_id }}">{{ $city->name }} ({{ $users->whereIn('city_id', [$city->simpla_region_id, null])->count() }})</option>
                    @else
                        <option value="{{ $city->simpla_region_id }}">{{ $city->name }} ({{ $users->whereIn('city_id', [$city->simpla_region_id])->count() }})</option>
                    @endif
                @endforeach
            </select>
        </div>
        <hr class="col-xs-12">

        <div class="form-group">
            {!! Form::label('sticker', 'Стикер', ['class'=>'col-xs-2 control-label']) !!}
            <select name="sticker" id="sticker" class="browser-default custom-select w-100">
                <option value="" selected>Не выбран</option>
                @foreach($stickers as $sticker)
                    <option value="{{ $sticker['sticker_value'] }}">{{ $sticker['sticker_command'] }}</option>
                @endforeach
            </select>
            <div id="sticker_img"></div>
        </div>
        <hr class="col-xs-12">
        <div class="form-group">
            {!! Form::file('image', ['id' => 'image_file', 'class'=>'filestyle', 'data-buttonText'=>"Выберите изображение", 'data-buttonName'=>"btn-success", 'data-placeholder'=>"Изображение не выбрано", 'accept'=>'image/jpeg,image/png,image/gif']) !!}
            <button type="button" id="clear_file_image" class="btn btn-danger">Убрать изображение</button>
            {{--                {!! Form::hidden('action', $data['action']) !!}--}}
        </div>
        <hr class="col-xs-12 mt-4">
        <div class="row mt-4">
{{--            <div class="col">--}}
{{--                {!! Form::label('text_ru', 'Текст сообщения на русском', ['id' => 'message_text_ru','class'=>'col-xs-2 control-label']) !!}--}}
{{--                {!! Form::textarea('text_ru', old('text_ru') !== null ? old('text_ru') : '', ['rows'=>'3','class'=>'form-control text_area', 'placeholder'=>'Текст сообщения на русском', 'required']) !!}--}}
{{--            </div>--}}
            <div class="col">
                {!! Form::label('text_uk', 'Текст сообщения на украинском', ['id' => 'message_text_uk','class'=>'col-xs-2 control-label']) !!}
                {!! Form::textarea('text_uk', old('text_uk') !== null ? old('text_uk') : '', ['id' => 'text_uk', 'rows'=>'3','class'=>'form-control text_area', 'placeholder'=>'Текст сообщения на украинском', 'required']) !!}
            </div>
            <div class="col">
                {!! Form::label('text_en', 'Текст сообщения на английском', ['id' => 'message_text_en','class'=>'col-xs-2 control-label']) !!}
                {!! Form::textarea('text_en', old('text_en') !== null ? old('text_en') : '', ['rows'=>'3','class'=>'form-control text_area', 'placeholder'=>'Текст сообщения на английском', 'required']) !!}
            </div>
        </div>
        <hr class="col-xs-12 mt-4">
        Реакции
        <button type="button" class="btn btn-success" id="reaction_add">+</button>
        <button type="button" class="btn btn-danger" id="reaction_remove">-</button>
        <table class="reactions_inputs w-100"></table>
        <table class="reactions_answers w-100"></table>
        <hr class="col-xs-12 mt-4">
        <div class="row mt-4">
{{--            <div class="col">--}}
{{--                {!! Form::label('button_text_ru', 'Текст кнопки на русском', ['class'=>'col-xs-2 control-label']) !!}--}}
{{--                {!! Form::text('button_text_ru', '', ['class'=>'form-control text', 'id'=>'button_text_ru', 'placeholder'=>'Текст кнопки на русском']) !!}--}}
{{--            </div>--}}
            <div class="col">
                {!! Form::label('button_text_uk', 'Текст кнопки на украинском', ['class'=>'col-xs-2 control-label']) !!}
                {!! Form::text('button_text_uk', '', ['class'=>'form-control text', 'id'=>'button_text_uk', 'placeholder'=>'Текст кнопки на украинском']) !!}
            </div>
            <div class="col">
                {!! Form::label('button_text_en', 'Текст кнопки на английском', ['class'=>'col-xs-2 control-label']) !!}
                {!! Form::text('button_text_en', '', ['class'=>'form-control text', 'id'=>'button_text_en', 'placeholder'=>'Текст кнопки на английском']) !!}
            </div>
        </div>
        <div class="form-group mt-1">
            {!! Form::label('button_data', 'Команда кнопки', ['class'=>'col-xs-2 control-label']) !!}
            <select name="button_data" id="button_data" class="browser-default custom-select w-100">
                <option value="addstat_gotostart___" selected>по умолчанию - Старт</option>
                <option value="addstat_gotostart___">🏁 Старт</option>
                <option value="addstat_begin___">🏁 Сделать заказ</option>
                <option value="addstat_gocontactus___">📞 Связаться с нами</option>
                <option value="addstat_rafflego___">🍕 Испытай свою удачу</option>
                <option value="addstat_gomore___">🧐 Ещё...</option>
                <option value="addstat_change_lang___">⚙️ Изменить язык</option>
                <option value="addstat_gocashback___">🤑‍ Кешбэк</option>
                <option value="addstat_goactions___">💣 Акции</option>
                <option value="addstat_goreviews___">📢 Отзывы</option>
                <option value="addstat_goshare___">👍 Поделиться</option>
                <option value="addstat_select_games___">🎲 Выбор игры</option>
                <option value="addstat_game_sea_battle_warning___">⛵️ Морской бой</option>
                <option value="addstat_addtocartproductfrommailing___">Добавить к заказу</option>
            </select>
        </div>
        <div class="form-group mt-1">
            {!! Form::label('text', 'Акционный товар - используется только при выборе "Добавить к заказу"', ['class'=>'col-xs-2 control-label']) !!}
{{--            {!! Form::text('variant_id', '', ['class'=>'form-control text', 'id'=>'variant_id', 'placeholder'=>'ID варианта - используется только при выборе "Добавить к заказу']) !!}--}}
            <select name="variant_id" id="variant_id" class="browser-default custom-select w-100">
                <option value="0" selected>Не выбран</option>
                @foreach($action_products as $product)
                    <option value="{{ $product->id_product_attribute }}">{{ $product->name }}{{ $product->variant ? ' ('.$product->variant.')' : '' }} - {{ bcadd($product->price, 0, 2) }} грн</option>
                @endforeach
            </select>
        </div>
        <hr class="col-xs-12 mt-5">
        <div class="row mt-4">
{{--            <div class="col">--}}
{{--                {!! Form::label('button_text2_ru', 'Текст второй кнопки на русском', ['class'=>'col-xs-2 control-label']) !!}--}}
{{--                {!! Form::text('button_text2_ru', '', ['class'=>'form-control text', 'id'=>'button_text2_ru', 'placeholder'=>'Текст второй кнопки на русском']) !!}--}}
{{--            </div>--}}
            <div class="col">
                {!! Form::label('button_text2_uk', 'Текст второй кнопки на украинском', ['class'=>'col-xs-2 control-label']) !!}
                {!! Form::text('button_text2_uk', '', ['class'=>'form-control text', 'id'=>'button_text2_uk', 'placeholder'=>'Текст второй кнопки на украинском']) !!}
            </div>
            <div class="col">
                {!! Form::label('button_text2_en', 'Текст второй кнопки на английском', ['class'=>'col-xs-2 control-label']) !!}
                {!! Form::text('button_text2_en', '', ['class'=>'form-control text', 'id'=>'button_text2_en', 'placeholder'=>'Текст второй кнопки на английском']) !!}
            </div>
        </div>
        <div class="form-group mt-1">
            {!! Form::label('button_data2', 'Команда второй кнопки', ['class'=>'col-xs-2 control-label']) !!}
            <select name="button_data2" id="button_data2" class="browser-default custom-select w-100">
                <option value="addstat_gotostart___" selected>по умолчанию - Старт</option>
                <option value="addstat_gotostart___">🏁 Старт</option>
                <option value="addstat_begin___">🏁 Сделать заказ</option>
                <option value="addstat_gocontactus___">📞 Связаться с нами</option>
                <option value="addstat_rafflego___">🍕 Испытай свою удачу</option>
                <option value="addstat_gomore___">🧐 Ещё...</option>
                <option value="addstat_change_lang___">⚙️ Изменить язык</option>
                <option value="addstat_gocashback___">🤑‍ Кешбэк</option>
                <option value="addstat_goactions___">💣 Акции</option>
                <option value="addstat_goreviews___">📢 Отзывы</option>
                <option value="addstat_goshare___">👍 Поделиться</option>
                <option value="addstat_select_games___">🎲 Выбор игры</option>
                <option value="addstat_game_sea_battle_warning___">⛵️ Морской бой</option>
            </select>
        </div>
        <hr class="col-xs-12 mt-4">
        <div class="form-group mt-3">
            {!! Form::button('Отправить', ['class'=>'btn btn-primary btn_submit', 'type'=>'submit']) !!}
            {!! Form::close() !!}
        </div>
    </div>

    <div style="width: 100%; margin: 0 auto; text-align: center;" class="w-100 mt-5">

        <h3>Отправленные</h3>
        <table class="table table-striped data-table" id="posts_datatable" style="width: 100%;">
            <thead>
            <tr>
                <th>Стикер</th>
                <th>Текст</th>
                <th>Фото</th>
                <th>Реакции</th>
                <th>Кнопка 1</th>
                <th>Кнопка 2</th>
                <th>Всего</th>
                <th>Отправлено</th>
                <th>Не отправлено</th>
                <th>CTR</th>
                <th>Дата</th>
                <th>Повтор</th>
                <th>Удалить</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <br />
    </div>

</div>


<script type="text/javascript">

    window.reset = function(e) {

        e.wrap('<form>').closest('form').get(0).reset();
        e.unwrap();
        $('#image_file').attr({ value: '' });
        $('#image_file').attr({ value: '' });
        e.replaceWith($('#image_file').clone());

    }

    var reaction_n = 0;

    $(document).ready( function () {

        $( "#clear_file_image" ).click(function() {
            $('#image_file').val('').change();
        });

        $( "#sticker" ).change(function(e) {
            e.preventDefault();
            var value = this.value;
            // alert(this.text);
            if (value != '') $("#sticker_img").html('<img src="'+value+'" height="150" />');
            else $("#sticker_img").html('');
        });

        $( "#button_data" ).change(function(e) {
            e.preventDefault();
            var text = $("#button_data option:selected").text();
            $("#button_text_ru").val(text);
        });

        $( "#button_data2" ).change(function(e) {
            e.preventDefault();
            var text = $("#button_data2 option:selected").text();
            $("#button_text2_ru").val(text);
        });

        $("#reaction_add").click(function() {
            reaction_n++;
            $('<tr class="reaction_tr mt-4">' +
                // '<td>' +
                // '   <label for="button_text_ru'+reaction_n+'" class="col-xs-2 control-label">Реакция на русском</label>' +
                // '   <input class="form-control text" id="reaction_text_ru'+reaction_n+'" placeholder="Реакция на русском" name="reaction_text_ru['+reaction_n+']" type="text" value="" required>' +
                // '</td>' +
                '<td>' +
                '   <label for="reaction_text_uk'+reaction_n+'" class="col-xs-2 control-label">Реакция на украинском</label>' +
                '   <input class="form-control text" id="reaction_text_uk'+reaction_n+'" placeholder="Реакция на украинском" name="reaction_text_uk['+reaction_n+']" type="text" value="" required>' +
                '</td>' +
                '<td>' +
                '   <label for="reaction_text_en'+reaction_n+'" class="col-xs-2 control-label">Реакция на английском</label>' +
                '   <input class="form-control text" id="reaction_text_en'+reaction_n+'" placeholder="Реакция на английском" name="reaction_text_en['+reaction_n+']" type="text" value="" required>' +
                '</td>' +
                '</tr>').fadeIn('slow').appendTo('.reactions_inputs');
            if (reaction_n == 1) {
                $('<tr class="reaction_answer_tr mt-4">' +
                    // '<td>' +
                    // '   <label for="reaction_answer_ru" class="col-xs-2 control-label">Ответ на реакции на русском</label>' +
                    // '   <textarea class="form-control text" id="reaction_answer_ru" placeholder="Ответ на реакции на русском" name="reaction_answer_ru" required></textarea>' +
                    // '</td>' +
                    '<td>' +
                    '   <label for="reaction_answer_uk" class="col-xs-2 control-label">Ответ на реакции на украинском</label>' +
                    '   <textarea class="form-control text" id="reaction_answer_uk" placeholder="Ответ на реакции на украинском" name="reaction_answer_uk" required></textarea>' +
                    '</td>' +
                    '<td>' +
                    '   <label for="reaction_answer_en" class="col-xs-2 control-label">Ответ на реакции на английском</label>' +
                    '   <textarea class="form-control text" id="reaction_answer_en" placeholder="Ответ на реакции на английском" name="reaction_answer_en" required></textarea>' +
                    '</td>' +
                    '</tr>').fadeIn('slow').appendTo('.reactions_answers');
            }
        });

        $("#reaction_remove").click(function() {
            if (reaction_n === 1) {
                $('.reaction_answer_tr:last').remove();
            }
            if(reaction_n > 0) {
                $('.reaction_tr:last').remove();
                reaction_n--;
            }
        });


        $('#form_send').on('submit',(function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var variant_id = parseInt($("#variant_id").val());

            if ($("#button_data").val() === 'addstat_addtocartproductfrommailing___') {

                if (!isNaN(variant_id) && variant_id !== 0 && variant_id > 0) {

                    $("#modal_body").html('<img src="{{ url('/assets/img/loader.gif') }}" width="50" />');
                    $("#modal_header").html("Рассылка");
                    $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');

                    $.ajax({
                        type: "POST",
                        url: $(this).attr("action"),
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function(data) {
                            $("#modal_body").html(data);
                        },
                        error: function(data) {
                            $("#modal_body").html(data);
                        }
                    });

                    $("#exampleModalCenter").modal("show");

                }
                else {
                    alert('Не заполнено поле ID варианта!');
                    return false;
                }

            }
            else {

                $("#modal_body").html('<img src="{{ url('/assets/img/loader.gif') }}" width="50" />');
                $("#modal_header").html("Рассылка");
                $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');

                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success:function(data) {
                        $("#modal_body").html(data);
                    },
                    error: function(data) {
                        $("#modal_body").html(data);
                    }
                });

                $("#exampleModalCenter").modal("show");

            }

            {{--var only_us = $("#only_us").prop("checked");--}}
            {{--var image = $(".filestyle").val();--}}
            {{--var text = $(".text_area").val();--}}
            {{--alert('debug= '+only_us+"; "+image+"; "+text);--}}

            {{--$.ajax({--}}
            {{--    type: "POST",--}}
            {{--    url: "{{ route('add_mailing') }}",--}}
            {{--    data: "_token={{ csrf_token() }}&order_id="+order_id,--}}
            {{--    cache: false--}}
            {{--}).done(function(modaldata) {--}}
            {{--    $("#large_modal_title").html("Заказ №"+order_id);--}}
            {{--    $("#large_modal_body").html(modaldata);--}}
            {{--    $("#large_modal_footer").html("");--}}

            {{--    $("#largeModalCenter").modal("show");--}}
            {{--    // $("#modal_dialog").html(modaldata);--}}
            {{--}).fail(function() {--}}
            {{--    $("#modal_body").html("Произошла ошибка");--}}
            {{--    $("#largeModalCenter").modal("show");--}}
            {{--});--}}
        }));

        var table = $("#posts_datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('posts_list') }}",
            columns: [
                { data: 'sticker', name: 'sticker' },
                { data: 'text', name: 'text' },
                { data: 'photo', name: 'photo' },
                { data: 'reactions', name: 'reactions' },
                { data: 'button1', name: 'button1' },
                { data: 'button2', name: 'button2' },
                { data: 'total', name: 'total' },
                { data: 'send_yes', name: 'send_yes' },
                { data: 'send_no', name: 'send_no' },
                { data: 'ctr', name: 'ctr' },
                { data: 'date_z', name: 'date_z' },
                { data: 'repeat_post', name: 'repeat_post' },
                { data: 'delete_post', name: 'delete_post' }
            ],
            // destroy: true,
            stateSave: true,
            "aLengthMenu": [[10, 25, 50, 75, -1], [10, 25, 50, 75, "Все"]],
            "iDisplayLength": 10,
            "language": {
                "processing": "🚀 Загружаю...",
                "lengthMenu": "Показывать по _MENU_ записей на странице",
                "zeroRecords": "Ничего не найдено",
                // "info": "Страница _PAGE_ из _PAGES_",
                "info": "Записи с _START_ по _END_ из _TOTAL_",
                "infoEmpty": "Нет записей",
                "infoFiltered": "(фильтр: _TOTAL_ из _MAX_ записей)",
                "search": "Поиск",
                "paginate": {
                    "first":      "Первая",
                    "last":       "Последняя",
                    "next":       "Следующая",
                    "previous":   "Предыдущая"
                }
            },
//             "columnDefs": [
//                 { "targets": [ 3 ], "visible": false },
//                 { "type": "string", "targets": [3, 4, 5] },
//             ]//,
            //"order": [[ 5, "desc" ], [ 2, "asc" ]]
            "order": [[ 10, "desc" ]]
        });

        table.on( 'draw', function () {

            /**
             * Подгружаем функцию обработки нажатия кнопки удаления поста
             */
            $( ".button_delete" ).click(function() {
                var id = this.id;
                $("#modal_header").html("Удаление поста");
                $.ajax({
                    type: "POST",
                    url: "{{ route('post_delete') }}",
                    data: "_token={{ csrf_token() }}&id="+id,
                    cache: false
                }).done(function(deletedata) {
                    $("#modal_body").html(deletedata);
                    $("#exampleModalCenter").modal("show");
                }).fail(function() {
                    $("#modal_body").html("Произошла ошибка");
                    $("#exampleModalCenter").modal("show");
                });
            });

            $( ".button_repeat" ).click(function() {
                var id = this.id;
                $('#text_uk').html($(this).attr('data-text_uk'));
                $("html, body").animate({ scrollTop: 0 }, "slow");
                return false;
            });

        });

    });
</script>

{{--<script type="text/javascript">--}}

{{--    $(document).on('click', '[data-toggle="lightbox"]', function(event) {--}}
{{--        event.preventDefault();--}}
{{--        $(this).ekkoLightbox();--}}
{{--    });--}}

{{--</script>--}}

