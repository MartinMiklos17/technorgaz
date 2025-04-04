<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Mail\MailchimpTransport;
use Illuminate\Support\Facades\Mail;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use App\Models\ProductIntakeItem;
use App\Observers\ProductIntakeItemObserver;
use App\Models\ProductOutputItem;
use App\Observers\ProductOutputItemObserver;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['hu']); // also accepts a closure
        });
        Mail::extend('mailchimp', function () {
            return new MailchimpTransport();
        });
        Filament::serving(function () {
            FilamentAsset::register([
                // Google Maps API külső script
                /*Js::make('maps-api', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDPru2UVBvywRDSLv61KcaybltdSKSUHGY&callback=initMap&libraries=&v=weekly'),

                // A saját google-maps.js fájlod (például a public/js/google-maps.js-ban)
                Js::make('google-maps', asset('js/google-maps.js')),
                /*Css::make('leaflet-css', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.css'),

                // Leaflet JS (külső forrás)
                Js::make('leaflet-js', 'https://unpkg.com/leaflet@1.9.3/dist/leaflet.js'),

                // A saját leaflet-map.js fájlod, amely a térkép inicializálását végzi
                Js::make('leaflet-map', asset('js/leaflet-map.js')),*/
            ]);
        });
        ProductIntakeItem::observe(ProductIntakeItemObserver::class);
        ProductOutputItem::observe(ProductOutputItemObserver::class);
    }
}
