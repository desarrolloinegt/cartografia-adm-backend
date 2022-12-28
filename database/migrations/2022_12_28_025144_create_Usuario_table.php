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
        Schema::create('Usuario', function (Blueprint $table) {
            $table->comment('');
            $table->integer('Id', true);
            $table->string('DPI', 13)->unique('DPI_UNIQUE');
            $table->string('Nombres', 25);
            $table->string('Apellidos', 25);
            $table->string('Email', 45);
            $table->integer('Codigo_Usuario');
            $table->tinyInteger('Estado_Usuario');
            $table->string('Password', 100);
            $table->string('username', 50)->unique('username_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Usuario');
    }
};
