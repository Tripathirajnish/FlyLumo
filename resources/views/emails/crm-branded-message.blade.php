<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6fa;font-family:Arial,Helvetica,sans-serif;color:#20344f;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fa;">
<tr>
<td align="center" style="padding:28px 12px;">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 18px rgba(15,45,80,.08);">

<tr>
<td align="center" style="background:#082e63;padding:24px 30px 20px;">
    <img src="{{ rtrim(config('app.url'), '/') . '/mail_images/fareshunt-logo-white.png' }}"
         alt="FaresHunt"
         width="190"
         style="display:block;width:190px;max-width:70%;height:auto;border:0;margin:0 auto;">
</td>
</tr>

<tr>
<td align="center" style="background:#0b3d78;padding:22px 30px;">
    <div style="font-size:24px;line-height:1.3;font-weight:700;color:#ffffff;">
        {{ $heading }}
    </div>
    @if(!empty($subheading))
        <div style="margin-top:8px;font-size:13px;line-height:1.6;color:#dce9f7;">
            {{ $subheading }}
        </div>
    @endif
</td>
</tr>

<tr>
<td style="padding:34px 36px 28px;">
    <div style="font-size:15px;line-height:1.8;color:#344f70;">
        {!! nl2br(e($message)) !!}
    </div>
</td>
</tr>

<tr>
<td style="padding:0 36px 30px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#f6f9fc;border:1px solid #e2eaf3;border-radius:12px;">
        <tr>
            <td align="center" style="padding:18px 16px;">
                <div style="font-size:14px;font-weight:700;color:#143d6a;margin-bottom:7px;">
                    Need help with your travel plans?
                </div>
                <div style="font-size:12px;line-height:1.8;color:#536b86;">
                    @if($supportEmail)
                        &#9993;&nbsp; {{ $supportEmail }}
                    @endif
                    @if($supportPhone)
                        &nbsp;&nbsp;&bull;&nbsp;&nbsp; &#9742;&nbsp; {{ $supportPhone }}
                    @endif
                    @if($supportWhatsApp)
                        <br>WhatsApp: {{ $supportWhatsApp }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td style="padding:16px 30px;border-top:1px solid #e8edf3;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td width="32%" valign="middle">
                <img src="{{ rtrim(config('app.url'), '/') . '/mail_images/fareshunt-footer-logo.png' }}"
                     alt="FaresHunt"
                     width="145"
                     style="display:block;width:145px;max-width:100%;height:auto;border:0;">
            </td>
            <td width="68%" align="right" valign="middle"
                style="font-size:10px;line-height:1.7;color:#6b7f96;">
                FaresHunt Travel Support<br>
                Professional assistance for your journey
            </td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td align="center" style="background:#082e63;padding:12px 20px;font-size:10px;line-height:1.6;color:#ffffff;">
    Thank you for choosing FaresHunt.
</td>
</tr>

</table>

</td>
</tr>
</table>
</body>
</html>
