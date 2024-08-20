<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Product extends Model
{
    //
    protected $table = 'produtos';

    public function getProductForHome(){
        $listProduct = DB::table('produtos')
        ->join('precos', 'produtos.id', '=','precos.produto_id')
        ->get();

        return response()->json($listProduct);
    }
}
