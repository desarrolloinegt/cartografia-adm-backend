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
        Schema::create('asginacion_upm_usuario', function (Blueprint $table) {
            $table->comment('');
            $table->integer('upm_id')->index('fk_asginacion_upm_usuario_upm1_idx');
            $table->integer('usuario_id')->index('fk_asginacion_upm_usuario_usuario1_idx');
            $table->integer('proyecto_id')->index('fk_asginacion_upm_usuario_proyecto1_idx');

            $table->primary(['upm_id', 'usuario_id', 'proyecto_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asginacion_upm_usuario');
    }
};
