<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecolhaEntregaInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recolha_entrega_infos', function (Blueprint $table) {
            $table->id();
            $table->string('entregaInfo');
            $table->string('distanciaInfo');
            $table->decimal('valorEntrega', 20,2);
            $table->decimal('valorDistancia', 20,2);
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recolha_entrega_infos');
    }
}
