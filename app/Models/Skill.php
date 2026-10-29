<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'category',
    'icon',
    'accent_color',
    'sort_order',
    'is_featured',
])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
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
        ];
    }

    /**
     * Get the IDs of the skills whose name or slug matches any of the given terms, such as
     * GitHub topics and languages. Matching ignores case, spaces and punctuation.
     *
     * @param  iterable<string>  $terms
     * @return list<int>
     */
    public static function idsMatching(iterable $terms): array
    {
        $normalize = fn (string $value): string => preg_replace('/[^a-z0-9]/', '', Str::lower($value));

        $normalizedTerms = collect($terms)->map($normalize)->filter()->unique();

        if ($normalizedTerms->isEmpty()) {
            return [];
        }

        return static::query()
            ->get(['id', 'name', 'slug'])
            ->filter(fn (Skill $skill): bool => $normalizedTerms->contains($normalize($skill->name))
                || $normalizedTerms->contains($normalize($skill->slug)))
            ->pluck('id')
            ->values()
            ->all();
    }

    /**
     * Get the projects attached to the skill.
     *
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /**
     * Fill a missing slug from the name.
     */
    protected static function booted(): void
    {
        static::saving(function (self $skill): void {
            if (blank($skill->slug) && filled($skill->name)) {
                $skill->slug = Str::slug($skill->name);
            }
        });
    }
}
