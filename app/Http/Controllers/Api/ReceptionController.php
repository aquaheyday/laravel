<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reception;
use App\Models\Order;
use App\Models\Type;
use Validator;
use DB;

class ReceptionController extends Controller
{

    public function get() {
        $data = Type::where('use_yn', 'Y')
            ->where('type', 'receptions_type')
            ->get();

        return Json(true, $data);
    }

    public function lists() {
        $email = auth()->guard('api')->user()->email;

        $list = Reception::with(['user' => function ($q) {
            $q->select(
                'name'
                ,'email'
            );
        }])->get();

        $data = [
            'all' => $list
            ,'create' => Reception::with(['user' => function ($q) {
                $q->select(
                    'name'
                    ,'email'
                );
            }])->where('email', $email)->get()
            ,'inside' => Order::with('reception.user')
                ->where('email', $email)
                ->get()
        ];

        $success = true;
        return Json($success, $data);
    }

    public function add(Request $request)
    {
        $r = json_decode($request->getContent(), true);
        $email = auth()->guard('api')->user()->email;
        $success = false;

        $validator = Validator::make($r, [
            //'type' => 'required'
            'title' => 'required'
            ,'password' => 'required'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
        } else {
            Reception::create([
                'email' => $email
                ,'receptions_type' => (int)$r['type']
                ,'title' => $r['title']
                ,'password' => $r['password']
            ]);

            $success = true;
        }

        return Json($success, null, $message ?? null);
    }

    public function edit($id, Request $request)
    {
        $success = false;

        $email = auth()->guard('api')->user()->email;

        $reception = Reception::where('id', $id)
            ->where('email', $email)
            ->first();

        if (!is_null($reception)) {
            if ($request->has('end')) {
                if ($request->input('end') == 'Y') {
                    $pickUpCount = ceil(Order::where('receptions_id', $id)->count() / 4);

                    $data = Order::where('receptions_id', $id)
                        ->inRandomOrder()
                        ->limit($pickUpCount)
                        ->get();

                    if ($data->count() > 0) {
                        foreach($data as $item) {
                            Order::where('receptions_id', $id)
                                ->where('email', $item->email)
                                ->update([
                                    'pickup' => 'Y'
                                ]);
                        }
                    }
                } else {
                    Order::where('receptions_id', $id)
                        ->where('pickup', 'Y')
                        ->update([
                            'pickup' => 'N'
                        ]);
                }
            }

            $update = $request->all();

            Reception::where('id', $id)
                ->where('email', $email)
                ->update($update);

            $success = true;
        }

        return Json($success, $data ?? null, $message ?? null);
    }

    public function delete($id)
    {
        $email = auth()->guard('api')->user()->email;

        Reception::where('id', $id)
            ->where('email', $email)
            ->delete();

        return Json(true);
    }
    
    public function list($id, Request $request)
    {
        $success = false;

        $email = auth()->guard('api')->user()->email;
        $password = $request->header('password') ?? null;

        $result = Reception::where('id', $id)
            ->with('orders')
            ->when(is_null($password), function($q) use($email) {
                $q->where('email', $email);
            })
            ->when(!is_null($password), function($q) use($password) {
                $q->where('password', $password);
            })
            ->first();

        if (!is_null($result)) {
            $success = true;
        }

        return Json($success, $result);
    }
}
