<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\Enquiry;
use App\Models\Markup;
use App\Models\Airport;
use App\Models\Temp;
use Session;

class EnquiryController extends Controller
{
    public function submit_holiday(Request $request)
    {
        $info                       =   new Enquiry;

        $info->type                 =   'holiday';
        $info->going_to             =   $request->form2_going_to;
        $info->checkin_date         =   $request->form2_checkin_date;
        $info->occupant             =   $request->form2_occupant;
        $info->name                 =   $request->form2_name;
        $info->contact              =   $request->form2_contact;
        $info->emailid              =   $request->form2_emailid;

        $info->save();

        return redirect('thank-you');
    }

    public function submit_search(Request $request)
    {
        $flightType = (string) $request->input('flight_types');

        if (!in_array($flightType, ['one_way', 'round_trip', 'multi_city'], true) ||
            $request->adult === '' || $request->cabin_classes === '') {
            return redirect('/');
        }

        if ($request->input('children') === null || $request->input('children') === '') {
            $request->merge(['children' => '0']);
        }
        if ($request->input('infant') === null || $request->input('infant') === '') {
            $request->merge(['infant' => '0']);
        }

        $searchLegs = [];

        if ($flightType === 'multi_city') {
            $froms = (array) $request->input('multi_from', []);
            $tos   = (array) $request->input('multi_to', []);
            $dates = (array) $request->input('multi_date', []);

            if (count($froms) < 2 || count($froms) > 5 || count($froms) !== count($tos) || count($froms) !== count($dates)) {
                return redirect('/')->with('search_error', 'Please enter between 2 and 5 complete Multi-City flights.');
            }

            $previousDate = null;
            foreach ($froms as $i => $fromFull) {
                $toFull = $tos[$i] ?? '';
                $dateRaw = $dates[$i] ?? '';
                $from = strtoupper(substr(trim((string)$fromFull), 0, 3));
                $to = strtoupper(substr(trim((string)$toFull), 0, 3));
                $date = $this->parseFlightSearchDate((string) $dateRaw);
                $dateTs = $date ? $date->getTimestamp() : false;

                if (strlen($from) !== 3 || strlen($to) !== 3 || $dateTs === false || $from === $to) {
                    return redirect('/')->with('search_error', 'Please check every Multi-City airport and date.');
                }
                if ($previousDate && $dateTs < $previousDate) {
                    return redirect('/')->with('search_error', 'Multi-City dates must be in travel order.');
                }
                $previousDate = $dateTs;

                $fromAirport = Airport::where('code', $from)->first();
                $toAirport = Airport::where('code', $to)->first();
                if (!$fromAirport || !$toAirport) {
                    return redirect('/')->with('search_error', 'Please select valid airports from the suggestions.');
                }

                $searchLegs[] = [
                    'Date' => date('d.m.Y', $dateTs),
                    'IATAFrom' => $from,
                    'IATATo' => $to,
                    'IATAFromFull' => $fromFull,
                    'IATAToFull' => $toFull,
                    'IATAFromC' => optional($fromAirport->country)->code,
                    'IATAToC' => optional($toAirport->country)->code,
                ];
            }
        } else {
            $fromValue = trim((string) $request->input('location_from', ''));
            $toValue = trim((string) $request->input('location_to', ''));
            $departureDate = $this->parseFlightSearchDate((string) $request->input('depature_date', ''));
            $returnDate = $flightType === 'round_trip'
                ? $this->parseFlightSearchDate((string) $request->input('return_date', ''))
                : null;

            if ($fromValue === '' || $toValue === '' || !$departureDate) {
                return redirect('/')->with('search_error', 'Please enter valid departure and arrival airports and a travel date.');
            }
            if ($flightType === 'round_trip' && !$returnDate) {
                return redirect('/')->with('search_error', 'Please select a valid return date.');
            }

            $from = strtoupper(substr($fromValue, 0, 3));
            $to = strtoupper(substr($toValue, 0, 3));

            // Server-side guard (browser validation can be bypassed by direct POST).
            if ($from === $to) {
                return redirect('/')->with('search_error', 'Departure and arrival airports cannot be the same.');
            }
            if ($flightType === 'round_trip' && $returnDate < $departureDate) {
                return redirect('/')->with('search_error', 'Return date cannot be before the departure date.');
            }

            $fromAirport = Airport::where('code', $from)->first();
            $toAirport = Airport::where('code', $to)->first();
            if (!$fromAirport || !$toAirport) {
                return redirect('/')->with('search_error', 'Please select valid airports from the suggestions.');
            }
            $dept = $departureDate->format('d.m.Y');
            $ret = $returnDate ? $returnDate->format('d.m.Y') : null;

            $searchLegs[] = [
                'Date'=>$dept, 'IATAFrom'=>$from, 'IATATo'=>$to,
                'IATAFromFull'=>$request->location_from, 'IATAToFull'=>$request->location_to,
                'IATAFromC'=>optional($fromAirport?->country)->code, 'IATAToC'=>optional($toAirport?->country)->code,
            ];
            if ($flightType === 'round_trip') {
                $searchLegs[] = [
                    'Date'=>$ret, 'IATAFrom'=>$to, 'IATATo'=>$from,
                    'IATAFromFull'=>$request->location_to, 'IATAToFull'=>$request->location_from,
                    'IATAFromC'=>optional($toAirport?->country)->code, 'IATAToC'=>optional($fromAirport?->country)->code,
                ];
            }
        }

        $first = $searchLegs[0];
        $last = $searchLegs[count($searchLegs)-1];
        $field = [
            'FlightType'=>$flightType,
            'Adults'=>$request->adult,
            'Childrens'=>$request->children,
            'Infants'=>$request->infant,
            'FlightClass'=>$request->cabin_classes,
            'IATAFromFull'=>$first['IATAFromFull'],
            'IATAToFull'=>$first['IATAToFull'],
            'IATAFrom'=>$first['IATAFrom'],
            'IATATo'=>$first['IATATo'],
            'IATAFromC'=>$first['IATAFromC'],
            'IATAToC'=>$first['IATAToC'],
            'DeptDate'=>$first['Date'],
            'RetnDate'=>$flightType === 'round_trip' ? $searchLegs[1]['Date'] : ($flightType === 'multi_city' ? $last['Date'] : null),
            'SearchLegs'=>$searchLegs,
        ];

        $code = md5(mt_rand(10000,99999).time().'holidaytrip.in');
        session([$code.'.Search-Field' => json_encode($field)]);
        session()->save();
        return redirect("flight-listing/$code");
    }

