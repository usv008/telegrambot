<?php

namespace App\Http\Controllers;

use App\Models\Simpla_Payment_Methods;

class SimplaPaymentController extends Controller
{

    public static function check_pay()
    {

        return Simpla_Payment_Methods::where('id', 22)->where('name', 'Telegram bot')->first()['enabled'];

    }

}
