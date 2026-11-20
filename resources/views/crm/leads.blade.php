@extends('crm.layout')
@section('content')

<style id="fareshunt-leads-clean-v1">
:root{
    --lm-navy:#071f4f;
    --lm-blue:#1f5bd8;
    --lm-red:#e3194a;
    --lm-line:#dbe6f2;
    --lm-muted:#73839a;
}

/* PAGE */
.lm-page{
    font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif!important;
}

.lm-page-head{
    display:flex;
    align-items:flex-end;
    justify-content:space-between;
    gap:20px;
    margin-bottom:18px;
}
.lm-eyebrow{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:7px;
    color:#1d5bd2;
    text-transform:uppercase;
    letter-spacing:.12em;
    font-size:10px;
    font-weight:800;
}
.lm-eyebrow:before{
    content:"";
    width:30px;
    height:3px;
    border-radius:999px;
    background:var(--lm-red);
}
.lm-page-head h1{
    margin:0;
    color:var(--lm-navy);
    font-size:34px;
    line-height:1.05;
    font-weight:700;
    letter-spacing:-.03em;
}
.lm-page-head p{
    margin:7px 0 0;
    color:var(--lm-muted);
    font-size:14px;
}
.lm-page-head .btn{
    min-height:44px;
    padding:0 16px!important;
    display:inline-flex;
    align-items:center;
    border-radius:12px!important;
    box-shadow:0 10px 22px rgba(31,91,216,.15);
}

