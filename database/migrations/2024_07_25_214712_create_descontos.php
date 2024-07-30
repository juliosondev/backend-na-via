<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDescontos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('descontos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preco_id');
            $table->decimal('taxa', 20,2);
            $table->decimal('valor_do_desconto', 20,2);
            $table->decimal('valor_com_desconto', 20,2);
            $table->text('descricao')->nullable();
            $table->string('status', 10);
            $table->timestamps();

            //Relacionamentos
            $table->foreign('preco_id')->references('id')->on('precos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('descontos');
    }
}
