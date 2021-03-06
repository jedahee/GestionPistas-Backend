<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Comentarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            
            $table->longText('texto');
            $table->timestamps();
            $table->boolean('like');
            
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('pistas_id');
            
            // Relaciones
            $table->foreign('users_id')->references('id')->on('users');
            $table->foreign('pistas_id')->references('id')->on('pistas');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comentarios');
    }
}
