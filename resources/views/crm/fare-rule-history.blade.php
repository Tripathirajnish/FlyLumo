@extends('crm.layout')
@section('content')
<style>
.frh-page{padding-bottom:24px}.frh-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:16px}.frh-head h1{font-size:34px;line-height:1.05;margin:6px 0 0;color:#082d59;letter-spacing:-.03em}.frh-head p{font-size:14px;color:#718299;margin:7px 0 0}.frh-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:10px;text-transform:uppercase;font-weight:900;letter-spacing:.09em;color:#1f5fda}.frh-eyebrow:before{content:"";width:34px;height:3px;border-radius:99px;background:#e91d4d;display:inline-block}.frh-card{background:#fff;border:1px solid #dfe7ef;border-radius:16px;overflow:hidden}.frh-card-head{padding:15px 18px;border-bottom:1px solid #e8eef5;display:flex;justify-content:space-between;align-items:center}.frh-card-head b{font-size:17px;color:#0d3158}.frh-card-head small{display:block;margin-top:3px;color:#8391a3}.frh-row{display:grid;grid-template-columns:170px 135px minmax(280px,1fr) 190px;gap:16px;padding:15px 18px;border-bottom:1px solid #edf1f5;font-size:12px;align-items:center}.frh-row:last-child{border:0}.frh-row small{color:#8190a2;font-size:10px;margin-top:3px}.frh-pill{display:inline-flex;padding:6px 9px;border-radius:999px;background:#edf3ff;color:#2459df;font-size:8px;font-weight:850;letter-spacing:.03em}.frh-empty{padding:28px;text-align:center;color:#8190a2}@media(max-width:800px){.frh-head{flex-direction:column;align-items:flex-start}.frh-row{grid-template-columns:1fr}.frh-card-head{align-items:flex-start;flex-direction:column;gap:5px}}
</style>
<div class="frh-page">
<div class="frh-head"><div>
<a href="{{ route('crm.fare_rule_edit',$rule->id) }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Rule #{{ $rule->id }}</a>
<span class="frh-eyebrow mt-2">Pricing audit</span><h1>Fare Rule Audit History</h1><p>{{ $rule->name }}</p>
</div></div>
<div class="frh-card">
<div class="frh-card-head"><div><b>Change history</b><small>Recorded CRM changes for this fare rule.</small></div><span class="frh-pill">{{ $history->count() }} entries</span></div>
@forelse($history as $h)
<div class="frh-row">
<div><b>{{ \Carbon\Carbon::parse($h->created_at)->format('d M Y') }}</b><small class="d-block">{{ \Carbon\Carbon::parse($h->created_at)->format('h:i:s A') }}</small></div>
<div><span class="frh-pill">{{ str_replace('_',' ',strtoupper($h->action)) }}</span></div>
<div><b>{{ $h->summary }}</b><small class="d-block">{{ $h->admin_name?:'CRM' }}</small></div>
<div>@if($h->before_json && $h->after_json)<small>Before/after snapshot stored</small>@elseif($h->before_json)<small>Pre-delete snapshot stored</small>@elseif($h->after_json)<small>Post-change snapshot stored</small>@else<small>No snapshot payload</small>@endif</div>
</div>
@empty<div class="frh-empty">No audit entries yet. New fare-rule changes will be recorded here.</div>@endforelse
</div></div>
@endsection