<?php

namespace App\Services\MetaMonitor\Adapters;

interface MetaPartnerAdapterInterface
{
    public function buildRequest(object $source): array;

    public function expectsRedirect(): bool;

    public function extractSearchCode(?string $location): ?string;
}
