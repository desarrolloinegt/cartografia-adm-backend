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
        Schema::table('asignacion_upm', function (Blueprint $table) {
            $table->foreign(['proyecto_id'], 'fk_Asignacion UPM_Proyecto1')->references(['id'])->on('proyecto')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['upm_id'], 'fk_Asignacion UPM_UPM1')->references(['id'])->on('upm')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['estado_upm'], 'fk_asignacion_upm_estado_upm1')->references(['cod_estado'])->on('estado_upm')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignacion_upm', function (Blueprint $table) {
            $table->dropForeign('fk_Asignacion UPM_Proyecto1');
            $table->dropForeign('fk_Asignacion UPM_UPM1');
            $table->dropForeign('fk_asignacion_upm_estado_upm1');
        });
    }
};
