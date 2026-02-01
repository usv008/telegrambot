<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use PhpTelegramBot\Laravel\PhpTelegramBotContract;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
    ];

    public function __construct(\Illuminate\Contracts\Foundation\Application $app, \Illuminate\Contracts\Encryption\Encrypter $encrypter, PhpTelegramBotContract $telegram_bot)
    {
        $this->app = $app;
        $this->encrypter = $encrypter;
        $this->except[] = env('PHP_TELEGRAM_BOT_API_KEY');
        $this->except[] = env('LIQPAY_CALLBACK');
        $this->except[] = env('WAYFORPAY_CALLBACK');
        $this->except[] = env('WAYFORPAY_UBUNTU_CALLBACK');
        $this->except[] = env('PRESTASHOP_CALLBACK');
        $this->except[] = env('DRINKCHERRY_TELEGRAM_BOT_API_KEY');
        //print_r ($this->except);
    }

}
