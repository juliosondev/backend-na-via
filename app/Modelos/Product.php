<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Product extends Model
{
    //
    protected $table = 'produtos';

    protected $casts = [
        'formas_pagamentos' => 'json',
    ];
    public static function getProductForHome()
    {
        return DB::table('produtos')
            ->leftJoin('precos', 'produtos.id', '=', 'precos.produto_id')
            ->get();
    }


}
