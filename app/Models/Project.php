<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'summary',
    'body',
    'featured_image_path',
    'category',
    'repo_url',
    'live_url',
    'case_study_url',
    'is_featured',
    'is_published',
    'sort_order',
    'published_at',
])]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_featured' => 'bool',
            'is_published' => 'bool',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope a query to only include published projects.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to the default public ordering.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at');
    }

    /**
     * Get the skills attached to the project.
     *
     * @return BelongsToMany<Skill, $this>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class)
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    /**
     * Fill a missing slug from the title.
     */
    protected static function booted(): void
    {
        static::saving(function (self $project): void {
            if (blank($project->slug) && filled($project->title)) {
                $project->slug = Str::slug($project->title);
            }
        });
    }
}
