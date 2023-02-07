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
        Schema::create('upm', function (Blueprint $table) {
            $table->comment('');
            $table->integer('id', true);
            $table->integer('municipio_id')->index('fk_UPM_Municipio1_idx');
            $table->tinyInteger('estado');
            $table->string('nombre', 100)->unique('nombre_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upm');
    }
};
