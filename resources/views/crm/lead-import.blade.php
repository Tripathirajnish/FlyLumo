@extends('crm.layout')
@section('content')

<style>
.li-page{max-width:1000px;margin:0 auto;font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Arial,sans-serif}
.li-head{margin-bottom:22px}
.li-back{color:#64748b;text-decoration:none;font-size:13px;font-weight:700}
.li-back:hover{color:#1f5bd8}
.li-head h1{margin:8px 0 4px;color:#071f4f;font-size:32px;font-weight:750;letter-spacing:-.03em}
.li-head p{margin:0;color:#73839a;font-size:14px}
.li-card{background:#fff;border:1px solid #dbe6f2;border-radius:18px;box-shadow:0 8px 24px rgba(12,44,88,.05);overflow:hidden;margin-bottom:18px}
.li-card-head{padding:17px 20px;border-bottom:1px solid #e7eef6}
.li-card-head small{display:block;color:#1f5bd8;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px}
.li-card-head h3{margin:0;color:#071f4f;font-size:17px;font-weight:750}
.li-body{padding:20px}
.li-field>span{display:block;margin-bottom:7px;color:#34445b;font-size:12px;font-weight:750}
.li-field .form-control{min-height:46px;border:1px solid #d8e2ee;border-radius:10px}
.li-help{margin-top:8px;color:#73839a;font-size:12px;line-height:1.6}
.li-info{padding:15px 17px;border-radius:12px;background:#f4f8ff;border:1px solid #d8e7ff;color:#34445b;font-size:13px;line-height:1.7;margin-bottom:18px}
.li-info strong{color:#071f4f}
.li-errors{padding:14px 18px;margin-bottom:18px;border:1px solid #fecdd3;border-radius:12px;background:#fff1f2;color:#9f1239;font-size:13px}
.li-success{padding:14px 18px;margin-bottom:18px;border:1px solid #bbf7d0;border-radius:12px;background:#f0fdf4;color:#166534;font-size:13px;font-weight:700}
.li-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px}
.li-actions .btn{min-height:44px;border-radius:10px;padding:0 18px;display:inline-flex;align-items:center;justify-content:center;font-weight:700}
.li-upload{background:#1f5bd8!important;border-color:#1f5bd8!important}
.li-columns{margin:8px 0 0;padding-left:20px;columns:2;column-gap:35px}
.li-columns li{margin-bottom:4px}
@media(max-width:700px){.li-columns{columns:1}.li-head h1{font-size:27px}}
</style>

<div class="li-page">
    <div class="li-head">
        <a class="li-back" href="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.leads' : 'legacy.crm.leads') }}">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to leads
        </a>
        <h1>Import Leads</h1>
        <p>Bulk import sales enquiries into Lead Management using the approved CSV format.</p>
    </div>

    @if(session('crm_success'))
        <div class="li-success">{{ session('crm_success') }}</div>
    @endif

    @if($errors->any())
        <div class="li-errors">
            <strong>Import could not be completed:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="li-info">
        <strong>Before uploading</strong><br>
        Save the Excel workbook as a <strong>CSV</strong> file. The first row must contain the exact approved column names.
        Duplicate leads are checked using email and phone before insertion. One-way trips may use <strong>One Way</strong> in the Return Date column.
    </div>

    <section class="li-card">
        <div class="li-card-head">
            <small>Bulk Import</small>
            <h3>Upload Lead CSV</h3>
        </div>

        <div class="li-body">
            <form method="POST"
                  enctype="multipart/form-data"
                  action="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.lead_import_store' : 'legacy.crm.lead_import_store') }}">
                @csrf

                <label class="li-field">
                    <span>CSV File</span>
                    <input class="form-control"
                           type="file"
                           name="lead_file"
                           accept=".csv,text/csv"
                           required>
                    <div class="li-help">Maximum file size: 5 MB. Only the approved lead CSV format is accepted.</div>
                </label>

                <div class="li-info" style="margin-top:20px;margin-bottom:0">
                    <strong>Required columns:</strong>
                    <ul class="li-columns">
                        <li>Pax / Contact Name</li>
                        <li>Email</li>
                        <li>Phone</li>
                        <li>Origin</li>
                        <li>Destination</li>
                        <li>Depart Date</li>
                        <li>Return Date</li>
                        <li>Airline</li>
                        <li>Passengers</li>
                        <li>Quoted Price (USD)</li>
                        <li>Source</li>
                        <li>Device</li>
                        <li>Lead Stage / Notes</li>
                    </ul>
                </div>

                <div class="li-actions">
                    <a class="btn btn-light"
                       href="{{ route(request()->getHost()==='crm.fareshunt.com' ? 'crm.leads' : 'legacy.crm.leads') }}">
                        Cancel
                    </a>
                    <button class="btn btn-primary li-upload" type="submit">
                        <i class="fa-solid fa-file-import me-2"></i>Import Leads
                    </button>
                </div>
            </form>
        </div>
    </section>
</div>

@endsection
