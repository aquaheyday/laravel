<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Room;
use App\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'menu',
        'menu_type',
        'menu_size',
        'menu_detail',
        'sub_menu',
        'sub_menu_type',
        'sub_menu_size',
        'sub_menu_detail',
        'pick_up_yn',
    ];

    public function room() {
        return $this->hasOne(Room::class, 'id', 'room_id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
