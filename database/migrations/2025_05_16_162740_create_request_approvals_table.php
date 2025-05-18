<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_approvals', function (Blueprint $table) {
            $table->id('approval_id');
            $table->unsignedBigInteger('request_id');
            $table->unsignedBigInteger('approver_id');
            $table->integer('approval_level');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->dateTime('approval_datetime')->nullable();
            $table->timestamps();
            
            $table->foreign('request_id')->references('request_id')->on('vehicle_requests');
            $table->foreign('approver_id')->references('user_id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_approvals');
    }
}