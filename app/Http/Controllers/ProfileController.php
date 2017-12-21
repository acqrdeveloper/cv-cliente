<?php namespace App\Http\Controllers;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\AppAuth;

class ProfileController extends Controller {

	public $user = null;

	public function __construct(){
		try {
			$this->user = (new AppAuth)->getAuth();
		} catch (\Exception $e) {
			return response()->json(['error'=>$e->getMessage()], 401);	
		}
	}

	public function index(){
		if(Auth::guard('api')->check()){
			return response()->json( $this->user );
		} else {
			return view('app');
		}
	}
}