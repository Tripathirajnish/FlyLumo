@extends('crm.layout')

@section('content')
<style>
.ga-page{--ga-blue:#1858c7;--ga-navy:#10284b;--ga-muted:#74839a;--ga-line:#e3eaf3}
.ga-head{margin-bottom:18px}
.ga-eyebrow{display:flex;align-items:center;gap:8px;color:var(--ga-blue);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.ga-eyebrow:before{content:"";width:28px;height:3px;border-radius:99px;background:#e43c46}
.ga-head h1{margin:7px 0 5px;font-size:30px;line-height:1.08;color:var(--ga-navy)}
.ga-head p{margin:0;color:var(--ga-muted);font-size:13px}
.ga-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.ga-card{background:#fff;border:1px solid var(--ga-line);border-radius:16px;padding:20px;box-shadow:0 5px 18px rgba(18,40,75,.035)}
.ga-label{font-size:10px;font-weight:800;text-transform:uppercase;color:#53657d}
.ga-value{margin-top:8px;font-size:20px;font-weight:800;color:var(--ga-navy)}
.ga-note{margin-top:10px;font-size:12px;line-height:1.6;color:var(--ga-muted)}
.ga-status{display:inline-flex;align-items:center;gap:7px;margin-top:8px;padding:6px 10px;border-radius:999px;background:#eef8f1;color:#25713a;font-size:11px;font-weight:800}
.ga-status.pending{background:#fff6e8;color:#9a6400}

.ga-section{margin-top:22px}
.ga-section-title{margin:0 0 12px;font-size:17px;font-weight:800;color:var(--ga-navy)}
.ga-report-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.ga-report-card{background:#fff;border:1px solid var(--ga-line);border-radius:16px;padding:20px;box-shadow:0 5px 18px rgba(18,40,75,.035)}
.ga-list{margin-top:12px}
.ga-row{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:10px 0;border-bottom:1px solid #edf1f6}
.ga-row:last-child{border-bottom:0}
.ga-row-name{min-width:0;color:#334a67;font-size:12px;line-height:1.4;overflow-wrap:anywhere}
.ga-row-value{flex:0 0 auto;font-size:12px;font-weight:800;color:var(--ga-navy)}
.ga-empty{padding:15px 0;color:var(--ga-muted);font-size:12px}
.ga-trend{overflow-x:auto;margin-top:12px}
.ga-table{width:100%;border-collapse:collapse;min-width:500px}
.ga-table th,.ga-table td{padding:11px 10px;border-bottom:1px solid #edf1f6;text-align:left;font-size:12px}
.ga-table th{font-size:10px;text-transform:uppercase;letter-spacing:.04em;color:#63748b}
.ga-table td{color:#334a67}
.ga-table td strong{color:var(--ga-navy)}
@media(max-width:900px){.ga-report-grid{grid-template-columns:1fr}}

@media(max-width:700px){.ga-grid{grid-template-columns:1fr}}
</style>

<div class="ga-page">
    <div class="ga-head">
        <div class="ga-eyebrow">Website Intelligence</div>
        <h1>Google Analytics</h1>
        <p>Google Analytics 4 tracking and reporting integration.</p>
    </div>

    <div class="ga-grid">
        <div class="ga-card">
            <div class="ga-label">Website Tracking</div>
            <div class="ga-value">{{ $measurementId }}</div>
            <div class="ga-status">● Google tag installed</div>
            <div class="ga-note">
                The GA4 measurement tag is installed on the public website.
            </div>
        </div>

        <div class="ga-card">
            <div class="ga-label">CRM Reporting</div>
            <div class="ga-value">Google Analytics Data API</div>

            @if($dataApiConnected)
                <div class="ga-status">● Connected</div>
            @else
                <div class="ga-status pending">● Setup required</div>
            @endif

            <div class="ga-note">
                Connect the GA4 Data API to display users, sessions, traffic sources,
                landing pages and other Google Analytics reports directly inside CRM.
            </div>
        </div>
    </div>

    @if($dataApiConnected)
        <div class="ga-grid" style="margin-top:16px;">
            <div class="ga-card">
                <div class="ga-label">Active Users</div>
                <div class="ga-value">{{ number_format($activeUsers ?? 0) }}</div>
                <div class="ga-note">Active users during the last 7 days.</div>
            </div>

            <div class="ga-card">
                <div class="ga-label">New Users</div>
                <div class="ga-value">{{ number_format($newUsers ?? 0) }}</div>
                <div class="ga-note">New users during the last 7 days.</div>
            </div>

            <div class="ga-card">
                <div class="ga-label">Sessions</div>
                <div class="ga-value">{{ number_format($sessions ?? 0) }}</div>
                <div class="ga-note">Sessions recorded during the last 7 days.</div>
            </div>

            <div class="ga-card">
                <div class="ga-label">Views</div>
                <div class="ga-value">{{ number_format($views ?? 0) }}</div>
                <div class="ga-note">Page and screen views during the last 7 days.</div>
            </div>
        </div>

        <div class="ga-section">
            <h2 class="ga-section-title">Traffic Breakdown — Last 7 Days</h2>

            <div class="ga-report-grid">
                <div class="ga-report-card">
                    <div class="ga-label">Traffic Sources</div>
                    <div class="ga-note">Sessions grouped by GA4 session source / medium.</div>

                    <div class="ga-list">
                        @forelse($trafficSources as $item)
                            <div class="ga-row">
                                <div class="ga-row-name">{{ $item['label'] ?: '(blank)' }}</div>
                                <div class="ga-row-value">{{ number_format($item['value']) }} sessions</div>
                            </div>
                        @empty
                            <div class="ga-empty">No traffic source data available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="ga-report-card">
                    <div class="ga-label">Top Countries</div>
                    <div class="ga-note">Active users grouped by country.</div>

                    <div class="ga-list">
                        @forelse($countries as $item)
                            <div class="ga-row">
                                <div class="ga-row-name">{{ $item['label'] ?: '(blank)' }}</div>
                                <div class="ga-row-value">{{ number_format($item['value']) }} users</div>
                            </div>
                        @empty
                            <div class="ga-empty">No country data available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="ga-report-card">
                    <div class="ga-label">Devices</div>
                    <div class="ga-note">Active users grouped by device category.</div>

                    <div class="ga-list">
                        @forelse($devices as $item)
                            <div class="ga-row">
                                <div class="ga-row-name">{{ ucfirst($item['label'] ?: '(blank)') }}</div>
                                <div class="ga-row-value">{{ number_format($item['value']) }} users</div>
                            </div>
                        @empty
                            <div class="ga-empty">No device data available.</div>
                        @endforelse
                    </div>
                </div>

                <div class="ga-report-card">
                    <div class="ga-label">Landing Pages</div>
                    <div class="ga-note">Top entry pages ranked by sessions.</div>

                    <div class="ga-list">
                        @forelse($landingPages as $item)
                            <div class="ga-row">
                                <div class="ga-row-name">{{ $item['label'] ?: '(blank)' }}</div>
                                <div class="ga-row-value">{{ number_format($item['value']) }} sessions</div>
                            </div>
                        @empty
                            <div class="ga-empty">No landing page data available.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="ga-section">
            <div class="ga-report-card">
                <div class="ga-label">Daily Traffic Trend</div>
                <div class="ga-note">Daily active users and sessions reported by GA4 during the last 7 days.</div>

                <div class="ga-trend">
                    @if(count($dailyTrend))
                        <table class="ga-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Active Users</th>
                                    <th>Sessions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyTrend as $item)
                                    <tr>
                                        <td>
                                            {{ \Carbon\Carbon::createFromFormat('Ymd', $item['date'])->format('M d, Y') }}
                                        </td>
                                        <td><strong>{{ number_format($item['activeUsers']) }}</strong></td>
                                        <td><strong>{{ number_format($item['sessions']) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="ga-empty">No daily trend data available.</div>
                    @endif
                </div>
            </div>
        </div>
    @elseif($dataApiError)
        <div class="ga-card" style="margin-top:16px;">
            <div class="ga-label">Reporting Status</div>
            <div class="ga-status pending">● Temporarily unavailable</div>
            <div class="ga-note">{{ $dataApiError }}</div>
        </div>
    @endif
</div>
@endsection
