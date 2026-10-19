<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('ncm', 8)->nullable()->after('barcode');
            $table->string('cest', 7)->nullable()->after('ncm');
            $table->string('cfop', 4)->nullable()->after('cest');
            $table->string('csosn', 3)->nullable()->after('cfop');
            $table->string('unidade_comercial', 6)->default('UN')->after('csosn');
            $table->tinyInteger('origem')->default(0)->after('unidade_comercial');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['ncm', 'cest', 'cfop', 'csosn', 'unidade_comercial', 'origem']);
        });
    }
};
