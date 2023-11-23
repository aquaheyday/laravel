<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Order;
use App\Models\Type;
use Validator;
use DB;
use Str;

class RoomController extends Controller
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

    public function list()
    {
        try {
            //전체 리스트
            $all = Room::join('users', 'rooms.user_id', 'users.id')
            ->leftJoin('orders', function($q) {
                $q->on('rooms.id', 'orders.room_id')
                    ->where('orders.user_id', $this->id);
            })
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.end_yn',
                'rooms.token',
                'users.name',
                DB::raw("if(count(orders.user_id) > 0, 'Y', 'N') as inside_yn"),
                DB::raw("if(rooms.user_id = '" . $this->id ."', 'Y', 'N') as create_yn"),
            )
            ->groupBy('rooms.id')
            ->orderBy('rooms.id', 'desc')
            ->get();
    
            //주문을 했던 리스트
            $inside = Room::join('users', 'rooms.user_id', 'users.id')
            ->join('orders', function($q) {
                $q->on('rooms.id', 'orders.room_id')
                    ->where('orders.user_id', $this->id);
            })
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.end_yn',
                'rooms.token',
                'users.name',
                DB::raw("if(rooms.user_id = '" . $this->id ."', true, false) as create_yn"),
            )
            ->groupBy('rooms.id')
            ->orderBy('rooms.id', 'desc')
            ->get();

            //생성한 리스트
            $create = Room::select(
                'rooms.id',
                'rooms.title',
                'rooms.end_yn',
                'rooms.token',
            )
            ->orderBy('id', 'desc')
            ->where('user_id', $this->id)
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
                //토큰 생성
                $token = hash('sha256', Str::random(60));

                //방 생성
                $test = Room::create([
                    'user_id' => $this->id
                    ,'type' => $data['type']
                    ,'title' => $data['title']
                    ,'password' => $data['password']
                    ,'token' => $token
                ]);

                $result = [
                    'token' => $token
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

    public function delete($token)
    {
        Room::where('token', $token)
            ->where('user_id', $this->id)
            ->delete();

        return Json(true);
    }
    
    public function room($token, Request $request)
    {
        $id = $this->id;
        $password = $request->header('password') ?? null;

        try {
            $result = Room::with('orders')
                ->when(is_null($password), function($q) use($id) {
                    $q->where('user_id', $id);
                })
                ->when(!is_null($password), function($q) use($password) {
                    $q->where('password', $password);
                })
                ->where('token', $token)
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

    public function state($token, $type)
    {
        $room = Room::where('user_id', $this->id)->where('token', $token)->first();

        if (!is_null($room)) {
            if ($type == 'end') {
                $state = 'Y';

                $limit = ceil(Order::where('room_id', $room->id)->count() / 4);
                $list = Order::where('room_id', $room->id)->inRandomOrder()->limit($limit)->get();
                
                foreach ($list as $item) {
                    Order::where('id', $item->id)->update([
                        'pick_up_yn' => 'Y'
                    ]);
                }
            } else {
                $state = 'N';

                $list = Order::where('room_id', $room->id)->where('pick_up_yn', 'Y')->get();

                foreach ($list as $item) {
                    Order::where('id', $item->id)->update([
                        'pick_up_yn' => 'N'
                    ]);
                }
            }

            //주문 오픈 or 마감 처리
            Room::where('user_id', $this->id)->where('token', $token)->update([
                'end_yn' => $state
            ]);

            $success = true;
        } else {
            $message = __('auth.error');
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function chart() {
        try {
            $list = Order::where('user_id', $this->id)->get();

            $order = Order::select(
                DB::raw("ROUND((SUM(IF(pick_up_yn = 'Y', 1, 0)) / COUNT(*)) * 100) cnt")
            )
            ->groupBy('user_id')
            ->get();

            $result = [
                'pickUpCount' => $list->where('pick_up_yn', 'Y')->count()
                ,'allCount' => $list->count()
                ,'userRate' => round(($list->where('pick_up_yn', 'Y')->count() / ($list->count() > 0 ? $list->count() : 1)) * 100)
                ,'totalRate' => round($order->sum('cnt') / ($order->count() > 0 ? $order->count() : 1))
            ];

            $success = true;
        } catch(\Exception $e) {
            $message = __('auth.error');
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function top() {
        try {
            $menuList = Order::select(
                'menu'
                ,DB::raw("count(*) as cnt")
            )
            ->groupBy('menu')
            ->orderBy('cnt', 'desc')
            ->limit(10)
            ->get();

            $emailList = Order::select(
                'orders.user_id'
                ,DB::raw("count(orders.user_id) as cnt")
                ,'users.name'
            )
            ->leftJoin('users', 'users.id', 'orders.user_id')
            ->where('pick_up_yn', 'Y')
            ->groupBy('user_id')
            ->orderBy('cnt', 'desc')
            ->limit(10)
            ->get();

            $result = [
                'menu' => $menuList
                ,'email' => $emailList
            ];

            $success = true;
        } catch(\Exception $e) {
            $message = __('auth.error');
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }
}
