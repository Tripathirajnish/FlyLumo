<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $template->label }} · {{ $subject }}</title>
</head>
<body style="margin:0;background:#eef2f6;font-family:Arial,Helvetica,sans-serif;color:#10233f;">
    <div style="max-width:820px;margin:20px auto;padding:0 10px;">
        <div style="background:#ffffff;border:1px solid #dfe6ee;border-radius:12px;padding:14px 16px;margin-bottom:12px;">
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:#8190a3;font-weight:700;">Email preview</div>
            <div style="font-size:13px;margin-top:7px;"><b>Template:</b> {{ $template->label }}</div>
            <div style="font-size:13px;margin-top:5px;"><b>To:</b> {{ $booking->o_Email }}</div>
            <div style="font-size:13px;margin-top:5px;"><b>Subject:</b> {{ $subject }}</div>
        </div>

        {!! $body !!}
    </div>
</body>
</html>
