<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nfce', function (Blueprint $table) {
            $table->string('qrcode_url')->nullable()->after('xml_path');
        });
    }

    public function down(): void
    {
        Schema::table('nfce', function (Blueprint $table) {
            $table->dropColumn('qrcode_url');
        });
    }
};
