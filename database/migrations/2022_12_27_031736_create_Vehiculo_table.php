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
        Schema::create('Vehiculo', function (Blueprint $table) {
            $table->comment('');
            $table->string('Placa', 7)->primary();
            $table->string('Modelo', 25);
            $table->date('Año');
            $table->integer('Equipo Campo_Id')->index('fk_Vehiculo_Equipo Campo1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Vehiculo');
    }
};
