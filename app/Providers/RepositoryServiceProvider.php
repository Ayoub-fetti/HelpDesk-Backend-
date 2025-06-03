<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Tickets\Repositories\TicketRepositoryInterface;
use App\Infrastructure\Repositories\EloquentTicketRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}