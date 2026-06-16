<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->unsignedTinyInteger('score')->default(0);
            $table->string('status', 20)->default('red');
            $table->json('issues')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_audits');
    }
};
