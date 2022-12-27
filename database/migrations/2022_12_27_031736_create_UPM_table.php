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
        Schema::create('UPM', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Id', true);
            $table->string('Descripcion', 200)->nullable();
            $table->integer('Municipio_Id')->index('fk_UPM_Municipio1_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('UPM');
    }
};
