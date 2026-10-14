<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('sport', 40);
            $table->unsignedSmallInteger('year');
            $table->date('event_date')->nullable();
            $table->string('location');
            $table->text('description');
            $table->json('categories');
            $table->json('gallery');
            $table->string('cover_id')->nullable();
            $table->unsignedTinyInteger('image_focus')->default(50);
            $table->string('result_url', 2048)->nullable();
            $table->string('legacy_article_url', 2048)->nullable();
            $table->string('article_title')->nullable();
            $table->text('article_body')->nullable();
            $table->boolean('article_published')->default(false);
            $table->boolean('published')->default(false)->index();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('revision')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['year', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
