<div class="text-center">
    {!! Form::open(['url'=>route('stat_advertising_edit'), 'id' =>'form_add', 'class'=>'form-horizontal', 'method'=>'POST', 'enctype'=>'multipart/form-data']) !!}

    {!! Form::label('name', 'Имя рекламного канала', ['class'=>'col-xs-2 control-label']) !!}
    {!! Form::text('name', $channel->name, ['class'=>'form-control text', 'id'=>'button_text', 'placeholder'=>'Имя рекламного канала', 'required']) !!}
    <br />
    {!! Form::label('url', 'Url рекламного канала', ['class'=>'col-xs-2 control-label']) !!}
    {!! Form::text('url', $channel->url, ['class'=>'form-control text', 'id'=>'button_text', 'placeholder'=>'Имя рекламного канала', 'required']) !!}
    {!! Form::hidden('channel_id', $channel->id) !!}
    <br />
    <div class="form-check text-left">
        <input type="checkbox" class="form-check-input" id="check_bonus" name="check_bonus"{{ $channel->bonus > 0 || $channel->product_present > 0 ? ' checked' : '' }}>
        <label class="form-check-label" for="check_bonus">Начислить бонусы / Товар в подарок</label>
    </div>
    <div class="text-left bonuses{{ $channel->bonus > 0 || $channel->product_present > 0 ? '' : ' d-none' }}">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="bonus_only_new_users" name="bonus_only_new_users"{{ $channel->only_new_users > 0 ? ' checked' : '' }}>
            <label class="form-check-label" for="bonus_only_new_users">Только новым пользователям</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="bonus_only_exists_users" name="bonus_only_exists_users"{{ $channel->only_exists_users > 0 ? ' checked' : '' }}>
            <label class="form-check-label" for="bonus_only_exists_users">Только существующим пользователям</label>
        </div>
        <div class="form-check mt-3">
            <input type="checkbox" class="form-check-input" id="product_present" name="product_present"{{ $channel->product_present > 0 ? ' checked' : '' }}>
            <label class="form-check-label" for="product_present">Товар в подарок (id варианта)</label>
        </div>
        <div class="form-group variant_id{{ $channel->product_present == 0 ? ' d-none' : '' }}">
            <input type="text" class="form-control" id="product_present_variant_id" name="product_present_variant_id" placeholder="id варианта" value="{{ $channel->product_present_variant_id }}">
        </div>
        <div class="form-group mt-3 bonus_add{{ $channel->product_present > 0 ? ' d-none' : '' }}">
            <label for="bonus_value">Сколько бонусов?</label>
            <input type="number" class="form-control" id="bonus_value" name="bonus_value" placeholder="Сколько бонусов начислить?" value="{{ $channel->bonus }}">
        </div>
        <div class="form-check mt-3">
            <input type="checkbox" class="form-check-input" id="limit_in" name="limit_in"{{ $channel->limit_in > 0 ? ' checked' : '' }}>
            <label class="form-check-label" for="limit_in">Лимит переходов</label>
        </div>
        <div class="form-group">
            <input type="number" class="form-control" id="limit_in_value" name="limit_in_value" placeholder="Лимит переходов" value="{{ $channel->limit_in_value }}">
        </div>
        <div class="form-group">
            <label for="text_ru">Текст (русский)</label>
            <textarea class="form-control" id="text_ru" name="text_ru" placeholder="Текст на русском">{{ $text !== null ? $text->text_value_ru : '' }}</textarea>
        </div>
        <div class="form-group">
            <label for="text_ru">Текст (украинский)</label>
            <textarea class="form-control" id="text_ru" name="text_uk" placeholder="Текст на украинском">{{ $text !== null ? $text->text_value_uk : '' }}</textarea>
        </div>
        <div class="form-group">
            <label for="text_ru">Текст (английский)</label>
            <textarea class="form-control" id="text_ru" name="text_en" placeholder="Текст на английском">{{ $text !== null ? $text->text_value_en : '' }}</textarea>
        </div>
    </div>
    <br />
    {!! Form::button('Сохранить', ['class'=>'btn btn-primary', 'type'=>'submit']) !!}
    {!! Form::close() !!}
</div>

<script type="text/javascript">

    $(document).ready( function () {
        $("#check_bonus").change(function () {
            if (this.checked) {
                // alert("13");
                $(".bonuses").removeClass('d-none');
                $("#bonus_value").prop('required',true);
                $("#text_ru").prop('required',true);
                $("#text_uk").prop('required',true);
                $("#text_en").prop('required',true);
            }
            else {
                $(".bonuses").addClass('d-none');
                $("#bonus_value").prop('required',false);
                $("#text_ru").prop('required',false);
                $("#text_uk").prop('required',false);
                $("#text_en").prop('required',false);
            }
        });
        $("#product_present").change(function () {
            if (this.checked) {
                $(".bonus_add").addClass('d-none');
                $("#bonus_value").prop('required',false);
                $("#product_present_variant_id").prop('required',true);
                $(".variant_id").removeClass('d-none');
            }
            else {
                $(".bonus_add").removeClass('d-none');
                $("#bonus_value").prop('required',true);
                $("#product_present_variant_id").prop('required',false);
                $(".variant_id").addClass('d-none');
            }
        });
    });

</script>
