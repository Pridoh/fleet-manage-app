<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id('driver_id');
            $table->unsignedBigInteger('user_id')->unique()->nullable();
            $table->string('name', 100);
            $table->string('license_number', 50)->unique();
            $table->string('license_type', 20);
            $table->date('license_expiry');
            $table->string('phone', 20);
            $table->string('address', 255);
            $table->date('date_of_birth');
            $table->date('join_date')->nullable();
            $table->enum('status', ['available', 'on_duty', 'day_off', 'sick', 'inactive'])->default('available');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('user_id')->on('users');
            $table->foreign('location_id')->references('location_id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
    }
}