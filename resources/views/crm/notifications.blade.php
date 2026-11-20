@extends('crm.layout')

@section('content')

<style>
.nv2-head{display:flex;justify-content:space-between;gap:15px;align-items:flex-start}.nv2-actions{display:flex;gap:8px;flex-wrap:wrap}.nv2-stats{display:grid;grid-template-columns:repeat(6,1fr);gap:11px;margin:18px 0}.nv2-stat{background:#fff;border:1px solid var(--crm-line);border-radius:14px;padding:15px}.nv2-stat span{display:block;color:#7c899d;font-size:9px;text-transform:uppercase;font-weight:700}.nv2-stat b{display:block;font-size:22px;margin-top:6px}.nv2-filter{background:#fff;border:1px solid var(--crm-line);border-radius:15px;padding:13px;display:flex;gap:9px;flex-wrap:wrap;margin-bottom:14px}.nv2-filter .search{flex:1;min-width:220px}.nv2-list{background:#fff;border:1px solid var(--crm-line);border-radius:16px;overflow:hidden}.nv2-row{display:grid;grid-template-columns:42px minmax(0,1fr) auto;gap:12px;padding:15px 17px;border-bottom:1px solid #edf1f5;align-items:center}.nv2-row:last-child{border-bottom:0}.nv2-row.unread{background:#f8fbff}.nv2-icon{width:38px;height:38px;border-radius:11px;display:grid;place-items:center;background:#eef5ff;color:#2459df}.nv2-icon.red{background:#fff0f0;color:#d6383f}.nv2-icon.amber{background:#fff5df;color:#bb6b08}.nv2-icon.purple{background:#f5edff;color:#7c3aed}.nv2-title{font-weight:700;font-size:12px;color:#142945;text-decoration:none}.nv2-title:hover{color:#2459df}.nv2-meta{color:#78879b;font-size:10px;margin-top:4px}.nv2-badges{display:flex;gap:6px;align-items:center;margin-top:7px}.nv2-dot{width:7px;height:7px;background:#2459df;border-radius:50%;display:inline-block}.nv2-side{display:flex;align-items:center;gap:8px}.nv2-time{font-size:10px;color:#8190a4;white-space:nowrap}.nv2-read{border:0;background:#f2f5f9;width:32px;height:32px;border-radius:9px;color:#536780}.nv2-note{background:#fff8e6;border:1px solid #f5dfaa;padding:10px 13px;border-radius:11px;color:#8a6210;margin-bottom:14px;font-size:11px}
.notification-pagination nav{display:flex;align-items:center;justify-content:space-between;gap:12px}.notification-pagination nav>div{display:flex;align-items:center;gap:6px}.notification-pagination svg{width:16px!important;height:16px!important;max-width:16px!important;max-height:16px!important}.notification-pagination a,.notification-pagination span{font-size:12px}.notification-pagination a[rel="prev"],.notification-pagination a[rel="next"]{display:inline-flex;align-items:center;justify-content:center}.notification-pagination p{margin:0;font-size:11px;color:#718096}
@media(max-width:1100px){.nv2-stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:700px){.nv2-stats{grid-template-columns:repeat(2,1fr)}.nv2-row{grid-template-columns:38px 1fr}.nv2-side{grid-column:2;justify-content:flex-start;flex-wrap:wrap}.nv2-head{flex-direction:column}.nv2-filter .search{min-width:100%;}.nv2-filter .form-select{width:100%!important}}
</style>

<div class="nv2-head">
    <div>
        <span class="eyebrow" style="color:#2459df">Operations intelligence</span>
        <h1 class="title">Notifications</h1>
        <div class="sub">Actionable alerts generated from current CRM booking, lead, payment and follow-up data.</div>
    </div>

    @if($hasState)
        <form method="post" action="{{ route('crm.notifications_read_all') }}">
            @csrf
            @foreach($alerts as $a)
                @if(!$a->is_read)
                    <input type="hidden" name="keys[]" value="{{ $a->key }}">
                @endif
            @endforeach
            <button class="btn btn-light" type="submit">
                <i class="fa-solid fa-check-double me-1"></i> Mark visible as read
            </button>
        </form>
    @endif
</div>

@if(session('crm_success'))
    <div class="crm-success mt-3">{{ session('crm_success') }}</div>
@endif

@if(!$hasState)
    <div class="nv2-note mt-3"><b>Read/unread tracking is not active yet.</b> Run the Notifications V2 migration, then refresh this page.</div>
@endif

<div class="nv2-stats">
    <div class="nv2-stat"><span>Total alerts</span><b>{{ $stats['total'] }}</b></div>
    <div class="nv2-stat"><span>Unread</span><b>{{ $stats['unread'] }}</b></div>
    <div class="nv2-stat"><span>Bookings</span><b>{{ $stats['bookings'] }}</b></div>
    <div class="nv2-stat"><span>Leads</span><b>{{ $stats['leads'] }}</b></div>
    <div class="nv2-stat"><span>Payment issues</span><b>{{ $stats['payments'] }}</b></div>
    <div class="nv2-stat"><span>Follow-ups due</span><b>{{ $stats['followups'] }}</b></div>
</div>

<form class="nv2-filter" method="get">
    <input class="search" name="q" value="{{ request('q') }}" placeholder="Search booking ID, customer or alert">
    <select class="form-select" name="category" style="width:170px">
        <option>All</option>
        @foreach(['Bookings','Leads','Payments','Follow-ups'] as $c)
            <option {{ request('category') === $c ? 'selected' : '' }}>{{ $c }}</option>
        @endforeach
    </select>
    <select class="form-select" name="view" style="width:140px">
        <option value="all">All alerts</option>
        <option value="unread" {{ request('view') === 'unread' ? 'selected' : '' }}>Unread only</option>
    </select>
    <button class="btn btn-primary" type="submit">Apply</button>
    <a class="btn btn-light" href="{{ route('crm.notifications') }}">Reset</a>
</form>

<div class="nv2-list">
    @forelse($alerts as $a)
        @php
            // Presentation-only branding cleanup. Keep notification keys, IDs and URLs untouched.
            $displayTitle = preg_replace('/#TNB(?=\d)/i', '#FH', (string) $a->title);
            $displayMeta  = preg_replace('/#TNB(?=\d)/i', '#FH', (string) $a->meta);
        @endphp
        <div class="nv2-row {{ !$a->is_read ? 'unread' : '' }}">
            <div class="nv2-icon {{ $a->tone }}"><i class="fa-solid {{ $a->icon }}"></i></div>
            <div>
                <a class="nv2-title" href="{{ $a->url }}">{{ $displayTitle }}</a>
                <div class="nv2-meta">{{ $displayMeta }}</div>
                <div class="nv2-badges">
                    <span class="soft {{ $a->priority === 'Critical' ? 'red' : ($a->priority === 'High' ? 'amber' : 'blue') }}">{{ $a->priority }}</span>
                    <span class="soft">{{ $a->category }}</span>
                    @if(!$a->is_read)<span class="nv2-dot" title="Unread"></span>@endif
                </div>
            </div>
            <div class="nv2-side">
                <span class="nv2-time">{{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
                @if($hasState && !$a->is_read)
                    <form method="post" action="{{ route('crm.notification_read') }}">
                        @csrf
                        <input type="hidden" name="key" value="{{ $a->key }}">
                        <button class="nv2-read" type="submit" title="Mark read"><i class="fa-solid fa-check"></i></button>
                    </form>
                @endif
                <a class="row-action" href="{{ $a->url }}" title="Open record"><i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    @empty
        <div class="empty">No notifications match these filters.</div>
    @endforelse
</div>

<div class="mt-3 notification-pagination">
    {{ $alerts->links() }}
</div>

@endsection
