@extends('crm.layout')
@section('content')
<div class="crm-headline"><div><h1>API Control</h1><p>Read-only operational health view. Flight API contracts and fare logic are not modified here.</p></div></div>
<div class="crm-kpi-grid">
@foreach(['Airports'=>$stats['airports'],'Airlines'=>$stats['airlines'],'Active fare rules'=>$stats['active_rules'],'Website bookings'=>$stats['website_bookings']] as $k=>$v)<div class="crm-card crm-kpi"><div class="label">{{ $k }}</div><div class="value">{{ number_format($v) }}</div><small>Connected database records</small></div>@endforeach
</div>
<div class="crm-card mt-3"><div class="crm-card-head"><b>Integration status</b><span class="soft green">Connected</span></div><div class="crm-card-body"><div class="detail-grid"><div class="detail-item"><span>API mode</span><strong>{{ $settings['api_type'] ?? 'Configured' }}</strong></div><div class="detail-item"><span>SOAP runtime</span><strong>Uses server PHP SOAP extension</strong></div><div class="detail-item"><span>Fare engine</span><strong>Existing markup/fare rules preserved</strong></div></div><p class="safe-note">This screen intentionally does not expose API credentials or secrets.</p></div></div>
@endsection
