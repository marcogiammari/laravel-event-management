<?php

namespace App\Providers;

use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // i gate si usano per logiche semplici applicabili a più risorse
        // per logiche più complesse e diversificate sono più adatte le policy

        // definizione del gate
        // solo lo user proprietario dell'evento può modificarlo
        // Gate::define(
        //     'update-event',
        //     function ($user, Event $event) {
        //         return $user->id === $event->user_id;
        //     }
        // );

        // Gate::define('delete-attendee', 
        //     function ($user, Event $event, Attendee $attendee) {
        //         return $user->id === $attendee->user_id || $user->id === $event->user_id;
        //     }
        // );
    }
}
