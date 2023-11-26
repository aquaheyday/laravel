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
    protected $id;

    public function __construct()
    {
        if (auth()->guard('api')->check()) {
            $this->id = auth()->guard('api')->user()->id;
        } else {
            response()->json([], 401, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function list($token)
    {
        $room = Room::select(
            'id'
            ,'type'
            ,'title'
            ,'end_yn'
            ,DB::raw("if(user_id = '" . $this->id ."', 'Y', 'N') as create_yn")
        )->where('token', $token)
        ->get();

        $menu = Order::join('rooms', function ($q) use ($token) {
            $q->on('rooms.id', 'orders.room_id')
            ->where('rooms.token', $token);
        })
            ->leftJoin('users', 'users.id', 'orders.user_id')
            ->select(
                'orders.menu'
                ,'orders.menu_type'
                ,'orders.menu_size'
                ,'orders.menu_detail'
                ,DB::raw('count(*) as count')
                ,DB::raw('group_concat(users.name) as name')
            )
            ->groupBy('menu', 'menu_type', 'menu_size', 'menu_detail')
            ->get();

        $user = Order::join('rooms', function ($q) use ($token) {
            $q->on('rooms.id', 'orders.room_id')
            ->where('rooms.token', $token);
        })
        ->leftjoin('users', 'users.id', 'orders.user_id')
                ->select(
                    'orders.id'
                    ,'users.name'
                    ,'users.image_path'
                    ,'orders.menu'
                    ,'orders.menu_type'
                    ,'orders.menu_size'
                    ,'orders.menu_detail'
                    ,'orders.sub_menu'
                    ,'orders.sub_menu_type'
                    ,'orders.sub_menu_size'
                    ,'orders.sub_menu_detail'
                    ,'orders.pick_up_yn'
                    ,DB::raw("if(orders.user_id = '" . $this->id ."', 'Y', 'N') as create_yn")
                )
                ->get();
        
        $data = [
            'room' => $room
            ,'user' => $user
            ,'menu' => $menu
        ];

        $success = true;

        return Json($success ?? false, $data ?? null, $message ?? null);
    }

    public function add($token, Request $request)
    {
        $check = Room::where('token', $token)->where('end_yn', 'N')->first();

        if (!is_null($check)) {
            $validator = Validator::make($request->all(), [
                'menu' => 'required'
                ,'menu_type' => 'required'
                ,'menu_size' => 'required'
            ]);
    
            if (!$validator->fails()) {
                Order::create([
                    'room_id' => $check->id
                    ,'user_id' => $this->id
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
        $order = Order::where('id', $id)
            ->where('user_id', $this->id)
            ->first();

        if (!is_null($order)) {
            if (Room::where('id', $order->room_id)->where('end_yn', 'N')->exists()) {
                $update = $request->all();
    
                Order::where('id', $order->id)
                ->where('user_id', $this->id)
                ->update($update);
    
            } else {
                $message = __('return.end');
            }
        }

        return Json(true, null, $message ?? null);
    }

    public function delete($id)
    {
        Order::where('id', $id)
            ->where('user_id', $this->id)
            ->delete();

        return Json(true);
    }
}