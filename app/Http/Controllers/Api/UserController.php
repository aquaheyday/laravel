<?php
     
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use DB;
     
class UserController extends Controller
{
    protected $email;

    public function __construct()
    {
        $this->email = auth()->guard('api')->user()->email;
    }

    public function info() {

        $userInfo = User::select(
            'users.name'
            ,'users.email'
            ,DB::raw("count('orders.email') as totalCount")
            ,DB::raw("sum(if(orders.pickup = 'Y', 1, 0)) as pickupCount")
        )
        ->leftJoin('orders', 'users.email', 'orders.email')
        ->where('users.email', $this->email)
        ->first();

        $success = true;

        return Json($success ?? false, $data ?? null, $message ?? null);
    }
}