    /** Parse dates emitted by Flatpickr desktop (m/d/Y) and native mobile (Y-m-d). */
    private function parseFlightSearchDate(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);
        foreach (['!m/d/Y', '!n/j/Y', '!Y-m-d'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                $expected = ltrim($format, '!');
                if ($date->format($expected) === $value || ($expected === 'n/j/Y' && $date->format($expected) === $value)) {
                    return $date->setTime(0, 0, 0);
                }
            }
        }
        return null;
    }

    public function submit_contact(Request $request)
    {
        // Honeypot: real visitors leave this hidden field empty.
        if ($request->filled('website')) {
            return redirect('thank-you');
        }

        $request->validate([
            'name'    => ['required', 'string', 'min:2', 'max:100'],
            'emailid' => ['required', 'email', 'max:150'],
            'phoneno' => ['required', 'string', 'min:7', 'max:25'],
            'subject' => ['required', 'string', 'min:3', 'max:150'],
            'message' => ['required', 'string', 'min:5', 'max:3000'],
        ]);

        $info                   =   new Enquiry;

        $info->name             =   $request->name;
        $info->emailid          =   $request->emailid;
        $info->contact          =   $request->phoneno;
        $info->subject          =   $request->subject;
        $info->message          =   $request->message;

        $info->save();

        return redirect('thank-you');
    }

    public function submit_tempbooking(Request $request)
    {
        // Prevent only an identical checkout lead from being stored repeatedly
        // within a short window. Different searches/results/prices remain valid.
        $duplicate = Temp::where('emailid', $request->email)
            ->where('phone', $request->phone)
            ->where('field', $request->i_field_encode)
            ->where('price', $request->total_price)
            ->where('result', $request->resultSelected)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($duplicate) {
            return response()->json(['stored' => false, 'duplicate' => true]);
        }

        $info               =   new Temp;
        $info->emailid      =   $request->email;
        $info->phone        =   $request->phone;
        $info->field        =   $request->i_field_encode;
        $info->price        =   $request->total_price;
        $info->result       =   $request->resultSelected;
        $info->save();

        return response()->json(['stored' => true, 'duplicate' => false]);
    }

    public function getServiceInfo(Request $request)
    {
        $service      = $request->service;
        $total_price  = (float)$request->total_price;
        $web_checkin  = $request->web_checkin ?: 'No';
        $cancellation = $request->cancellation ?: 'No';
        $premium      = $request->input('premium_addons', []);

        $decoded = json_decode($service, true);
        if(!is_array($decoded)) {
            $decoded = [];
        }

        if(isset($decoded['Web_Check_In'])) {
            $decoded['Web_Check_In']['Enable'] = $web_checkin;
        }

        if(isset($decoded['Cancellation'])) {
            $decoded['Cancellation']['Enable'] = $cancellation;
        }

        $premiumKeys = [
            'Travel_Care'       => 'Travel Care',
            'Priority_Checkin'  => 'Priority Check-In',
            'Airport_Lounge'    => 'Airport Lounge',
            'Flexible_Ticket'   => 'Flexible Ticket',
            'Extra_Baggage'     => 'Extra Baggage',
        ];

        foreach($premiumKeys as $key => $label) {
            if(isset($decoded[$key])) {
                $decoded[$key]['Enable'] = (($premium[$key] ?? 'No') === 'Yes') ? 'Yes' : 'No';
            }
        }

        $service_encoded = json_encode($decoded);

        $SubTotal1 = 0;
        $SubTotal2 = 0;
        $premiumTotal = 0;
        $premiumHtml = [];

        echo $service_encoded;
        echo "***";

        if($web_checkin === 'Yes' && isset($decoded['Web_Check_In']))
        {
            $way1      = @$decoded['Web_Check_In']['Way'];
            $pw1       = @$decoded['Web_Check_In']['Pax'];
            $SubTotal1 = (float)@$decoded['Web_Check_In']['SubTotal'];

            echo "<div class='fh-addon-line'><span>Web Check-In (".$way1." way) · ".$pw1." pax</span><strong>$".number_format($SubTotal1,2)."</strong></div>";
        }

        echo "***";

        if($cancellation === 'Yes' && isset($decoded['Cancellation']))
        {
            $pw2       = @$decoded['Cancellation']['Pax'];
            $SubTotal2 = (float)@$decoded['Cancellation']['SubTotal'];

            echo "<div class='fh-addon-line'><span>Cancellation Protection · ".$pw2." pax</span><strong>$".number_format($SubTotal2,2)."</strong></div>";
        }

        echo "***";

        foreach($premiumKeys as $key => $label)
        {
            if(($decoded[$key]['Enable'] ?? 'No') === 'Yes')
            {
                $price = (float)($decoded[$key]['SubTotal'] ?? $decoded[$key]['Price'] ?? 0);
                $premiumTotal += $price;
                $premiumHtml[] = "<div class='fh-addon-line'><span>".$label."</span><strong>$".number_format($price,2)."</strong></div>";
            }
        }

        echo implode("", $premiumHtml);

        echo "***";

        $grandTotal = $total_price + $SubTotal1 + $SubTotal2 + $premiumTotal;
        echo "$".number_format($grandTotal,2);
    }

    public function trackFareleaderEvent(Request $request)
    {
        try {
            if (!Schema::hasTable('website_analytics_events')) {
                return response()->json(['ok' => false], 503);
            }

            $event = (string) $request->input('event_type');

            if (!in_array($event, ['contact_entered', 'payment_section', 'confirm_clicked'], true)) {
                return response()->json(['ok' => false], 422);
            }

            $ua = strtolower((string) $request->userAgent());
            $device = str_contains($ua, 'mobile')
                ? 'mobile'
                : ((str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) ? 'tablet' : 'desktop');

            DB::table('website_analytics_events')->insert([
                'session_key' => hash('sha256', (string) $request->session()->getId()),
                'event_type'  => $event,
                'source'      => 'fareleaders',
                'device'      => $device,
                'ip_hash'     => hash('sha256', (string) $request->ip() . config('app.key')),
                'referrer'    => substr((string) $request->headers->get('referer', ''), 0, 1000),
                'meta_json'   => json_encode(['route' => 'fareleader_checkout_funnel']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false], 500);
        }
    }

}