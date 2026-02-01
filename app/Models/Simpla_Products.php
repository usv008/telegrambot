<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpla_Products extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 's_products';
    public $timestamps = false;

    public static function getProduct($product_id)
    {
        return self::find($product_id);
    }

}
