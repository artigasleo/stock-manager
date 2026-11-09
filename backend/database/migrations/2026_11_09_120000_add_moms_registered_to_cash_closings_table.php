<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->unsignedInteger('moms_registered')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->dropColumn('moms_registered');
        });
    }
};
