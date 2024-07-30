<?php

namespace App\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Modelos\Contacto;

class Clientes extends Model
{
    //
    protected $table = 'clientes';
    protected $fillable = [
        'polo_id',
        'tipo_entidade_id',
        'nome',
        'nif',
        'pais',
        'morada',
        'localidade',
        'retencao',
        'factura_eletronica',
        'observacoes',
        'localizacao',
        'status'
    ];
    public function insertGetIdCliente($data){   
        // Iniciar a transação
        DB::beginTransaction();
        try{
            $id_cliente = DB::table('clientes')->insertGetId([
                'polo_id' => $data->polo_id,
                'tipo_entidade_id' => $data->tipo_entidade_id,
                'nome' => $data->nome,
                'nif' => $data->nif,
                'pais' => $data->pais,
                'morada' => $data->morada,
                'localidade' => $data->localidade,
                'retencao' => $data->retencao,
                'factura_eletronica' => $data->factura_eletronica,
                'observacoes' => $data->observacoes,
                'localizacao' => $data->localizacao,
                'status' => 'activo'
            ]);
            $dados_contacto = $data->only(['tipo_contacto', 'descricao']);
            $modelContact = new Contacto();
            $modelContact->registarContato($data, $id_cliente);
            // Commit se tudo estiver bem
            DB::commit();
            return 'sucesso';
        }
        catch (\Exception $e) 
        {
            // Desfazer a transação em caso de erro
            DB::rollback();        
            // Lidar com o erro, se necessário
            return 'Erro'.$e;
        }
        
        

    }
}
