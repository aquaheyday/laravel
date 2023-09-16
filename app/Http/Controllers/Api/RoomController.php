<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Order;
use App\Models\Type;
use Validator;
use DB;

class RoomController extends Controller
{
    protected $email;

    public function __construct()
    {
        $this->email = auth()->guard('api')->user()->email;
    }

    public function list()
    {
        try {
            $all = Room::with(['user' => function ($query) {
                $query->select(
                    'name'
                    ,'email'
                );
            }])
            ->get();
    
            $create = Room::with(['user' => function ($query) {
                $query->select(
                    'name'
                    ,'email'
                );
            }])
            ->where('email', $this->email)
            ->get();
    
            $inside = Order::with('room.user')
                ->where('email', $this->email)
                ->get();

            $data = [
                'all' => $all
                ,'create' => $create
                ,'inside' => $inside
            ];
            
            $success = true;
        } catch(\Exception $e) {
            $message = __('auth.error');
        }

        return Json($success ?? false, $data ?? null, $message ?? null);
    }

    public function add(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $validator = Validator::make($data, [
            'type' => 'required'
            ,'title' => 'required'
            ,'password' => 'required'
        ]);

        if (!$validator->fails()) {
            try {
                //방 생성
                Room::create([
                    'email' => $this->email
                    ,'room_type' => $data['type']
                    ,'title' => $data['title']
                    ,'password' => $data['password']
                ]);

                //방 정보 조회
                $room = Room::select('id')
                    ->where('email', $this->email)
                    ->where('room_type', $data['type'])
                    ->where('title', $data['title'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                $result = [
                    'room_id' => $room->id
                ];
    
                $success = true;
            } catch(\Exception $e) {
                $message = __('auth.error');
            }
        } else {
            $message = $validator->errors()->first();
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    /*public function edit($id, Request $request)
    {
        $success = false;

        $email = auth()->guard('api')->user()->email;

        $reception = Room::where('id', $id)
            ->where('email', $email)
            ->first();

        if (!is_null($reception)) {
            if ($request->has('end')) {
                if ($request->input('end') == 'Y') {
                    $pickUpCount = ceil(Order::where('room_id', $id)->count() / 4);

                    $data = Order::where('room_id', $id)
                        ->inRandomOrder()
                        ->limit($pickUpCount)
                        ->get();

                    if ($data->count() > 0) {
                        foreach($data as $item) {
                            Order::where('room_id', $id)
                                ->where('email', $item->email)
                                ->update([
                                    'pickup' => 'Y'
                                ]);
                        }
                    }
                } else {
                    Order::where('room_id', $id)
                        ->where('pickup', 'Y')
                        ->update([
                            'pickup' => 'N'
                        ]);
                }
            }

            $update = $request->all();

            Room::where('id', $id)
                ->where('email', $email)
                ->update($update);

            $success = true;
        }

        return Json($success, $data ?? null, $message ?? null);
    }

    public function delete($id)
    {
        $email = auth()->guard('api')->user()->email;

        Room::where('id', $id)
            ->where('email', $email)
            ->delete();

        return Json(true);
    }*/
    
    public function room($room_id, Request $request)
    {
        $email = $this->email;
        $password = $request->header('password') ?? null;

        //try {
            $result = Room::with('orders')
                ->when(is_null($password), function($q) use($email) {
                    $q->where('email', $email);
                })
                ->when(!is_null($password), function($q) use($password) {
                    $q->where('password', $password);
                })
                ->where('id', $room_id)
                ->first();

            if (!is_null($result)) {
                $success = true;
            }
        //} catch(\Exception $e) {
            //$message = __('auth.error');
        //}

        return Json($success ?? false, $result ?? null, $message ?? null);
    }
}
