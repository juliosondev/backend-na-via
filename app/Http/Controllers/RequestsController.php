<?php

namespace App\Http\Controllers;

use App\ComentarioProduto;
use App\Events\PackageLocationUpdate;
use App\Http\Controllers\Controller;
use App\Pedido;
use App\User;
use ExponentPhpSDK\Exceptions\ExpoException;
use ExponentPhpSDK\Expo;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notifications;




class RequestsController extends Controller
{
    public function paymentMethods()
    {
        $methods = DB::table('formas_pagamentos')
            ->get();
        return response()->json($methods);
    }

    public function addRequest(Request $request)
    {
        $req = DB::table('pedidos')->insert([
            'user_cliente_id' => $request->input('client_id'),
            'forma_pagamento_id' => $request->input('forma_pagamento'),
            'data_pedido' => now(),
            'valor_total' => $request->input('total'),
            'info' => json_encode($request->input('info')),
            'status' => 'activo',
            'created_at' => now(),
            'updated_at' => now()
        ]);


        Notifications::createAndSendForAllMotoboys([
            'title' => 'Novo Pedido!!!',
            'message' => 'Tem novo pedido no aplicativo. Vá rapidamente ver os detalhes e ver se aceitas!',
            'data' => ['extraData' => 'Some extra data here'],
            'type' => 'aceite',
            'user_id' => null,
            'expo_push_token' => null,
        ]);
        Notifications::createAndSendForAllMotoboys([
            'title' => 'Novo Pedido!!!',
            'message' => 'Tem novo pedido no aplicativo. Vá rapidamente ver os detalhes e ver se aceitas!',
            'data' => ['extraData' => 'Some extra data here'],
            'type' => 'aceite',
            'user_id' => null,
            'expo_push_token' => null,
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
            $info = json_decode($pedido->info, true);
            if (isset($info['cart'])) {
                $cart = $info['cart'];
                $cart = collect($cart)->map(function ($item) {
                    $prod = DB::table('produtos')
                        ->where('id', $item['id'])
                        ->first();
                    $fornecedor = DB::table('users')
                        ->where('users.id', $prod->fornecedor_id)
                        ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                        ->select('users.*', 'users_dados.*')
                        ->first();
                    $products = DB::table('produtos')
                        ->where('fornecedor_id', $prod->fornecedor_id)
                        ->get();
                    $products = $products->map(function ($prod) {
                        $comments = ComentarioProduto::where('produto_id', $prod->id)
                            ->get();
                        return $comments->map(function ($it) {
                            return json_decode($it->info)->rating;
                        });
                    });

                    $fornecedor->reviews = $products;
                    $item['fornecedor'] = $fornecedor;
                    return $item;
                });

                $info['cart'] = $cart;
            }
            $pedido->info = json_encode($info);
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
        $info = json_decode($pedido->info, true);
        if (isset($info['cart'])) {
            $cart = $info['cart'];
            $cart = collect($cart)->map(function ($item) {
                $prod = DB::table('produtos')
                    ->where('id', $item['id'])
                    ->first();
                $fornecedor = DB::table('users')
                    ->where('users.id', $prod->fornecedor_id)
                    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                    ->select('users.*', 'users_dados.*')
                    ->first();
                $products = DB::table('produtos')
                    ->where('fornecedor_id', $prod->fornecedor_id)
                    ->get();
                $products = $products->map(function ($prod) {
                    $comments = ComentarioProduto::where('produto_id', $prod->id)
                        ->get();
                    return $comments->map(function ($it) {
                        return json_decode($it->info)->rating;
                    });
                });

                $fornecedor->reviews = $products;
                $item['fornecedor'] = $fornecedor;
                return $item;
            });

            $info['cart'] = $cart;
        }
        $pedido->info = json_encode($info);
        return response()->json($pedido);
    }

    public function editRequest(Request $request, $id, $field)
    {
        $pedido = Pedido::findOrFail($id);
        if ($field == 'cancel') {
            $pedido->status = 'inactivo';
            $pedido->info = $request->input('info');
            $pedido->save();
            return response()->json($request->all());
        } else if ($field == 'accept') {
            $pedido->info = $request->input('info');
            $pedido->save();

            $user = User::find($request->noti['user_id']);
            DB::table('package_locations')->insert([
                'custom_id' => "{$request->motoBoy['user_id']}",
                'location' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            if (isset($user->expo_push_token)) {
                Notifications::createAndSend([
                    'title' => $request->noti['title'],
                    'message' => $request->noti['message'],
                    'data' => ['extraData' => 'Some extra data here'],
                    'type' => 'aceite',
                    'user_id' => $request->noti['user_id'],
                    'expo_push_token' => $user->expo_push_token,
                ]);
            }
            return response()->json($request->all());
        } else if ($field == 'stats') {
            $pedido->info = $request->input('info');
            $pedido->save();

            $user = User::find($request->noti['user_id']);

            if (isset($user->expo_push_token)) {
                Notifications::createAndSend([
                    'title' => $request->noti['title'],
                    'message' => $request->noti['message'],
                    'data' => ['extraData' => 'Some extra data here'],
                    'type' => 'aceite',
                    'user_id' => $request->noti['user_id'],
                    'expo_push_token' => $user->expo_push_token,
                ]);


                if (isset($request->noti1)) {
                    Notifications::createAndSend([
                        'title' => $request->noti1['title'],
                        'message' => $request->noti1['message'],
                        'data' => ['extraData' => 'Some extra data here'],
                        'type' => 'aceite',
                        'user_id' => $request->noti1['user_id'],
                        'expo_push_token' => $user->expo_push_token,
                    ]);
                }
            }

            return response()->json($pedido);
        }
    }


    public function testNotification(Request $request, $id)
    {
        $user = User::find($id);
        $token = $user->expo_push_token;
        // if (!Str::startsWith($token, 'ExpoPushToken[')) {
        //     return response()->json(['error' => 'Token inválido: '.$token], 400);
        // }
        if (!$token) {
            return response()->json(['error' => 'Você precisa habilitar permissões para receber notificações'], 400);
        }
        try {
            // // Create a new instance of the Expo SDK
            // $expo = Expo::normalSetup();

            // // You can create a custom key for your tokens, or use the userId as the key
            // $recipient = $user->expo_push_token;

            // // Add the recipient (Expo Push Token)
            // $expo->subscribe('user_528491', $recipient);

            // // Notification data
            // $notificationData = [
            //     'title' => 'O autocarro chegou!1',
            //     'body' => "O autocarro C",
            //     'sound' => 'default', // Optional
            //     'data' => ['extraData' => 'Some extra data here'] // Optional
            // ];

            // // Send the notification
            // $expo->notify(['user_528491'], $notificationData);

            Notifications::createAndSendForAllUnlogged([
                'title' => 'O autocarro chegou my nigga!',
                'message' => 'O autocarro C',
                'data' => ['extraData' => 'Some extra data here'],
                'type' => 'info',
                'user_id' => 1,
                'expo_push_token' => 'ExponentPushToken[4I1NWZGL47721WRmFl8S8b]',
            ]);

            return response()->json(['success' => 'Notification sent successfully!']);
        } catch (ExpoException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function sendAllUnlogged(Request $request)
    {
        Notifications::createAndSendForAllUnlogged([
            'title' => $request->title,
            'message' => $request->message,
            'data' => ['extraData' => 'Some extra data here'],
            'type' => 'info',
            'user_id' => null,
            'expo_push_token' => null,
        ]);

        return response()->json(['success' => 'Notification sent successfully!']);
    }
    public function addProductReview(Request $request)
    {
        $req = DB::table('comentarios_produtos')->insert([
            'user_id' => $request->input('user_id'),
            'produto_id' => $request->input('product_id'),
            'mensagem' => $request->input('mensagem'),
            'info' => json_encode($request->input('info')),
            'status' => 'activo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $user = User::find($request->noti['user_id']);

        if (isset($user->expo_push_token)) {
            Notifications::createAndSend([
                'title' => $request->noti['title'],
                'message' => $request->noti['message'],
                'data' => ['extraData' => 'Some extra data here'],
                'type' => 'aceite',
                'user_id' => $request->noti['user_id'],
                'expo_push_token' => $user->expo_push_token,
            ]);
        }

        $comments = ComentarioProduto::where('user_id', $request->input('user_id'))
            ->get();

        return response()->json($comments);
    }
    public function myReviews(Request $request, $id)
    {

        $comments = ComentarioProduto::where('user_id', $id)
            ->get();
        $comments = $comments->map(function ($item) {
            $info = json_decode($item->info, true);
            $prod = DB::table('produtos')
                ->where('id', $info['produto']['id'])
                ->first();
            $fornecedor = DB::table('users')
                ->where('users.id', $prod->fornecedor_id)
                ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                ->select('users.*', 'users_dados.*')
                ->first();
            $info['produto']['fornecedor'] = $fornecedor;
            $item->info = json_encode($info);

            return $item;
        });

        return response()->json($comments);
    }
    public function editMyReview(Request $request, $id)
    {

        $comentario = ComentarioProduto::find($id);
        $comentario->update($request->all());

        return response()->json($comentario);
    }
    public function stats(Request $request, $id)
    {
        $products = DB::table('produtos')
            ->where('fornecedor_id', $id)
            ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
            ->select('produtos.*', 'precos.valor')
            ->get();
        $ratings = $products->map(function ($prod) {
            $comments = ComentarioProduto::where('produto_id', $prod->id)
                ->get();
            return $comments->map(function ($it) {
                return json_decode($it->info)->rating;
            });
        });
        $reviews = $products->map(function ($prod) {
            $comments = ComentarioProduto::where('produto_id', $prod->id)
                ->get();
            $comments = $comments->map(function ($comment) {
                $comment->user = DB::table('users')
                    ->where('users.id', $comment->user_id)
                    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                    ->select('users.*', 'users_dados.*')
                    ->first();
                return $comment;
            });
            return $comments;
        });



        $products = $products->map(function ($item) {
            $comments = ComentarioProduto::where('produto_id', $item->id)
                ->get();
            //
            $fornecedor = DB::table('users')
                ->where('users.id', $item->fornecedor_id)
                ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                ->select('users.*', 'users_dados.*')
                ->get();
            $fornecedor = $fornecedor->map(function ($it) use ($item) {
                $products = DB::table('produtos')
                    ->where('fornecedor_id', $item->fornecedor_id)
                    ->get();
                $products = $products->map(function ($prod) {
                    $comments = ComentarioProduto::where('produto_id', $prod->id)
                        ->get();
                    return $comments->map(function ($it) {
                        return json_decode($it->info)->rating;
                    });
                });

                $it->reviews = $products;
                return $it;
            })->first();
            $item->fornecedor = $fornecedor;
            $item->comments = $comments;

            return $item;
        });

        $requests = DB::table('pedidos')->get();
        // $requests->filter(function ($item){
        //     $cart = json_decode($item->info, true)['cart'];
        //     $cart->map(function ($prod){
        //         $currProd = DB::table('produtos')
        //         ->where('id', $prod['id'])
        //         ->first();

        //         if ($currProd->fornecedor_id == $id){
        //             return true;
        //         }else {
        //             return false;
        //         }
        //     });

        // });
        $filteredRequests = $requests->filter(function ($item) use ($id) {
            $cart = json_decode($item->info, true)['cart'] ?? [];
            $productIds = collect($cart)->pluck('id');

            return DB::table('produtos')
                ->whereIn('id', $productIds)
                ->where('fornecedor_id', $id)
                ->exists();
        })->map(function ($item) use ($id) {
            $cart = json_decode($item->info, true)['cart'] ?? [];
            $clientInfo = DB::table('users')
                ->where('users.id', $item->user_cliente_id)
                ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                ->select('users.*', 'users_dados.*')
                ->first();
            $filteredCart = collect($cart)->filter(function ($product) use ($id) {
                return DB::table('produtos')
                    ->where('id', $product['id'])
                    ->where('fornecedor_id', $id)
                    ->exists();
            });
            $item->info = json_encode(['cart' => $filteredCart->values()->all(), 'delivered' => json_decode($item->info, true)['delivered'], 'user' => $clientInfo]);
            return $item;
        });
        return response()->json([
            'ratings' => $ratings,
            'reviews' => $reviews,
            'products' => $products,
            'requests' => $filteredRequests->values()->all()
        ]);
    }
    public function myProducts(Request $request, $id)
    {
        $produtos = DB::table('produtos')
            ->where('fornecedor_id', $id)
            ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
            ->select('produtos.*', 'precos.valor')
            ->get();

        return response()->json($produtos);
    }

    public function packageLocation($id)
    {
        $pacote = User::find($id);
        return $pacote;
    }
    public function updateLocation(Request $request, $id)
    {

        $customId = "{$id}";



        $pacote = User::find($id);



        $newLocation = $request->localizacao;
        if ($request->has('only')) {
            //get the already existing localizacao with calculated eta and everything and just change the coords that are coming from the front

            //i want you (AI) to calculate the distanceF (how far it is from arrival) and duration (eta) and add it to the $newLocation['distanceF'] and $newLocation['duration']
            $existingLocation = json_decode($pacote->localizacao, true);

            $newLocationData = $request->localizacao;

            $newLocation = array_merge($existingLocation ?? [], (array)$newLocationData);
        } else {
            $directions = $request->localizacao['directions'];
            $location = $request->localizacao;
            $distance = (float) $request->localizacao['distanceF'];
            $duration = (float) $request->localizacao['duration'];
        }
        broadcast(new PackageLocationUpdate($customId, $newLocation))->toOthers();
        DB::table('package_locations')->updateOrInsert(
            ['custom_id' => $customId],
            ['location' => json_encode($newLocation), 'updated_at' => now()]
        );
        $pacote->localizacao = $newLocation;

        $pacote->save();

        return $pacote;
    }



    private function calculateTotalDistanceAndDuration($directions)
    {
        $totalDistance = 0;
        $totalDuration = 0;

        foreach ($directions as $step) {
            $totalDistance += $step['distance'];
            $totalDuration += $step['duration'];
        }

        return [
            'distance' => round($totalDistance / 1000, 3),
            'duration' => $totalDuration
        ];
    }
}
