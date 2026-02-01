<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotAdvertisingChannelTexts extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_advertising_channel_texts';
    public $timestamps = false;

    public function get_text()
    {
        return $this->belongsTo(BotSettingsTexts::class, 'text_id', 'id');
    }

}


