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
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
     
        if (! $validator->fails()) {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->name;
        }
   
        return ResponseJson($success ?? null, 'User register successfully.');
    }

    public function login(Request $request)
    {
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
                $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->name;
            }
        }

        return JsonResponse($success ?? null, 'User login successfully.');
    }
}