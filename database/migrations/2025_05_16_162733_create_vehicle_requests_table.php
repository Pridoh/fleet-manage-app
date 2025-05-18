<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('vehicle_type_id');
            $table->text('purpose');
            $table->unsignedBigInteger('pickup_location_id');
            $table->unsignedBigInteger('destination_location_id');
            $table->dateTime('pickup_datetime');
            $table->dateTime('return_datetime');
            $table->integer('passenger_count')->nullable();
            $table->text('goods_description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'partially_approved', 'approved', 'rejected', 'cancelled', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('requester_id')->references('user_id')->on('users');
            $table->foreign('vehicle_type_id')->references('vehicle_type_id')->on('vehicle_types');
            $table->foreign('pickup_location_id')->references('location_id')->on('locations');
            $table->foreign('destination_location_id')->references('location_id')->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_requests');
    }
}