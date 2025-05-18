<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->string('registration_number', 20)->unique();
            $table->unsignedBigInteger('vehicle_type_id');
            $table->string('brand', 50);
            $table->string('model', 100);
            $table->year('year');
            $table->integer('capacity')->nullable();
            $table->enum('ownership_type', ['owned', 'leased']);
            $table->string('lease_company', 100)->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'inactive'])->default('available');
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->timestamps();
            
            $table->foreign('vehicle_type_id')->references('vehicle_type_id')->on('vehicle_types');
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
        Schema::dropIfExists('vehicles');
    }
}