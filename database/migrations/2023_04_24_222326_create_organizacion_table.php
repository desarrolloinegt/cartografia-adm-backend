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
        Schema::create('organizacion', function (Blueprint $table) {
            $table->comment('');
            $table->integer('usuario_superior')->index('fk_organizacion_usuario1_idx');
            $table->integer('usuario_inferior')->index('fk_organizacion_usuario2_idx');
            $table->integer('proyecto_id')->index('fk_organizacion_proyecto1_idx');
            $table->integer('usuario_asignador')->index('fk_organizacion_usuario3_idx');
            $table->dateTime('fecha_asignacion')->nullable();

            $table->primary(['usuario_superior', 'usuario_inferior', 'proyecto_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organizacion');
    }
};
