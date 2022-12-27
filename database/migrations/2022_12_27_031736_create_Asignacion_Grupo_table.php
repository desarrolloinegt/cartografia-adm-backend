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
        Schema::create('Asignacion Grupo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Usuario_Id')->index('fk_Asignacion Grupo_Usuario1_idx');
            $table->integer('Grupo_Id')->index('fk_Asignacion Grupo_Grupo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Asignacion Grupo');
    }
};
