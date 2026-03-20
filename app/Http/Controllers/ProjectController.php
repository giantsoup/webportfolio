<?php

namespace App\Http\Controllers;

use App\Models\PortfolioSetting;
use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display the public project index.
     */
    public function index(): View
    {
        return view('portfolio.projects.index', [
            'settings' => PortfolioSetting::query()->first() ?? new PortfolioSetting(PortfolioSetting::defaults()),
            'projects' => Project::query()
                ->with('skills')
                ->published()
                ->ordered()
                ->paginate(9),
        ]);
    }

    /**
     * Display a public project detail page.
     */
    public function show(Project $project): View
    {
        abort_unless($project->is_published && filled($project->published_at) && $project->published_at->isPast(), 404);

        $project->loadMissing('skills');

        return view('portfolio.projects.show', [
            'settings' => PortfolioSetting::query()->first() ?? new PortfolioSetting(PortfolioSetting::defaults()),
            'project' => $project,
            'relatedProjects' => Project::query()
                ->with('skills')
                ->published()
                ->whereKeyNot($project->getKey())
                ->ordered()
                ->limit(3)
                ->get(),
        ]);
    }
}
