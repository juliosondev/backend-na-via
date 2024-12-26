<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dados_user = DB::table('taxas_servicos')->insert([
            'descricao' => 'Uma taxa de Kz 1 500 para uma entrega confiável ao domicílio. Esta taxa inclui despesas relacionadas com combustível, manuntenção da nossa frota e pagamento dos motoristas para garantir que as suas encomendas sejam entregues de forma eficiente',
            'valor' => 1500,
            'status' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
