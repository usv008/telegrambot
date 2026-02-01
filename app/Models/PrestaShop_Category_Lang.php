<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_Category_Lang extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_category_lang';
    public $timestamps = false;
}
