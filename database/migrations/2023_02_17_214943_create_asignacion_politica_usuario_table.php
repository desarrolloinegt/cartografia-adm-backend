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
        Schema::create('asignacion_politica_usuario', function (Blueprint $table) {
            $table->comment('');
            $table->integer('usuario_id')->index('fk_administrador_usuario1_idx');
            $table->integer('rol_id')->index('fk_asignacion_administrador_rol1_idx');

            $table->primary(['usuario_id', 'rol_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_politica_usuario');
    }
};
