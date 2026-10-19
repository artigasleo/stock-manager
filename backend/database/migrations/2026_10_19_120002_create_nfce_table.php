<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfce', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')->unique()->constrained('sales')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();

            $table->string('status', 20)->default('pending');
            $table->string('environment', 15);
            $table->unsignedInteger('serie');
            $table->unsignedBigInteger('numero');

            $table->string('chave_acesso', 44)->nullable()->unique();
            $table->string('protocolo_autorizacao', 20)->nullable();
            $table->string('cstat', 4)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->string('xml_path')->nullable();
            $table->text('erro')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfce');
    }
};
