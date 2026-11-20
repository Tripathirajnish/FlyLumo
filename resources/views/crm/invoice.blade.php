<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $invoice->invoice_number }} · FaresHunt</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#eef3f8;color:#172b46;font-family:Arial,Helvetica,sans-serif}.toolbar{max-width:940px;margin:15px auto 0;display:flex;gap:8px;justify-content:flex-end;padding:0 10px}.toolbar a,.toolbar button{border:1px solid #d7e1ec;border-radius:9px;background:#fff;color:#173a61;padding:9px 12px;font-weight:700;cursor:pointer;text-decoration:none}.toolbar .primary{background:#d7193f;color:#fff;border-color:#d7193f}.sheet{max-width:940px;margin:12px auto 30px;background:#fff;border:1px solid #dfe7ef;border-radius:16px;overflow:hidden;box-shadow:0 18px 50px rgba(12,39,70,.08)}.head{padding:28px 32px;background:#071f4f;color:#fff;display:flex;justify-content:space-between;gap:20px}.brand{font-size:29px;font-weight:900;letter-spacing:-.04em}.brand span{color:#ef234c}.legal{margin-top:5px;color:#b8c7da;font-size:10px}.inv-title{text-align:right}.inv-title h1{margin:0;font-size:25px}.inv-title div{margin-top:6px;color:#c4d1e0;font-size:11px}.body{padding:28px 32px}.two{display:grid;grid-template-columns:1fr 1fr;gap:18px}.box{padding:15px;border:1px solid #e1e8f0;border-radius:12px}.eyebrow{font-size:9px;text-transform:uppercase;letter-spacing:.08em;color:#8491a3;font-weight:800}.box strong{display:block;margin-top:6px;font-size:13px}.box span{display:block;margin-top:4px;color:#667b95;font-size:11px}.meta{display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin:18px 0}.meta div{padding:11px;border-radius:10px;background:#f7f9fc}.meta small{display:block;color:#8795a7;font-size:8px;text-transform:uppercase}.meta b{display:block;margin-top:4px;font-size:11px}.section-title{margin:22px 0 9px;font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#294767}.segments{border:1px solid #e1e8f0;border-radius:12px;overflow:hidden}.seg{display:grid;grid-template-columns:110px 1fr 1fr 1fr;gap:12px;padding:12px 14px;border-bottom:1px solid #edf1f5;align-items:center}.seg:last-child{border-bottom:0}.seg small{display:block;color:#8795a7;font-size:8px}.seg b{display:block;margin-top:3px;font-size:11px}.fare{margin-left:auto;width:min(430px,100%);border:1px solid #e1e8f0;border-radius:12px;overflow:hidden}.fare-row{display:flex;justify-content:space-between;padding:10px 13px;border-bottom:1px solid #edf1f5;font-size:11px}.fare-row.total{background:#071f4f;color:#fff;font-size:15px;font-weight:800}.fare-row.balance{background:#fff3f5;color:#b20e31;font-weight:800}.notes{margin-top:18px;padding:13px 15px;background:#f7f9fc;border-radius:11px;color:#647993;font-size:10px;line-height:1.5}.foot{padding:18px 32px;background:#f8fafc;border-top:1px solid #e5ebf1;color:#6e8197;font-size:9px;text-align:center}@media(max-width:700px){.head,.two{grid-template-columns:1fr;display:grid}.inv-title{text-align:left}.meta{grid-template-columns:1fr 1fr}.seg{grid-template-columns:1fr 1fr}.body{padding:20px 16px}.head{padding:22px 16px}}@media print{body{background:#fff}.toolbar{display:none}.sheet{margin:0;max-width:none;border:0;border-radius:0;box-shadow:none}@page{size:A4;margin:10mm}}
</style>
</head>
<body>
<div class="toolbar">
    <a href="{{ route('crm.booking_detail',$booking->id) }}">Back to booking</a>
    <button class="primary" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="sheet">
    <div class="head">
        <div>
            <div class="brand">FARES<span>HUNT</span></div>
            <div class="legal">Operated by AERONZA TRAVEL GROUP LLC</div>
        </div>
        <div class="inv-title">
            <h1>INVOICE</h1>
            <div>{{ $invoice->invoice_number }}</div>
            <div>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</div>
        </div>
    </div>

    <div class="body">
        <div class="two">
            <div class="box">
                <div class="eyebrow">Bill to</div>
                <strong>{{ $invoice->customer_name }}</strong>
                <span>{{ $invoice->customer_email ?: '—' }}</span>
                <span>{{ $invoice->customer_phone ?: '—' }}</span>
            </div>
            <div class="box">
                <div class="eyebrow">FaresHunt support</div>
                <strong>{{ $setting['website_name'] ?? 'FaresHunt' }}</strong>
                <span>{{ $setting['content_emailid'] ?? $setting['header_email'] ?? 'support@fareshunt.com' }}</span>
                <span>{{ $setting['header_contact'] ?? '—' }}</span>
            </div>
        </div>

        <div class="meta">
            <div><small>Booking</small><b>#FH{{ $booking->id }}</b></div>
            <div><small>PNR</small><b>{{ data_get($snapshot,'pnr') ?: '—' }}</b></div>
            <div><small>Ticket</small><b>{{ data_get($snapshot,'ticket_number') ?: '—' }}</b></div>
            <div><small>Status</small><b>{{ $booking->status ?: '—' }}</b></div>
        </div>

        @php
            $all = data_get($snapshot,'itinerary',[]);
            if (!$all) {
                $all = ['Rph_1'=>data_get($snapshot,'sector_go',[])];
                $back = data_get($snapshot,'sector_back',[]);
                if ($back) $all['Rph_2']=$back;
            }

            $segments=[];
            $walk=function($node,$leg='') use (&$walk,&$segments){
                if(!is_array($node)) return;

                if(isset($node['Departure_Iata']) || isset($node['Arrival_Iata'])){
                    $node['_leg']=$leg;
                    $segments[]=$node;
                    return;
                }

                foreach($node as $k=>$v){
                    $walk($v,str_starts_with((string)$k,'Rph_')?(string)$k:$leg);
                }
            };

            $walk($all);
        @endphp

        @if(count($segments))
        <div class="section-title">Flight itinerary</div>
        <div class="segments">
            @foreach($segments as $s)
            <div class="seg">
                <div><small>{{ $s['_leg'] ?: 'Flight' }}</small><b>{{ $s['AirlineName'] ?? $s['Airline'] ?? 'Airline' }} {{ $s['FlightNum'] ?? '' }}</b></div>
                <div><small>Route</small><b>{{ $s['Departure_Iata'] ?? '—' }} → {{ $s['Arrival_Iata'] ?? '—' }}</b></div>
                <div><small>Departure</small><b>{{ !empty($s['Departure_Date']) ? \Carbon\Carbon::parse($s['Departure_Date'])->format('M d, Y h:i A') : '—' }}</b></div>
                <div><small>Arrival</small><b>{{ !empty($s['Arrival_Date']) ? \Carbon\Carbon::parse($s['Arrival_Date'])->format('M d, Y h:i A') : '—' }}</b></div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="section-title">Fare breakdown</div>
        <div class="fare">
            <div class="fare-row"><span>Base fare</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->base_fare,2) }}</b></div>
            <div class="fare-row"><span>Taxes & fees</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->taxes,2) }}</b></div>
            <div class="fare-row"><span>Service fee / markup</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->service_fee,2) }}</b></div>
            @if((float)$invoice->addons>0)
                <div class="fare-row"><span>Add-ons</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->addons,2) }}</b></div>
            @endif
            @if((float)$invoice->discount>0)
                <div class="fare-row"><span>Discount</span><b>- {{ $invoice->currency }} {{ number_format((float)$invoice->discount,2) }}</b></div>
            @endif
            <div class="fare-row total"><span>Total</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->total_amount,2) }}</b></div>
            <div class="fare-row"><span>Amount paid</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->paid_amount,2) }}</b></div>
            <div class="fare-row balance"><span>Balance due</span><b>{{ $invoice->currency }} {{ number_format((float)$invoice->balance_due,2) }}</b></div>
        </div>

        @if($invoice->notes)
            <div class="notes"><b>Notes</b><br>{{ $invoice->notes }}</div>
        @endif
    </div>

    <div class="foot">
        Thank you for choosing FaresHunt. Flight schedules, baggage, change and refund conditions remain subject to the applicable airline and fare rules.
    </div>
</div>
</body>
</html>
