<?php

use App\Http\Controllers\Agents\CxAgentController;
use App\Http\Controllers\Genesys\BotConnectorController;
use App\Http\Controllers\Genesys\GenesysConnectionController;
use App\Http\Controllers\Interactions\InteractionController;
use App\Http\Controllers\Knowledge\KnowledgeProxyController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Settings\InfinizedConnectionController;
use App\Http\Controllers\Webhooks\InfinizedWebhookController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/infinized', [InfinizedWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('webhooks.infinized');

Route::post('bot/genesys/{connectorId}', [BotConnectorController::class, 'handle'])
    ->middleware('throttle:240,1')
    ->name('genesys.bot');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('onboarding/workspace', [OnboardingController::class, 'workspace'])->name('onboarding.workspace');
    Route::post('onboarding/infinized', [OnboardingController::class, 'infinized'])->name('onboarding.infinized');
    Route::post('onboarding/agent', [OnboardingController::class, 'agent'])->name('onboarding.agent');
    Route::post('onboarding/skip-agent', [OnboardingController::class, 'skipAgent'])->name('onboarding.skip-agent');

    Route::get('settings/infinized', [InfinizedConnectionController::class, 'edit'])->name('infinized.edit');
    Route::post('settings/infinized', [InfinizedConnectionController::class, 'store'])->name('infinized.store');
    Route::post('settings/infinized/provision', [InfinizedConnectionController::class, 'provision'])->name('infinized.provision');
    Route::post('settings/infinized/health', [InfinizedConnectionController::class, 'health'])->name('infinized.health');
    Route::delete('settings/infinized', [InfinizedConnectionController::class, 'destroy'])->name('infinized.destroy');
});

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('agents', [CxAgentController::class, 'index'])->name('agents.index');
        Route::get('agents/create', [CxAgentController::class, 'create'])->name('agents.create');
        Route::post('agents', [CxAgentController::class, 'store'])->name('agents.store');
        Route::get('agents/{agent}', [CxAgentController::class, 'show'])->name('agents.show');
        Route::post('agents/{agent}/test-chat', [CxAgentController::class, 'testChat'])->name('agents.test-chat');

        Route::get('knowledge', [KnowledgeProxyController::class, 'index'])->name('knowledge.index');
        Route::get('knowledge/search', [KnowledgeProxyController::class, 'search'])->name('knowledge.search');
        Route::post('knowledge', [KnowledgeProxyController::class, 'store'])->name('knowledge.store');

        Route::get('genesys', [GenesysConnectionController::class, 'edit'])->name('genesys.edit');
        Route::post('genesys', [GenesysConnectionController::class, 'store'])->name('genesys.store');

        Route::get('interactions', [InteractionController::class, 'index'])->name('interactions.index');
        Route::get('interactions/{interaction}', [InteractionController::class, 'show'])->name('interactions.show');
    });
