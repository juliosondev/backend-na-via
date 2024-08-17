<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contacto extends Model
{
    //
    protected $table = 'contactos';
    protected $fillable = [
        'cliente_id',
        'tipo',
        'designacao',
        'status',
    ];
    public function registarContato($data, $id_cliente){
        $data_time = new \DateTime();
        $registar = DB::table('contactos')->insert([
            'cliente_id' => $id_cliente,
            'tipo' => $data->tipo_contacto,
            'designacao' => $data->descricao,
            'status' => 'activo',
            'created_at' => $data_time,
            'updated_at' => $data_time,
        ]);
    }
}
