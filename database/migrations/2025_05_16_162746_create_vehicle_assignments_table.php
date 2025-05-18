<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('assigned_by');
            $table->dateTime('actual_start_datetime')->nullable();
            $table->dateTime('actual_end_datetime')->nullable();
            $table->integer('start_odometer')->nullable();
            $table->integer('end_odometer')->nullable();
            $table->decimal('fuel_used', 10, 2)->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'cancelled'])->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('request_id')->references('request_id')->on('vehicle_requests');
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles');
            $table->foreign('driver_id')->references('driver_id')->on('drivers');
            $table->foreign('assigned_by')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_assignments');
    }
}