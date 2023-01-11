<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organizacion', function (Blueprint $table) {
            $table->foreign(['usuario_superior'], 'fk_organizacion_usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['usuario_inferior'], 'fk_organizacion_usuario2')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organizacion', function (Blueprint $table) {
            $table->dropForeign('fk_organizacion_usuario1');
            $table->dropForeign('fk_organizacion_usuario2');
        });
    }
};
