<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTripayTransactions extends Migration
{
    public function up()
    {
        Schema::create('tripay_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trans');
            $table->text('tripay')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tripay_transactions');
    }
}
