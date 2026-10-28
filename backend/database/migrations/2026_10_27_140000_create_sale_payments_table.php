<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('payment_method', 30);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        // Cada venda já existente tinha uma única forma de pagamento pro valor
        // total — vira uma linha em sale_payments pra não perder esse histórico.
        DB::table('sales')->whereNotNull('payment_method')->orderBy('id')
            ->chunk(200, function ($sales) {
                foreach ($sales as $sale) {
                    DB::table('sale_payments')->insert([
                        'sale_id' => $sale->id,
                        'payment_method' => $sale->payment_method,
                        'amount' => $sale->total,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('payment_method', 30)->nullable()->after('status');
        });

        DB::table('sales')->orderBy('id')->each(function ($sale) {
            $firstPayment = DB::table('sale_payments')
                ->where('sale_id', $sale->id)
                ->orderBy('id')
                ->first();

            if ($firstPayment) {
                DB::table('sales')->where('id', $sale->id)->update([
                    'payment_method' => $firstPayment->payment_method,
                ]);
            }
        });

        Schema::dropIfExists('sale_payments');
    }
};
