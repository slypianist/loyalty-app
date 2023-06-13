<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('rep_id')->nullable();
            $table->foreign('rep_id')->references('id')->on('reps')->onDelete('cascade');
            $table->string('shopCode');
            $table->string('companyCode')->nullable();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('location')->nullable();
            $table->enum('status',['ASSIGNED-TO-PARTNER', 'UNASSIGNED'])->default('UNASSIGNED');
            $table->enum('status2',['ASSIGNED-TO-REP', 'UNASSIGNED'])->default('UNASSIGNED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shops');
    }
};
