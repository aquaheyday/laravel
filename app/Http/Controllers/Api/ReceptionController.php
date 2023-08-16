<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reception;
use App\Models\Order;
use App\Models\Type;
use Validator;

class ReceptionController extends Controller
{

    public function get() {
        $data = Type::where('use_yn', 'Y')
            ->where('type', 'receptions_type')
            ->get();

        return Json(true, $data);
    }

    public function lists() {
        $data = Reception::get();
        $success = true;
        
        return Json($success, $data);
    }

    public function add(Request $request)
    {
        $success = false;

        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
            ,'password' => 'required'
            ,'title' => 'required'
            ,'receptions_type' => 'required'
            ,'end' => 'required'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
        } else {
            Reception::create([
                'email' => $request->input('email')
                ,'password' => $request->input('password')
                ,'title' => $request->input('title')
                ,'receptions_type' => $request->input('receptions_type')
                ,'end' => $request->input('end')
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
