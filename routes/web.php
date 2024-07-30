<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthApiController;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/google', 'Api\Auth\SocialateController@callbackGoogle');
Route::get('/google', function () {
    header("Access-Control-Allow-Origin:  http://localhost:4200");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization");
    $user = Socialite::driver('google')->user();
    $selecionar_usuario_google = DB::table('users')->where('email', $user->email)->first();
    // VERICAR USER
    if(isset($selecionar_usuario_google->email)):
        $dados_login = DB::table('users')->where('id', $selecionar_usuario_google->id)->first();
    else:
        $usuario = DB::table('users')->insertGetid([
            'name' => $user->name,
            'email' => $user->email,
            'password' => Hash::make('G-2023'),
            'created_at' => new \DateTime(),
            'updated_at' => new \DateTime()
        ]);
        $papel_usuario = DB::table('model_has_roles')->insert([
            'role_id' => 1,
            'model_type' => 'App\User',
            'model_id' => $usuario
        ]);
        $dados_login = DB::table('users')->where('id', $usuario)->first();
    endif;
    
    // dd($usuario);
   
    // dd($papel_usuario);
    // $dados_login = DB::table('users')->where('id', $usuario)->first();

    // dd('ola');
    $dados_controller = new AuthApiController;
    
    return $dados_controller->googleCallback($user->email, 'G-2023');
    // return redirect()->route('login.google.callback');


    $this->credentials = 
    [
        'email' =>  $dados_login->email,
        'password' => 'G-2023'
    ];
    if (! $token = auth()->attempt($this->credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user_logado = auth()->user();
    $permissions = collect([]);

    $roles = DB::table('model_has_roles')->where('model_id', $user_logado->id)
    ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
    ->select('roles.*')
    ->get();

    
    foreach($roles as $role):
        $permissions_role = DB::table('role_has_permissions')->where('role_id', $role->id)
        ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
        ->select('permissions.*')
        ->pluck('permissions.name');
        
        $permissions = $permissions->concat(collect($permissions_role));
        // dd($permissions);
    endforeach;
    // $permissions = $this->criarArrayUnico($permissions);

    $colecaoUnica = collect($permissions)->unique();
        // return $colecaoUnica->all();

    $this->dados_login = [
        'token' => $token,
        'user' => $user_logado,
        'roles' => $roles,
        'permissions' => $colecaoUnica
    ];

    function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    // return response()->json($this->dados_login);
    return $this->respondWithToken($this->dados_login);
    // Pegar Users
    // $user_logado = auth()->user();
    // $user = Socialite::driver('facebook')->user();
    // dd($user);
    // return 'Feito';
});
Route::get('login', 'Api\Auth\SocialateController@google')->name('login');
Route::get('google/login', 'Api\Auth\SocialateController@google')->name('login.google');
Route::get('google/login/callback', 'Api\Auth\AuthApiController@googleCallback')->name('login.google.callback');
Route::get('facebook/login', 'Api\Auth\SocialateController@facebook')->name('login.facebook');

