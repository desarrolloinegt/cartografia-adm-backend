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
        Schema::create('Asignacion UPM', function (Blueprint $table) {
            $table->comment('');
            $table->integer('UPM_Id')->index('fk_Asignacion UPM_UPM1_idx');
            $table->integer('Proyecto_Id')->index('fk_Asignacion UPM_Proyecto1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Asignacion UPM');
    }
};
