<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{

	/**
	*login api 
	* @return \Illuminate\Http\Response 
	*/
	public function user()
	{ 
		if(auth()->check()){ 
			$user = Auth::user(); 
			return new UserResource($user);
		}else{ 
			throw new ValidationException(__("password incorrect"), 401);
		} 
	}
}