<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'papel' => 'required',
            'nome' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
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
            $usuario = DB::table('users')->insertGetId([
                'name' => $request->nome,
                'password' => Hash::make('123'),
                'email' => $request->email,
                'status' => 'activo',
                'created_at' => $data_time,
                'updated_at' => $data_time,
            ]);
            foreach($request->papel as $key => $item):
                $papel_usuario = DB::table('model_has_roles')->insert([
                    'role_id' => $item,
                    'model_type' => 'App\User',
                    'model_id' => $usuario
                ]);
            endforeach;

            return response()->json([
                "message" => "Usuario registado com sucesso",
                "status" => 201
            ], 201);
        endif;

        // return response()->json();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('users.id', $id)
        ->join('model_has_roles','model_has_roles.model_id', '=','users.id')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->select('users.*', 'roles.name as papel')->first();

        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $id_user = User::find($id);
        if($id_user->id === Auth::user()->id):
            return response()->json([
                "message" => "Não é possível auto eliminar-se do sistema.",
                "status" => 422
            ], 422);
        else:
            User::find($id)->delete();
            return response()->json([
                "message" => "Usuario eleminado com sucesso.",
                "status" => 201
            ], 201);
        endif;

    }
}
