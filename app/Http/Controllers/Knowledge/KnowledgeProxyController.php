<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Services\Infinized\InfinizedClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeProxyController extends Controller
{
    public function index(Request $request, InfinizedClient $client): Response
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($team !== null && $connection !== null, 404);

        $sources = $client->forConnection($connection)->listKnowledge();

        return Inertia::render('knowledge/index', [
            'sources' => $sources['data'] ?? [],
            'organization_slug' => $connection->infinized_organization_slug,
            'infinized_url' => config('infinized.url'),
        ]);
    }

    public function search(Request $request, InfinizedClient $client): Response
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($team !== null && $connection !== null, 404);

        $query = $request->string('q')->trim()->toString();

        $results = $query !== ''
            ? $client->forConnection($connection)->searchKnowledge($query)
            : ['data' => []];

        return Inertia::render('knowledge/search', [
            'query' => $query,
            'results' => $results['data'] ?? [],
        ]);
    }

    public function store(Request $request, InfinizedClient $client): RedirectResponse
    {
        $team = $request->user()?->currentTeam;
        $connection = $team?->infinizedConnection;

        abort_unless($team !== null && $connection !== null, 404);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:50000'],
        ]);

        $client->forConnection($connection)->createKnowledgeSource([
            'title' => $validated['title'],
            'type' => 'text',
            'content' => $validated['content'],
        ]);

        return back()->with('status', 'knowledge-created');
    }
}
