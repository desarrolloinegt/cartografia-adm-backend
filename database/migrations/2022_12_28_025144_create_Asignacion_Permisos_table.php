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
        Schema::create('Asignacion Permisos', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Rol_Id')->index('fk_Asignacion Permisos_Rol1_idx');
            $table->integer('Permiso_Id')->index('fk_Asignacion Permisos_Permiso1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Asignacion Permisos');
    }
};
