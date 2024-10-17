<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComentarioProduto extends Model
{
    protected $table = 'comentarios_produtos';
    protected $fillable = [
        'mensagem',
        'status',
        'info',
        'updated_at'
     ];
}
