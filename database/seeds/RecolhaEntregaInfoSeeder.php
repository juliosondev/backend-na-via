<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecolhaEntregaInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $table->string('entregaInfo');
        // $table->string('distanciaInfo');
        // $table->decimal('valorEntrega', 20,2);
        // $table->decimal('valorDistancia', 20,2);
        // $table->string('status', 20);
        $dados_user = DB::table('recolha_entrega_infos')->insert([
            'entregaInfo' => 'Uma taxa de Kz 1 000 para uma entrega confiável ao domicílio. Esta taxa inclui despesas relacionadas com combustível, manuntenção da nossa frota e pagamento dos motoristas para garantir que as suas encomendas sejam entregues de forma eficiente',
            'distanciaInfo' => 'Uma taxa fixa de KZ 250/Km',
            'valorEntrega' => 1000,
            'valorDistancia' => 250,
            'status' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
