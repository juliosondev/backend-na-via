<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('localizacao')->nullable();
            $table->json('noti')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */

   
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('localizacao');
            $table->dropColumn('noti');
        });
    }
}
