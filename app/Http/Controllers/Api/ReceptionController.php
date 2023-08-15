<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reception;
use Validator;

class ReceptionController extends Controller
{
    public function lists() {
        $result = Reception::get();

        return Json($result ?? null, 'success.');
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
            ,'password' => 'required'
            ,'title' => 'required'
            ,'type' => 'required'
        ]);

        if (!$validator->fails()) {
            $create = $request->all();

            Reception::create($create);
        }

        return Json($result ?? null, 'success.');
    }

    public function edit($id, Request $request)
    {
        $email = auth()->guard('api')->user()->email;

        $update = $request->all();

        Reception::where('id', $id)
            ->where('email', $email)
            ->update($update);

        return Json($result ?? null, 'success.');
    }

    public function delete($id)
    {
        $email = auth()->guard('api')->user()->email;

        Reception::where('id', $id)
            ->where('email', $email)
            ->delete();

        return Json($result ?? null, 'success.');
    }
    
    public function list($id, Request $request)
    {
        $email = auth()->guard('api')->user()->email;
        $password = $request->header('password') ?? null;

        $result = Reception::where('id', $id)
            ->when(is_null($password), function($q) use($email) {
                $q->where('email', $email);
            })
            ->when(!is_null($password), function($q) use($password) {
                $q->where('password', $password);
            })
            ->first();

        return Json($result ?? null, 'success.');
    }
}
