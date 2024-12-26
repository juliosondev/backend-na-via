<?php

namespace App\Http\Controllers;

use App\RecolhaEntregaInfo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecolhaEntregaInfoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        $infos = DB::table('recolha_entrega_infos')->get();
        return $infos;
    }

    public function allTaxas()
    {
        $infos = DB::table('taxas_servicos')->get();
        return $infos;
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
     * @param  \App\RecolhaEntregaInfo  $recolhaEntregaInfo
     * @return \Illuminate\Http\Response
     */
    public function show(RecolhaEntregaInfo $recolhaEntregaInfo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\RecolhaEntregaInfo  $recolhaEntregaInfo
     * @return \Illuminate\Http\Response
     */
    public function edit(RecolhaEntregaInfo $recolhaEntregaInfo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RecolhaEntregaInfo  $recolhaEntregaInfo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RecolhaEntregaInfo $recolhaEntregaInfo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\RecolhaEntregaInfo  $recolhaEntregaInfo
     * @return \Illuminate\Http\Response
     */
    public function destroy(RecolhaEntregaInfo $recolhaEntregaInfo)
    {
        //
    }
}
