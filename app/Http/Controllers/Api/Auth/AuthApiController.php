<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthApiController extends Controller
{
    //
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            // return response()->json(['error' => 'Unauthorized'], 401);
            return response()->json([
                "message" => "Senha ou email errada",
                "status" => 401
            ], 401);
        }
        // Pegar Users
        $user = auth()->user();

        $this->dados_login = [
            'token' => $token,
            'user' => $user,
            'status' => 'success'
        ];
        return $this->dados_login;
        return $this->respondWithToken($this->dados_login);

    }

    public function googleCallback($email, $senha){

        // Configuração de cabeçalhos CORS
        // header("Access-Control-Allow-Origin:  http://localhost:4200");
        // header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        // header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token, Origin, Authorization");

        $this->credentials =
        [
            'email' =>  $email,
            'password' => $senha
        ];
        $credentials = request(['email', 'password']);
        if (! $token = auth()->attempt($this->credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // Pegar Users
        $user = auth()->user();
        $permissions = collect([]);

        $roles = DB::table('model_has_roles')->where('model_id', $user->id)
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('roles.*')
        ->get();


        foreach($roles as $role):
            $permissions_role = DB::table('role_has_permissions')->where('role_id', $role->id)
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->select('permissions.*')
            ->pluck('permissions.name');

            $permissions = $permissions->concat(collect($permissions_role));
        endforeach;
        $permissions = $this->criarArrayUnico($permissions);

        $this->dados_login = [
            'token' => $token,
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions
        ];
        return $this->respondWithToken($this->dados_login);
    }

    public function criarArrayUnico($arr){
        $colecaoUnica = collect($arr)->unique();
        return $colecaoUnica->all();
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try 
        {
            // Invalida o token
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out'], 200);
        } 
        catch (\Exception $e) 
        {
            return response()->json(['error' => 'Failed to logout, please try again.'], 500);
        }
        //auth()->logout();
        //return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function loginGoogle(){
        // return Socialite::driver('google')->redirect();
    }
}
