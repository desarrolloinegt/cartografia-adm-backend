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
        Schema::table('upm', function (Blueprint $table) {
            $table->foreign(['municipio_id', 'departamento_id'], 'fk_municipio')->references(['id', 'departamento_id'])->on('municipio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upm', function (Blueprint $table) {
            $table->dropForeign('fk_municipio');
        });
    }
};
