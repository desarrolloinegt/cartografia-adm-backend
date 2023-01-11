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
        Schema::create('grupo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('id', true);
            $table->string('nombre', 100)->unique('Nombre_UNIQUE');
            $table->string('descripcion', 400);
            $table->tinyInteger('estado');
            $table->integer('proyecto_id')->index('fk_Grupo_Proyecto1_idx');
            $table->integer('jerarquia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('grupo');
    }
};
