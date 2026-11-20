@extends('crm.layout')
@section('content')

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="small text-primary fw-bold text-uppercase" style="letter-spacing:.1em;">Supplier environment & diagnostics</div>
            <h1 class="mb-1">API Control</h1>
            <div class="text-muted">Switch Demo/Live safely and verify the SOAP configuration without exposing credentials.</div>
        </div>

        <form method="POST" action="{{ route('crm.api_control_v2_test') }}">
            @csrf
            <button class="btn btn-light border">
                <i class="fa-solid fa-plug-circle-check me-1"></i> Test Connection
            </button>
        </form>
    </div>

    @if(session('crm_success'))
        <div class="alert alert-success">{{ session('crm_success') }}</div>
    @endif
    @if(session('crm_error'))
        <div class="alert alert-danger">{{ session('crm_error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4" style="border-left:5px solid {{ $mode === 'Live' ? '#dc3545' : '#0d6efd' }} !important;">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-xl-7">
                    <div class="small text-uppercase fw-bold text-muted mb-2">Current environment</div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="display-6 fw-bold">{{ strtoupper($mode) }}</div>
                        @if($mode === 'Live')
                            <span class="badge bg-danger">REAL SUPPLIER ENVIRONMENT</span>
                        @else
                            <span class="badge bg-primary">TEST ENVIRONMENT</span>
                        @endif
                    </div>

                    <div class="mt-3 text-muted">
                        @if($mode === 'Live')
                            Live mode selects the real supplier configuration already defined by your application. Real supplier actions can affect real bookings when the normal booking flow calls them.
                        @else
                            Demo mode selects the test supplier configuration already defined by your application.
                        @endif
                    </div>
                </div>

                <div class="col-xl-5">
                    @if($mode === 'Demo')
                        <form method="POST" action="{{ route('crm.api_control_v2_switch') }}" onsubmit="return confirm('Switch API environment to LIVE? The normal booking flow will start selecting real supplier services where configured.');">
                            @csrf
                            <input type="hidden" name="mode" value="Live">
                            <input type="hidden" name="confirm_live" value="1">
                            <button class="btn btn-danger w-100 py-3 fw-bold">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Switch to LIVE
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('crm.api_control_v2_switch') }}" onsubmit="return confirm('Switch API environment back to DEMO?');">
                            @csrf
                            <input type="hidden" name="mode" value="Demo">
                            <button class="btn btn-primary w-100 py-3 fw-bold">
                                <i class="fa-solid fa-flask me-1"></i> Switch to DEMO
                            </button>
                        </form>
                    @endif

                    <div class="small text-muted mt-2 text-center">
                        Environment changes are written to the API Control audit log.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="small text-muted text-uppercase fw-bold">SOAP Runtime</div>
                <div class="fs-4 fw-bold mt-2">{{ extension_loaded('soap') ? 'Available' : 'Missing' }}</div>
                <div class="small text-muted">PHP SOAP extension</div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="small text-muted text-uppercase fw-bold">WSDL</div>
                <div class="fs-4 fw-bold mt-2">{{ ($config['wsdl_configured'] ?? false) ? 'Configured' : 'Not configured' }}</div>
                <div class="small text-muted">{{ $config['wsdl_type'] ?? 'Unknown' }}</div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="small text-muted text-uppercase fw-bold">SOAP Endpoint</div>
                <div class="fs-5 fw-bold mt-2 text-break">{{ $config['soap_host'] ?? 'Not configured' }}</div>
                <div class="small text-muted">Host only; credentials hidden</div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="small text-muted text-uppercase fw-bold">Supplier Currency</div>
                <div class="fs-4 fw-bold mt-2">{{ $config['currency'] ?? '—' }}</div>
                <div class="small text-muted">Current environment</div>
            </div></div>
        </div>
    </div>

    @if($configError)
        <div class="alert alert-warning">Configuration check: {{ $configError }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['Airports',$stats['airports']],
            ['Airlines',$stats['airlines']],
            ['Active Fare Rules',$stats['active_rules']],
            ['Website Bookings',$stats['website_bookings']],
            ['FareLeader Bookings',$stats['fareleader_bookings']]
        ] as [$label,$value])
            <div class="col-6 col-lg">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="small text-muted text-uppercase fw-bold">{{ $label }}</div>
                        <div class="fs-3 fw-bold mt-2">{{ number_format($value) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="fw-bold">Safe connection test</div>
                    <div class="small text-muted">Loads/parses the WSDL only. It does not call search, prebook, booking or ticketing methods.</div>
                </div>
                <div class="card-body">
                    @if($lastTest)
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Last result</span>
                            <strong class="{{ $lastTest->status === 'success' ? 'text-success' : 'text-danger' }}">{{ strtoupper($lastTest->status) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Environment</span>
                            <strong>{{ $lastTest->mode }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Latency</span>
                            <strong>{{ $lastTest->latency_ms !== null ? $lastTest->latency_ms.' ms' : '—' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tested</span>
                            <strong>{{ $lastTest->created_at ? \Carbon\Carbon::parse($lastTest->created_at)->format('d M Y, h:i A') : '—' }}</strong>
                        </div>
                    @else
                        <div class="text-muted">No connection test has been run yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <div class="fw-bold">Environment behavior</div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Demo</strong>
                        <div class="text-muted small">Uses the test credentials, test WSDL and test SOAP endpoint already defined in SoapService.</div>
                    </div>
                    <div class="mb-3">
                        <strong>Live</strong>
                        <div class="text-muted small">Uses the live supplier configuration already defined in SoapService. The active WSDL is also domain-dependent in your existing code.</div>
                    </div>
                    <div class="alert alert-warning mb-0">
                        This dashboard does not create bookings by itself. Real bookings will still be created only when your normal booking flow calls the supplier booking methods in Live mode.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <div class="fw-bold">API Control audit & diagnostics</div>
            <div class="small text-muted">Environment switches and safe connection tests. Passwords/tokens are not stored here.</div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Time</th><th>Admin</th><th>Mode</th><th>Action</th><th>Status</th><th>Latency</th><th>Message</th></tr>
                </thead>
                <tbody>
                @forelse($recentLogs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d M Y, h:i A') : '—' }}</td>
                        <td>{{ $log->admin_name ?: 'Admin #'.$log->admin_id }}</td>
                        <td><strong>{{ $log->mode ?: '—' }}</strong></td>
                        <td>{{ str_replace('_',' ',ucwords($log->action,'_')) }}</td>
                        <td><span class="badge {{ $log->status === 'success' ? 'bg-success' : 'bg-danger' }}">{{ $log->status }}</span></td>
                        <td>{{ $log->latency_ms !== null ? $log->latency_ms.' ms' : '—' }}</td>
                        <td class="small">{{ $log->message ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No API Control activity yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
