<?php

namespace App\Http\Requests;

use App\Models\Skill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertSkillRequest extends FormRequest
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
        /** @var Skill|null $skill */
        $skill = $this->route('skill');

        return self::rulesFor($skill);
    }

    /**
     * Get reusable validation rules for a skill upsert.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rulesFor(?Skill $skill = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('skills', 'slug')->ignore($skill)],
            'category' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'accent_color' => ['required', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
        ];
    }
}
