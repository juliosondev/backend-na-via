<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;

class GestedeConta extends Model
{
    //
    protected $table = 'gestor_conta';
    protected $fillable = [
        'id_user',
        'tipo_de_gestor',
        'status',
    ];
}
