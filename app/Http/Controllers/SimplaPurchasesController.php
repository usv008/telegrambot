<?php

namespace App\Http\Controllers;

use App\Models\SimplaPurchases;

class SimplaPurchasesController extends Controller
{

    public static function add_purchase($purcheses)
    {


        if (is_array($purcheses)) {
            return SimplaPurchases::insertGetId($purcheses);
        }

        return null;

    }

}
