<?php

namespace App\Services\GitHub;

use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GitHubProjectImporter
{
    /**
     * The summary used for imported drafts when the repository has no description.
     */
    public const string PLACEHOLDER_SUMMARY = 'Imported from GitHub. Add a summary before publishing.';

    public function __construct(private GitHubClient $github) {}

    /**
     * Get the editable project fields suggested by a repository.
     *
     * @return array{title: string, slug: string, summary: string, body: string, category: string, repo_url: string, live_url: ?string}
     */
    public function attributesFor(GitHubRepository $repository, GitHubRepositoryDetails $details, ?Project $ignore = null): array
    {
        $summary = Str::limit((string) $repository->description, 500, '');

        return [
            'title' => Str::headline($repository->name),
            'slug' => $this->uniqueSlug(Str::slug($repository->name), $ignore),
            'summary' => filled($summary) ? $summary : self::PLACEHOLDER_SUMMARY,
            'body' => self::plainTextFromMarkdown($details->readme) ?: (filled($summary) ? $summary : self::PLACEHOLDER_SUMMARY),
            'category' => $repository->language ?? 'Open Source',
            'repo_url' => $repository->htmlUrl,
            'live_url' => $repository->homepage,
        ];
    }

    /**
     * Get the IDs of the skills that match a repository's topics and languages.
     *
     * @return list<int>
     */
    public function skillIdsFor(GitHubRepository $repository, GitHubRepositoryDetails $details): array
    {
        return Skill::idsMatching([
            ...$repository->topics,
            ...array_keys($details->languages),
            ...array_filter([$repository->language]),
        ]);
    }

    /**
     * Create unpublished draft projects for repositories that are not linked to a project yet,
     * and link existing projects whose repository URL already points at one of them.
     *
     * @param  Collection<int, GitHubRepository>  $repositories
     * @return array{created: int, linked: int}
     */
    public function importAll(Collection $repositories): array
    {
        $linkedRepositories = Project::query()
            ->whereNotNull('github_repository')
            ->pluck('github_repository')
            ->map(fn (string $fullName): string => Str::lower($fullName));

        $pending = $repositories
            ->reject(fn (GitHubRepository $repository): bool => $linkedRepositories->contains(Str::lower($repository->fullName)))
            ->values();

        if ($pending->isEmpty()) {
            return ['created' => 0, 'linked' => 0];
        }

        $details = $this->github->detailsFor($pending);
        $projectsByRepository = Project::query()
            ->whereNull('github_repository')
            ->whereNotNull('repo_url')
            ->get()
            ->toBase()
            ->keyBy(fn (Project $project): string => Str::lower((string) Project::gitHubRepositoryFromUrl($project->repo_url)))
            ->except('');

        $created = 0;
        $linked = 0;

        foreach ($pending as $repository) {
            $repositoryDetails = $details[$repository->fullName];
            $project = $projectsByRepository->get(Str::lower($repository->fullName));

            if ($project) {
                $linked++;
            } else {
                $project = Project::query()->create([
                    ...$this->attributesFor($repository, $repositoryDetails),
                    'is_published' => false,
                    'is_featured' => false,
                    'published_at' => null,
                    'sort_order' => 0,
                ]);
                $project->skills()->sync($this->skillIdsFor($repository, $repositoryDetails));
                $created++;
            }

            $project->syncFromGitHub($repository, $repositoryDetails);
        }

        return ['created' => $created, 'linked' => $linked];
    }

    /**
     * Convert README Markdown into the plain paragraphs project bodies use: formatting, images,
     * badges, code blocks, tables and raw HTML are dropped, and each block becomes its own paragraph.
     */
    public static function plainTextFromMarkdown(?string $markdown): string
    {
        if (blank($markdown)) {
            return '';
        }

        $html = Str::markdown($markdown, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $html = preg_replace('#<(pre|table)\b.*?</\1>#is', '', $html);
        $html = preg_replace('#</(p|h[1-6]|li|pre|blockquote|tr)>#i', "$0\n\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return collect(preg_split('/\R{2,}/', $text))
            ->map(fn (string $block): string => trim(preg_replace('/[ \t]*\R[ \t]*/', ' ', $block)))
            ->filter()
            ->implode("\n\n");
    }

    /**
     * Make a slug unique among projects by appending a number when it is taken.
     */
    private function uniqueSlug(string $slug, ?Project $ignore): string
    {
        $candidate = $slug;

        for ($suffix = 2; Project::query()->where('slug', $candidate)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))->exists(); $suffix++) {
            $candidate = "{$slug}-{$suffix}";
        }

        return $candidate;
    }
}
