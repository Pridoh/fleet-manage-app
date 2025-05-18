<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleMaintenanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_maintenance', function (Blueprint $table) {
            $table->id('maintenance_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->enum('maintenance_type', ['routine', 'repair', 'inspection', 'emergency']);
            $table->text('description');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('odometer_reading')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->string('performed_by', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('vehicle_id')->references('vehicle_id')->on('vehicles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_maintenance');
    }
}