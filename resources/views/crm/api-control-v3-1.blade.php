@extends('crm.layout')
@section('content')
<div class="container-fluid py-4">

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
<div>
<div class="small text-primary fw-bold text-uppercase">Secure supplier configuration</div>
<h1 class="mb-1">API Control V3.1</h1>
<div class="text-muted">Demo/Live switching with separate Search and General/PreBook SOAP endpoints.</div>
</div>
<form method="POST" action="{{route('crm.api_control_v3_test')}}">@csrf
<button class="btn btn-light border"><i class="fa-solid fa-plug-circle-check me-1"></i>Test Connection</button>
</form>
</div>

@if(session('crm_success'))<div class="alert alert-success">{{session('crm_success')}}</div>@endif
@if(session('crm_error'))<div class="alert alert-danger">{{session('crm_error')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif

<div class="card border-0 shadow-sm mb-4">
<div class="card-body p-4"><div class="row g-4 align-items-center">
<div class="col-xl-7">
<div class="small text-muted text-uppercase fw-bold">Current environment</div>
<div class="display-6 fw-bold mt-1">{{strtoupper($mode)}}</div>
<div class="mt-2">@if($mode==='Live')<span class="badge bg-danger">REAL SUPPLIER ENVIRONMENT</span>@else<span class="badge bg-primary">TEST ENVIRONMENT</span>@endif</div>
</div>
<div class="col-xl-5">
@if($mode==='Demo')
<form method="POST" action="{{route('crm.api_control_v3_switch')}}" onsubmit="return confirm('Switch to LIVE? Normal booking flow will use Live supplier services.');">@csrf
<input type="hidden" name="mode" value="Live"><input type="hidden" name="confirm_live" value="1">
<button class="btn btn-danger w-100 py-3 fw-bold">Switch to LIVE</button>
</form>
@else
<form method="POST" action="{{route('crm.api_control_v3_switch')}}" onsubmit="return confirm('Switch to DEMO?');">@csrf
<input type="hidden" name="mode" value="Demo"><button class="btn btn-primary w-100 py-3 fw-bold">Switch to DEMO</button>
</form>
@endif
</div>
</div></div>
</div>

<div class="row g-3 mb-4">
<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted fw-bold">WSDL</div><div class="fs-5 fw-bold">{{$config['wsdl_configured']??false?'Configured':'Missing'}}</div><small>{{$config['wsdl_type']??'Unknown'}}</small></div></div></div>
<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted fw-bold">Search Endpoint</div><div class="fw-bold text-break">{{$config['search_host']??'Missing'}}</div></div></div></div>
<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted fw-bold">General/PreBook</div><div class="fw-bold text-break">{{$config['general_host']??'Missing'}}</div></div></div></div>
<div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="small text-muted fw-bold">Currency</div><div class="fs-5 fw-bold">{{$config['currency']??'—'}}</div></div></div></div>
</div>

<div class="row g-4 mb-4">
@foreach(['demo'=>'Demo API Configuration','live'=>'Live API Configuration'] as $envKey=>$title)
<div class="col-xl-6"><div class="card border-0 shadow-sm h-100">
<div class="card-header bg-white py-3"><div class="fw-bold">{{$title}}</div><div class="small text-muted">Secret fields remain hidden. Leave blank to keep the saved value.</div></div>
<div class="card-body">
<form method="POST" action="{{route('crm.api_control_v3_credentials')}}">@csrf
<input type="hidden" name="environment" value="{{ucfirst($envKey)}}">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">API Login</label><input type="password" class="form-control" name="api_login" placeholder="{{$secure[$envKey]['login']?'Configured - enter only to replace':'Enter login'}}"></div>
<div class="col-md-6"><label class="form-label">API Password</label><input type="password" class="form-control" name="api_password" placeholder="{{$secure[$envKey]['password']?'Configured - enter only to replace':'Enter password'}}"></div>
<div class="col-md-6"><label class="form-label">Device ID</label><input type="password" class="form-control" name="device_id" placeholder="{{$secure[$envKey]['device_id']?'Configured - enter only to replace':'Enter Device ID'}}"></div>
<div class="col-md-6"><label class="form-label">Token</label><input type="password" class="form-control" name="token" placeholder="{{$secure[$envKey]['token']?'Configured - enter only to replace':'Enter token'}}"></div>
<div class="col-md-4"><label class="form-label">Currency</label><input class="form-control" name="currency" value="{{$secure[$envKey]['currency']}}" required></div>
<div class="col-12"><label class="form-label">WSDL</label><input class="form-control" name="wsdl" value="{{$secure[$envKey]['wsdl']}}" placeholder="URL or local SiteCity.wsdl path"></div>
<div class="col-12"><label class="form-label">Search SOAP Endpoint</label><input class="form-control" name="search_soap" value="{{$secure[$envKey]['search_soap']}}" required></div>
<div class="col-12"><label class="form-label">General / PreBook SOAP Endpoint</label><input class="form-control" name="general_soap" value="{{$secure[$envKey]['general_soap']}}" required></div>
<div class="col-12"><div class="d-flex flex-wrap gap-2 small">
<span class="badge {{$secure[$envKey]['login']?'bg-success':'bg-secondary'}}">Login {{$secure[$envKey]['login']?'configured':'missing'}}</span>
<span class="badge {{$secure[$envKey]['password']?'bg-success':'bg-secondary'}}">Password {{$secure[$envKey]['password']?'configured':'missing'}}</span>
<span class="badge {{$secure[$envKey]['device_id']?'bg-success':'bg-secondary'}}">Device ID {{$secure[$envKey]['device_id']?'configured':'missing'}}</span>
<span class="badge {{$secure[$envKey]['token']?'bg-success':'bg-secondary'}}">Token {{$secure[$envKey]['token']?'configured':'missing'}}</span>
</div></div>
<div class="col-12 d-flex justify-content-end"><button class="btn btn-primary">Save {{ucfirst($envKey)}} Configuration</button></div>
</div></form>
</div></div></div>
@endforeach
</div>

<div class="alert alert-warning">
<strong>Live testing note:</strong> A successful WSDL test only confirms that the WSDL can be loaded. The supplier may still require IP whitelisting for authenticated Search/PreBook/Booking calls.
</div>

<div class="card border-0 shadow-sm">
<div class="card-header bg-white py-3"><div class="fw-bold">API Control audit & diagnostics</div></div>
<div class="table-responsive"><table class="table align-middle mb-0">
<thead class="table-light"><tr><th>Time</th><th>Admin</th><th>Mode</th><th>Action</th><th>Status</th><th>Latency</th><th>Message</th></tr></thead>
<tbody>
@forelse($recentLogs as $log)
<tr><td>{{$log->created_at?\Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A'):'—'}}</td><td>{{$log->admin_name?:'Admin #'.$log->admin_id}}</td><td>{{$log->mode}}</td><td>{{str_replace('_',' ',ucwords($log->action,'_'))}}</td><td><span class="badge {{$log->status==='success'?'bg-success':'bg-danger'}}">{{$log->status}}</span></td><td>{{$log->latency_ms!==null?$log->latency_ms.' ms':'—'}}</td><td class="small">{{$log->message}}</td></tr>
@empty<tr><td colspan="7" class="text-center py-4 text-muted">No activity yet.</td></tr>@endforelse
</tbody></table></div>
</div>

</div>
@endsection
