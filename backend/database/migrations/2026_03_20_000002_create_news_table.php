<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->longText('content');
            $table->string('image_url')->nullable();
            $table->string('author');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Matches list queries: WHERE category_id = ? ORDER BY published_at DESC
            $table->index(['category_id', 'published_at']);
            // All-news listing: ORDER BY published_at DESC
            $table->index('published_at');
            // Speeds up filtering featured articles
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
