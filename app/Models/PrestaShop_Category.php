<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Category extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_category';
    public $timestamps = false;
    public static $id_lang = 2;

    public static function getCategoriesAll()
    {
        return self::join('ps_category_lang', 'ps_category_lang.id_category', 'ps_category.id_category')
            ->where('ps_category.active', 1)
            ->where('ps_category_lang.id_lang', self::$id_lang)
            ->orderBy('position', 'asc')
            ->get();
    }

}
