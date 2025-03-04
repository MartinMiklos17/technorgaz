<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        FilamentAsset::register([
            Js::make('mapbox-gl', 'https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.js'),
            Js::make('mapbox-geocoder', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.2/mapbox-gl-geocoder.min.js'),
            Css::make('mapbox-css', 'https://api.mapbox.com/mapbox-gl-js/v3.10.0/mapbox-gl.css'),
            Css::make('mapbox-geocoder-css', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v4.7.2/mapbox-gl-geocoder.css'),
        ]);
    }
}
