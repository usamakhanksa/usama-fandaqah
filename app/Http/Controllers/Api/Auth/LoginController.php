<?php

namespace App\Http\Controllers\Api\Auth;

use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class LoginController extends Controller
{

	/**
	*login api
	* @return \Illuminate\Http\Response
	*/
	public function login()
	{
		if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
			$user = Auth::user();
			if($user->roles && $user->roles->contains('slug', 'housekeeping')){
				throw new ValidationException("you dont have privilege to enter this portal", 401);
			}else{
				$success['data']['token'] = $user->createToken('myApp')->plainTextToken;
				$success['data']['user'] = new UserResource($user->load('teams'));
				return response()->json($success, 200);
			}

		}elseif(User::where('email', request('email'))->count() == 0){
			throw new ValidationException(__("We can't find a user with that e-mail address."), 401);
		}
		else{
			throw new ValidationException(__("password incorrect"), 401);
		}
	}

	/**
	*login api
	* @return \Illuminate\Http\Response
	*/
	public function user(Request $request)
	{
		return $request->user();
	}


	/**
	*login api
	* @return \Illuminate\Http\Response
	*/
	public function login_housekeeping()
	{
		if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
			$user = auth()->user();
			// dd($user->roles->toArray());
			if(!$user->roles || !$user->roles->contains('slug', 'housekeeping')){
				throw new ValidationException("you dont have privilege to enter this portal", 401);
			}

			if(\request('device_token')){
                $user->device_token = \request('device_token');
                $user->save();
            }

			$success['data']['token'] = $user->createToken('myApp')->plainTextToken;
			$success['data']['user'] = new UserResource($user->load('teams'));
			return response()->json($success, 200);
		}elseif(User::where('email', request('email'))->count() == 0){

		   return  $this->apiCustomResponse("We can't find a user with that e-mail address." , 401);
//			throw new ValidationException("We can't find a user with that e-mail address.", 401);
		}
		else{
           return  $this->apiCustomResponse("password incorrect" , 401);
//			throw new ValidationException("password incorrect", 401);
		}
	}

	function apiCustomResponse($message , $error_code)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $error_code,
        ], $error_code);
    }
}
