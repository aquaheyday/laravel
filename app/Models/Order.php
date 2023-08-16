<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
