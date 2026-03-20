<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Project|null $project */
        $project = $this->route('project');

        return self::rulesFor($project);
    }

    /**
     * Get reusable validation rules for a project upsert.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?Project $project = null): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($project)],
            'summary' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'featured_image' => ['nullable', 'image', 'max:4096'],
            'category' => ['required', 'string', 'max:255'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'live_url' => ['nullable', 'url', 'max:255'],
            'case_study_url' => ['nullable', 'url', 'max:255'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'skill_ids' => ['array'],
            'skill_ids.*' => ['integer', Rule::exists('skills', 'id')],
        ];
    }
}
