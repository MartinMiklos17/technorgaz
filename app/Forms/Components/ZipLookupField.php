<?php
// app/Forms/Components/ZipLookupField.php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Http;

class ZipLookupField extends TextInput
{
    protected string $cityFieldName = 'city';

    public function cityField(string $name): static
    {
        $this->cityFieldName = $name;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Irányítószám')
            ->reactive()
            ->live(onBlur: true)
            ->maxLength(20)
            ->afterStateUpdated(function ($set, $state) {
                if (blank($state)) {
                    return;
                }

                try {
                    $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$state}.json", [
                        'access_token' => env('MAPBOX_API_KEY'),
                        'country' => 'HU',
                        'limit' => 1,
                    ]);

                    $data = $response->json();

                    if (!empty($data['features'][0]['place_name'])) {
                        $place = $data['features'][0]['place_name'];
                        $parts = explode(',', $place);

                        if (count($parts) >= 2) {
                            $city = trim($parts[1]);
                            $set($this->cityFieldName, $city);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Mapbox geocoding error: ' . $e->getMessage());
                }
            });
    }
}
