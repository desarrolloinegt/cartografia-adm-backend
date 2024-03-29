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
        Schema::create('control_de_progreso', function (Blueprint $table) {
            $table->comment('');
            $table->dateTime('fecha');
            $table->integer('upm_id');
            $table->integer('usuario_id');
            $table->integer('proyecto_id');
            $table->integer('estado_upm')->index('fk_control_de_progreso_estado_upm_idx');

            $table->index(['upm_id', 'usuario_id', 'proyecto_id'], 'fk_control_de_progreso_asginacion_upm_usuario1_idx');
            $table->primary(['usuario_id', 'proyecto_id', 'upm_id', 'estado_upm']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('control_de_progreso');
    }
};
