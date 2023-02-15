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
        Schema::create('usuario', function (Blueprint $table) {
            $table->comment('');
            $table->integer('id', true);
            $table->string('DPI', 13)->unique('DPI_UNIQUE');
            $table->string('nombres', 50);
            $table->string('apellidos', 50);
            $table->string('email', 45);
            $table->integer('codigo_usuario');
            $table->tinyInteger('estado_usuario');
            $table->string('password', 100);
            $table->string('telefono', 45);
            $table->string('descripcion', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usuario');
    }
};
