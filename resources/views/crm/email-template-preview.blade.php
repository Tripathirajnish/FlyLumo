<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Preview · {{ $template->label }}</title>
<style>
body{margin:0;background:#eef3f8;color:#14263d;font-family:Inter,Arial,sans-serif}
.bar{position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:15px;padding:12px 18px;background:#071b31;color:#fff}
.bar b{font-size:13px}.bar span{font-size:10px;color:#aebfd1}.wrap{max-width:900px;margin:28px auto;padding:0 15px}.subject{background:#fff;border:1px solid #dfe6ee;border-radius:12px;padding:14px 16px;margin-bottom:12px;box-shadow:0 10px 28px rgba(9,34,62,.05)}.subject small{display:block;text-transform:uppercase;letter-spacing:.08em;color:#8090a3;font-size:9px;margin-bottom:5px}.subject strong{font-size:14px}.email{background:#fff;border:1px solid #dfe6ee;border-radius:14px;padding:20px;box-shadow:0 15px 45px rgba(9,34,62,.06);overflow:auto}.tnc{margin-top:15px;padding-top:14px;border-top:1px solid #e8edf2;color:#66778a;font-size:11px}
</style>
</head>
<body>
<div class="bar">
    <div><b>Email Preview</b><span> · sample data is used for placeholders</span></div>
    <div>{{ $template->label }}</div>
</div>
<div class="wrap">
    <div class="subject"><small>Subject</small><strong>{{ $subject }}</strong></div>
    <div class="email">
        {!! $body ?: '<p style="color:#8290a2">This template has no body content.</p>' !!}
        @if($tnc)<div class="tnc">{!! $tnc !!}</div>@endif
    </div>
</div>
</body>
</html>
