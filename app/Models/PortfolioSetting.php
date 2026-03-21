<?php

namespace App\Models;

use Database\Factories\PortfolioSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_kicker',
    'hero_title',
    'hero_emphasis',
    'hero_summary',
    'availability_text',
    'years_experience',
    'projects_completed',
    'location_label',
    'schedule_label',
    'github_url',
    'linkedin_url',
    'x_url',
])]
class PortfolioSetting extends Model
{
    /** @use HasFactory<PortfolioSettingFactory> */
    use HasFactory;

    /**
     * Get the default portfolio content.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'hero_kicker' => 'Focused on long-term product systems',
            'hero_title' => 'Architecting',
            'hero_emphasis' => 'Scalable',
            'hero_summary' => 'Senior Full Stack Developer specializing in high-performance Laravel, PHP, and cloud-backed product delivery.',
            'availability_text' => 'Laravel architecture, operational software, and durable internal tools.',
            'years_experience' => 10,
            'projects_completed' => 40,
            'location_label' => 'Remote / Los Angeles, CA',
            'schedule_label' => 'Pacific Time',
            'github_url' => 'https://github.com',
            'linkedin_url' => 'https://linkedin.com',
            'x_url' => null,
        ];
    }
}
