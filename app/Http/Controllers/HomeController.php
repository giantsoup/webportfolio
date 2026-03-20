<?php

namespace App\Http\Controllers;

use App\Models\PortfolioSetting;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public home page.
     */
    public function __invoke(): View
    {
        return view('portfolio.home', [
            'settings' => PortfolioSetting::query()->first() ?? new PortfolioSetting(PortfolioSetting::defaults()),
            'featuredProjects' => Project::query()
                ->with('skills')
                ->published()
                ->ordered()
                ->limit(3)
                ->get(),
            'featuredSkills' => Skill::query()
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
