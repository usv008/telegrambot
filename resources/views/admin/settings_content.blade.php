<div style="width: 99%;">

    @include('admin.header_content')

    <div class="w-100" style="width: 100%; margin: 0 auto; text-align: center;" id="settings_div">
        <form action="{{ route('settings_save') }}" method="post">
            @csrf
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'time_open')->first()['id'] }}">Работаем с</span>
                </div>
                <input type="time" class="form-control" name="time_open" placeholder="Работаем с" aria-label="Работаем с" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'time_open')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'time_open')->first()['settings_value'] }}" />
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'time_close')->first()['id'] }}">Работаем до</span>
                </div>
                <input type="time" class="form-control" name="time_close" placeholder="Работаем до" aria-label="Работаем до" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'time_close')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'time_close')->first()['settings_value'] }}" />
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'min_sum_order')->first()['id'] }}">Минимальная сумма заказа</span>
                </div>
                <input type="text" class="form-control" name="min_sum_order" placeholder="Минимальная сумма заказа" aria-label="Минимальная сумма заказа" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'min_sum_order')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'min_sum_order')->first()['settings_value'] }}" />
                <div class="input-group-append">
                    <span class="input-group-text">грн</span>
                </div>
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'sum_delivery')->first()['id'] }}">Сумма доставки</span>
                </div>
                <input type="text" class="form-control" name="sum_delivery" placeholder="Сумма доставки" aria-label="Сумма доставки" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'sum_delivery')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'sum_delivery')->first()['settings_value'] }}" />
                <div class="input-group-append">
                    <span class="input-group-text">грн</span>
                </div>
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'max_sum_order')->first()['id'] }}">Максимальная сумма заказа</span>
                </div>
                <input type="text" class="form-control" name="max_sum_order" placeholder="Максимальная сумма заказа" aria-label="Максимальная сумма заказа" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'max_sum_order')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'max_sum_order')->first()['settings_value'] }}" />
                <div class="input-group-append">
                    <span class="input-group-text">грн</span>
                </div>
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'youtube_link')->first()['id'] }}">Ссылка на youtube</span>
                </div>
                <input type="text" class="form-control" name="youtube_link" placeholder="Ссылка на youtube" aria-label="Ссылка на youtube" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'youtube_link')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'youtube_link')->first()['settings_value'] }}" />
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'product_present_quantity_max')->first()['id'] }}">Макс. кол-во презентационных товаров</span>
                </div>
                <input type="text" class="form-control" name="product_present_quantity_max" placeholder="Макс. кол-во презентационных товаров" aria-label="Макс. кол-во презентационных товаров" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'youtube_link')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'product_present_quantity_max')->first()['settings_value'] }}" />
            </div>
            <div class="settings_save">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>

        <div class="settings_header mt-5"><h4>Картинки</h4></div>
        <div class="input-group mb-2">
            <div class="input-group-prepend">
                <span class="input-group-text" style="width: 350px;" id="text_for_update_pictures">Обновить картинки</span>
            </div>
            <button type="button" id="update_pictures" class="btn btn-primary" aria-describedby="text_for_update_pictures">Обновить картинки</button>
        </div>

        <div class="settings_header mt-5"><h4>CashBack</h4></div>
        <form action="{{ route('settings_cashback_save') }}" method="post">
            @csrf
            @foreach($cashback_settings as $setting)
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text" style="width: 350px;" id="cashback-addon{{ $setting->id }}">{{ $setting->settings_name_display }}</span>
                    </div>
                    <input type="text" class="form-control" name="{{ $setting->settings_name }}" placeholder="{{ $setting->settings_name_display }}" aria-label="{{ $setting->settings_name_display }}" aria-describedby="cashback-addon{{ $setting->id }}" value="{{ $setting->settings_value }}" />
                    <div class="input-group-append">
                        <span class="input-group-text">{{ $setting->settings_type }}</span>
                    </div>
                </div>
            @endforeach
            <div class="settings_save">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>

        <div class="settings_header mt-5"><h4>Права доступа</h4></div>
        <form action="{{ route('settings_user_role_save') }}" method="post">
            @csrf
            @foreach($users as $user)
                <div class="input-group mb-2">
                    <div class="input-group-prepend rounded">
                        <span class="input-group-text" style="width: 450px;" id="users-addon{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</span>
                    </div>
                    <select class="form-control rounded" name="users_role[{{ $user->id }}]">
                        <option value=""{{ $users_roles->where('user_id', $user->id)->count() == 0 ? ' selected' : '' }}>не назначены</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}"{{ $users_roles->where('user_id', $user->id)->where('role_id', $role->id)->count() > 0 ? ' selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-danger ml-1 button_delete" data-delete_id="{{ $user->id }}">Удалить</button>
                </div>
            @endforeach
            <div class="settings_save mb-3">
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </div>
        </form>
    </div>

</div>

<script type="text/javascript">

    $(document).ready( function () {

        $( "#update_pictures" ).click(function() {
            $("#modal_body").html('<img src="{{ url('/assets/img/loader.gif') }}" width="50" />');
            $("#modal_header").html("Обновление картинок");
            $("#modal_footer").html('<button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>');
            $.ajax({
                type: "GET",
                url: "{{ route('thumb_all') }}",
                data: '',
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
            // alert('123');
        });

        $( ".button_delete" ).click(function() {
            var id = $(this).attr('data-delete_id');
            $("#modal_header").html("Удаление пользователя");
            $.ajax({
                type: "POST",
                url: "{{ route('settings_user_delete') }}",
                data: "_token={{ csrf_token() }}&id="+id,
                cache: false
            }).done(function(deletedata) {
                $("#modal_body").html(deletedata);
                $("#exampleModalCenter").modal("show");
            }).fail(function() {
                $("#modal_body").html("Произошла ошибка");
                $("#exampleModalCenter").modal("show");
            });
            return false;
        });

    });

</script>
