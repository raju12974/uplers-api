<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {

            $user = Auth::user();
            $token = $user->createToken('Token Name')->accessToken;

            $data['access_token'] = $token;
            $data['user'] =$user->only('id', 'name', 'email', 'username');
            $data['admin'] = $user->is_admin=='y'?'Y':'N';
            $data['success'] = 'Y';
            return $data;
        }else{
            $data['success'] = 'N';

            $check_email_exist = User::where('email', $request->email)->count();

            if($check_email_exist){
                $data['msg'] = 'Invalid credentials';
                $data['email_exist'] = 'Y';
                $data['invalid'] = 'Y';
            }else{
                $data['msg'] = 'Email does not exist';
                $data['email_exist'] = 'N';
            }

            return $data;
        }
    }

    public function register(Request $request){

    }
}
