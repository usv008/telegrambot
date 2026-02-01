<?php

namespace App\Http\Controllers;

use App\Models\SimplaUsers;

class SimplaUsersController extends Controller
{

    public static function check_user($phone)
    {

        if ($phone == null || !is_numeric($phone)) return null;
        return SimplaUsers::where('phone', '+'.$phone)->count();

    }

    public static function get_user($phone)
    {

        if ($phone == null || !is_numeric($phone)) return null;
        return SimplaUsers::where('phone', '+'.$phone)->first();

    }

    public static function add_user($user)
    {

        if (self::check_user($user['phone']) == 0) {
            $simpla_users = new SimplaUsers();
            $simpla_users->name = $user['name'];
            $simpla_users->phone = '+'.$user['phone'];
            $simpla_users->enabled =1;

            if ($simpla_users->save()) {
                return $simpla_users->id;
            }
            return null;
        }
        else return self::get_user($user['phone'])['id'];

    }

}
