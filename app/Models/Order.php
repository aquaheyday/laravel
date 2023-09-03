<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Reception;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'receptions_id',
        'email',
        'menu',
        'menu_type',
        'menu_size',
        'sub_menu',
        'sub_menu_type',
        'sub_menu_size',
        'detail',
    ];

    public function reception() {
        return $this->hasOne(Reception::class, 'id', 'receptions_id');
    }
}
