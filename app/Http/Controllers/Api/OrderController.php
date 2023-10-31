<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Room;
use App\Models\Type;
use Validator;
use DB;

class OrderController extends Controller
{
    protected $email;

    public function __construct()
    {
        $this->email = auth()->guard('api')->user()->email;
    }

    public function list($room_id)
    {
        $room = Room::select(
            'id'
            ,'room_type'
            ,'title'
            ,'end'
            ,DB::raw("if(email = '" . $this->email ."', true, false) as creater")
        )->where('id', $room_id)
        ->get();

        $menu = Order::leftJoin('users', 'users.email', 'orders.email')
            ->select(
                'orders.menu'
                ,'orders.menu_type'
                ,'orders.menu_size'
                ,'orders.menu_detail'
                ,DB::raw('count(*) as count')
                ,DB::raw('group_concat(users.name) as name')
            )
            ->where('room_id', $room_id)
            ->groupBy('menu', 'menu_type', 'menu_size', 'menu_detail')
            ->get();

        $user = Order::leftjoin('users', 'users.email', 'orders.email')
                ->select(
                    'users.name'
                    ,'orders.menu'
                    ,'orders.menu_type'
                    ,'orders.menu_size'
                    ,'orders.menu_detail'
                    ,'orders.sub_menu'
                    ,'orders.sub_menu_type'
                    ,'orders.sub_menu_size'
                    ,'orders.sub_menu_detail'
                    ,'orders.pickup'
                    DB::raw("if(orders.email = '" . $this->email ."', true, false) as creater")
                )
                ->where('room_id', $room_id)
                ->get();
        
        $data = [
            'room' => $room
            ,'user' => $user
            ,'menu' => $menu
        ];

        $success = true;

        return Json($success ?? false, $data ?? null, $message ?? null);
    }

    public function add($room_id, Request $request)
    {
        $check = Room::where('id', $room_id)->where('end', 'N')->exists();

        if ($check) {
            $validator = Validator::make($request->all(), [
                'menu' => 'required'
                ,'menu_type' => 'required'
                ,'menu_size' => 'required'
            ]);
    
            if (!$validator->fails()) {
                Order::create([
                    'room_id' => $room_id
                    ,'email' => $this->email
                    ,'menu' => $request->input('menu')
                    ,'menu_type' => $request->input('menu_type')
                    ,'menu_size' => $request->input('menu_size')
                    ,'menu_detail' => $request->input('menu_detail') ?? null
                    ,'sub_menu' => $request->input('sub_menu') ?? null
                    ,'sub_menu_type' => $request->input('sub_menu_type') ?? null
                    ,'sub_menu_size' => $request->input('sub_menu_size') ?? null
                    ,'sub_menu_detail' => $request->input('sub_menu_detail') ?? null
                ]);
    
                $success = true;
            } else {
                $message = $validator->errors()->first();
            }
        } else {
            $message = __('return.end');
        }

        return Json($success ?? false, null, $message ?? null);
    }

    public function edit($id, Request $request)
    {
        $email = auth()->guard('api')->user()->email;

        $order = Order::where('id', $id)
            ->where('email', $email)
            ->first();

        if (!is_null($order)) {
            if (Room::where('id', $order->receptions_id)->where('end', 'N')->exists()) {
                $update = $request->all();
    
                Order::where('id', $order->id)
                ->where('email', $order->email)
                ->update($update);
    
            } else {
                $message = __('return.end');
            }
        }

        return Json(true, null, $message ?? null);
    }

    public function delete($id)
    {
        $email = auth()->guard('api')->user()->email;

        Order::where('id', $id)
            ->where('email', $email)
            ->delete();

        return Json(true);
    }
}