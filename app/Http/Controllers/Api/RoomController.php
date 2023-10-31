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
        //$this->email = auth()->guard('api')->user()->email;
    }

    public function list()
    {
        try {
            $all = Room::join('users', 'rooms.email', 'users.email')
            ->leftJoin('orders', function($q) {
                $q->on('rooms.id', 'orders.room_id')
                    ->where('orders.email', $this->email);
            })
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.end',
                'rooms.email',
                'users.name',
                DB::raw("if(count(orders.email) > 0, true, false) as insider"),
                DB::raw("if(rooms.email = '" . $this->email ."', true, false) as creater"),
            )
            ->groupBy('rooms.id')
            ->orderBy('rooms.id', 'desc')
            ->get();
    
            $inside = Room::join('users', 'rooms.email', 'users.email')
            ->join('orders', function($q) {
                $q->on('rooms.id', 'orders.room_id')
                    ->where('orders.email', $this->email);
            })
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.end',
                'rooms.email',
                'users.name',
                DB::raw("if(rooms.email = '" . $this->email ."', true, false) as creater"),
            )
            ->groupBy('rooms.id')
            ->orderBy('rooms.id', 'desc')
            ->get();

            $create = Room::select(
                'rooms.id',
                'rooms.title',
                'rooms.end',
            )
            ->orderBy('id', 'desc')
            ->where('email', $this->email)
            ->get();

            $data = [
                'all' => $all
                ,'inside' => $inside
                ,'create' => $create
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
    }*/

    public function delete($id)
    {
        Room::where('id', $id)
            ->where('email', $this->email)
            ->delete();

        return Json(true);
    }
    
    public function room($room_id, Request $request)
    {
        $email = $this->email;
        $password = $request->header('password') ?? null;

        try {
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
            } else {
                $message = __('auth.password');
            }
        } catch(\Exception $e) {
            $message = __('auth.error');
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function state($no, $type)
    {
        if (Room::where('email', $this->email)->where('id', $no)->exists()) {
            if ($type == 'end') {
                $state = 'Y';

                $limit = ceil(Order::where('room_id', $no)->count() / 4);
                $list = Order::where('room_id', $no)->inRandomOrder()->limit($limit)->get();
                
                foreach ($list as $item) {
                    Order::where('id', $item->id)->update([
                        'pickup' => 'Y'
                    ]);
                }
            } else {
                $state = 'N';

                $list = Order::where('room_id', $no)->where('pickup', 'Y')->get();

                foreach ($list as $item) {
                    Order::where('id', $item->id)->update([
                        'pickup' => 'N'
                    ]);
                }
            }
            Room::where('email', $this->email)->where('id', $no)->update([
                'end' => $state
            ]);

            $success = true;
        } else {
            $message = __('auth.error');
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function chart() {
        try {
            $list = Order::where('email', 'test@test.com')->get();

            $order = Order::select(
                DB::raw("ROUND((SUM(IF(pickup = 'Y', 1, 0)) / COUNT(*)) * 100) cnt")
            )
            ->groupBy('email')
            ->get();

            $menuList = Order::select(
                'menu'
                ,DB::raw("count(*) as cnt")
            )
            ->groupBy('menu')
            ->orderBy('cnt', 'desc')
            ->limit(10)
            ->get();

            $emailList = Order::select(
                'orders.email'
                ,DB::raw("count(orders.email) as cnt")
                ,'users.name'
            )
            ->leftJoin('users', 'users.email', 'orders.email')
            ->where('pickup', 'Y')
            ->groupBy('email')
            ->orderBy('cnt', 'desc')
            ->limit(10)
            ->get();

            $result = [
                'count' => [
                    'pickup' => $list->where('pickup', 'Y')->count()
                    ,'all' => $list->count()
                ]
                ,'rate' => [
                    'user' => round((($list->where('pickup', 'Y')->count() > 0 ? $list->where('pickup', 'Y')->count() : 1) / $list->count()) * 100)
                    ,'total' => round(($order->sum('cnt') > 0 ? $order->sum('cnt') : 1) / $order->count())
                ]
                ,'list' => [
                    'menu' => $menuList
                    ,'email' => $emailList
                ]
            ];

            $success = true;
        } catch(\Exception $e) {
            $message = __('auth.error');
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }
}
