<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Traits\ServerTimeTrait;

class RegistrationController extends Controller
{
    // 
    use ServerTimeTrait;
 

    public function register(Request $request){
        $time = $this->getServerTime();

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:11|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['message'=>'Incomplete Registration','timestamp'=>$time,'details'=>$validator->messages()]);
        }

        $user = User::create($request->all());
        return response()->json(['data'=>$user,'timestamp'=>$time, 'message'=>'User Created successfully!']);

    }

    public function allUser(Request $request){
        return User::all();
    }
 
}
