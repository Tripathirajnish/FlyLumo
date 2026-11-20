<?php

namespace App\Services\MetaMonitor\Adapters;

use Illuminate\Support\Str;

class FareLeadersAdapter implements MetaPartnerAdapterInterface
{
    public function buildRequest(object $source): array
    {
        return [
            'method' => strtoupper($source->http_method ?: 'GET'),

            'url' => $source->entry_url
                ?: rtrim(config('app.url'), '/') . '/api/search',

            'query' => [
                'origin1' => 'DEN',
                'destination1' => 'LAX',
                'departDate1' => now()->addDays(7)->format('d-M-Y'),
                'returnDate1' => now()->addDays(14)->format('d-M-Y'),
                'tripType' => 'round_trip',
                'adults' => 1,
                'childs' => 0,
                'infants' => 0,
                'classType' => 'Y',
                'currency' => 'USD',
                'metaName' => $source->meta_name ?: $source->slug,
                'flclick_id' => 'META_MONITOR_' . Str::random(16),
            ],

            'context' => [
                'origin' => 'DEN',
                'destination' => 'LAX',
                'trip_type' => 'round_trip',
                'meta_name' => $source->meta_name ?: $source->slug,
            ],
        ];
    }

    public function expectsRedirect(): bool
    {
        return true;
    }

    public function extractSearchCode(?string $location): ?string
    {
        if (
            $location &&
            preg_match(
                '#/flight-listing/([A-Za-z0-9]+)#',
                parse_url($location, PHP_URL_PATH) ?: '',
                $match
            )
        ) {
            return $match[1];
        }

        return null;
    }
}
