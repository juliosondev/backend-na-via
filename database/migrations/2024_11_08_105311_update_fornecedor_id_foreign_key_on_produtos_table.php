<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFornecedorIdForeignKeyOnProdutosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Drop the existing foreign key constraint referencing the 'fornecedores' table
            $table->dropForeign(['fornecedor_id']);

            // Define a new foreign key relationship to the 'users' table
            $table->foreign('fornecedor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Drop foreign key referencing 'users'
            $table->dropForeign(['fornecedor_id']);

            // Set up the foreign key back to 'fornecedores' table
            $table->foreign('fornecedor_id')->references('id')->on('fornecedores')->onDelete('cascade');
        });
    }
}
