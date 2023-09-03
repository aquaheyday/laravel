<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;

class Reception extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'email',
        'receptions_type',
        'password',
        'title',
        'end',
    ];

    public function orders() {
        return $this->hasMany(Order::class, 'receptions_id', 'id');
    }

    public function user() {
        return $this->hasOne(User::class, 'email', 'email');
    }
}
