<?php namespace CVClient\Http\Controllers;

use Auth;

class AppAuth {

	public function getAuth(){
		if(Auth::guard('api')->check()){
			return Auth::guard('api')->user();
		} else if(Auth::guard('web')->check()){
			return Auth::user();
		} else {
			throw new \Exception("Unauthenticated", 401);
		}
	}
}