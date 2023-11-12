<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'type',
        'password',
        'title',
        'end_yn',
        'token'
    ];

    public function orders() {
        return $this->hasMany(Order::class, 'room_id', 'id');
    }

    public function user() {
        return $this->hasOne(User::class, 'id', 'uesr_id');
    }
}
