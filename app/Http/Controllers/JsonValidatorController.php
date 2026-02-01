<?php

namespace App\Http\Controllers;

class JsonValidatorController extends Controller
{

    public static function jsonValidate($data) {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

}
