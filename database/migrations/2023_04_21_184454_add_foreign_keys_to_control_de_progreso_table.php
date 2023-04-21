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
        Schema::table('control_de_progreso', function (Blueprint $table) {
            $table->foreign(['upm_id', 'usuario_id', 'proyecto_id'], 'fk_control_de_progreso_asginacion_upm_usuario1')->references(['upm_id', 'usuario_id', 'proyecto_id'])->on('asignacion_upm_usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['estado_upm'], 'fk_control_de_progreso_estado_upm')->references(['cod_estado'])->on('estado_upm')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('control_de_progreso', function (Blueprint $table) {
            $table->dropForeign('fk_control_de_progreso_asginacion_upm_usuario1');
            $table->dropForeign('fk_control_de_progreso_estado_upm');
        });
    }
};
