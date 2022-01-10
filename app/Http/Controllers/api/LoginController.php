<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Traits\ServerTimeTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // 
    use ServerTimeTrait;
 
    //Used to mock credentials from AuthenticateUsers Trait
    private function getCredentials($request){
        $filter = filter_var($request->get('email'), FILTER_VALIDATE_EMAIL);
        return [$filter ? 'email':'phone' => $request->get('email')];
    } 
    

    public function login(Request $request){
        $time = $this->getServerTime();
        $password = $request->get('password');

        try {
            $user = Auth::attempt(array_merge($this->getCredentials($request), ['password' => $password]));
            if(!$user){
                return response()->json(['data'=>[],'timestamp'=>$time, 'message'=>'Incorrect email or password!']);
            }
    
            $token = Auth::user()->createToken('token')->accessToken;
            return response()->json(['data'=>auth()->user(),'timestamp'=>$time, 'token'=>$token, 'message'=>'Authentication successful']);

            } catch (\Throwable $th) {
                dd($th);
            return response()->json(['data'=>[],'timestamp'=>$time, 'message'=>'Server Error'],500);

        }
      

    }

 
}
