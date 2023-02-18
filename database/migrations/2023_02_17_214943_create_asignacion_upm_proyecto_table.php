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
        Schema::create('asignacion_upm_proyecto', function (Blueprint $table) {
            $table->comment('');
            $table->integer('upm_id')->index('fk_Asignacion UPM_UPM1_idx');
            $table->integer('proyecto_id')->index('fk_Asignacion UPM_Proyecto1_idx');
            $table->integer('estado_upm')->index('fk_asignacion_upm_estado_upm1_idx');

            $table->primary(['upm_id', 'proyecto_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asignacion_upm_proyecto');
    }
};
