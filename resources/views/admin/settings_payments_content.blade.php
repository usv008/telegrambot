<div style="width: 99%; margin-bottom: 20px;">

    @include('admin.header_content')

    <div class="w-100" style="width: 100%; margin: 0 auto; text-align: left;" id="settings_div">
        <div class="settings_header mt-3"><h4>LiqPay</h4></div>
        <form action="{{ route('settings_payments_save') }}" method="post">
            @csrf
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'liqpay_token')->first()['id'] }}">LiqPay token (BotFather)</span>
                </div>
                <input type="text" class="form-control" name="liqpay_token" placeholder="LiqPay token (BotFather)" aria-label="LiqPay token (BotFather)" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'liqpay_token')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'liqpay_token')->first()['settings_value'] }}" />
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'liqpay_private_key')->first()['id'] }}">LiqPay private key</span>
                </div>
                <input type="text" class="form-control" name="liqpay_private_key" placeholder="LiqPay private key" aria-label="LiqPay private key" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'liqpay_private_key')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'liqpay_private_key')->first()['settings_value'] }}" />
            </div>
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text" style="width: 350px;" id="bot-settings-addon{{ $bot_settings->where('settings_name', 'liqpay_public_key')->first()['id'] }}">LiqPay public key</span>
                </div>
                <input type="text" class="form-control" name="liqpay_public_key" placeholder="LiqPay public key" aria-label="LiqPay public key" aria-describedby="bot-settings-addon{{ $bot_settings->where('settings_name', 'liqpay_public_key')->first()['id'] }}" value="{{ $bot_settings->where('settings_name', 'liqpay_public_key')->first()['settings_value'] }}" />
            </div>
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
