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
        Schema::create('reemplazo_upm', function (Blueprint $table) {
            $table->comment('');
            $table->integer('usuario_id')->index('fk_reemplazo_upm_usuario1_idx');
            $table->integer('upm_anterior')->index('fk_reemplazo_upm_upm1_idx');
            $table->integer('upm_nuevo')->index('fk_reemplazo_upm_upm2_idx');
            $table->dateTime('fecha');
            $table->string('descripcion', 200)->nullable();

            $table->primary(['usuario_id', 'upm_anterior', 'upm_nuevo']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reemplazo_upm');
    }
};
