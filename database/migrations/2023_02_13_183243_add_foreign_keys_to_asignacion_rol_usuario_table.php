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
        Schema::table('asignacion_rol_usuario', function (Blueprint $table) {
            $table->foreign(['usuario_id'], 'fk_administrador_usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['rol_id'], 'fk_asignacion_administrador_rol1')->references(['id'])->on('rol')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asignacion_rol_usuario', function (Blueprint $table) {
            $table->dropForeign('fk_administrador_usuario1');
            $table->dropForeign('fk_asignacion_administrador_rol1');
        });
    }
};
