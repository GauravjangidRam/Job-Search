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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('logo_url', 2048)->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->text('description')->nullable();
            $table->text('culture')->nullable();
            $table->unsignedInteger('employee_count')->nullable();
            $table->unsignedInteger('founded_year')->nullable();
            $table->string('industry', 100)->nullable();
            $table->boolean('is_hiring')->default(false);
            $table->json('metrics')->nullable();
            $table->json('perks')->nullable();
            $table->timestamps();

            $table->index('is_hiring');
            $table->index('industry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
