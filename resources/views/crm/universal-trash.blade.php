@extends('crm.layout')

@section('content')
<div class="crm-page-head">
    <div>
        <span class="eyebrow" style="color:#1858c7;font-weight:700;letter-spacing:.14em;">RECORD MANAGEMENT</span>
        <h1 class="title">CRM Trash</h1>
        <p class="sub">Archived CRM records live here until you restore or permanently delete them.</p>
    </div>
</div>

@if(session('crm_success'))
    <div class="crm-success">{{ session('crm_success') }}</div>
@endif

@if($errors->any())
    <div class="crm-danger">{{ $errors->first() }}</div>
@endif

@if(!empty($canPermanentDelete))
<div class="trash-warning">
    <div class="trash-warning-icon"><i class="fa fa-triangle-exclamation"></i></div>
    <div>
        <strong>Permanent delete is irreversible</strong>
        <span>Restore keeps the source record. Permanently Delete removes the underlying CRM record and related operational data and cannot be undone.</span>
    </div>
</div>
@endif

<div class="crm-card trash-card">
    <div class="crm-card-head">
        <h3>Archived records</h3>
        <span class="trash-count">{{ method_exists($archives, 'total') ? $archives->total() : $archives->count() }} record{{ (method_exists($archives, 'total') ? $archives->total() : $archives->count()) === 1 ? '' : 's' }}</span>
    </div>

    <div class="table-responsive">
        <table class="table trash-table">
            <thead>
            <tr>
                <th>Module</th>
                <th>Record</th>
                <th>Archived By</th>
                <th>Archived At</th>
                <th class="text-end">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($archives as $archive)
                <tr>
                    <td>
                        <span class="trash-module">{{ ucfirst((string)$archive->module) }}</span>
                    </td>
                    <td>
                        <div class="trash-record-label">{{ $archive->label ?: ucfirst((string)$archive->module).' #'.$archive->record_key }}</div>
                        <div class="trash-record-key">{{ $archive->record_key }}</div>
                    </td>
                    <td>{{ $archive->admin_name ?: 'System' }}</td>
                    <td>{{ !empty($archive->archived_at) ? \Carbon\Carbon::parse($archive->archived_at)->format('d M Y, h:i A') : '—' }}</td>
                    <td>
                        <div class="trash-actions">
                            <form method="POST" action="{{ route('crm.universal_restore', $archive->id) }}">
                                @csrf
                                <button type="submit" class="trash-btn trash-restore">
                                    <i class="fa fa-rotate-left"></i> Restore
                                </button>
                            </form>

                            @if(!empty($canPermanentDelete) && Route::has('crm.universal_permanent_delete'))
                                <form method="POST"
                                      action="{{ route('crm.universal_permanent_delete', $archive->id) }}"
                                      class="trash-delete-form"
                                      data-record="{{ e($archive->label ?: ucfirst((string)$archive->module).' #'.$archive->record_key) }}">
                                    @csrf
                                    <input type="hidden" name="confirmation" value="DELETE">
                                    <button type="submit" class="trash-btn trash-delete">
                                        <i class="fa fa-trash"></i> Permanently Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">CRM Trash is empty.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($archives, 'links'))
        <div class="trash-pagination">{{ $archives->links() }}</div>
    @endif
</div>

<style>
.trash-warning{display:flex;align-items:flex-start;gap:12px;margin:0 0 16px;padding:13px 15px;border:1px solid #f2d3d8;border-radius:13px;background:#fff8f9;color:#7b2535}
.trash-warning-icon{width:34px;height:34px;flex:0 0 34px;display:grid;place-items:center;border-radius:9px;background:#ffecef;color:#d72f4a}
.trash-warning strong{display:block;font-size:13px;font-weight:600;margin:1px 0 3px}.trash-warning span{display:block;color:#826170;font-size:12px;line-height:1.45}
.trash-card{overflow:hidden}.trash-count{color:#7c8ba0;font-size:11px}.trash-table th{padding:13px 14px!important}.trash-table td{padding:14px!important;vertical-align:middle!important}
.trash-module{display:inline-flex;align-items:center;min-height:25px;padding:0 9px;border-radius:999px;background:#f0f3f7;color:#50627b;font-size:11px;font-weight:600}
.trash-record-label{font-weight:600;color:#11233f}.trash-record-key{margin-top:2px;color:#7c8ba0;font-size:11px}.trash-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap}.trash-actions form{margin:0}
.trash-btn{display:inline-flex;align-items:center;gap:7px;min-height:36px;padding:0 12px;border-radius:10px;border:1px solid #dfe6ef;background:#fff;font-size:12px;font-weight:500;white-space:nowrap;transition:.15s ease}
.trash-restore{color:#172b48}.trash-restore:hover{background:#f5f8fc;border-color:#cfd9e6}.trash-delete{border-color:#f0c5cd;background:#fff7f8;color:#c62c46}.trash-delete:hover{background:#ffedf1;border-color:#e9a9b5;color:#b91f3b}.trash-pagination{padding:14px 16px;border-top:1px solid #edf1f5}
@media(max-width:760px){.trash-actions{justify-content:flex-start}.trash-btn{width:100%;justify-content:center}.trash-actions form{width:100%}}
</style>

<script>
document.addEventListener('submit', function (event) {
    var form = event.target.closest('.trash-delete-form');
    if (!form) return;
    var label = form.getAttribute('data-record') || 'this record';
    var ok = window.confirm('Permanently delete ' + label + '?\n\nThis will remove the source CRM record and cannot be undone.');
    if (!ok) event.preventDefault();
});
</script>
@endsection
