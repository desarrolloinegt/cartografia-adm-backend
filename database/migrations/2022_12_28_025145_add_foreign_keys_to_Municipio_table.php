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
        Schema::table('Municipio', function (Blueprint $table) {
            $table->foreign(['Departamento_Id1'], 'fk_Municipio_Departamento1')->references(['Id'])->on('Departamento')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Municipio', function (Blueprint $table) {
            $table->dropForeign('fk_Municipio_Departamento1');
        });
    }
};
