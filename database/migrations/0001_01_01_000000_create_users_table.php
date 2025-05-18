<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id('user_id');
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->enum('role', ['admin', 'approver']);
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('position', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('department_id')->references('department_id')->on('departments');
        });
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