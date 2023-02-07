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
        Schema::table('asginacion_upm_usuario', function (Blueprint $table) {
            $table->foreign(['proyecto_id'], 'fk_asginacion_upm_usuario_proyecto1')->references(['id'])->on('proyecto')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['upm_id'], 'fk_asginacion_upm_usuario_upm1')->references(['id'])->on('upm')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['usuario_id'], 'fk_asginacion_upm_usuario_usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('asginacion_upm_usuario', function (Blueprint $table) {
            $table->dropForeign('fk_asginacion_upm_usuario_proyecto1');
            $table->dropForeign('fk_asginacion_upm_usuario_upm1');
            $table->dropForeign('fk_asginacion_upm_usuario_usuario1');
        });
    }
};
