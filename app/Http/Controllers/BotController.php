<?php

namespace App\Http\Controllers;

class BotController extends Controller
{

    public static function show_menu() {

        if (view()->exists('admin.bot')) {

            $data = [
                'title' => 'Бот',
                'page' => 'bot',
            ];

            return view('admin.bot', $data);

        }

    }

}
