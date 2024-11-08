<?php

namespace App\Http\Controllers\Api;

use App\ComentarioProduto;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modelos\Product;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->produtos = new Product();
        //$this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function groups()
    {
        // $groups = DB::table('grupos')->get();
        $groups = DB::table('grupos')
        ->leftJoin('categorias', 'grupos.id', '=', 'categorias.grupo_id')
        ->leftJoin('subcategorias', 'categorias.id', '=', 'subcategorias.categoria_id')
        ->leftJoin('produtos', 'subcategorias.id', '=', 'produtos.subcategoria_id')
        ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
        ->select('grupos.id as grupo_id', 'grupos.*', 'categorias.*', 'categorias.status as categoria_status', 'categorias.id as categoria_id', 'subcategorias.status as subcategoria_status', 'subcategorias.imagem as subcategoria_imagem', 'subcategorias.categoria_id as subcategoria_categoria_id', 'subcategorias.id as subcategoria_id', 'subcategorias.nome_subcategoria as nome_subcategoria', 'produtos.imagem as produto_imagem', 'produtos.nome_produto as nome_produto', 'produtos.descricao as produto_descricao', 'produtos.status as produto_status', 'produtos.id as produto_id', 'produtos.fornecedor_id as produto_fornecedor_id', 'produtos.created_at as produto_created_at', 'precos.valor as produto_valor')
        ->get()
        ->groupBy('grupo_id')
        ->map(function ($group) {
            $grupoData = $group->first(); // Get the first instance of the grupo
            $categorias = $group->groupBy('categoria_id')->map(function ($categoriaGroup) {
                $categoriaData = $categoriaGroup->first();


            // Group the subcategorias under each categoria
            $subcategorias = $categoriaGroup->groupBy('subcategoria_id')->map(function ($subcategoriaGroup) {
                $subcategoriaData = $subcategoriaGroup->first();

                $produtos = $subcategoriaGroup->map(function ($item) {
                    $fornecedor = DB::table('users')
                    ->where('users.id', $item->produto_fornecedor_id)
                    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                    ->select('users.*', 'users_dados.*')
                    ->get();
                    $fornecedor = $fornecedor->map(function ($it) use ($item){
                        $products = DB::table('produtos')
                        ->where('fornecedor_id', $item->produto_fornecedor_id)
                        ->get();
                        $products = $products->map(function ($prod) {
                            $comments = ComentarioProduto::where('produto_id', $prod->id)
                            ->get();
                            return $comments->map(function ($it){
                                return json_decode($it->info)->rating;
                            });
                        });

                        $it->reviews = $products;
                        return $it;
                    })->first();
                    return [
                        'id' => $item->produto_id,
                        'nome_produto' => $item->nome_produto,
                        'status' => $item->produto_status,
                        'imagem' => $item->produto_imagem,
                        'valor' => $item->produto_valor,
                        'descricao' => $item->produto_descricao,
                        'created_at' => $item->produto_created_at,
                        'fornecedor' => $fornecedor

                    ];
                })->filter(function ($item) {
                    return $item['id'] !== null;
                })->values()->all();

                return [
                    'id' => $subcategoriaData->subcategoria_id,
                    'nome_subcategoria' => $subcategoriaData->nome_subcategoria,
                    'status' => $subcategoriaData->subcategoria_status,
                    'imagem' => $subcategoriaData->subcategoria_imagem,
                    'produtos' => $produtos

                ];
            })->filter(function ($item) {
                return $item['id'] !== null; // Exclude null produto_id values
            })->values()->all();


                return [
                    'id' => $categoriaData->categoria_id,
                    'nome_categoria' => $categoriaData->nome_categoria,
                    'status' => $categoriaData->categoria_status,
                    'subcategorias' => $subcategorias,
                ];
            })->filter(function ($item) {
                return $item['id'] !== null; // Exclude null produto_id values
            })->values()->all();


            return [

                'id' => $grupoData->grupo_id,
                'nome_grupo' => $grupoData->nome_grupo,
                'imagem' => $grupoData->imagem,
                'status' => $grupoData->status,
                'created_at' => $grupoData->created_at,

                'categorias' => $categorias,
            ];
        })
        ->values();
        return response()->json($groups);
    }

    public function anuncios()
    {
        $anuncios = DB::table('anuncios')->get();

        return response()->json($anuncios);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function mostRequestedProduts()
    {

        $produtos = DB::table('produtos')
        ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
        ->select('produtos.*', 'precos.valor') // Select the fields you need
        ->get();
        $produtos = $produtos->map(function ($item){
            $comments = ComentarioProduto::where('produto_id', $item->id)
            ->get();
            $item->comments = $comments;
            $fornecedor = DB::table('users')
                    ->where('users.id', $item->fornecedor_id)
                    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                    ->select('users.*', 'users_dados.*')
                    ->get();
                    $fornecedor = $fornecedor->map(function ($it) use ($item){
                        $products = DB::table('produtos')
                        ->where('fornecedor_id', $item->fornecedor_id)
                        ->get();
                        $products = $products->map(function ($prod) {
                            $comments = ComentarioProduto::where('produto_id', $prod->id)
                            ->get();
                            return $comments->map(function ($it){
                                return json_decode($it->info)->rating;
                            });
                        });

                        $it->reviews = $products;
                        return $it;
                    })->first();
            $item->fornecedor = $fornecedor;
            return $item;
        });

        return response()->json($produtos);

    }
    public function products(Request $request)
    {
        $q = $request->query('q');


        $produtos = DB::table('produtos')
        ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
        ->select('produtos.*', 'precos.valor')
        ->when($q, function ($query) use ($q) {
            return $query->where('produtos.nome_produto', 'like', '%'.$q.'%');
        })
        ->get();
        $produtos = $produtos->map(function ($item){
            $comments = ComentarioProduto::where('produto_id', $item->id)
            ->get();
            $item->comments = $comments;
            $fornecedor = DB::table('users')
                    ->where('users.id', $item->fornecedor_id)
                    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                    ->select('users.*', 'users_dados.*')
                    ->get();
                    $fornecedor = $fornecedor->map(function ($it) use ($item){
                        $products = DB::table('produtos')
                        ->where('fornecedor_id', $item->fornecedor_id)
                        ->get();
                        $products = $products->map(function ($prod) {
                            $comments = ComentarioProduto::where('produto_id', $prod->id)
                            ->get();
                            return $comments->map(function ($it){
                                return json_decode($it->info)->rating;
                            });
                        });

                        $it->reviews = $products;
                        return $it;
                    })->first();
                $item->fornecedor = $fornecedor;
            return $item;
        });

        return response()->json($produtos);

    }
    public function product(Request $request, $id)
    {


        $produto = DB::table('produtos')
        ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
        ->select('produtos.*', 'precos.valor')
        ->where('produtos.id', $id)
        ->first();
        $comments = ComentarioProduto::where('produto_id', $id)
        ->get();
        $comments = $comments->map(function ($comment) {
            $comment->user = DB::table('users')
            ->where('users.id', $comment->user_id)
            ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
            ->select('users.*', 'users_dados.*')
            ->first();
            return $comment;
        });
        $fornecedor = DB::table('users')
                    ->where('users.id', $produto->fornecedor_id)
                    ->join('users_dados', 'users.id', '=', 'users_dados.user_id')
                    ->select('users.*', 'users_dados.*')
                    ->get();
                    $fornecedor = $fornecedor->map(function ($it) use ($produto){
                        $products = DB::table('produtos')
                        ->where('fornecedor_id', $produto->fornecedor_id)
                        ->get();
                        $products = $products->map(function ($prod) {
                            $comments = ComentarioProduto::where('produto_id', $prod->id)
                            ->get();
                            return $comments->map(function ($it){
                                return json_decode($it->info)->rating;
                            });
                        });

                        $it->reviews = $products;
                        return $it;
                    })->first();
        $produto->fornecedor = $fornecedor;
        $produto->reviews = $comments;


        return response()->json($produto);

    }
}
