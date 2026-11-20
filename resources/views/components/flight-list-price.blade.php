<div class="col-md-auto tnb-price-col">
    @php
        $OfferCode = $results['OfferCode'];
        $displayPrice = $results['Is_Markup']=='No' ? $results['AdultPrice'] : ($results['MinMarkup']['New_AdultPrice'] ?? $results['AdultPrice']);
    @endphp
    <div class="tnb-price-box">
        <small>{{ $results['Is_Markup']=='No' ? 'Fare from' : 'Adjusted fare from' }}</small>
        @if($results['Is_Markup']!='No')
            <span class="tnb-old-price">${{ number_format((float)$results['AdultPrice'],2) }}</span>
        @endif
        <strong>${{ number_format((float)$displayPrice,2) }}</strong>
        <span>per person · taxes included</span>
        <button class="btn btn-primary tnb-select-btn"
            onclick='checkArray("{{$code}}","{{$OfferCode}}","{{$searchguid}}","{{$key}}");'>
            Select <i class="fa-solid fa-arrow-right ms-2"></i>
        </button>
    </div>
</div>