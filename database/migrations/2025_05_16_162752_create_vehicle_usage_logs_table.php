<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleUsageLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_usage_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('assignment_id');
            $table->enum('log_type', ['departure', 'arrival', 'refuel', 'checkpoint', 'issue']);
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('odometer_reading')->nullable();
            $table->decimal('fuel_level', 5, 2)->nullable();
            $table->decimal('fuel_added', 5, 2)->nullable();
            $table->decimal('fuel_cost', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('logged_by');
            $table->dateTime('log_datetime');
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('assignment_id')->references('assignment_id')->on('vehicle_assignments');
            $table->foreign('location_id')->references('location_id')->on('locations');
            $table->foreign('logged_by')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_usage_logs');
    }
}