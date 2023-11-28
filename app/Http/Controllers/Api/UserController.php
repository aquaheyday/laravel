<?php
     
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
     
class UserController extends Controller
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

    public function info() {

        $data = User::select(
            'users.name'
            ,'users.email'
            ,'users.number'
            ,'users.image_path'
            ,DB::raw("SUM(IF(ISNULL('orders.pick_up_yn'), 0, 1)) as total_count")
            ,DB::raw("SUM(IF(orders.pick_up_yn = 'Y', 1, 0)) as pick_up_count")
        )
        ->leftJoin('orders', 'users.id', 'orders.user_id')
        ->where('users.id', $this->id)
        ->first();

        $success = true;

        return Json($success ?? false, $data ?? null, $message ?? null);
    }

    public function update($type, Request $request) {

        $data = json_decode($request->getContent(), true);

        if ($type == 'image') {
            Storage::disk('public')->put($this->id . '_profile.png', base64_decode($data['image']));

            User::where('id', $this->id)
            ->update([
                'image_path' => '/api/storage/' . $this->id . '_profile.png'
            ]);

            $result = '/api/storage/' . $this->id . '_profile.png';

        } else {
            User::where('id', $this->id)
            ->update([
                $type => $type == 'password' ? bcrypt($data[$type]) : $data[$type]
            ]);
        }

       

        $success = true;
        
        return Json($success ?? false, $result ?? null, $message ?? null);
    }

}