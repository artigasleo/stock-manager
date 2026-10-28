<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->date('date');

            $table->foreignId('opened_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('opened_at')->nullable();
            $table->decimal('opening_cash', 10, 2)->nullable();
            $table->decimal('opening_cash_usd', 10, 2)->nullable();

            $table->foreignId('closed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['unit_id', 'date']);
        });

        Schema::create('cash_closing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_closing_id')->constrained('cash_closings')->cascadeOnDelete();
            $table->string('payment_method', 30);
            $table->decimal('expected_amount', 10, 2);
            $table->decimal('counted_amount', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_closing_items');
        Schema::dropIfExists('cash_closings');
    }
};
