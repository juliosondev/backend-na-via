<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProdutos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subcategoria_id');
            $table->string('nome_produto');
            $table->text('imagem')->nullable();
            $table->text('descricao')->nullable();
            $table->string('status', 10);
            $table->timestamps();

            //Relacionamentos
            $table->foreign('subcategoria_id')->references('id')->on('subcategorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produtos');
    }
}
