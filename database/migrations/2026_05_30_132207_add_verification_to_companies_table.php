<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    { 
        Schema::table('companies', function (Blueprint $table) {
            $table->string('verification_status', 20)->default('pending')->after('is_hiring');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->text('rejection_reason')->nullable()->after('verified_at');

            $table->index('verification_status');
        }); 
    } 

    public function down(): void
    { 
        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['verification_status']);
            $table->dropColumn(['verification_status', 'verified_at', 'rejection_reason']);
        });
    }
}; 