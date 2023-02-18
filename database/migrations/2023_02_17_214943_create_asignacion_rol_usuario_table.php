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
        Schema::create('asignacion_rol_usuario', function (Blueprint $table) {
            $table->comment('');
            $table->integer('usuario_id')->index('fk_Asignacion Grupo_Usuario1_idx');
            $table->integer('rol_id')->index('fk_Asignacion Grupo_Grupo1_idx');
            $table->integer('proyecto_id')->index('fk_asignacion_rol_usuario_proyecto1_idx');

            $table->primary(['usuario_id', 'proyecto_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_rol_usuario');
    }
};
