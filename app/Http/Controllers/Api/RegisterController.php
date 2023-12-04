<?php
     
namespace App\Http\Controllers\Api;
     
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use Laravel\Passport\Passport;
     
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
     
        if (!$validator->fails()) {
            try {
                //이메일 등록 여부 확인
                $check = User::where('email', $request->email)->exists();

                if ($check) {
                    $message = __('auth.email');
                } else {
                    //비밀번호 암호화
                    $password = bcrypt($request->password);

                    //사용자 생성
                    $user = User::create([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => $password,
                    ]);
    
                    //토큰 생성
                    $result['token'] =  $user->createToken('MyApp')->accessToken;
                    //사용자명
                    $result['name'] =  $user->name;
    
                    $success = true;
                }
            } catch(\Exception $e) {
                $message = __('auth.error');
            }
        } else {
            $message = $validator->errors()->first();
        }
   
        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function email(Request $request)
    {
        //유효성 검사
        $validator = Validator::make($request->all(), [
            'number' => 'required',
        ]);

        if (!$validator->fails()) {
            $user = User::where('number', $request->number)->first();

            $result['email'] = is_null($user) ? null : $user->email;

            $success = true;
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function password(Request $request)
    {
        //유효성 검사
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if (!$validator->fails()) {
            $user = User::where('email', $request->email)->first();
            $result['password'] = null;
            
            if (!is_null($user)) {
                $pool = '0123456789abcdefghijklmnopqrstuvwxyz';

                $password = substr(str_shuffle(str_repeat($pool, 5)), 0, 5);

                User::where('email', $request->email)->update([
                    'password' => bcrypt($password)
                ]);

                $result['password'] = $password;

            }

            $success = true;
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }

    public function login(Request $request)
    {
        //유효성 검사
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!$validator->fails()) {
            try {
                //계정 확인
                $check = Auth::attempt([
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                if ($check) {
                    //계정 로그인
                    $user = Auth::user();
                    //토큰 생성
                    $token_result = $user->createToken('MyApp');
                    $result['token'] =  $token_result->accessToken;
                    $result['name'] =  $user->name;

                    if (!$request->check) {
                        $token = $token_result->token;
                        $token->expires_at = now()->addHours(1);
                        $token->save();
                    }
                    $success = true;
                } else {
                    $message = __('auth.failed');
                }
            } catch(\Exception $e) {
                $message = __('auth.error');
            }
        } else {
            $message = $validator->errors()->first();
        }

        return Json($success ?? false, $result ?? null, $message ?? null);
    }
}