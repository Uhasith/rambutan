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
            $table->string('customer_name');
            $table->string('bank_name');
            $table->string('account_number');
            $table->date('book_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('forward_balance');
            $table->integer('salary')->nullable();
            $table->integer('salary_date')->nullable();
            $table->integer('transactions_count');
            $table->string('address');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->json('transactions_meta_data')->nullable();
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
