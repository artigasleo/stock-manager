<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('zip_code', 10)->nullable()->after('email');
            $table->string('street', 150)->nullable()->after('zip_code');
            $table->string('number', 20)->nullable()->after('street');
            $table->string('complement', 100)->nullable()->after('number');
            $table->string('neighborhood', 100)->nullable()->after('complement');
            $table->string('city', 100)->nullable()->after('neighborhood');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('country', 60)->nullable()->after('state');
            $table->string('instagram', 100)->nullable()->after('country');
            $table->string('state_registration', 30)->nullable()->after('instagram');
            $table->string('type', 50)->nullable()->after('state_registration');
        });

        // Aproveita o que já estava no campo de endereço livre antigo, jogando
        // pra dentro do novo campo "Rua" em vez de simplesmente descartar.
        DB::statement("UPDATE suppliers SET street = address WHERE address IS NOT NULL AND address != ''");

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('address')->nullable()->after('email');
        });

        DB::statement('UPDATE suppliers SET address = street WHERE street IS NOT NULL');

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'zip_code', 'street', 'number', 'complement',
                'neighborhood', 'city', 'state', 'country', 'instagram',
                'state_registration', 'type',
            ]);
        });
    }
};
