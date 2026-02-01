<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrestaShop_WD_MegaMenu extends Model
{
    protected $connection = 'mysql_ecopizza_stage';
    protected $table = 'ps_wdmegamenu';
    public $timestamps = false;
}
