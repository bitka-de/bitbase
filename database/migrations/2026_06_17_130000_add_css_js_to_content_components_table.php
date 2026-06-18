<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_components', function (Blueprint $table): void {
            $table->longText('css')->nullable()->after('content');
            $table->longText('js')->nullable()->after('css');
        });
    }

    public function down(): void
    {
        Schema::table('content_components', function (Blueprint $table): void {
            $table->dropColumn(['css', 'js']);
        });
    }
};