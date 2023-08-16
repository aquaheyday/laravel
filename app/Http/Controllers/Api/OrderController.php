<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Reception;
use App\Models\Type;
use Validator;

class OrderController extends Controller
{

    public function get($receptions_id)
    {
        $success = false;
        $email = auth()->guard('api')->user()->email;

        $reception = Reception::where('id', $receptions_id)
            ->where('end', 'N')
            ->first();
            
        if (is_null($reception)) {
            $message = __('return.end');
        } else {
            $data = Type::where('use_yn', 'Y')->where(function($q) use($reception) {
                $q->where('type', $reception->receptions_type)
                    ->orWhere('type', $reception->receptions_type . '_size');
            })->get();
        }

        return Json(true, $data ?? null, $message ?? null);
    }

    public function add($receptions_id, Request $request)
    {
        $success = false;

        if (Reception::where('id', $receptions_id)->where('end', 'N')->exists()) {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
                ,'menu' => 'required'
                ,'menu_type' => 'required'
                ,'menu_size' => 'required'
            ]);
    
            if ($validator->fails()) {
                $message = $validator->errors()->first();
            } else {
                Order::create([
                    'receptions_id' => $receptions_id
                    ,'email' => $request->input('email')
                    ,'menu' => $request->input('menu')
                    ,'menu_type' => $request->input('menu_type')
                    ,'menu_size' => $request->input('menu_size')
                ]);
    
                $success = true;
            }
        } else {
            $message = __('return.end');
        }

        return Json($success, null, $message ?? null);
    }

    public function edit($id, Request $request)
    {
        $email = auth()->guard('api')->user()->email;

        $order = Order::where('id', $id)
            ->where('email', $email)
            ->first();

        if (!is_null($order)) {
            if (Reception::where('id', $order->receptions_id)->where('end', 'N')->exists()) {
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