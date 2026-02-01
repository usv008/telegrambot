<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Product_Attribute_Shop extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_product_attribute_shop';
    public $timestamps = false;
}
