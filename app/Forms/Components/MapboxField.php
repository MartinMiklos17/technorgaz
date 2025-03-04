<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class MapboxField extends Field
{
    protected string $view = 'filament.forms.components.mapbox-field';

    /*protected function setUp(): void
    {
        parent::setUp();

        // Ensure that the field hydrates properly
        $this->afterStateHydrated(function ($state, callable $set) {
            // If state is not an array, initialize it as an empty array
            if (!is_array($state)) {
                $state = [];
            }

            $set('latitude', $state['latitude'] ?? null);
            $set('longitude', $state['longitude'] ?? null);
            $set('location_address', $state['location_address'] ?? null);
        });

        // Ensure that the field stores values correctly
        $this->dehydrateStateUsing(function ($state) {
            // Make sure $state is an array before returning it
            if (!is_array($state)) {
                $state = [];
            }

            return [
                'latitude' => $state['latitude'] ?? request()->input('latitude'),
                'longitude' => $state['longitude'] ?? request()->input('longitude'),
                'location_address' => $state['location_address'] ?? request()->input('location_address'),
            ];
        });
    }*/
}
