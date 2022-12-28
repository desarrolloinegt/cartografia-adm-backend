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
        Schema::create('Grupo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Id', true);
            $table->string('Nombre', 100)->unique('Nombre_UNIQUE');
            $table->string('Descripcion', 400);
            $table->tinyInteger('estado');
            $table->integer('Proyecto_Id')->index('fk_Grupo_Proyecto1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Grupo');
    }
};
