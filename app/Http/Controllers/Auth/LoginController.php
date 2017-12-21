<?php namespace CVClient\Http\Controllers\Auth;

use Auth;
use CVClient\CV\Models\CLocal;
use CVClient\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFoundException;
use CVClient\User;
use Socialite;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except(['logout','setWebSocketId']);
    }

    public function index(){
        $params = request()->all();

        if(isset($params['error']) && $params['error'] == 1)
            return view('login', ['error'=>'Usted no está registrado en el sistema, o su cuenta fue dada de baja.']);
        else
            session()->put('register', null);
            return view('login');
    }

    public function login(){
        $params = request()->all();
        if(Auth::attempt(['preferencia_login'=>$params['email'], 'password'=>$params['password']])){
            return redirect()->intended('dashboard');
        } else {
            echo "no se pudo logear";
        }
    }


    public function handleCallbackFromFacebook(){
        return $this->handleCallback('facebook');
    }

    public function handleCallbackFromGoogle(){
        return $this->handleCallback('google');
    }

    public function redirectToFacebook(){
        $params = request()->all();
        if(isset($params['src']) && $params['src'] == "register")
            session(['register'=>1]);

        return Socialite::driver('facebook')->fields(['name','firs_tname','last_name','email','gender'])->redirect();
    }

    public function redirectToGoogle(){
        if(isset($params['src']) && $params['src'] == "register"){
            return Socialite::driver('google')->with(['register'=>'1'])->redirect();
        } else {
            return Socialite::driver('google')->redirect();
        }
    }

    private function handleCallback($service){
        
        try {

            $user = Socialite::driver($service)->user();

            $u = User::whereIn('preferencia_estado',['A','X','S'])->where(function($query) use ($user, $service){
                $query->orWhere($service.'_id', $user->id)
                      ->orWhere('preferencia_login', $user->email);
            })->first();

            if( !is_null(session('register')) ){
                if(!is_null($u)) {
                    echo "<script>";
                    echo "window.opener.setMessage('danger','El correo ya esta registrado.');";
                    echo "window.close();";
                    echo "</script>";
                } else {
                    echo "<script>";
                    echo "var data = " . json_encode(['name'=>$user->name,'email'=>$user->email,'service'=>$service,'social_id'=>$user->id]) . ";";
                    echo "window.opener.openForm(data);";
                    echo "window.close();";
                    echo "</script>";
                }
            } else {
                if(is_null($u))
                    throw new \Exception("El usuario no existe");

                $u->fill([
                    $service.'_id' => $user->id
                ])->save();

                //session()->put('login_error', null);
                Auth::login($u);
                return redirect()->route('dashboard');
            }
        } catch (\Exception $e) {
            return redirect()->route('login', ['error'=>$e->getMessage()]);
        }
    }

    public function setWebSocketId($id){
        try {

            $user = Auth::user();
            $user->websocket = $id;
            $user->save();
            return response('',204);

        } catch (\Exception $e) {
            return response()->json(['error'=>$e->getMessage(), 412]);            
        }
    }
}