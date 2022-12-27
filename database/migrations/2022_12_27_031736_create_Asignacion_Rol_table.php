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
        Schema::create('Asignacion Rol', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Rol_Id')->index('fk_Asignacion Rol_Rol1_idx');
            $table->integer('Grupo_Id')->index('fk_Asignacion Rol_Grupo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Asignacion Rol');
    }
};
