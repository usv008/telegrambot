<div class="sidenav bg-dark">

    <div class="catalog"><b>Бот</b></div>

    @permission('users')
        <a href="{{ route('users') }}">🙍‍♀️ Пользователи</a>
    @endpermission

    @permission('orders')
        <a href="{{ route('orders') }}">📦 Заказы</a>
    @endpermission

    @permission('feedback')
        <a href="{{ route('feedback') }}">🏅 Оценки</a>
    @endpermission

    @permission('reviews')
    <p>📢 Отзывы</p>
        <a class="ml-3" href="{{ route('reviews') }}">🤖 Бот</a>
        <a class="ml-3" href="{{ route('sea-battle-rates') }}">🚢 Морской бой</a>
    @endpermission

    @permission('raffle')
    <p>🎲 Розыгрыш</p>
        <a class="ml-3" href="{{ route('raffle') }}">🍕 Выиграй пиццу</a>
        <a class="ml-3" href="{{ route('sea-battle') }}">🚢 Морской бой</a>
    @endpermission

    @permission('users_history')
        <a href="{{ route('users_history') }}">📖 История</a>
    @endpermission

{{--    @permission('stat')--}}
{{--        <a href="{{ route('stat') }}">📊 Статистика</a>--}}
{{--    @endpermission--}}

    @permission('stat')
    <p>📊 Статистика</p>
        <a class="ml-3" href="{{ route('stat') }}">📊 Общая</a>
        <a class="ml-3" href="{{ route('stat_new_users') }}">📊 Новые юзеры</a>
    @endpermission

    @permission('advertising')
        <a href="{{ route('stat_advertising') }}">📊 Реклама</a>
    @endpermission

    @permission('mailing')
        <a href="{{ route('mailing') }}">✉️ Рассылка</a>
    @endpermission

    @permission('chat')
        <a href="{{ route('chat') }}">💬 Чат{{ isset($messages_unreaded) && $messages_unreaded > 0 ? ' ('.$messages_unreaded.') ❗️' : '' }}</a>
    @endpermission

    @permission('settings')
        <p>⚙️ Настройки</p>
        <a class="ml-3" href="{{ route('settings') }}">🕰 Основные</a>
        <a class="ml-3" href="{{ route('settings_cb_and_actions') }}">🤑 КБ и акции</a>
        <a class="ml-3" href="{{ route('settings_payments') }}">💳 Платежи</a>
        <a class="ml-3" href="{{ route('settings_working_hours') }}">🕐 Робочий час</a>
    @endpermission

</div>
