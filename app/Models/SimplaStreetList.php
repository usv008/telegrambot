<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimplaStreetList extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_street_list';
    public $timestamps = false;

    public static function getStreets()
    {
        return self::orderBy('name', 'asc')->take(100)->get();
    }

    public static function searchStreet($text)
    {
        if ($text == null) return self::getStreets();
        return self::where('name', 'like', '%'.$text.'%')->get();
    }
}
