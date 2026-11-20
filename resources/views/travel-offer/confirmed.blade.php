<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Travel Plan Request Received | FaresHunt</title>
<style>
*{box-sizing:border-box}
body{margin:0;background:#f4f7fb;color:#102a43;font-family:Arial,sans-serif}
.wrap{max-width:760px;margin:45px auto;padding:0 18px}
.card{background:#fff;border-radius:20px;box-shadow:0 12px 40px rgba(15,42,67,.11);overflow:hidden}
.head{background:#062b4f;color:#fff;text-align:center;padding:34px 28px}
.logo{max-width:190px;max-height:64px;margin-bottom:22px}
.check{width:62px;height:62px;margin:0 auto 18px;border-radius:50%;background:#fff;color:#16794b;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800}
.head h1{margin:0 0 8px;font-size:28px}
.head p{margin:0;color:#d9e6f2}
.body{padding:32px}
.reference{text-align:center;background:#f6f8fb;border-radius:14px;padding:20px;margin-bottom:25px}
.reference small{display:block;color:#60758a;margin-bottom:7px}
.reference strong{font-size:22px;color:#062b4f}
.offer{border:1px solid #dfe7ef;border-radius:14px;padding:18px;margin-bottom:20px}
.offer h2{margin:0 0 7px;font-size:19px}
.row{display:flex;justify-content:space-between;gap:20px;padding:11px 0;border-bottom:1px solid #edf1f5}
.row:last-child{border-bottom:0}
.status{font-weight:800;color:#b7791f}
.notice{background:#fff8e6;border:1px solid #f3dfaa;border-radius:12px;padding:17px;line-height:1.55;margin-top:22px}
.footer{text-align:center;color:#60758a;font-size:13px;padding-top:25px;line-height:1.6}
@media(max-width:600px){.wrap{margin:15px auto}.body{padding:23px 18px}.head{padding:28px 18px}.row{display:block}.row b{display:block;margin-top:4px}}

.logo{
    display:block;
    width:190px;
    max-width:100%;
    max-height:none;
    height:auto;
    object-fit:contain;
    background:#fff;
    padding:10px 14px;
    border-radius:10px;
    box-shadow:0 5px 18px rgba(0,0,0,.12);
}

</style>

<style id="fareshunt-white-logo-final">
.head .logo{
    display:block;
    width:190px;
    max-width:100%;
    height:auto;
    max-height:none;
    object-fit:contain;
    background:transparent !important;
    padding:0 !important;
    border-radius:0 !important;
    box-shadow:none !important;
    filter:brightness(0) invert(1) !important;
    opacity:1 !important;
}
</style>

</head>
<body>
<div class="wrap">
<div class="card">

<div class="head">
<img class="logo" src="https://fareshunt.com/assets/img/fareshunt-logo-transparent.png" alt="FaresHunt">
<div class="check">✓</div>
<h1>Travel Plan Request Received</h1>
<p>Your request has been successfully confirmed.</p>
</div>

<div class="body">

<div class="reference">
<small>YOUR TRAVEL PLAN REFERENCE</small>
<strong>{{ $plan->reference }}</strong>
</div>

<div class="offer">
<h2>{{ $offer->title }}</h2>

<div class="row">
<span>Customer</span>
<b>{{ $plan->customer_name }}</b>
</div>

<div class="row">
<span>Plan Amount</span>
<b>{{ $plan->currency === 'USD' ? '$' : ($plan->currency.' ') }}{{ number_format((float)$plan->sale_amount,0) }}</b>
</div>

<div class="row">
<span>Travel Plan Status</span>
<b class="status">{{ $plan->status }}</b>
</div>

<div class="row">
<span>Payment Status</span>
<b class="status">{{ $plan->payment_status }}</b>
</div>

</div>

<div class="notice">
<strong>What happens next?</strong><br>
No payment has been collected during this process. FaresHunt will send your payment instructions separately. Please keep your Travel Plan reference for future communication.
</div>

<div class="footer">
FaresHunt — Hunt the Best Fares<br>
+1-888-717-0346 &nbsp; • &nbsp; info@fareshunt.com &nbsp; • &nbsp; fareshunt.com
</div>

</div>
</div>
</div>
</body>
</html>
