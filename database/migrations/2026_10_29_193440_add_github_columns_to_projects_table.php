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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('github_repository')->nullable()->unique()->after('case_study_url');
            $table->unsignedInteger('github_stars')->nullable()->after('github_repository');
            $table->unsignedInteger('github_forks')->nullable()->after('github_stars');
            $table->string('github_language')->nullable()->after('github_forks');
            $table->json('github_topics')->nullable()->after('github_language');
            $table->timestamp('github_pushed_at')->nullable()->after('github_topics');
            $table->timestamp('github_synced_at')->nullable()->after('github_pushed_at');
            $table->longText('github_readme')->nullable()->after('github_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique(['github_repository']);
            $table->dropColumn([
                'github_repository',
                'github_stars',
                'github_forks',
                'github_language',
                'github_topics',
                'github_pushed_at',
                'github_synced_at',
                'github_readme',
            ]);
        });
    }
};
