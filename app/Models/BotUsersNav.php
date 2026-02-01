<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUsersNav extends Model
{
    protected $connection = 'mysql_ecopizza';
    protected $table = 'bot_users_nav';
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'firstname',
        'cart_message_id',
        'menu_message_id',
        'order_message_id',
        'to_delete_message_id',
        'first_time',
        'change_key',
        'delivery_id',
        'date',
        'time',
        'payment_id',
        'name',
        'phone',
        'latitude',
        'longitude',
        'addr',
        'change_from',
        'sushi_sticks',
        'comment',
        'contactless',
        'contactless_comment',
        'no_call',
        'birthday',
        'order_sent',
        'feedback_id',
        'send_reminder',
        'send_reminder_datetime',
        'game_sea_battle_rate_id',
        'raffle_cherry_takeaway_id',
        'date_z'
    ];
}
