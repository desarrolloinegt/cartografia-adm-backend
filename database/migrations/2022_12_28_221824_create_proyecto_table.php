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
        Schema::create('proyecto', function (Blueprint $table) {
            $table->comment('');
            $table->integer('id', true);
            $table->date('fecha');
            $table->tinyInteger('estado_proyecto');
            $table->integer('encuesta_id')->index('fk_Proyecto_Encuesta1_idx');
            $table->string('nombre', 100)->unique('nombre_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proyecto');
    }
};
