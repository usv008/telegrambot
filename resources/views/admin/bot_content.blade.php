<div class="main" style="margin-left: 200px;">

    @if ($page == 'bot')
        @include('admin.users_content')
    @elseif ($page == 'users')
        @include('admin.users_content')
    @elseif ($page == 'orders')
        @include('admin.orders_content')
    @elseif ($page == 'feedback')
        @include('admin.feedback_content')
    @elseif ($page == 'reviews')
        @include('admin.reviews_content')
    @elseif ($page == 'stat')
        @include('admin.stat_content')
    @elseif ($page == 'stat_advertising')
        @include('admin.stat_advertising_content')
    @elseif ($page == 'mailing')
        @include('admin.mailing_content')
    @elseif ($page == 'chat')
        @include('admin.chats_content')
    @elseif ($page == 'chat_user')
        @include('admin.chat_content')
    @elseif ($page == 'users_history')
        @include('admin.users_history_content')
    @elseif ($page == 'raffle')
        @include('admin.raffle_content')
    @elseif ($page == 'sea_battle')
        @include('admin.sea_battle_content')
    @elseif ($page == 'sea_battle_rates')
        @include('admin.sea_battle_rates_content')
    @elseif ($page == 'settings')
        @include('admin.settings_content')
    @elseif ($page == 'settings_cb_and_actions')
        @include('admin.settings_cb_and_actions_content')
    @elseif ($page == 'settings_payments')
        @include('admin.settings_payments_content')
{{--    @elseif ($cat_prod == 'sizes')--}}
{{--        @include('admin.sizes_content')--}}
    @endif

</div>


