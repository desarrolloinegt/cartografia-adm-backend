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
        Schema::create('Permiso', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Id', true);
            $table->string('Nombre', 45)->unique('Nombre_UNIQUE');
            $table->tinyInteger('estado');
            $table->string('alias', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Permiso');
    }
};
