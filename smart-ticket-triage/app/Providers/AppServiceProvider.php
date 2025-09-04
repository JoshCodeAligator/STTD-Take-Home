<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\TicketClassifier;
use App\Services\OpenAIClassifier;
use App\Services\RulesClassifier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */

    public function register(): void
    {
        $this->app->bind(TicketClassifier::class, function () {
            $useOpenAI = (bool) env('USE_OPENAI', false);
            $hasKey    = (string) config('services.openai.key') !== '';

            if ($useOpenAI && $hasKey) {
                return new OpenAIClassifier();
            }
            return new RulesClassifier();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
