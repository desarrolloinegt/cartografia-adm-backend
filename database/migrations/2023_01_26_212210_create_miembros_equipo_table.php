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
        Schema::create('miembros_equipo', function (Blueprint $table) {
            $table->comment('');
            $table->integer('cartografo')->index('fk_Miembros Equipo_Usuario1_idx');
            $table->integer('supervisor')->index('fk_miembros_equipo_equipo_campo1_idx');

            $table->primary(['cartografo', 'supervisor']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('miembros_equipo');
    }
};
