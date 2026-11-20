<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $offer->title }}</title>
</head>
<body style="margin:0;padding:0;background:#f3f6f9;font-family:Arial,Helvetica,sans-serif;color:#243b53;">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6f9;padding:28px 12px;">
<tr>
<td align="center">

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:650px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(16,45,78,.10);">

<tr>
<td align="center" style="padding:24px 25px 20px;background:#ffffff;">
    <img
        src="https://fareshunt.com/assets/img/fareshunt-logo-transparent.png"
        alt="FaresHunt"
        style="display:block;max-width:190px;width:100%;height:auto;border:0;"
    >
</td>
</tr>

<tr>
<td align="center" style="padding:38px 28px;background:#08294d;">
    <div style="font-size:11px;letter-spacing:2px;text-transform:uppercase;font-weight:bold;color:#9fb8cf;margin-bottom:10px;">
        Exclusive Travel Offer
    </div>

    <h1 style="margin:0 0 16px;font-size:28px;line-height:1.25;color:#ffffff;">
        {{ $offer->title }}
    </h1>

    <div style="font-size:42px;line-height:1;font-weight:900;color:#ffffff;">
        {{ $price }}
    </div>

    <div style="margin-top:8px;font-size:12px;color:#b9ccdc;">
        {{ $offer->currency }} · per passenger
    </div>
</td>
</tr>

<tr>
<td style="padding:30px 34px 8px;">
    <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#334e68;">
        Hi {{ $customerName }},
    </p>

    <p style="margin:0;font-size:14px;line-height:1.7;color:#526d82;">
        We’ve prepared a special FaresHunt travel offer for you.
        Your personalized offer details, included trips, booking requirements,
        validity and applicable terms are available through the secure link below.
    </p>
</td>
</tr>

<tr>
<td style="padding:20px 34px;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f9fb;border:1px solid #e4eaf0;border-radius:12px;">
        <tr>
            <td style="padding:18px 20px;font-size:13px;color:#60788e;">
                <strong style="display:block;color:#183b5b;margin-bottom:5px;">Offer Reference</strong>
                {{ $offer->reference }}
            </td>

            <td align="right" style="padding:18px 20px;font-size:13px;color:#60788e;">
                <strong style="display:block;color:#183b5b;margin-bottom:5px;">Cabin</strong>
                {{ $offer->cabin_class ?: 'As specified' }}
            </td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td align="center" style="padding:10px 34px 30px;">
    <a
        href="{{ $customerUrl }}"
        style="display:inline-block;background:#d71943;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:15px 30px;border-radius:8px;"
    >
        View Your Offer
    </a>

    <div style="margin-top:14px;font-size:11px;line-height:1.5;color:#8798a8;">
        This secure link is intended for the recipient of this offer.
    </div>
</td>
</tr>

@if($offer->valid_until)
<tr>
<td align="center" style="padding:0 34px 26px;">
    <div style="padding:12px 15px;background:#fff5f6;border-radius:8px;color:#a92a43;font-size:12px;font-weight:bold;">
        Offer valid through {{ \Carbon\Carbon::parse($offer->valid_until)->format('F d, Y') }}
        @if($offer->availability_message)
            · {{ $offer->availability_message }}
        @endif
    </div>
</td>
</tr>
@endif

<tr>
<td align="center" style="padding:24px 30px;background:#071f3d;color:#b8c8d6;font-size:11px;line-height:1.7;">
    <strong style="color:#ffffff;">FaresHunt</strong><br>
    Hunt the Best Fares<br>
    +1-888-717-0346 &nbsp;·&nbsp; info@fareshunt.com<br>
    fareshunt.com
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
