<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Cargo' => 'App\Policies\CargoPolicy',
        'App\Models\OrganizacionPolitica' => 'App\Policies\OrganizacionPoliticaPolicy',
        'App\Models\Geografia' => 'App\Policies\GeografiaPolicy',
        'App\Models\Recinto' => 'App\Policies\RecintoPolicy',
        'App\Models\Mesa' => 'App\Policies\MesaPolicy',
        'App\Models\Candidato' => 'App\Policies\CandidatoPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