/* KPI */
.lm-kpis{
    display:grid;
    grid-template-columns:repeat(6,minmax(0,1fr));
    gap:12px;
    margin-bottom:14px;
}
.lm-kpi{
    min-height:86px;
    display:flex;
    align-items:center;
    gap:12px;
    padding:14px;
    border:1px solid var(--lm-line);
    border-radius:16px;
    background:#fff;
    box-shadow:0 8px 20px rgba(12,44,88,.04);
}
.lm-kpi>span{
    width:42px;
    height:42px;
    display:grid;
    place-items:center;
    border-radius:12px;
    font-size:16px;
}
.lm-kpi.blue>span{background:#edf4ff;color:#1f5bd8}
.lm-kpi.cyan>span{background:#eaf9fb;color:#10889a}
.lm-kpi.purple>span{background:#f2ebff;color:#7440c6}
.lm-kpi.amber>span{background:#fff5df;color:#b87107}
.lm-kpi.green>span{background:#eaf8f0;color:#16804b}
.lm-kpi.red>span{background:#fff0f3;color:#d3294d}
.lm-kpi small{
    display:block;
    color:#7c8ca1;
    font-size:9px;
    text-transform:uppercase;
    font-weight:800;
    letter-spacing:.05em;
}
.lm-kpi strong{
    display:block;
    margin-top:3px;
    color:var(--lm-navy);
    font-size:24px;
    line-height:1;
}

/* FILTERS */
.lm-toolbar{
    padding:18px!important;
    margin-bottom:14px;
    border:1px solid var(--lm-line)!important;
    border-radius:18px!important;
}
.lm-filters{
    display:block!important;
}
.lm-filter-row{
    display:grid;
    gap:12px;
    align-items:end;
}
.lm-filter-row + .lm-filter-row{
    margin-top:12px;
}
.lm-row-one{
    grid-template-columns:minmax(320px,1.4fr) minmax(180px,.65fr) minmax(180px,.65fr) minmax(180px,.65fr);
}
.lm-row-two{
    grid-template-columns:minmax(180px,.7fr) minmax(170px,.7fr) minmax(170px,.7fr) 140px auto auto 1fr;
    align-items:center;
}
.lm-field{
    min-width:0;
}
.lm-label{
    display:block;
    margin:0 0 6px 2px;
    color:#7a899c;
    font-size:10px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.06em;
}
.lm-search{
    position:relative;
}
.lm-search i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    z-index:2;
    color:#6f85a2;
}
.lm-search input,
.lm-filters .form-select,
.lm-filters .form-control{
    width:100%;
    height:46px!important;
    min-height:46px!important;
    border:1px solid #d6e2ef!important;
    border-radius:12px!important;
    background:#fff!important;
    color:#173555!important;
    font-size:14px!important;
    box-shadow:none!important;
}
.lm-search input{
    padding:0 14px 0 42px;
    outline:none;
}
.lm-search input:focus,
.lm-filters .form-select:focus,
.lm-filters .form-control:focus{
    border-color:#79a3e5!important;
    box-shadow:0 0 0 4px rgba(31,91,216,.07)!important;
}
.lm-page-size{
    width:140px!important;
}
.lm-apply{
    min-width:110px;
    height:46px;
    border-radius:11px!important;
}
.lm-clear{
    min-width:90px;
    height:46px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid #dbe5f0!important;
    background:#fff!important;
    color:#5c7089!important;
    border-radius:11px!important;
}

/* Remove injected Copy/Clear clutter from this page only */
.lm-page form .crm-ua-form-tools{
    display:none!important;
}

/* TABLE */
.lm-table-card{
    overflow:hidden!important;
    border:1px solid var(--lm-line)!important;
    border-radius:18px!important;
}
.lm-table-wrap{
    width:100%;
    overflow-x:auto;
}
.lm-table{
    width:max-content!important;
    min-width:1500px!important;
    border-collapse:separate!important;
    border-spacing:0!important;
}
.lm-table th{
    padding:13px 12px!important;
    background:#f7faff!important;
    color:#5b708c!important;
    font-size:10px!important;
    font-weight:800!important;
    text-transform:uppercase;
    letter-spacing:.04em;
    white-space:nowrap;
}
.lm-table td{
    padding:14px 12px!important;
    border-bottom:1px solid #edf1f6!important;
    color:#38536f;
    font-size:12px!important;
    vertical-align:middle;
    white-space:nowrap;
}
.lm-table td:nth-child(3),
.lm-table td:nth-child(5){
    white-space:normal;
}
.lm-table td strong{
    color:#153555;
    font-size:13px;
}
.lm-table td small{
    display:block;
    margin-top:3px;
    color:#8796a8;
    font-size:10px;
}
.lm-lead-id{
    color:#0d438f!important;
    font-size:13px;
    font-weight:850!important;
    text-decoration:none!important;
}
.lm-badge{
    display:inline-flex;
    align-items:center;
    padding:6px 9px;
    border-radius:999px;
    font-size:10px;
    font-weight:800;
}
.lm-badge.blue{background:#edf4ff;color:#2459df}
.lm-badge.purple{background:#f2ebff;color:#7440c6}
.lm-badge.cyan{background:#eaf9fb;color:#10889a}
.lm-badge.amber{background:#fff5df;color:#b87107}
.lm-badge.green{background:#eaf8f0;color:#16804b}
.lm-badge.slate{background:#eef2f6;color:#5a6d84}
.lm-badge.red{background:#fff0f3;color:#d3294d}
.lm-open{
    width:34px;
    height:34px;
    display:grid;
    place-items:center;
    border:1px solid #d7e2ee;
    border-radius:10px;
    background:#fff;
    color:#1f5bd8;
    text-decoration:none;
}
.lm-action-cell{
    display:flex;
    gap:6px;
    align-items:center;
}

/* PAGINATION */
.lm-pagination{
    min-height:62px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    padding:13px 16px;
    border-top:1px solid #e4ebf3;
    color:#74849a;
    font-size:12px;
}
.lm-pagination b{color:#17395f}
.lm-pagination>div{
    display:flex;
    align-items:center;
    gap:8px;
}
.lm-pagination a,
.lm-pagination>div>span{
    min-height:32px;
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:0 10px;
    border:1px solid #dbe5f0;
    border-radius:9px;
    background:#fff;
    color:#395778;
    text-decoration:none;
}
.lm-pagination .disabled{opacity:.5}

/* RESPONSIVE */
@media(max-width:1250px){
    .lm-kpis{grid-template-columns:repeat(3,1fr)}
    .lm-row-one{grid-template-columns:1fr 1fr}
    .lm-search-field{grid-column:1/-1}
    .lm-row-two{grid-template-columns:1fr 1fr}
}
@media(max-width:760px){
    .lm-page-head{flex-direction:column;align-items:flex-start}
    .lm-kpis{grid-template-columns:1fr 1fr}
    .lm-row-one,.lm-row-two{grid-template-columns:1fr}
    .lm-page-size{width:100%!important}
    .lm-pagination{flex-direction:column;align-items:flex-start}
}
</style>

@php
    $stageTone = function($stage){
        return match($stage){
            'New' => 'blue',
            'Follow-up' => 'purple',
            'Qualified' => 'cyan',
            'Quoted','Negotiation' => 'amber',
            'Converted' => 'green',
            'Closed' => 'slate',
            'Lost' => 'red',
            default => 'blue',
        };
    };
    $priorityTone = fn($p) => match($p){'Urgent'=>'red','High'=>'amber','Low'=>'slate',default=>'blue'};
@endphp

<div class="lm-page">

<div class="lm-page-head">
    <div>
        <span class="lm-eyebrow">Sales & enquiries</span>
        <h1>Lead Management</h1>
        <p>Track enquiries, follow-ups, qualification and conversion from one premium workspace.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <a class="btn btn-primary" href="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.lead_create' : 'legacy.crm.lead_create') }}">
            <i class="fa-solid fa-plus me-2"></i>Add Lead
        </a>
        <a class="btn btn-light" href="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.lead_import' : 'legacy.crm.lead_import') }}">
            <i class="fa-solid fa-file-import me-2"></i>Import Leads
        </a>
        <a class="btn btn-light" href="{{ route('crm.export','leads') }}">
            <i class="fa-solid fa-download me-2"></i>Export CSV
        </a>
    </div>
</div>

<section class="lm-kpis">
    <div class="lm-kpi blue"><span><i class="fa-solid fa-users"></i></span><div><small>Total Leads</small><strong>{{ $stats['total'] }}</strong></div></div>
    <div class="lm-kpi cyan"><span><i class="fa-solid fa-sparkles"></i></span><div><small>New</small><strong>{{ $stats['new'] }}</strong></div></div>
    <div class="lm-kpi purple"><span><i class="fa-solid fa-phone-volume"></i></span><div><small>Follow-up</small><strong>{{ $stats['follow_up'] }}</strong></div></div>
    <div class="lm-kpi amber"><span><i class="fa-solid fa-circle-check"></i></span><div><small>Qualified</small><strong>{{ $stats['qualified'] }}</strong></div></div>
    <div class="lm-kpi green"><span><i class="fa-solid fa-trophy"></i></span><div><small>Converted</small><strong>{{ $stats['converted'] }}</strong></div></div>
    <div class="lm-kpi red"><span><i class="fa-solid fa-triangle-exclamation"></i></span><div><small>Urgent</small><strong>{{ $stats['urgent'] }}</strong></div></div>
</section>

<section class="crm-card lm-toolbar">
    <form method="GET" class="lm-filters">

        <div class="lm-filter-row lm-row-one">
            <div class="lm-field lm-search-field">
                <span class="lm-label">Search leads</span>
                <div class="lm-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input name="q" value="{{ request('q') }}" placeholder="Lead ID, name, email, phone, destination or subject">
                </div>
            </div>

            <div class="lm-field">
                <span class="lm-label">Stage</span>
                <select class="form-select" name="stage">
                    <option value="">All stages</option>
                    @foreach($leadStages as $s)
                        <option value="{{ $s }}" @selected(request('stage')===$s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lm-field">
                <span class="lm-label">Priority</span>
                <select class="form-select" name="priority">
                    <option value="">All priorities</option>
                    @foreach($leadPriorities as $p)
                        <option value="{{ $p }}" @selected(request('priority')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lm-field">
                <span class="lm-label">Type</span>
                <select class="form-select" name="type">
                    <option value="">All types</option>
                    @foreach($leadTypes as $t)
                        <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="lm-filter-row lm-row-two">
            <div class="lm-field">
                <span class="lm-label">Agent</span>
                <select class="form-select" name="agent">
                    <option value="">All agents</option>
                    @foreach($admins as $a)
                        <option value="{{ $a->id }}" @selected((string)request('agent')===(string)$a->id)>{{ $a->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lm-field">
                <span class="lm-label">From date</span>
                <input class="form-control" type="date" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="lm-field">
                <span class="lm-label">To date</span>
                <input class="form-control" type="date" name="date_to" value="{{ request('date_to') }}">
            </div>

            <select class="form-select lm-page-size" name="per_page">
                @foreach([15,25,50,100] as $size)
                    <option value="{{ $size }}" @selected((int)request('per_page',15)===$size)>{{ $size }} / page</option>
                @endforeach
            </select>

            <button class="btn btn-primary lm-apply">
                <i class="fa-solid fa-filter me-1"></i>Apply
            </button>

            @if(request()->hasAny(['q','stage','priority','type','agent','date_from','date_to','per_page']))
                <a class="btn lm-clear" href="{{ route('crm.leads') }}">Clear filters</a>
            @endif

            <div></div>
        </div>
    </form>
</section>

<section class="crm-card lm-table-card">
    <div class="lm-table-wrap">
        <table class="crm-table lm-table">
            <thead>
                <tr>
                    <th>S.No.</th>
                    <th>Lead ID</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Destination / Subject</th>
                    <th>Type</th>
                    <th>Stage</th>
                    <th>Priority</th>
                    <th>Assigned Agent</th>
                    <th>Next Follow-up</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($leads as $e)
                @php
                    $stage = $e->crm_stage ?? 'New';
                    $priority = $e->crm_priority ?? 'Normal';
                @endphp
                <tr>
                    <td>{{ $leads->firstItem()+$loop->index }}</td>

                    <td>
                        <a class="lm-lead-id" href="{{ route('crm.lead_detail',$e->id) }}">
                            #FH-L{{ $e->id }}
                        </a>
                    </td>

                    <td>
                        <strong>{{ $e->name ?: 'New enquiry' }}</strong>
                        <small>{{ $e->emailid ?: '—' }}</small>
                    </td>

                    <td>{{ $e->contact ?: '—' }}</td>

                    <td>
                        <strong>{{ $e->going_to ?: ($e->subject ?: 'General enquiry') }}</strong>
                        <small>{{ \Illuminate\Support\Str::limit($e->subject ?: $e->message,45) }}</small>
                    </td>

                    <td><span class="lm-badge blue">{{ $e->type ?: 'Default' }}</span></td>
                    <td><span class="lm-badge {{ $stageTone($stage) }}">{{ $stage }}</span></td>
                    <td><span class="lm-badge {{ $priorityTone($priority) }}">{{ $priority }}</span></td>
                    <td>{{ $e->crm_agent_name ?? 'Unassigned' }}</td>

                    <td>
                        @if(!empty($e->crm_next_follow_up_at))
                            <strong>{{ \Carbon\Carbon::parse($e->crm_next_follow_up_at)->format('d M Y') }}</strong>
                            <small>{{ \Carbon\Carbon::parse($e->crm_next_follow_up_at)->format('h:i A') }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        {{ \Carbon\Carbon::parse($e->created_at)->format('d M Y') }}
                        <small>{{ \Carbon\Carbon::parse($e->created_at)->format('h:i A') }}</small>
                    </td>

                    <td>
                        <div class="lm-action-cell">
                            <a class="lm-open" href="{{ route('crm.lead_detail',$e->id) }}" title="Open lead">
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                            <form method="POST"
                                  action="{{ route('crm.universal_archive') }}"
                                  onsubmit="return confirm('Move this lead to CRM Trash?')">
                                @csrf
                                <input type="hidden" name="module" value="lead">
                                <input type="hidden" name="record_key" value="{{ $e->id }}">
                                <input type="hidden" name="label" value="Lead #FH-L{{ $e->id }} · {{ $e->name }}">
                                <button class="crm-ua-btn danger" title="Delete / Archive">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="empty">No matching leads.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="lm-pagination">
        <span>
            Showing <b>{{ $leads->firstItem() ?? 0 }}</b>
            to <b>{{ $leads->lastItem() ?? 0 }}</b>
            of <b>{{ $leads->total() }}</b> leads
        </span>

        <div>
            @if($leads->onFirstPage())
                <span class="disabled"><i class="fa-solid fa-chevron-left"></i> Previous</span>
            @else
                <a href="{{ $leads->previousPageUrl() }}"><i class="fa-solid fa-chevron-left"></i> Previous</a>
            @endif

            <span>Page {{ $leads->currentPage() }} of {{ $leads->lastPage() }}</span>

            @if($leads->hasMorePages())
                <a href="{{ $leads->nextPageUrl() }}">Next <i class="fa-solid fa-chevron-right"></i></a>
            @else
                <span class="disabled">Next <i class="fa-solid fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
</section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.lm-page form .crm-ua-form-tools').forEach(function(el){
        el.remove();
    });
});
</script>

@endsection