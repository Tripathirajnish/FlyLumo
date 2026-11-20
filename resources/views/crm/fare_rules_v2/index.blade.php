<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Enterprise Fare Rules V2 | Fares Hunt</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#f5f7fb;color:#17243b;font-family:Arial,sans-serif}
.wrap{max-width:1240px;margin:0 auto;padding:42px 24px}
.eyebrow{color:#4764a0;font-size:12px;font-weight:700;letter-spacing:2px}
h1{font-size:32px;letter-spacing:-1px;margin:12px 0}
.subtitle{color:#64748b;line-height:1.6}
.panel{background:white;border:1px solid #e5eaf2;border-radius:18px;padding:24px;margin-top:28px;box-shadow:0 8px 30px rgba(20,40,80,.04)}
.header{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.tag{background:#eaf1ff;color:#2854a0;padding:9px 13px;border-radius:30px;font-size:12px;font-weight:700}
table{width:100%;border-collapse:collapse;margin-top:22px}
th{text-align:left;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.6px;padding:15px 12px;border-bottom:1px solid #e5eaf2}
td{padding:17px 12px;border-bottom:1px solid #edf1f6;font-size:14px}
.code{font-weight:700;color:#243c68}
.status{display:inline-block;background:#fff4db;color:#91610c;padding:6px 11px;border-radius:30px;font-size:12px;font-weight:700}
.empty{text-align:center;padding:45px;color:#64748b}
.pagination{margin-top:22px}
.note{margin-top:20px;color:#64748b;font-size:13px}
@media(max-width:700px){.wrap{padding:24px 12px}h1{font-size:25px}.panel{padding:14px;overflow-x:auto}table{min-width:650px}}
.v2-success{
    margin-top:24px;
    padding:20px 24px;
    border:1px solid #b9dfc8;
    border-radius:14px;
    background:#f0faf4;
    color:#17603b;
}
.v2-success strong{font-size:15px}
.v2-success p{margin:8px 0 0;font-size:14px}
</style>
</head>
<body>
<main class="wrap">
<div class="eyebrow">FARES HUNT / ENTERPRISE CONTROL CENTER</div>
<h1>Fare Rules V2</h1>
<p class="subtitle">Manage enterprise pricing rules, versions and controlled publishing workflows.</p>

@if(session('v2_success'))
<div class="v2-success" role="status">
    <strong>Draft Saved Successfully</strong>
    <p>{{ session('v2_success') }}</p>
</div>
@endif

<section class="panel">
<div class="header">
<div>
<h2>Rule Management</h2>
<p class="subtitle">Existing fare rules and their current lifecycle status.</p>
</div>
<span class="tag">PRODUCTION — DRAFT ONLY</span>
</div>

<table>
<thead>
<tr>
<th>Rule Code</th>
<th>Name</th>
<th>Status</th>
<th>Priority</th>
<th>Version</th>
</tr>
</thead>
<tbody>
@forelse($rules as $rule)
<tr>
<td class="code">{{ $rule->rule_code }}</td>
<td>{{ $rule->name }}</td>
<td><span class="status">{{ strtoupper($rule->status) }}</span></td>
<td>{{ $rule->priority }}</td>
<td>v{{ $rule->current_version }}</td>
</tr>
@empty
<tr><td colspan="5" class="empty">No Fare Rules V2 found.</td></tr>
@endforelse
</tbody>
</table>

<div class="pagination">
@if($rules->hasPages())
<a href="{{ $rules->previousPageUrl() ?? '#' }}">Previous</a>
<span>Page {{ $rules->currentPage() }} of {{ $rules->lastPage() }}</span>
<a href="{{ $rules->nextPageUrl() ?? '#' }}">Next</a>
@endif
</div>
<p class="note">Draft rules do not affect live fares. Publishing remains disabled until the controlled approval workflow is implemented.</p>
</section>

@if(app(\App\Services\CrmAccessService::class)->can(
    (int) session('crm_admin_id', 0),
    'fare_rules_v2.manage'
))
@include('crm.fare_rules_v2.create_form')
@endif

</main>
</body>
</html>
