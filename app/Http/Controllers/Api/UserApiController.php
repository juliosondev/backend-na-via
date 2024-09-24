<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use App\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserApiController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return User::all();
        // return response()->json([
        //     "message" => "Senha ou email errada",
        //     "status" => 401
        // ], 401);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {

            if (DB::table('users')
            ->where('users.email', $request->email)->exists()){
                return response()->json([
                        "message" => 'Email em uso, escolha um outro email.',
                        "status" => 422
                ], 422);
            }else {

        $user_id = DB::table('users')->insertGetId([
            'level' => 3,
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'status' => 'inativo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::find($user_id);


        DB::table('users_dados')->insert([
            'user_id' => $user->id,
            'nome' => $request->input('nome'),
            'genero' => 'n',
            'data_nascimento' => null,
            'tel' => $request->input('telefone'),
            'morada' => null,
            'foto' => null,
            'localizacao'=> null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $token = JWTAuth::fromUser($user);
        $currentSite = $request->getSchemeAndHttpHost();
        $absoluteUrl = $currentSite . '/api/v1/email_verify?token=' . $token;

        $emailData = [
            'name' => $request->nome,
            'email' => $user->email,
            'verificationUrl' => $absoluteUrl,
        ];


        Mail::to($request->email)->send(new VerifyEmail($emailData));

        return response()->json([
            "message" => "Usuario registado com sucesso",
            "data" => $user,
            "status" => 201
        ], 201);
    }




        // endif;

        return response()->json();


    }
    public function resend(Request $request, $id)
    {
        // $validator = Validator::make($request->all(), [
        //     'papel' => 'required',
        //     'nome' => 'required|min:2',
        //     'email' => 'required|email|unique:users,email',
        //  ], $messages = [
        //     'required' => 'Este campo é obrigatório',
        //     'email.unique' => 'Este email já esta em uso',
        //     'email' => 'Formato de email incorrecto',
        //     'min' => 'No mínimo dois(2) caracteres para este campo',
        //  ]);

        //  if ($validator->fails()):
        //     return response()->json([
        //             "message" => $validator->errors(),
        //             "status" => 422
        //     ], 422);
        //  else:

            // $data_time = new \DateTime();
            // $usuario = DB::table('users')->insert([
            //     'name' => $request->nome,
            //     'password' => Hash::make('123'),
            //     'email' => $request->email,
            //     'status' => 'activo',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);
            // $user_dados = DB::table('users_dados')->insert([

            // ]);
            // foreach($request->papel as $key => $item):
            //     $papel_usuario = DB::table('model_has_roles')->insert([
            //         'role_id' => $item,
            //         'model_type' => 'App\User',
            //         'model_id' => $usuario
            //     ]);
            // endforeach;

        //         data_time = new DateTime;
        // $user = DB::table('users')->insertGetId([
        //     'level' => 0,
        //     'email' => 'root@gmail.com',
        //     'password' => Hash::make('123456'),
        //     'status' => 'activo',
        //     'created_at' => $data_time,
        //     'updated_at' => $data_time,
        // ]);

        // $dados_user = DB::table('users_dados')->insert([
        //     'user_id' => $user,
        //     'nome' => 'AVK JS',
        //     'genero' => 'm',
        //     'data_nascimento' => null,
        //     'tel' => null,
        //     'morada' => null,
        //     'foto' => null,
        //     'created_at' => $data_time,
        //     'updated_at' => $data_time,
        // ]);

        $user = User::find($id);
        $user1 = DB::table('users')
        ->where('users.id', $id)
        ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
        ->select('users.*', 'users_dados.*')
        ->first();

        $token = JWTAuth::fromUser($user);
        $currentSite = $request->getSchemeAndHttpHost();
        $absoluteUrl = $currentSite . '/api/v1/email_verify?token=' . $token;
        $emailData = [
            'name' => $user1->nome,
            'email' => $user->email,
            'verificationUrl' => $absoluteUrl,
        ];


        Mail::to($user->email)->send(new VerifyEmail($emailData));

        return response()->json([
            "message" => "Novo email enviado com sucesso",
            "data" => $user,
            "status" => 201
        ], 201);





        // endif;

        return response()->json();


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function verifyEmail(Request $request)
     {
         $token = $request->query('token');

         try {
             $payload = JWTAuth::setToken($token)->getPayload();

             $userId = $payload->get('sub');

             $user = User::find($userId);
             $user1 = DB::table('users')
            ->where('users.id', $userId)
            ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
            ->select('users.*', 'users_dados.*')
            ->first();

             if (!$user) {
                //later on try to create a view template for this
                 return response()->json(['error' => 'User not found'], 404);
             }

            $user->status = 'activo';
            $user->save();

             return view('emails.verified', ['user' => $user1]);
             return response()->json('Email enviado');

         } catch (Exception $e) {
             if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                 return response()->json(['error' => 'Activation Expired'], 400);
             } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                 return response()->json(['error' => 'Invalid Token'], 400);
             } else {
                 return response()->json(['error' => 'Error processing token'], 400);
             }
         }
     }

    public function show(Request $request, $id)
    {
        $email = $request->query('email');
        if ($email) {
            $user = DB::table('users')
            ->where('users.email', $email)
            ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
            ->select('users.*', 'users_dados.*')
            ->first();
        }else {
            $user = DB::table('users')
            ->where('users.id', $id)
            ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
            ->select('users.*', 'users_dados.*')
            ->first();
        }


        // ->join('model_has_roles','model_has_roles.model_id', '=','users.id')
        // ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        // ->select('users.*', 'roles.name as papel')->first();

        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editUser(Request $request, $field, $id)
    {
        if ($field == 'local'){
            DB::table('users_dados')
                ->where('user_id', $id)
                ->update(['localizacao' => $request->input('localizacao'), 'updated_at' => now()]);
        }
        $updatedUser = DB::table('users_dados')
        ->where('user_id', $id)
        ->first();

    return response()->json($updatedUser);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            //'papel' => 'required',
            'status' => 'required',
            'nome' => 'required|min:2',
            'email' => 'required|email',
         ], $messages = [
            'required' => 'Este campo é obrigatório',
            'email.unique' => 'Este email já esta em uso',
            'email' => 'Formato de email incorrecto',
            'min' => 'No mínimo dois(2) caracteres para este campo',
         ]);
         if ($validator->fails()):
            return response()->json([
                    "message" => $validator->errors(),
                    "status" => 422
            ], 422);
         else:
            $data_time = new \DateTime();
            $usuario = DB::table('users')->where('id', $id)->update([
                'name' => $request->nome,
                'email' => $request->email,
                'status' => $request->status,
                'updated_at' => $data_time,
            ]);

            $papeis = $request->input('papel', []);
            //Papeis existentes
            $papeis_associados = DB::table('model_has_roles')->where([
                ['model_id', $id]
            ])->pluck('model_has_roles.role_id')->toArray();
            //dd($papeis_associados);
            // Calcula os papeis para associar e desassociar
            $papeis_para_associar = array_diff($papeis, $papeis_associados);
            $papeis_para_desassociar = array_diff($papeis_associados, $papeis);

            // Associa os novas papeis
            foreach($papeis_para_associar as $papel):
                $verificar_se_existe = DB::table('model_has_roles')->where([
                    ['model_id', $id],
                    ['role_id', $papel],
                ])->count();
                if($verificar_se_existe == 0):
                    DB::table('model_has_roles')->insert([
                        'model_id' => $id,
                        'model_type' => 'App\User',
                        'role_id' => $papel,
                    ]);
                endif;
            endforeach;
            // Desassociar papeis não selecionadas
            foreach($papeis_para_desassociar as $papel):
                DB::table('model_has_roles')->where([
                    ['model_id', $id],
                    ['role_id', $papel]
                ])->delete();
            endforeach;

            return response()->json([
                "message" => "Dados alterados com sucesso",
                "status" => 201
            ], 201);
        endif;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return response()->json([
            "message" => "Usuario eleminado com sucesso.",
            "status" => 201
        ], 201);

    }
}
