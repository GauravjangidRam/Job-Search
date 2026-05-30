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
        Schema::create('career_insights', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['salary', 'trend', 'skill']);
            $table->string('label', 100);
            $table->string('value', 255);
            $table->unsignedInteger('sort_order');
            $table->timestamps();

            $table->index('type');
            $table->index(['type', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_insights');
    }
};
