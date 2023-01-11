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
        Schema::create('asignacion_permisos', function (Blueprint $table) {
            $table->comment('');
            $table->integer('rol_id')->index('fk_Asignacion Permisos_Rol1_idx');
            $table->integer('permiso_Id')->index('fk_Asignacion Permisos_Permiso1_idx');

            $table->primary(['rol_id', 'permiso_Id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_permisos');
    }
};
