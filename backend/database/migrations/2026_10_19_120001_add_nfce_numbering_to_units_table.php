<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('nfce_serie')->default(1)->after('active');
            $table->unsignedInteger('nfce_next_number')->default(1)->after('nfce_serie');
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['nfce_serie', 'nfce_next_number']);
        });
    }
};
