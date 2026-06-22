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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->boolean('aprobado')->default(false);
            $table->string('rol')->default('empresa');
            $table->string('device_hash')->nullable();
            $table->string('device_name')->nullable();
            $table->string('ip_registro')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('ultimo_acceso')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('empresa_id');
            $table->dropColumn([
                'aprobado',
                'rol',
                'device_hash',
                'device_name',
                'ip_registro',
                'user_agent',
                'ultimo_acceso',
            ]);
        });
    }
};
