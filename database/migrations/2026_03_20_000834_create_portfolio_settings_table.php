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
        Schema::create('portfolio_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_kicker')->default('Focused on long-term product systems');
            $table->string('hero_title')->default('Architecting');
            $table->string('hero_emphasis')->default('Scalable');
            $table->text('hero_summary');
            $table->string('availability_text')->default('Laravel architecture, operational software, and durable internal tools.');
            $table->unsignedSmallInteger('years_experience')->default(10);
            $table->unsignedInteger('projects_completed')->default(40);
            $table->string('location_label')->nullable();
            $table->string('schedule_label')->nullable();
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('x_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_settings');
    }
};
