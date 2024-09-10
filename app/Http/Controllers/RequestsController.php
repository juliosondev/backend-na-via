<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestsController extends Controller
{
    public function paymentMethods (){
        $methods = DB::table('formas_pagamentos')
        ->get();
        return response()->json($methods);
    }

    public function addRequest (Request $request){
        $req = DB::table('pedidos')->insert([
            'user_cliente_id' => $request->input('client_id'),
            'forma_pagamento_id' => $request->input('forma_pagamento'),
            'data_pedido' => now(),
            'valor_total' => $request->input('total'),
            'info' => json_encode($request->input('info')),
            'status'=> 'activo',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function myRequests($id)
{
    $pedidos = DB::table('pedidos')
        ->where('user_cliente_id', $id)
        ->get();

    $clientInfo = DB::table('users')
    ->where('users.id', $id)
    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
    ->select('users.*', 'users_dados.*')
    ->first();

    $pedidos = $pedidos->map(function ($pedido) use ($clientInfo) {
        $pedido->clientInfo = $clientInfo;
        return $pedido;
    });



    return response()->json($pedidos);
}
}
