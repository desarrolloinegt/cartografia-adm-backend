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
        Schema::create('asignacion_grupo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('usuario_id')->index('fk_Asignacion Grupo_Usuario1_idx');
            $table->integer('grupo_id')->index('fk_Asignacion Grupo_Grupo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_grupo');
    }
};
