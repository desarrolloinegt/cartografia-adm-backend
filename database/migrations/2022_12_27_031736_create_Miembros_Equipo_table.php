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
        Schema::create('Miembros Equipo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Id', true);
            $table->integer('Usuario_Id')->index('fk_Miembros Equipo_Usuario1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Miembros Equipo');
    }
};
