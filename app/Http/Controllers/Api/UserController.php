<?php
     
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use DB;
     
class UserController extends Controller
{
    protected $id;

    public function __construct()
    {
        $this->id = auth()->guard('api')->user()->id;
    }

    public function info() {

        $data = User::select(
            'users.name'
            ,'users.email'
            ,'users.image_path'
            ,DB::raw("sum(if('orders.id' != null, 1, 0)) as total_count")
            ,DB::raw("sum(if(orders.pick_up_yn = 'Y', 1, 0)) as pick_up_count")
        )
        ->leftJoin('orders', 'users.id', 'orders.id')
        ->where('users.id', $this->id)
        ->first();

        $success = true;

        return Json($success ?? false, $data ?? null, $message ?? null);
    }
}