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
    Schema::create('CATEGORIAS', function (Blueprint $table) {
        $table->id('ID');

        $table->string('NOME', 100)->unique();

        $table->boolean('ATIVO')->default(true);

        $table->softDeletes('DELETED_AT');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('CATEGORIAS');
    }
};
