@php
$resolveDisplayedFare = function($r) {
    $candidates = [
        $r['DisplayedAdultPrice'] ?? null,
        $r['FinalAdultPrice'] ?? null,
        $r['FinalPrice'] ?? null,
        $r['DisplayPrice'] ?? null,
        $r['TotalAdultFare'] ?? null,
        (($r['Is_Markup'] ?? 'No') === 'No'
            ? ($r['AdultPrice'] ?? null)
            : ($r['MinMarkup']['New_AdultPrice'] ?? $r['AdultPrice'] ?? null))
    ];

    foreach ($candidates as $v) {
        if (is_numeric($v) && (float)$v > 0) return (float)$v;
    }

    return 0.0;
};
@endphp

@foreach($batch as $key => $results)
    @include('partials.flight-result-card')
@endforeach
