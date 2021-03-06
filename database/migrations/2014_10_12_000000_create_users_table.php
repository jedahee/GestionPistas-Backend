<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            $table->string('nombre', 30);
            $table->string('apellidos', 60);
            $table->string('rutaImagen')->nullable();
            
            $table->integer('numAdvertencias')->default(0);
            $table->boolean('activo')->default(1);
            
            $table->longText('adv1')->nullable();
            $table->longText('adv2')->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('token_password_reset', 80)->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unsignedBigInteger('rol_id');

            // Relaciones
            $table->foreign('rol_id')->references('id')->on('roles');
        });

        // Datos insertados después de la migración

        DB::table("users")->insert([
            [
                "nombre" => "admin", 
                "apellidos" => "gestionpistas",
                "rutaImagen" => null,
                "adv1" => null,
                "adv2" => null, 
                "email" => "admin@gmail.com", 
                "password" => bcrypt("admin"),
                "token_password_reset" => null,
                "rol_id" => 1
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
