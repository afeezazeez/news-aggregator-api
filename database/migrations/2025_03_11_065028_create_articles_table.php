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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('unique_id');
            $table->longText('title');
            $table->longText('url')->nullable();
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->string('source');
            $table->string('category')->nullable();
            $table->text('contributor')->nullable();
            $table->timestamp('published_at');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['source', 'category','published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
