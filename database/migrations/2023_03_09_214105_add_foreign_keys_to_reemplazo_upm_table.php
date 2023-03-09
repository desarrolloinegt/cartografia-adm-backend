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
        Schema::table('reemplazo_upm', function (Blueprint $table) {
            $table->foreign(['upm_anterior'], 'fk_reemplazo_upm_upm1')->references(['id'])->on('upm')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['upm_nuevo'], 'fk_reemplazo_upm_upm2')->references(['id'])->on('upm')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['usuario_id'], 'fk_reemplazo_upm_usuario1')->references(['id'])->on('usuario')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reemplazo_upm', function (Blueprint $table) {
            $table->dropForeign('fk_reemplazo_upm_upm1');
            $table->dropForeign('fk_reemplazo_upm_upm2');
            $table->dropForeign('fk_reemplazo_upm_usuario1');
        });
    }
};
