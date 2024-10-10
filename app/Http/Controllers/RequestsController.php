<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Pedido;
use App\User;
use ExponentPhpSDK\Exceptions\ExpoException;
use ExponentPhpSDK\Expo;
use GuzzleHttp\Client;
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
    public function availableRequests()
    {
        $pedidos = DB::table('pedidos')
        ->whereNull('info->motoBoy')
        ->leftJoin('users', 'pedidos.user_cliente_id', '=', 'users.id')
        ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
        ->select('pedidos.*', 'users.*', 'users.id as client_id', 'users_dados.id as user_dados_id', 'users_dados.*', 'pedidos.id as id', 'pedidos.status as status')
        ->get();





        return response()->json($pedidos);
    }
    public function acceptedRequests($id)
    {
        $pedidos = DB::table('pedidos')
        ->where('info->motoBoy->user_id', $id)
        ->leftJoin('users', 'pedidos.user_cliente_id', '=', 'users.id')
        ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
        ->select('pedidos.*', 'users.*', 'users.id as client_id', 'users_dados.id as user_dados_id', 'users_dados.*', 'pedidos.id as id', 'pedidos.status as status')
        ->get();





        return response()->json($pedidos);
    }
    public function request($id)
    {
        $pedido = Pedido::findOrFail($id);
        return response()->json($pedido);
    }

    public function editRequest(Request $request, $id, $field)
    {
        $pedido = Pedido::findOrFail($id);
        if ($field == 'cancel'){
            $pedido->status = 'inactivo';
            $pedido->info = $request->input('info');
            $pedido->save();
            return response()->json($request->all());
        }else if ($field == 'accept'){
            $pedido->info = $request->input('info');
            $pedido->save();
            return response()->json($request->all());
        }else if ($field == 'stats') {
            $pedido->info = $request->input('info');
            $pedido->save();
            return response()->json($pedido);
        }
    }

    public function testNotification(Request $request, $id)
    {
        $user = User::find($id);
        $token = $user->expo_push_token;
        if (!$token){
            return response()->json(['error' => 'Você precisa habilitar permissões para receber notificações'], 400);

        }
        try {
            // Create a new instance of the Expo SDK
            $expo = Expo::normalSetup();

            // You can create a custom key for your tokens, or use the userId as the key
            $recipient = $request->token;

            // Add the recipient (Expo Push Token)
            $expo->subscribe($recipient, $recipient);

            // Notification data
            $notificationData = [
                'title' => $request->title,
                'body' => $request->body,
                'sound' => 'default', // Optional
                'data' => ['extraData' => 'Some extra data here'] // Optional
            ];

            // Send the notification
            $expo->notify([$recipient], $notificationData);

            return response()->json(['success' => 'Notification sent successfully!']);
        } catch (ExpoException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
