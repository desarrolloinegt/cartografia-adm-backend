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
        Schema::create('asignacion_rol_politica', function (Blueprint $table) {
            $table->comment('');
            $table->integer('politica_id')->index('fk_Asignacion Rol_Rol1_idx');
            $table->integer('rol_id')->index('fk_Asignacion Rol_Grupo1_idx');

            $table->primary(['politica_id', 'rol_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_rol_politica');
    }
};
