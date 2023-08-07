<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Reception;
use Validator;

class ReceptionController extends Controller
{
    public function list() {
        dd(Reception::get());
    }

    public function add(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'title' => 'required',
            'type' => 'required',
        ]);
     
        if (! $validator->fails()) {
            $input = $request->all();
            Reception::create($input);
        }
    
    }   
}
