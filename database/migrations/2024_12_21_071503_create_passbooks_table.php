<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('passbooks', function (Blueprint $table) {
            $table->id();
            $table->string('customerName');
            $table->string('address');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->date('bookDate');
            $table->string('account_number');
            $table->date('startDate');
            $table->date('endDate');
            $table->string('bankName');
            $table->string('forwardBalance');
            $table->string('monthlySalary')->nullable();
            $table->integer('salaryDate')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('passbooks');
    }
};
