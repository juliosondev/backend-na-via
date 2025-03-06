<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProdutoController extends Controller
{


    public function addProduto(Request $request){

        $list = array_map(function ($item) {
            $imageData = base64_decode($item);
            $filename = 'image_'.time(). Str::random(10). '.png';
            $imagePath = "images/" . $filename;
            Storage::disk('public')->put($imagePath, $imageData);
            return $filename;
            }, $request->images);

        $produto = DB::table('produtos')->insertGetId([
            'fornecedor_id' => $request->fornecedor_id,
            'subcategoria_id' => $request->subcategoria_id,
            'nome_produto' => $request->nome,
            'imagem' => !empty($list) ? $list[0] : null,
            'imagens' => json_encode($list),
            'quantidade' => (float)$request->quantidade,
            'descricao' => $request->descricao,
            'status' => 'activo',
            // 'created_at' => now(),
            // 'updated_at' => now(),
        ]);

        $preco = DB::table('precos')->insert([
            'produto_id' => $produto,
            'valor' => (float)$request->preco,
            'tipo' => null,
            'status' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

    }
}
