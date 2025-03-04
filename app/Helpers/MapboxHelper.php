<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class MapboxHelper
{
    /*
    MapBoxHElper was designed to get input from frontend, fetch address from coordinates BUT NOT USED IN THE BUSINESS LOGIC YET

    !*/
    public static function getCoordinates($address)
    {
        if (!$address) {
            return ['longitude' => null, 'latitude' => null];
        }

        $accessToken = env('MAPBOX_API_KEY'); // .env-ben tárolt API kulcs
        $url = "https://api.mapbox.com/geocoding/v5/mapbox.places/" . urlencode($address) . ".json?access_token=$accessToken";

        $response = Http::get($url);
        $data = $response->json();

        if (isset($data['features'][0]['geometry']['coordinates'])) {
            return [
                'longitude' => $data['features'][0]['geometry']['coordinates'][0],
                'latitude' => $data['features'][0]['geometry']['coordinates'][1],
            ];
        }

        return ['longitude' => null, 'latitude' => null];
    }
}
