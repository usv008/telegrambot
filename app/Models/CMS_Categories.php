<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CMS_Categories extends Model
{
    protected $connection = 'mysql_cms';
    protected $table = 'cms_categories';
    public $timestamps = false;
}
