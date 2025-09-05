<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\TicketClassifier;
use App\Services\{OpenAIClassifier, RulesClassifier, RateLimitedClassifier};

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketClassifier::class, function () {
            $base = env('OPENAI_CLASSIFY_ENABLED', false)
                ? new OpenAIClassifier()
                : new RulesClassifier();
            return new RateLimitedClassifier($base); 
        });
    }

    public function boot(): void {}
}