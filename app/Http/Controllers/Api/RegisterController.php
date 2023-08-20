<?php
     
namespace App\Http\Controllers\Api;
     
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
     
class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $success = false;

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
     
        if ($validator->fails()) {
            $message = $validator->errors()->first();
        } else {
            $password = bcrypt($request->password);
            try {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $password,
                ]);

                $result['token'] =  $user->createToken('MyApp')->accessToken;
                $result['name'] =  $user->name;

                $success = true;
                
            } catch(\Exception $e) {
                $message = $e->getMessage();
            }
        }
   
        return Json($success, $result ?? null, $message ?? null);
    }

    public function login(Request $request)
    {
        $success = false;

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! $validator->fails()) {
            $check = Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($check) {
                $user = Auth::user();
                $result['token'] =  $user->createToken('MyApp')->accessToken;
                $result['name'] =  $user->name;

                $success = true;
            }
        }

        return Json($success, $result);
    }
}