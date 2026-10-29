<?php

namespace App\Models;

use App\Services\GitHub\GitHubRepository;
use App\Services\GitHub\GitHubRepositoryDetails;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    'github_repository',
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
            'github_stars' => 'integer',
            'github_forks' => 'integer',
            'github_topics' => 'array',
            'github_pushed_at' => 'datetime',
            'github_synced_at' => 'datetime',
        ];
    }

    /**
     * Link the project to a GitHub repository and record its latest public stats and README.
     */
    public function syncFromGitHub(GitHubRepository $repository, GitHubRepositoryDetails $details): void
    {
        $this->forceFill([
            'github_repository' => $repository->fullName,
            'github_stars' => $repository->stars,
            'github_forks' => $repository->forks,
            'github_language' => $repository->language,
            'github_topics' => $repository->topics,
            'github_pushed_at' => $repository->pushedAt,
            'github_synced_at' => now(),
            'github_readme' => $details->readme,
        ])->save();
    }

    /**
     * Remove the GitHub link and the stats recorded from it.
     */
    public function unlinkFromGitHub(): void
    {
        $this->forceFill([
            'github_repository' => null,
            'github_stars' => null,
            'github_forks' => null,
            'github_language' => null,
            'github_topics' => null,
            'github_pushed_at' => null,
            'github_synced_at' => null,
            'github_readme' => null,
        ])->save();
    }

    /**
     * Get the "owner/name" of the GitHub repository a URL points at, if it points at one.
     */
    public static function gitHubRepositoryFromUrl(?string $url): ?string
    {
        if (blank($url) || ! preg_match('#^https?://(?:www\.)?github\.com/([A-Za-z0-9-]+)/([A-Za-z0-9._-]+?)(?:\.git)?/?$#i', trim($url), $matches)) {
            return null;
        }

        return "{$matches[1]}/{$matches[2]}";
    }

    /**
     * @return Attribute<list<string>, never>
     */
    protected function bodyParagraphs(): Attribute
    {
        return Attribute::get(fn (): array => array_values(array_filter(
            preg_split('/\R{2,}/', trim((string) $this->body)),
            fn (string $paragraph): bool => filled($paragraph),
        )));
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
