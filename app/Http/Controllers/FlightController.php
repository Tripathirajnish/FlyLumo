<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;

use App\Models\Airport;
use App\Models\Airline;
use App\Models\Booking;
use App\Models\BookingAttempt;
use App\Models\Markup;
use App\Models\Setting;
use App\Models\MailTemplate;
use App\Models\TelephoneCountryCode;
use App\Models\Country;
use App\Models\State;
use App\Models\Temp;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use SoapClient;

require base_path('vendor/autoload.php');

class FlightController extends Controller
{
    /** Mail-log fix: reason of the last failed sendSmptEmail() call (no secrets). */
    public $fhLastMailError = null;

    /**
     * L5-1 (17 Nov 2026): supplier calls that create NO booking may be retried once.
     * AeroBook / ConfirmBook are deliberately NOT in this list (a retry could double-book).
     */
    private const FH_RETRY_METHODS = ['AeroSearch', 'AeroPrebook', 'OrderInfo'];

    /** Creates the SOAP client (separate method so the retry can be tested without the supplier). */
    protected function fhMakeSoapClient($wsdl, array $connectParams)
    {
        return new SoapClient($wsdl, $connectParams);
    }

    /**
     * L5-1: call the supplier with a read timeout and ONE fresh retry, all inside ~55 s
     * so Apache (60 s) never answers 504 first. Returns the SOAP result on success.
     * AeroSearch: returns null when both attempts fail (caller shows a friendly message).
     * Other methods (AeroPrebook, OrderInfo): re-throws the last error exactly as before,
     * so callers such as the Stripe executor keep their current behaviour.
     */
    protected function fhSoapCallWithRetry($client, $wsdl, array $connectParams, string $method, array $args, float $requestStartedAt)
    {
        $budget      = 55.0;
        $firstLimit  = ($method === 'AeroPrebook') ? 30 : 25;
        $oldTimeout  = ini_get('default_socket_timeout');
        $lastError   = null;

        try {
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $left = $budget - (microtime(true) - $requestStartedAt);

                if ($attempt === 2 && $left < 5) {
                    break;
                }

                $timeout = (int) max(5, min($attempt === 1 ? $firstLimit : 25, floor($left)));
                ini_set('default_socket_timeout', (string) $timeout);
                $t0 = microtime(true);

                try {
                    if ($attempt === 2) {
                        usleep(500000);
                        $client = $this->fhMakeSoapClient($wsdl, $connectParams);   // fresh connection
                    }

                    $res = $client->$method($args);

                    if ($attempt === 2) {
                        Log::info('FARESHUNT_SOAP_RETRY', ['method' => $method, 'outcome' => 'success_on_retry', 'ms' => (int) round((microtime(true) - $t0) * 1000)]);
                    }

                    return $res;
                } catch (\Throwable $e) {
                    $lastError = $e;
                    Log::warning('FARESHUNT_SOAP_ATTEMPT_FAILED', [
                        'method'    => $method,
                        'attempt'   => $attempt,
                        'ms'        => (int) round((microtime(true) - $t0) * 1000),
                        'timeout_s' => $timeout,
                        'error'     => get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 120),
                    ]);
                }
            }
        } finally {
            ini_set('default_socket_timeout', (string) $oldTimeout);
        }

        Log::error('FARESHUNT_SUPPLIER_UNAVAILABLE', [
            'method'   => $method,
            'total_ms' => (int) round((microtime(true) - $requestStartedAt) * 1000),
        ]);

        if ($method === 'AeroSearch' || $lastError === null) {
            return null;
        }

        throw $lastError;
    }

    /*  View Pages */

    public function flight_listing($code)
    {
        if(!$this->isSessionCode($code)) { return redirect()->route('not-found-page'); }

        // V9 performance telemetry: measure where listing time is spent without
        // changing provider calls, fare rules, results, or booking behaviour.
        $listingStartedAt = microtime(true);

        $lFilter            =   array();
        $output             =   array();
        
        $fields             =   $this->getSearchField($code);
        
        $markupStartedAt = microtime(true);
        $this->getMarkupDB($code,$fields);
        $markupMs = (int) round((microtime(true) - $markupStartedAt) * 1000);

        $apiStartedAt = microtime(true);
        $output         =   $this->SearchApi($code,$fields);
        $apiMs = (int) round((microtime(true) - $apiStartedAt) * 1000);
        $status         =   @$output['status'];
        $error          =   @$output['error'];
        $result         =   @$output['result'];

        $markup         =   Session::get($code.".MarkupDB");

        if($result)  
        {
            if($status=='1')
            {
                $rearrangeStartedAt = microtime(true);
                $finalInfo      =   $this->arrayRearrange($code,$result);
                $rearrangeMs = (int) round((microtime(true) - $rearrangeStartedAt) * 1000);
                $resultData     =   @$finalInfo['result'];
                $lFilter        =   @$finalInfo['filterArray'];

                $output['Search-ResultCount']       =   Session::get($code.".Search-ResultCount");
                $output['Search-SearchGuid']        =   Session::get($code.".Search-SearchGuid");
                $output['result']                   =   $resultData;
            }
        }      

        $rearrangeMs = $rearrangeMs ?? 0;
        $totalMs = (int) round((microtime(true) - $listingStartedAt) * 1000);

        Log::info('FARESHUNT_LISTING_PERFORMANCE', [
            'search_code' => $code,
            'markup_ms' => $markupMs,
            'api_ms' => $apiMs,
            'rearrange_ms' => $rearrangeMs,
            'total_ms' => $totalMs,
            'result_count' => Session::get($code.'.Search-ResultCount'),
            'target_ms' => 5000,
        ]);

        if ($totalMs > 5000) {
            Log::warning('FARESHUNT_LISTING_SLOW', [
                'search_code' => $code,
                'api_ms' => $apiMs,
                'rearrange_ms' => $rearrangeMs,
                'total_ms' => $totalMs,
            ]);
        }

        $info       =   compact('lFilter','status','error','code','fields','output');

        $renderStartedAt = microtime(true);
        $response = view('flight_list')->with($info)->render();
        $renderMs = (int) round((microtime(true) - $renderStartedAt) * 1000);

        Log::info('FARESHUNT_LISTING_RENDER_PERFORMANCE', [
            'search_code' => $code,
            'render_ms' => $renderMs,
            'result_count' => Session::get($code.'.Search-ResultCount'),
        ]);

        return response($response);
    }

    public function flight_booking($code,$selectedkey)
    {
        $passengerStartedAt = microtime(true);

        // $this->SendingMail('11006');
        // die;
        
        if(!$this->isSessionCode($code)) { return redirect()->route('not-found-page'); }

        $fields             =   $this->getSearchField($code);

        $SearchGuid         =   Session::get($code.".Search-SearchGuid");
        $OfferCode          =   Session::get($code.".Selected-OfferCode");
        $SelectedArrayKey   =   Session::get($code.".Selected-ArrayKey");
        
        if(Session::get($code.".Array-Filter")=='Yes')
        {
            $result             =   Session::get($code.".Arrange-FilterResult");
        }
        else
        {
            $result             =   Session::get($code.".Arrange-Result");
        }

        // Instant booking page (18 Nov 2026): the results page now opens this page at once (no
        // Prebook popup), so the flight is taken from the URL key when it is a valid result of
        // this search; the supplier check runs in the background on this page. A bad key falls
        // back to the old session value. (Also fixes P3-2: page shows the flight in the URL.)
        if (is_array($result) && preg_match('/^\d{1,5}$/D', (string) $selectedkey)
            && isset($result[$selectedkey]) && is_array($result[$selectedkey])
            && is_string($result[$selectedkey]['OfferCode'] ?? null) && $result[$selectedkey]['OfferCode'] !== '') {
            $SelectedArrayKey = (int) $selectedkey;
            $OfferCode = $result[$selectedkey]['OfferCode'];
            session([$code.".Selected-ArrayKey" => $SelectedArrayKey, $code.".Selected-OfferCode" => $OfferCode]);
        }

        if(@$result)
        {
            $resultSelected     =   $result[$SelectedArrayKey];

            session([$code.".Selected-Result" => $resultSelected]);

            $Markup     =   $resultSelected['Markup'];
            $MinMarkup  =   $resultSelected['MinMarkup'];
            $priceInfo  =   $resultSelected['priceInfo'];

            //print_r($MinMarkup);

            session([$code.".Markup"          => $Markup]);
            session([$code.".Min-Markup"      => $MinMarkup]);
            session([$code.".priceInfo"       => $priceInfo]);
            session()->save();

            $key                =   $selectedkey;

            // Get Session Form Fields
            $Booking_Form       =   Session::get($code.".Booking-Form");
            //print_r($Booking_Form);

            $masterDataStartedAt = microtime(true);
            $telcode            =   TelephoneCountryCode::get();
            $country            =   Country::get();
            $state              =   State::get();
            $masterDataMs = (int) round((microtime(true) - $masterDataStartedAt) * 1000);

            $info               =   compact('Booking_Form','code','OfferCode','SearchGuid','key','fields','resultSelected','telcode','country','state');

            $renderStartedAt = microtime(true);
            $response = view('flight_booking')->with($info)->render();
            $renderMs = (int) round((microtime(true) - $renderStartedAt) * 1000);
            $totalMs = (int) round((microtime(true) - $passengerStartedAt) * 1000);

            Log::info('FARESHUNT_PASSENGER_PAGE_PERFORMANCE', [
                'search_code' => $code,
                'master_data_ms' => $masterDataMs,
                'render_ms' => $renderMs,
                'total_ms' => $totalMs,
            ]);

            return response($response);
        }
        else
        {
            return redirect()->route('not-found-page');
        }
        
    }

    public function flight_search()
    {
        return view('search_flight');
    }

    public function booking_successful($bookingid)
    {
        $info = Booking::where('confirmation_token', $bookingid)->firstOrFail();

        return view('booking_successful')->with(compact('info'));
    }

    /* Ajax Function */
    
    public function ajaxGetAirport(Request $request)
    {
        $term = trim((string) $request->get('term', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $term = mb_substr($term, 0, 80);
        $upperTerm = mb_strtoupper($term);
        $contains = '%' . $term . '%';

        /*
         * Fast + relevant airport lookup.
         * Supports airport code, airport name, city and country.
         * Exact IATA code is ranked first.
         */
        $results = Airport::query()
            ->join('cities', 'airports.city_id', '=', 'cities.id')
            ->leftJoin('countries', 'airports.country_id', '=', 'countries.id')
            ->where(function ($query) use ($contains) {
                $query->where('airports.code', 'LIKE', $contains)
                    ->orWhere('airports.name', 'LIKE', $contains)
                    ->orWhere('cities.name', 'LIKE', $contains)
                    ->orWhere('countries.name', 'LIKE', $contains);
            })
            ->orderByRaw(
                "CASE
                    WHEN UPPER(airports.code) = ? THEN 0
                    WHEN UPPER(airports.code) LIKE ? THEN 1
                    WHEN UPPER(cities.name) = ? THEN 2
                    WHEN UPPER(cities.name) LIKE ? THEN 3
                    WHEN UPPER(airports.name) LIKE ? THEN 4
                    ELSE 5
                 END",
                [
                    $upperTerm,
                    $upperTerm . '%',
                    $upperTerm,
                    $upperTerm . '%',
                    $upperTerm . '%',
                ]
            )
            ->orderBy('cities.name')
            ->limit(10)
            ->get([
                'airports.code',
                'airports.name as airport_name',
                'cities.name as city_name',
                'countries.name as country_name',
            ]);

        $formatted = $results->map(function ($item) {
            $code = strtoupper(trim((string) $item->code));
            $airport = trim((string) $item->airport_name);
            $city = trim((string) $item->city_name);
            $country = trim((string) $item->country_name);

            $location = collect([$city, $country])
                ->filter()
                ->implode(', ');

            // Preserve the old form value format for the existing search flow.
            $value = $code . ' - ' . ($airport !== '' ? $airport : $location);

            return [
                'label'   => $value,
                'value'   => $value,
                'code'    => $code,
                'airport' => $airport,
                'city'    => $city,
                'country' => $country,
            ];
        })->values();

        return response()->json($formatted);
    }


    public function ajaxGetFlightResults(Request $request)
    {
        $code   = (string) $request->get('code');
        $offset = max(0, (int) $request->get('offset', 0));
        $limit  = min(50, max(1, (int) $request->get('limit', 30)));

        $fields = $this->getSearchField($code);

        if (Session::get($code.".Array-Filter") == 'Yes') {
            $result = Session::get($code.".Arrange-FilterResult", []);
        } else {
            $result = Session::get($code.".Arrange-Result", []);
        }

        if (!is_array($result)) {
            $result = [];
        }

        $batch = array_slice($result, $offset, $limit, true);

        $output = [
            'Search-SearchGuid' => Session::get($code.".Search-SearchGuid"),
        ];

        return response()->json([
            'html'     => view('partials.flight-result-batch', compact(
                'batch', 'code', 'fields', 'output'
            ))->render(),
            'next'     => $offset + count($batch),
            'total'    => count($result),
            'has_more' => ($offset + count($batch)) < count($result),
        ]);
    }

    public function ajaxGetFlightDetail(Request $request)
    {
        $code       =   $request->get('code');
        $key        =   $request->get('key');

        if (!$this->isSessionCode($code)) {
            return response('<div class="py-3 text-center text-muted">This flight search has expired. Please start a new search.</div>', 410);
        }

        $fields     =   $this->getSearchField($code);

        if(Session::get($code.".Array-Filter")=='Yes')
        {
            $result     =   Session::get($code.".Arrange-FilterResult");
            $results    =   $result[$key];
        }
        else
        {
            $result     =   Session::get($code.".Arrange-Result");
            $results    =   $result[$key];
        }

        $info       =   compact('key','fields','results');

        return view('flight-list-lazy-detail')->with($info);
    }

    public function ajaxGetMarkupDetail(Request $request)
    {
        $code       =   $request->get('code');
        $key        =   $request->get('key');

        $fields     =   $this->getSearchField($code);
        $fields     =   json_encode($fields);

        if(Session::get($code.".Array-Filter")=='No')
        {
            $result     =   Session::get($code.".Arrange-Result");
            $results    =   $result[$key];
        }
        else
        {
            $result     =   Session::get($code.".Arrange-FilterResult");
            $results    =   $result[$key];
        }

        $Markup         =   $results['Markup'];
        if(@$Markup)
        {
            echo "<table width='100%' style='text-align:left;'>";
            echo "<tr class='row'>";
                echo "<td class='col'><b>Markup Name</b></td>";
                echo "<td class='col'><b>Adult Price</b></td>";
                echo "<td class='col'><b>Discount</b></td>";
                echo "<td class='col'><b>New Adult Price</b></td>";
            echo "</tr>";

            foreach($Markup as $Markups)
            {
                echo "<tr class='row' style='font-size:13px; text-align:left;'>";
                    echo "<td class='col'>".$Markups['MarkupName']."</td>";
                    echo "<td class='col'>$".$Markups['AdultPrice']."</td>";
                    echo "<td class='col'>$".$Markups['Discount']."</td>";
                    echo "<td class='col'>$".$Markups['Discounted_APrice']."</td>";
                echo "</tr>";
            }

            echo "</table>";
        }
    }

    /*  Submit Function */

    public function submitPreBook(Request $request)
    {
        $code           =   $request->code;

        // Reject invalid/expired search sessions before calling the PreBook API.
        if (!$this->isSessionCode($code)) {
            return response('0***Invalid or expired search session', 400);
        }

        $OfferCode      =   $request->OfferCode;
        $SearchGuid     =   $request->SearchGuid;
        $key            =   $request->key;

        Session::forget($code.".FH-Prebook");   // speed cache: only a successful answer is kept (below)
        $output         =   $this->PreBookApi($OfferCode,$SearchGuid); 
        $status         =   @$output['status'];
        $error          =   @$output['error'];
        $result         =   @$output['result'];

        if($status=='1') 
        {
            $result = @json_decode(@json_encode($result), true);

            if(@$result['Success']=='1')
            {
                session([$code.".Selected-OfferCode"   =>  @$OfferCode]);
                session([$code.".Selected-ArrayKey"    =>  @$key]);
                // Speed (17 Nov 2026): keep this fresh supplier answer for a short time so the
                // Stripe "Pay" step can reuse it instead of waiting for a second Prebook.
                session([$code.".FH-Prebook" => ['offer' => (string) $OfferCode, 'guid' => (string) $SearchGuid, 'at' => time(), 'result' => $result]]);
                
                echo "1";
                echo "***";
                echo "";
            }
            else
            {
                \Log::warning('FARESHUNT_PREBOOK_RESULT_FAILURE', [
                    'status' => $status ?? null,
                    'error' => $error ?? '',
                    'errorCode' => $output['errorCode'] ?? null,
                    'result_success' => $result['Success'] ?? null,
                    'result_error' => $result['ErrorString'] ?? null,
                    'result_errorCode' => $result['ErrorCode'] ?? null,
                ]);

                echo "0";
                echo "***";
                echo "This fare is no longer available. Please refresh and choose another flight.";
            }
        }
        else
        {
            \Log::warning('FARESHUNT_PREBOOK_FAILURE', [
                'status' => $status ?? null,
                'error' => $error ?? '',
                'errorCode' => $output['errorCode'] ?? null,
                'has_result' => !empty($result),
            ]);

            echo "0";
            echo "***";
            echo "This fare is no longer available. Please refresh and choose another flight.";
        }
    }

    public function submitBooking(Request $request)
    {
        if(!$this->isSessionCode($request->code)) { return redirect()->route('not-found-page'); }
        
        $request->validate([
            'OfferCode'             => 'required',
            'SearchGuid'            => 'required',
            'flight_emailid'        => 'required|email',
            'flight_mobile'         => 'required|digits:10',
            'creditcard_no'         => 'required',
            'cardholder_name'       => 'required',
            'expire_month'          => 'required',
            'expire_year'           => 'required',
            'cvv'                   => 'required',
            'billing_address'       => 'required',
            'billing_country'       => 'required',
            'billing_state_97'      => 'required_if:billing_country,United States',
            'billing_state'         => 'required_unless:billing_country,United States',
            'billing_city'          => 'required',
            'billing_pincode'       => 'required',
            'billing_mobile'        => 'required'
        ]);

        $paxNameRules = [];
        $paxLoop = max(0, (int) $request->input("loop", 0));
        for ($k = 0; $k <= $paxLoop; $k++) {
            $paxNameRules["adult_fname_".$k] = ["required", "string", "max:50", "regex:/^[\\p{L}\\p{M} .'’\\-]+$/u"];
            $paxNameRules["adult_lname_".$k] = ["required", "string", "max:50", "regex:/^[\\p{L}\\p{M} .'’\\-]+$/u"];
            $paxNameRules["adult_mname_".$k] = ["nullable", "string", "max:50", "regex:/^[\\p{L}\\p{M} .'’\\-]+$/u"];
        }
        $request->validate($paxNameRules);

        $OfferCode              =   $request->OfferCode;
        $SearchGuid             =   $request->SearchGuid;
        $priceinfo              =   $request->priceinfo;
        $field                  =   $request->field;

        $code                   =   $request->code;
        $service                =   $request->service;
        $priceMarkup            =   $request->priceMarkup;
        $infoMarkup             =   $request->infoMarkup;
        $o_FullPrice            =   $request->o_FullPrice;
        $i_field_encode         =   $request->i_field_encode;

        $flight_emailid         =   $request->flight_emailid;
        $flight_mobile          =   $request->flight_mobile;
        $flight_mobile_telcode  =   $request->flight_mobile_telcode;

        $PaxList                =   array();
        $loop                   =   $request->loop;

        $temp                   =  array();
        
        for($k=0;$k<=$loop;$k++)
        {
            $string     =   @rand(100, 999);

            $AgeType    =   "AgeType_".$k;
            $Title      =   "adult_title_".$k;
            $BirthDay   =   "adult_dob_".$k;
            $GenderType =   "adult_gender_".$k;
            $MiddleName =   "adult_mname_".$k;
            $Name       =   "adult_fname_".$k;
            $Surname    =   "adult_lname_".$k;

            $PaxData[$k]['AgeType']    = $request->$AgeType;
            $PaxData[$k]['Title']      = $request->$Title;
            $PaxData[$k]['BirthDay']   = @date('d.m.Y',@strtotime($request->$BirthDay));
            $PaxData[$k]['BirthISO']   = 'RUS';
            $PaxData[$k]['Document']   = $AgeType.$string.'doc'.$string;    // Optional
            $PaxData[$k]['GenderType'] = $request->$GenderType;
            $PaxData[$k]['MiddleName'] = $request->$MiddleName;
            $PaxData[$k]['Name']       = $request->$Name;
            $PaxData[$k]['Surname']    = $request->$Surname;

            $temp["adult_title_".$k]        =  $request->$Title;
            $temp["adult_fname_".$k]        =  $request->$Name;
            $temp["adult_mname_".$k]        =  $request->$MiddleName;
            $temp["adult_lname_".$k]        =  $request->$Surname;
            $temp["adult_gender_".$k]       =  $request->$GenderType;
            $temp["adult_dob_".$k]          =  @date('Y-m-d',@strtotime($request->$BirthDay));
        }

        $params   =   array('ClientReference'    => $string.$flight_emailid,
                            'Email'              => $flight_emailid,
                            'Phone'              => $flight_mobile,
                            'OfferCode'          => $OfferCode,
                            'SearchGuid'         => $SearchGuid,
                            );

        $params['PaxList']['PaxData']   =   $PaxData;

        // Data Saved in Session
        $temp['flight_emailid']         =  $request->flight_emailid;
        $temp['flight_mobile_telcode']  =  $request->flight_mobile_telcode;
        $temp['flight_mobile']          =  $request->flight_mobile;
        $temp['creditcard_no']          =  $request->creditcard_no;
        $temp['cardholder_name']        =  $request->cardholder_name;
        $temp['expire_month']           =  $request->expire_month;
        $temp['expire_year']            =  $request->expire_year;
        $temp['cvv']                    =  $request->cvv;
        $temp['billing_address']        =  $request->billing_address;
        $temp['billing_country']        =  $request->billing_country;
        $temp['billing_state']          =  $request->billing_state;
        $temp['billing_state_97']       =  $request->billing_state_97;
        $temp['billing_city']           =  $request->billing_city;
        $temp['billing_pincode']        =  $request->billing_pincode;
        $temp['billing_mobile_telcode'] =  $request->billing_mobile_telcode;
        $temp['billing_mobile']         =  $request->billing_mobile;
        
        session([$code.".Booking-Form"=>$temp]);
        session()->save();

        $bookingGuardHash = hash('sha256', implode('|', [
            (string) $SearchGuid,
            (string) $OfferCode,
            strtolower(trim((string) $flight_emailid)),
            trim((string) $flight_mobile),
            (string) $request->session()->getId(),
        ]));
        $bookingCompletedKey = 'booking_completed_'.$bookingGuardHash;

        if ($completedBookingId = session($bookingCompletedKey)) {
            $completedBooking = Booking::find($completedBookingId);
            if ($completedBooking && $completedBooking->confirmation_token) {
                return redirect('booking-successful/'.$completedBooking->confirmation_token);
            }
        }

        $bookingLock = Cache::lock('booking_submit_'.$bookingGuardHash, 300);

        if (!$bookingLock->get()) {
            return back()->with('Booking-Error-Msz', 'Your booking is already being processed. Please wait a moment.');
        }

        try {
        $bookStartedAt        =   microtime(true);
        $output               =   $this->BookApi($params);
        $bookApiMs            =   (int) round((microtime(true) - $bookStartedAt) * 1000);
        $status               =   @$output['status'];
        $error                =   @$output['error'];
        $result               =   @$output['result'];

        $safeWebsiteBookError = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}/i', '[redacted-email]', (string) $error);
        $safeWebsiteBookError = preg_replace('/\\b\\d{7,19}\\b/', '[redacted-number]', $safeWebsiteBookError);
        $safeWebsiteBookError = mb_substr(strip_tags($safeWebsiteBookError), 0, 300);

        Log::info('FARESHUNT_WEBSITE_AEROBOOK_OUTCOME', [
            'status' => (string) $status,
            'error_code' => (string) (@$output['errorCode'] ?? ''),
            'error' => $safeWebsiteBookError,
            'book_api_ms' => $bookApiMs,
            'has_result' => !empty($result),
        ]);

        session(["Test-Result"     =>  @$result]);

        try {
            if (Schema::hasTable('website_analytics_events')) {
                $analyticsResult = is_array($result)
                    ? $result
                    : json_decode(json_encode($result), true);

                $analyticsEvent = (
                    (string) $status === '1'
                    && (string) ($analyticsResult['Success'] ?? '') === '1'
                ) ? 'book_api_success' : 'book_api_failed';

                $ua = strtolower((string) $request->userAgent());
                $device = str_contains($ua, 'mobile')
                    ? 'mobile'
                    : ((str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) ? 'tablet' : 'desktop');

                DB::table('website_analytics_events')->insert([
                    'session_key' => hash('sha256', (string) $request->session()->getId()),
                    'event_type'  => $analyticsEvent,
                    'source'      => 'fareleaders',
                    'device'      => $device,
                    'ip_hash'     => hash('sha256', (string) $request->ip() . config('app.key')),
                    'referrer'    => substr((string) $request->headers->get('referer', ''), 0, 1000),
                    'meta_json'   => json_encode([
                        'route' => 'fareleader_booking_submit',
                        'api_status' => (string) $status,
                        'has_result' => !empty($result),
                    ]),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);
        }
        
        if($status=='1') 
        {
            $result = json_decode(json_encode($result), true);
            
            if(@$result['Success']=='1')
            {
                $o_Name                     =   @$PaxData[0]['Name'].' '.@$PaxData[0]['MiddleName'].' '.@$PaxData[0]['Surname'];

                $allPassengerInfo           =   $this->filterPassengers($result['PaxList'], $PaxData);
                $allSectorInfo              =   $this->filterSectors($result['Offers']['OfferInfo']);
                $allCreditInfo              =   $this->filterCredit($request);
                $allBillingInfo             =   $this->filterBilling($request);
                // SECURITY: price data comes from the server session of this search, never from hidden form fields.
                $Min_Markup                 =   Session::get($code.'.Min-Markup');
                $serverIsMarkup             =   (is_array($Min_Markup) && (float) ($Min_Markup['TotalPrice'] ?? 0) > 0) ? 'Yes' : 'No';

                $sessionPriceInfo           =   Session::get($code.'.priceInfo');
                if(is_array($sessionPriceInfo) && !empty($sessionPriceInfo)) { $priceMarkup = json_encode($sessionPriceInfo); }

                $sessionSelected            =   Session::get($code.'.Selected-Result');
                if(is_array($sessionSelected) && isset($sessionSelected['priceMarkup'])) { $infoMarkup = json_encode($sessionSelected['priceMarkup']); }

                $allOtherInfo               =   $this->filterOther($request,$result,$infoMarkup);

                if($serverIsMarkup=='Yes')
                {
                    $o_FullP                  =   $Min_Markup['TotalPrice'];
                }
                else
                {
                    $o_FullP                  =   @$result['FullPrice'];
                }

                $allpriceInfo                 =   $this->filterPrice($priceMarkup,$service,$o_FullP,$this->getSearchField($code));
                $ServiceP                     =   $allpriceInfo['ServiceP'];
                $FullP                        =   $allpriceInfo['FullP'];
                
                // Preserve original booking attribution from trusted search session context.
                $bookingSource = 'Website';
                $searchField = $this->getSearchField($code);

                if (($searchField['Source'] ?? '') === 'FareLeader') {
                    $bookingSource = 'FareLeader';
                }

                // Insert Into Booking
                $rInfo                    =   new Booking();
                //$rInfo->i_field_encode  =   Session::get($code.'.Search-Field');
                $rInfo->i_field_encode    =   $i_field_encode;
                $rInfo->o_Name            =   $o_Name;
                $rInfo->o_Email           =   $flight_emailid;
                $rInfo->o_Phone           =   $flight_mobile_telcode." ".$flight_mobile;

                $rInfo->o_Passenger       =   @$allPassengerInfo;
                $rInfo->o_SectorGo        =   @$allSectorInfo['Rph_1'];
                $rInfo->o_SectorBack      =   @$allSectorInfo['Rph_2'];
                if (\Schema::hasColumn('bookings', 'o_Itinerary')) {
                    $rInfo->o_Itinerary = @$allSectorInfo['Itinerary'];
                }
                //$rInfo->o_Price         =   @$priceMarkup;
                //$rInfo->o_FullPrice     =   $o_FullP;
                $rInfo->o_Price           =   @$ServiceP;
                $rInfo->o_FullPrice       =   @$FullP;
                $rInfo->o_Credit          =   @$allCreditInfo;
                $rInfo->o_Billing         =   @$allBillingInfo;
                $rInfo->is_markup         =   $serverIsMarkup;
                $rInfo->o_Other           =   @$allOtherInfo;
                $rInfo->source            =   $bookingSource;

                $rInfo->confirmation_token = bin2hex(random_bytes(32));
                $rInfo->save(); 
                $bookingid                =   $rInfo->id;

                $message = "Your Booking Id:".$result['BookId']." BookGuid:".$result['BookGuid']." Full Price:".$result['FullPrice'];
                //$message = "Your Booking Id: FH".$bookingid." Full Price: $".$o_FullPrice;
                
                session([
                    "Booking-Success-Msz" => $message,
                    $bookingCompletedKey => $bookingid,
                ]);
                session()->save();

                app()->terminating(function () use ($bookingid) {
                    $mailStartedAt = microtime(true);

                    try {
                        $this->SendingMail($bookingid);
                    } catch (\Throwable $e) {
                        Log::error('FARESHUNT_WEBSITE_BOOKING_EMAIL_AFTER_RESPONSE_FAILED', [
                            'booking_id' => $bookingid,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $mailMs = (int) round((microtime(true) - $mailStartedAt) * 1000);

                    Log::info('FARESHUNT_WEBSITE_BOOKING_EMAIL_PERFORMANCE', [
                        'booking_id' => $bookingid,
                        'mail_ms' => $mailMs,
                        'after_response' => true,
                    ]);
                });
                $string         =   @rand(100, 999);

                $this->DeleteTemp($flight_emailid,$flight_mobile);

                return redirect('booking-successful/'.$rInfo->confirmation_token);
            }  
            else
            {
                session(["Booking-Error-Msz" =>  $error]);
                session()->save();
                
                //echo $request->key;
                //return redirect('search-flight');
                return redirect('flight-payment/'.$request->code.'/'.$request->key);
            }
        }
        else
        {
            session(["Booking-Error-Msz" =>  $error]);
            session()->save();
            
            //echo $request->key;
            //return redirect('search-flight');
            return redirect('flight-payment/'.$request->code.'/'.$request->key);
        }
        } finally {
            optional($bookingLock)->release();
        }
  
    }

    public function submitBookingFLeader(Request $request)
    {
        $request->validate([
            'OfferCode'             => 'required',
            'SearchGuid'            => 'required',
            'flight_emailid'        => 'required|email',
            'flight_mobile'         => 'required|digits:10',
            'creditcard_no'         => 'required',
            'cardholder_name'       => 'required',
            'expire_month'          => 'required',
            'expire_year'           => 'required',
            'cvv'                   => 'required',
            'billing_address'       => 'required',
            'billing_country'       => 'required',
            'billing_state_97'      => 'required_if:billing_country,United States',
            'billing_city'          => 'required',
            'billing_pincode'       => 'required',
            'billing_mobile'        => 'required'
        ]);
        $paxNameRules = [];
        $paxLoop = max(0, (int) $request->input("loop", 0));
        for ($k = 0; $k <= $paxLoop; $k++) {
            $paxNameRules["adult_fname_".$k] = ["required", "string", "max:50", "regex:/^[A-Za-z .'’-]+$/u"];
            $paxNameRules["adult_lname_".$k] = ["required", "string", "max:50", "regex:/^[A-Za-z .'’-]+$/u"];
            $paxNameRules["adult_mname_".$k] = ["nullable", "string", "max:50", "regex:/^[A-Za-z .'’-]+$/u"];
        }
        $request->validate($paxNameRules);

        
        $OfferCode              =   $request->OfferCode;
        $SearchGuid             =   $request->SearchGuid;
        $priceinfo              =   $request->priceinfo;
        $field                  =   $request->field;

        $code                   =   $request->code;
        $service                =   $request->service;
        $priceMarkup            =   $request->priceMarkup;
        $infoMarkup             =   $request->infoMarkup;
        $o_FullPrice            =   $request->o_FullPrice;
        $i_field_encode         =   $request->i_field_encode;

        // SECURITY: the fare must come from the server session created when the (signed) meta deeplink
        // was opened -- never from the hidden form fields, which a customer can edit in the browser.
        // Verify that the submitted flight matches the successful PreBook session.
        $sessionOfferCode = Session::get($code.".FL-OfferCode");
        $sessionSearchGuid = Session::get($code.".FL-SearchGuid");
        if (!is_string($sessionOfferCode) || $sessionOfferCode === "" ||
            !is_string($sessionSearchGuid) || $sessionSearchGuid === "" ||
            !hash_equals($sessionOfferCode, (string) $OfferCode) ||
            !hash_equals($sessionSearchGuid, (string) $SearchGuid)) {
            Log::warning("FARESHUNT_FARELEADER_FLIGHT_SESSION_MISMATCH", ["session_hash" => hash("sha256", session()->getId())]);
            return redirect()->back()->withErrors(["flight_emailid" => "Your selected flight session is invalid or expired. Please select the flight again."]);
        }

        $flPriceMarkup          =   Session::get($code.'.FL-priceMarkup');
        if(!is_array($flPriceMarkup) || (float) ($flPriceMarkup['Total'] ?? 0) <= 0)
        {
            Log::warning('FARESHUNT_FARELEADER_PRICE_SESSION_MISSING', ['session_hash' => hash('sha256', session()->getId())]);
            return redirect()->back()->withInput($request->except(['creditcard_no','cvv']))->withErrors(['flight_emailid' => 'Your booking session has expired. Please go back to the search and select the flight again.']);
        }
        $priceMarkup            =   json_encode($flPriceMarkup);
        $infoMarkup             =   json_encode(Session::get($code.'.FL-infoMarkup') ?: array());
        $o_FullPrice            =   $flPriceMarkup['Total'];

        $flight_emailid         =   $request->flight_emailid;
        $flight_mobile          =   $request->flight_mobile;
        $flight_mobile_telcode  =   $request->flight_mobile_telcode;

        $PaxList                =   array();
        $loop                   =   $request->loop;

        $temp                   =  array();
        
        for($k=0;$k<=$loop;$k++)
        {
            $string     =   @rand(100, 999);

            $AgeType    =   "AgeType_".$k;
            $Title      =   "adult_title_".$k;
            $BirthDay   =   "adult_dob_".$k;
            $GenderType =   "adult_gender_".$k;
            $MiddleName =   "adult_mname_".$k;
            $Name       =   "adult_fname_".$k;
            $Surname    =   "adult_lname_".$k;

            $PaxData[$k]['AgeType']    = $request->$AgeType;
            $PaxData[$k]['Title']      = $request->$Title;
            $PaxData[$k]['BirthDay']   = @date('d.m.Y',@strtotime($request->$BirthDay));
            $PaxData[$k]['BirthISO']   = 'RUS';
            $PaxData[$k]['Document']   = $AgeType.$string.'doc'.$string;    // Optional
            $PaxData[$k]['GenderType'] = $request->$GenderType;
            $PaxData[$k]['MiddleName'] = $request->$MiddleName;
            $PaxData[$k]['Name']       = $request->$Name;
            $PaxData[$k]['Surname']    = $request->$Surname;

            $temp["adult_title_".$k]        =  $request->$Title;
            $temp["adult_fname_".$k]        =  $request->$Name;
            $temp["adult_mname_".$k]        =  $request->$MiddleName;
            $temp["adult_lname_".$k]        =  $request->$Surname;
            $temp["adult_gender_".$k]       =  $request->$GenderType;
            $temp["adult_dob_".$k]          =  @date('Y-m-d',@strtotime($request->$BirthDay));
        }

        $params   =   array('ClientReference'    => $string.$flight_emailid,
                            'Email'              => $flight_emailid,
                            'Phone'              => $flight_mobile,
                            'OfferCode'          => $OfferCode,
                            'SearchGuid'         => $SearchGuid,
                            );

        $params['PaxList']['PaxData'] = $PaxData;

        // Data Saved in Session
        $temp['flight_emailid']         =  $request->flight_emailid;
        $temp['flight_mobile_telcode']  =  $request->flight_mobile_telcode;
        $temp['flight_mobile']          =  $request->flight_mobile;
        $temp['creditcard_no']          =  $request->creditcard_no;
        $temp['cardholder_name']        =  $request->cardholder_name;
        $temp['expire_month']           =  $request->expire_month;
        $temp['expire_year']            =  $request->expire_year;
        $temp['cvv']                    =  $request->cvv;
        $temp['billing_address']        =  $request->billing_address;
        $temp['billing_country']        =  $request->billing_country;
        $temp['billing_state']          =  $request->billing_state;
        $temp['billing_state_97']       =  $request->billing_state_97;
        $temp['billing_city']           =  $request->billing_city;
        $temp['billing_pincode']        =  $request->billing_pincode;
        $temp['billing_mobile_telcode'] =  $request->billing_mobile_telcode;
        $temp['billing_mobile']         =  $request->billing_mobile;
        
        //session([$code.".Booking-Form"=>$temp]);
        session(["Fareleader.Booking-Form"=>$temp]);
        session()->save();
        
        $fareleaderBookingGuardHash = hash('sha256', implode('|', [
            (string) $SearchGuid,
            (string) $OfferCode,
            strtolower(trim((string) $flight_emailid)),
            trim((string) $flight_mobile),
            (string) $request->session()->getId(),
        ]));
        $fareleaderBookingCompletedKey = 'fareleader_booking_completed_'.$fareleaderBookingGuardHash;

        if ($completedBookingId = session($fareleaderBookingCompletedKey)) {
            $completedBooking = Booking::find($completedBookingId);
            if ($completedBooking && $completedBooking->confirmation_token) {
                return redirect('booking-successful/'.$completedBooking->confirmation_token);
            }
        }

        $fareleaderBookingLock = Cache::lock('fareleader_booking_submit_'.$fareleaderBookingGuardHash, 300);

        if (!$fareleaderBookingLock->get()) {
            return back()->with('Booking-Error-Msz', 'Your booking is already being processed. Please wait a moment.');
        }

        try {
        $fareleaderBookStartedAt = microtime(true);
        $output               =   $this->BookApi($params);
        $fareleaderBookMs     =   (int) round((microtime(true) - $fareleaderBookStartedAt) * 1000);
        $status               =   @$output['status'];
        $error                =   @$output['error'];
        $result               =   @$output['result'];

        $safeBookError = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}/i', '[redacted-email]', (string) $error);
        $safeBookError = preg_replace('/\\b\\d{7,19}\\b/', '[redacted-number]', $safeBookError);
        $safeBookError = mb_substr(strip_tags($safeBookError), 0, 300);

        Log::info('FARESHUNT_FARELEADER_AEROBOOK_OUTCOME', [
            'status' => (string) $status,
            'error_code' => (string) (@$output['errorCode'] ?? ''),
            'error' => $safeBookError,
            'book_api_ms' => $fareleaderBookMs,
            'has_result' => !empty($result),
            'session_hash' => hash('sha256', session()->getId()),
        ]);
        
        // print_r($params);
        // echo "<pre>";
        // print_r($output);
        // die;

        session(["Test-Result"     =>  @$result]);
        
        if($status=='1') 
        {
            $result = json_decode(json_encode($result), true);
            
            if(@$result['Success']=='1')
            {
                $o_Name                     =   @$PaxData[0]['Name'].' '.@$PaxData[0]['MiddleName'].' '.@$PaxData[0]['Surname'];

                $allPassengerInfo           =   $this->filterPassengers($result['PaxList'], $PaxData);
                $allSectorInfo              =   $this->filterSectors($result['Offers']['OfferInfo']);
                $allCreditInfo              =   $this->filterCredit($request);
                $allBillingInfo             =   $this->filterBilling($request);
                $allOtherInfo               =   $this->filterOther($request,$result,$infoMarkup);

                $allpriceInfo               =   $this->filterPrice($priceMarkup,$service,$o_FullPrice,$this->getSearchField($code));
                $ServiceP                   =   $allpriceInfo['ServiceP'];
                $FullP                      =   $allpriceInfo['FullP'];
                
                // Insert Into Booking
                $rInfo                    =   new Booking();
                //$rInfo->i_field_encode    =   Session::get($code.'.Search-Field'); // hidden form field from booking page
                $rInfo->i_field_encode    =   $i_field_encode;
                $rInfo->o_Name            =   $o_Name;
                $rInfo->o_Email           =   $flight_emailid;
                $rInfo->o_Phone           =   $flight_mobile_telcode." ".$flight_mobile;
                
                $rInfo->o_Passenger       =   @$allPassengerInfo;
                $rInfo->o_SectorGo        =   @$allSectorInfo['Rph_1'];
                $rInfo->o_SectorBack      =   @$allSectorInfo['Rph_2'];
                if (\Schema::hasColumn('bookings', 'o_Itinerary')) {
                    $rInfo->o_Itinerary = @$allSectorInfo['Itinerary'];
                }

                //$rInfo->o_Price           =   @$priceMarkup;
                //$rInfo->o_FullPrice       =   @$o_FullPrice;
                $rInfo->o_Price           =   @$ServiceP;
                $rInfo->o_FullPrice       =   @$FullP;

                $rInfo->o_Credit          =   @$allCreditInfo;
                $rInfo->o_Billing         =   @$allBillingInfo;
                $rInfo->o_Other           =   @$allOtherInfo;
                $partnerSource = Session::get($code.".FL-partner-source");
                $rInfo->source = in_array($partnerSource, ["FareLeaders Core", "FareLeaders Compare"], true)
                    ? $partnerSource : "FareLeader";

                $rInfo->confirmation_token = bin2hex(random_bytes(32));
                $rInfo->save(); 
                $bookingid                =   $rInfo->id;

                //$message = "Your Booking Id:".$result['BookId']." BookGuid:".$result['BookGuid']." Full Price:".$result['FullPrice'];
                $message = "Your Booking Id: FH".$bookingid." Full Price: $".$FullP;
                
                session([
                    "Booking-Success-Msz" => $message,
                    $fareleaderBookingCompletedKey => $bookingid,
                ]);
                session()->save();

                app()->terminating(function () use ($bookingid) {
                    $fareleaderMailStartedAt = microtime(true);

                    try {
                        $this->SendingMail($bookingid);
                    } catch (\Throwable $e) {
                        Log::error('FARESHUNT_FARELEADER_BOOKING_EMAIL_AFTER_RESPONSE_FAILED', [
                            'booking_id' => $bookingid,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $fareleaderMailMs = (int) round((microtime(true) - $fareleaderMailStartedAt) * 1000);

                    Log::info('FARESHUNT_FARELEADER_BOOKING_EMAIL_PERFORMANCE', [
                        'booking_id' => $bookingid,
                        'mail_ms' => $fareleaderMailMs,
                        'after_response' => true,
                    ]);
                });

                $string         =   @rand(100, 999);

                $this->DeleteTemp($flight_emailid,$flight_mobile);

                return redirect('booking-successful/'.$rInfo->confirmation_token);
            }  
            else
            {
                //session(["Booking-Error-Msz" =>  $error]);
                //session()->save();

                return redirect('flights-payment/?priceinfo='.$priceinfo.'&field='.$field.'&searchguid='.$SearchGuid.'&offercode='.$OfferCode);
            }
        }
        else
        {
            session(["Booking-Error-Msz" =>  $error]);
            session()->save();

            return redirect('flights-payment/?priceinfo='.$priceinfo.'&field='.$field.'&searchguid='.$SearchGuid.'&offercode='.$OfferCode);
        }
        } finally {
            optional($fareleaderBookingLock)->release();
        }
  
    }

    public function submitConfirm(Request $request)
    {
        $params['BookGuid']         =   $request->BookGuid;
        $params['BookId']           =   $request->BookId;
        $params['Price']            =   $request->Price;

        $confirm_result             =   $this->ConfirmApi($params);

        return response()->json([
            'status' => !empty($confirm_result) ? 'ok' : 'failed',
        ]);
    }

    public function submitOrderInfo(Request $request)
    {
        $params['BookId']           =   $request->BookId;

        $orderinfo_result           =   $this->OrderInfoApi($params);

        return response()->json([
            'status' => !empty($orderinfo_result) ? 'ok' : 'failed',
        ]);
    }

    public function arrayRearrange($code,$result)
    {
        $markupDB       =   Session::get($code.".MarkupDB");
        $Search_Field   =   $this->getSearchField($code);

        if(!array_is_list(@$result['AirCompany']['CodeValue']))
        {
            $temp1   =   $result['AirCompany']['CodeValue'];

            $result['AirCompany']['CodeValue']      =   array();
            $result['AirCompany']['CodeValue'][0]   =   $temp1;
        }

        if(!array_is_list(@$result['AirPorts']['AirPortInfo']))
        {
            $temp2   =   $result['AirPorts']['AirPortInfo'];

            $result['AirPorts']['AirPortInfo']      =   array();
            $result['AirPorts']['AirPortInfo'][0]   =   $temp2;
        }
        
        // Add Logo From DB
        $AirCompany_Code    =   @$result['AirCompany']['CodeValue'];
        $codes              =   @array_column($AirCompany_Code,'Code');
        
        $matchedRecords     =   DB::table('airlines')->select('code', 'logo')->whereIn('code', $codes)->get();
        $matchedData        =   collect($matchedRecords)->keyBy('code')->toArray();
        
        $newArrayAirCompany =   [];

        foreach ($AirCompany_Code as $item) 
        {
            if (isset($matchedData[$item['Code']])) 
            {
                $newArrayAirCompany[] = [
                    'Code'  => $item['Code'],
                    'Value' => $item['Value'],
                    'Logo'  => $matchedData[$item['Code']]->logo
                ];
            }
        }

        session([$code.".Filter-Airline"        =>  @$newArrayAirCompany]);
        session([$code.".Filter-AirPort"        =>  @$result['AirPorts']['AirPortInfo']]);  
        session([$code.".Search-ResultCount"    =>  @$result['ResultCount']]);
        session([$code.".Search-SearchGuid"     =>  @$result['SearchGuid']]);
        session()->save();

        // echo "<pre>";
        // print_r($result);
        // die;

$AllAirPorts = @$result['AirPorts']['AirPortInfo'];
$FlightData  = @$result['FlightData']['FlightData'];
if (is_array($FlightData) && $FlightData !== [] && !array_key_exists(0, $FlightData)) { $FlightData = [$FlightData]; } // L4 fix 16 Nov 2026: supplier sends ONE flight as an object, not a list


$filterArray = array(
                                          'FilterMinPrice'   => PHP_INT_MAX,
                                          'FilterMaxPrice'   => PHP_INT_MIN,

                                          'FilterDptStop'    => array(),
                                          'FilterRtnStop'    => array(),

                                          'FilterDptTime'    => array(),
                                          'FilterRtnTime'    => array(),

                                          'FilterDptAirline' => array(),
                                          'FilterRtnAirline' => array(),

                                          'FilterDptAirport' => array(),
                                          'FilterRtnAirport' => array()
                                        );

        if(@$FlightData)
        {
            session([$code.'.Filter-CheapestPrice'    =>  PHP_INT_MAX]);
            session([$code.'.Filter-HighestPrice'     =>  PHP_INT_MIN]);
            session([$code.'.Filter-ShorestPrice'     =>  PHP_INT_MAX]);
            
            foreach($FlightData as $key=>$value_FData)
            {
                $resultData[$key]['OfferCode']          =   @$value_FData['OfferCode'];
                $resultData[$key]['AdultPrice']         =   @$value_FData['AdultPrice'];
                $resultData[$key]['ChildPrice']         =   @$value_FData['ChildPrice'];
                $resultData[$key]['InfantPrice']        =   @$value_FData['InfantPrice'];
                $resultData[$key]['TotalPrice']         =   @$value_FData['TotalPrice'];

                $resultData[$key]['AirlineList']        =    array();
                $resultData[$key]['AllAirline']         =    '';
                
                $resultData[$key]['AllFlight']['Rph_1'] =   array();
                $resultData[$key]['AllFlight']['Rph_2'] =   array();
                $resultData[$key]['DptFlightStop']      =    0;
                $resultData[$key]['RtnFlightStop']      =    0;
                
                $resultData[$key]['FtrAirline']         =    '';
                $resultData[$key]['FtrDptTime']         =    '';
                $resultData[$key]['FtrRtnTime']         =    '';
                
                if($value_FData['AdultPrice'] < Session::get($code.".Filter-CheapestPrice")) 
                {
                    session([$code.".Filter-CheapestPrice"    =>  $value_FData['AdultPrice']]);
                }

                if($value_FData['AdultPrice'] > Session::get($code.".Filter-HighestPrice")) 
                {
                    session([$code.".Filter-HighestPrice"    =>  $value_FData['AdultPrice']]);
                }

                $OfferInfo      =   $value_FData['Offers']['OfferInfo'];
                
                if($OfferInfo)
                {
                    $airlinelist = Session::get($code.".Filter-Airline");
                    
                    if(@$OfferInfo['Rph'])
                    {
                        $OfferSegment    =   @$OfferInfo['Segments']['OfferSegment'];

                        if(@$OfferSegment['AirCraft'] || isset($OfferSegment['MarketingAirline']))
                        {
                            //$searchCode     =   $OfferInfo['ValidatingAirline'];
                            $searchCode     =   $OfferSegment['MarketingAirline'];
                            
                            $matchA         =   @array_filter($airlinelist, function ($item) use ($searchCode) {
                                                    return $item['Code'] === $searchCode;
                                                });  
                            $matchA         =   @reset($matchA);

                            $AirlineName                    =   @array_column($airlinelist, 'Value', 'Code')[$searchCode] ?? null; 
                            $Airlineinfo['Airline']         =   $searchCode;
                            $Airlineinfo['AirlineName']     =   @$matchA['Value'];
                            $Airlineinfo['AirlineLogo']     =   @$matchA['Logo'];
                        
                            $resultData     =   $this->getArrResultLoop($AllAirPorts,$resultData,$key,$OfferSegment,$OfferSegment['Rph'],$Airlineinfo);  
                        }
                        else
                        {
                            foreach($OfferSegment as $key1=>$OfferSegments)
                            {
                                //$searchCode     =   $OfferInfo['ValidatingAirline'];
                                $searchCode     =   $OfferSegments['MarketingAirline'];

                                $matchA         =   @array_filter($airlinelist, function ($item) use ($searchCode) {
                                                        return $item['Code'] === $searchCode;
                                                    });  
                                $matchA         =   @reset($matchA);

                                $AirlineName                    =   @array_column($airlinelist, 'Value', 'Code')[$searchCode] ?? null; 
                                $Airlineinfo['Airline']         =   $searchCode;
                                $Airlineinfo['AirlineName']     =   @$matchA['Value'];
                                $Airlineinfo['AirlineLogo']     =   @$matchA['Logo'];
                            
                                $resultData =   $this->getArrResultLoop($AllAirPorts,$resultData,$key,$OfferSegments,$OfferSegments['Rph'],$Airlineinfo);   
                            }
                        }
                    }
                    else
                    {
                        foreach($OfferInfo as $value1)
                        {
                            $OfferSegment               =   $value1['Segments']['OfferSegment'];

                            if(@$OfferSegment['AirCraft'] || isset($OfferSegment['MarketingAirline']))
                            {  
                                //$searchCode     =   $value1['ValidatingAirline'];
                                $searchCode     =   $OfferSegment['MarketingAirline'];

                                $matchA         =   @array_filter($airlinelist, function ($item) use ($searchCode) {
                                                        return $item['Code'] === $searchCode;
                                                    });  
                                $matchA         =   @reset($matchA);

                                $AirlineName                    =   @array_column($airlinelist, 'Value', 'Code')[$searchCode] ?? null; 
                                $Airlineinfo['Airline']         =   $searchCode;
                                $Airlineinfo['AirlineName']     =   @$matchA['Value'];
                                $Airlineinfo['AirlineLogo']     =   @$matchA['Logo'];
                                
                                $resultData =   $this->getArrResultLoop($AllAirPorts,$resultData,$key,$OfferSegment,$value1['Rph'],$Airlineinfo);
                            }
                            else
                            {
                                foreach($OfferSegment as $OfferSegments)
                                {
                                    //$searchCode     =   $value1['ValidatingAirline'];
                                    $searchCode     =   $OfferSegments['MarketingAirline'];

                                    $matchA         =   @array_filter($airlinelist, function ($item) use ($searchCode) {
                                                            return $item['Code'] === $searchCode;
                                                        });  
                                    $matchA         =   @reset($matchA);

                                    $AirlineName                    =   @array_column($airlinelist, 'Value', 'Code')[$searchCode] ?? null; 
                                    $Airlineinfo['Airline']         =   $searchCode;
                                    $Airlineinfo['AirlineName']     =   @$matchA['Value'];
                                    $Airlineinfo['AirlineLogo']     =   @$matchA['Logo'];
                                    
                                    $resultData =   $this->getArrResultLoop($AllAirPorts,$resultData,$key,$OfferSegments,$value1['Rph'],$Airlineinfo);
                                }
                            }
                        }
                    }

                    // Check All Airlines are same or not
                    if (count(array_unique($resultData[$key]['AirlineList'])) === 1) 
                    {
                        $resultData[$key]['AllAirline'] = $resultData[$key]['AirlineList'][0];
                    } 

                    // Airline block rules: hide offers that contain a blocked airline
                    if($this->isAirlineBlocked($markupDB,$resultData[$key]['AirlineList']))
                    {
                        unset($resultData[$key]);
                        continue;
                    }

                    // START MARKUP
                    $markupArray     =   $this->addReduceMarkupArray($Search_Field,$value_FData,$this->withoutBlockRules($markupDB),$resultData[$key]['AllAirline']);
                    
                    $reduceMarkupDB  =   $markupArray['MarkupDB'];

                    //if($markupDB)
                    if($reduceMarkupDB) 
                    { 
                        $resultData[$key]['Is_Markup']  =   'Yes'; 

                        $arr         =   $markupArray['MinMarkup'];

                        if(@$arr['New_ChildPrice']=='')  { @$arr['New_ChildPrice'] = '0'; }
                        if(@$arr['New_InfantPrice']=='') { @$arr['New_InfantPrice'] = '0'; }

                        $priceInfo_['Adult']['Price']       =   @$arr['New_AdultPrice'];
                        $priceInfo_['Adult']['Number']      =   @$Search_Field['Adults'];
                        $priceInfo_['Adult']['SubTotal']    =   @$arr['New_AdultPrice']*@$Search_Field['Adults'];
                
                        $priceInfo_['Child']['Price']       =   @$arr['New_ChildPrice'];
                        $priceInfo_['Child']['Number']      =   @$Search_Field['Childrens'];
                        $priceInfo_['Child']['SubTotal']    =   @$arr['New_ChildPrice']*@$Search_Field['Childrens'];
                
                        $priceInfo_['Infant']['Price']      =   @$arr['New_InfantPrice'];
                        $priceInfo_['Infant']['Number']     =   @$Search_Field['Infants'];
                        $priceInfo_['Infant']['SubTotal']   =   @$arr['New_InfantPrice']*@$Search_Field['Infants'];
                
                        $priceInfo_['Total']                =   @$arr['TotalPrice'];
                
                        $priceMarkup_['OldAdultPrice']         =   @$value_FData['AdultPrice'];
                        $priceMarkup_['OldChildPrice']         =   @$value_FData['ChildPrice'];
                        $priceMarkup_['OldInfantPrice']        =   @$value_FData['InfantPrice'];
                        $priceMarkup_['OldTotalPrice']         =   @$value_FData['TotalPrice'];
                        
                        $priceMarkup_['IsMarkup']              =   "Yes";
                        $priceMarkup_['MarkupId']              =   @$arr['MarkupId'];
                        $priceMarkup_['MarkupName']            =   @$arr['MarkupName'];
                        $priceMarkup_['MarkupDiscount']        =   @$arr['Discount'];
                        
                        // $priceInfo_  =   "A:".@$arr['New_AdultPrice'].",C:".@$arr['New_ChildPrice'].",I:".@$arr['New_InfantPrice'].",";
                        // $priceInfo_ .=   "Ps:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].",T:".@$arr['TotalPrice'].",";
                        // $priceInfo_ .=   "MId:".@$arr['MarkupId'].",MN:".@$arr['MarkupName'].",Dis:".@$arr['Discount'];

                        $resultData[$key]['priceInfo']    =  $priceInfo_; 
                        $resultData[$key]['priceMarkup']  =  $priceMarkup_; 
                    } 
                    else 
                    { 
                        $resultData[$key]['Is_Markup']  =   'No'; 

                        $arr         =   $resultData[$key];

                        if(@$arr['ChildPrice']=='')  { @$arr['ChildPrice'] = '0'; }
                        if(@$arr['InfantPrice']=='') { @$arr['InfantPrice'] = '0'; }
                        
                        // $priceInfo_  =   "A:".@$arr['AdultPrice'].",C:".@$arr['ChildPrice'].",I:".@$arr['InfantPrice'].",";
                        // $priceInfo_ .=   "Ps:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].",T:".@$arr['TotalPrice'];

                        $priceInfo_['Adult']['Price']       =   @$arr['AdultPrice'];
                        $priceInfo_['Adult']['Number']      =   @$Search_Field['Adults'];
                        $priceInfo_['Adult']['SubTotal']    =   @$arr['AdultPrice']*@$Search_Field['Adults'];
                
                        $priceInfo_['Child']['Price']       =   @$arr['ChildPrice'];
                        $priceInfo_['Child']['Number']      =   @$Search_Field['Childrens'];
                        $priceInfo_['Child']['SubTotal']    =   @$arr['ChildPrice']*@$Search_Field['Childrens'];
                
                        $priceInfo_['Infant']['Price']      =   @$arr['InfantPrice'];
                        $priceInfo_['Infant']['Number']     =   @$Search_Field['Infants'];
                        $priceInfo_['Infant']['SubTotal']   =   @$arr['InfantPrice']*@$Search_Field['Infants'];
                
                        $priceInfo_['Total']                =   @$arr['TotalPrice'];
                        
                        $priceMarkup_['IsMarkup']              =   "No";
                        
                        $resultData[$key]['priceInfo']      =  $priceInfo_; 
                        $resultData[$key]['priceMarkup']    =  $priceMarkup_; 

                    }

                    $resultData[$key]['Markup']             =   $markupArray['Markup'];
                    $resultData[$key]['MinMarkup']          =   $markupArray['MinMarkup'];   
                    // END MARKUP

                    $resultData[$key]['DptFlightStop']      =   ($resultData[$key]['DptFlightStop'])-1;
                    $resultData[$key]['RtnFlightStop']      =   ($resultData[$key]['RtnFlightStop'])-1;   
                    
                    // MAKE FILTER ARRAY
                    $filterArray        =   $this->makeFilterArray($filterArray,$resultData[$key]);
                }
            }
        }

        $filterArray    =   $this->makeFilterArrayLabel($filterArray,$result['AirCompany']['CodeValue'],$result['AirPorts']['AirPortInfo']);
        
        session([$code.".Arrange-Result"   =>  @$resultData]);
        session()->save();

        session([$code.".filterArray"      =>  @$filterArray]);
        session()->save();
        
        //dd($resultData);
        // echo "<pre>";
        // print_r($filterArray);
        // die;
        
        $finalInfo['result']        =   $resultData;
        $finalInfo['filterArray']   =   $filterArray;
        return $finalInfo;

    }

    public function getMarkupDB($code,$fields)
    {
        $markupSession  =   $this->fetchMarkupRows($fields);

        session([$code.".MarkupDB" =>  $markupSession]);
        session()->save();

        $markup = Session::get($code.".MarkupDB");
    }

    // Returns the active fare rules that match this search (the list that is stored in the session).
    // The CRM Fare Rule Simulator calls this same method, so the simulator uses the real matching code.
    public function fetchMarkupRows($fields)
    {
        $flight_types   =   @$fields['FlightType'];
        $cabin          =   @$fields['FlightClass'];
        
        $IATAFrom       =   @$fields['IATAFrom'];
        $IATATo         =   @$fields['IATATo'];
        
        $IATAFromC      =   @$fields['IATAFromC'];
        $IATAToC        =   @$fields['IATAToC'];

        $DeptDate       =   @$fields['DeptDate'];
        $RetnDate       =   @$fields['RetnDate'];

        $DeptDate_      =   date('Y-m-d',strtotime($DeptDate));
        $RetnDate_      =   $RetnDate ? date('Y-m-d',strtotime($RetnDate)) : $DeptDate_;
        $advDays        =   max(0,(int) round((strtotime($DeptDate_) - strtotime(date('Y-m-d'))) / 86400));

        $markup = Markup::
                
                whereDate('from_date', '<=', $DeptDate_)->
                whereDate('to_date', '>=', $RetnDate_)->

                where('is_ownsite','Yes')->

                where(function ($query) use ($IATAFrom, $IATAFromC) {
                    $query->where('departure_type', 'airport')
                          ->where('departure_code', 'like', '%' . $IATAFrom . '%')
                          ->orWhere(function ($query) use ($IATAFromC) {
                              $query->where('departure_type', 'country')
                                    ->where('departure_code', 'like', '%' . $IATAFromC . '%');
                          });
                })->
                
                where(function ($query) use ($IATATo, $IATAToC) {
                    $query->where('arrival_type', 'airport')
                          ->where('arrival_code', 'like', '%' . $IATATo . '%')
                          ->orWhere(function ($query) use ($IATAToC) {
                              $query->where('arrival_type', 'country')
                                    ->where('arrival_code', 'like', '%' . $IATAToC . '%');
                          });
                })->
                
                when($flight_types == 'one_way', function ($query) { return $query->where('is_oneway', 'Yes'); })->
                when($flight_types == 'round_trip', function ($query) { return $query->where('is_roundtrip', 'Yes'); })->
                when($flight_types == 'multi_city', function ($query) { return $query->where('is_multicity', 'Yes'); })->
                
                when($cabin == 'Econom', function ($query) { return $query->where('is_economy', 'Yes'); })->
                when($cabin == 'Peconom', function ($query) { return $query->where('is_peconomy', 'Yes'); })->
                when($cabin == 'Business', function ($query) { return $query->where('is_business', 'Yes'); })->
                when($cabin == 'firstclass', function ($query) { return $query->where('is_fclass', 'Yes'); })->

                when($this->markupPhase2Ready(), function ($query) use ($advDays) {
                    return $query->where(function ($w) use ($advDays) { $w->whereNull('adv_min_days')->orWhere('adv_min_days', '<=', $advDays); })
                                 ->where(function ($w) use ($advDays) { $w->whereNull('adv_max_days')->orWhere('adv_max_days', '>=', $advDays); });
                })->

                where('is_active','Yes')->
                
                get()->filter(function ($m) use ($IATAFrom, $IATAFromC, $IATATo, $IATAToC) {
                    return $this->routeMatchesExactly($m, $IATAFrom, $IATAFromC, $IATATo, $IATAToC);
                });

        if($markup)
        {
            $tempArray      =   array();
            $markupSession  =   array();

            foreach($markup as $markups)
            {
                $tempArray['id']                = $markups['id'];
                $tempArray['name']              = $markups['name'];
                $tempArray['departure_code']    = $markups['departure_code'];
                $tempArray['arrival_code']      = $markups['arrival_code'];
                $tempArray['airline_code']      = $markups['airline_code'];
                $tempArray['departure_type']    = $markups['departure_type'];
                $tempArray['arrival_type']      = $markups['arrival_type'];
                $tempArray['rule_action']       = (($markups->getAttributes()['rule_action'] ?? 'discount') ?: 'discount');
                $tempArray['fare_min_price']    = $markups['fare_min_price'];
                $tempArray['markup']            = $markups['markup'];
                $tempArray['amt_per']           = $markups['amt_per'];

                array_push($markupSession,$tempArray);
            }
        }

        return isset($markupSession) ? $markupSession : array();
    }

    public function addMarkupArray($Search_Field,$value_FData,$markupDB)
    {
        $temp   =   array();
        $markup =   array();

        if($markupDB)
        {
            $AdultPrice     =   @$value_FData['AdultPrice'];
            $ChildPrice     =   @$value_FData['ChildPrice'];
            $InfantPrice    =   @$value_FData['InfantPrice'];
            
            foreach($markupDB as $markupDBs)
            {
                $amt_per        =   $markupDBs['amt_per'];

                if($amt_per=='per')
                {
                    $temp['MarkupId']               =   $markupDBs['id'];
                    $temp['MarkupName']             =   @$markupDBs['name'];
                    $temp['AdultPrice']             =   $AdultPrice;
                    $temp['ChildPrice']             =   $ChildPrice;
                    $temp['DiscountType']           =   $markupDBs['markup'].'%';
                    $temp['Discount']               =   number_format(($markupDBs['markup']/100)*$AdultPrice,2);

                    $AdultPrice                     =   floatval($AdultPrice);
                    $temp['Discount']               =   floatval($temp['Discount']);
                    $temp['Discounted_APrice']      =   $AdultPrice-$temp['Discount'];

                    if(@$ChildPrice) { $temp['Discounted_CPrice']      =   @$ChildPrice-@$temp['Discount'];   } 
                    else { $temp['Discounted_CPrice']      =NULL; }
                    
                }
                if($amt_per=='amt')
                {
                    $temp['MarkupId']               =   $markupDBs['id'];
                    $temp['MarkupName']             =   @$markupDBs['name'];
                    $temp['AdultPrice']             =   $AdultPrice;
                    $temp['ChildPrice']             =   $ChildPrice;
                    $temp['DiscountType']           =   '$'.$markupDBs['markup'];
                    $temp['Discount']               =   $markupDBs['markup'];

                    $AdultPrice                     =   floatval($AdultPrice);
                    $markupDBs['markup']            =   floatval($markupDBs['markup']);
                    $temp['Discounted_APrice']      =   $AdultPrice-$markupDBs['markup'];

                    if(@$ChildPrice) { $temp['Discounted_CPrice']      =   @$ChildPrice-@$temp['Discount'];   } 
                    else { $temp['Discounted_CPrice']      =NULL; }

                }
                
                array_push($markup,$temp);
            }

            $minDiscountedPrice = INF;
            $minKey             = null;

            foreach ($markup as $key => $value) 
            {
                if ($value['Discounted_APrice'] < $minDiscountedPrice) 
                {
                    $minDiscountedPrice = $value['Discounted_APrice'];

                    $minKey = $key;

                    $arr['MarkupId']                =   $value['MarkupId'];
                    $arr['MarkupName']              =   $value['MarkupName'];
                    $arr['Discount']                =   $value['Discount'];
                   
                    $arr['Old_AdultPrice']          =   $AdultPrice;
                    $arr['New_AdultPrice']          =   @$value['Discounted_APrice'];
                    $arr['Old_ChildPrice']          =   $ChildPrice;
                    $arr['New_ChildPrice']          =   @$value['Discounted_CPrice'];
                    $arr['InfantPrice']             =   $InfantPrice;
                    
                    $arr['Adult']                   =   $Search_Field['Adults'];
                    $arr['Child']                   =   $Search_Field['Childrens'];
                    $arr['Infant']                  =   $Search_Field['Infants'];
                    
                    $arr['Sub_Adult']               =   $Search_Field['Adults']*@$value['Discounted_APrice'];
                    
                    if(@$value['Discounted_CPrice'])
                    {
                        $arr['Sub_Child']               =   $Search_Field['Childrens']*@$value['Discounted_CPrice'];
                    }
                    else
                    {
                        $arr['Sub_Child']               =   NULL;
                    }

                    $arr['Sub_Infant']              =   $Search_Field['Infants']*@$InfantPrice;
                    
                    $arr['TotalPrice']              =   @$arr['Sub_Adult']+@$arr['Sub_Child']+@$arr['Sub_Infant'];
                }
            }
            
        }
        else
        {
            $markup =   ''; 
            $arr    =   '';
        }

        $markupArray['Markup']      = $markup;
        $markupArray['MinMarkup']   = $arr;
         
        return $markupArray;
        
    }

    /* ---- Phase 2 helpers: rule type (discount / fixed / block) and advance purchase ---- */

    private function markupPhase2Ready(): bool
    {
        static $ready = null;

        if ($ready === null)
        {
            try
            {
                // Short cache: the new columns switch on within a minute of running the SQL.
                $ready = (bool) \Illuminate\Support\Facades\Cache::remember('markups_phase2_cols', 60, function () {
                    return \Illuminate\Support\Facades\Schema::hasColumn('markups','rule_action')
                        && \Illuminate\Support\Facades\Schema::hasColumn('markups','adv_min_days')
                        && \Illuminate\Support\Facades\Schema::hasColumn('markups','adv_max_days');
                });
            }
            catch (\Throwable $e)
            {
                $ready = false;
            }
        }

        return $ready;
    }

    public function isAirlineBlocked($markupDB, $airlines): bool
    {
        if (!is_array($markupDB) || empty($markupDB)) { return false; }

        $airlines = array_filter(array_map(function ($a) { return strtoupper(trim((string) $a)); }, (array) $airlines));
        if (empty($airlines)) { return false; }

        foreach ($markupDB as $r)
        {
            if (($r['rule_action'] ?? 'discount') !== 'block') { continue; }

            $codes = array_filter(array_map(function ($c) { return strtoupper(trim($c)); }, explode(',', (string) ($r['airline_code'] ?? ''))));

            if (!empty($codes) && !empty(array_intersect($codes, $airlines))) { return true; }
        }

        return false;
    }

    // Exact route match on the rule's code list (the SQL LIKE pre-filter can match substrings, and an
    // empty country code would match every country-scope rule). Airport rules compare the airport code,
    // country rules compare the country code; a missing code never matches.
    private function routeMatchesExactly($rule, $from, $fromC, $to, $toC): bool
    {
        $side = function ($type, $codes, $airport, $country) {
            $tokens = array_filter(array_map(function ($c) { return strtoupper(trim($c)); }, preg_split('/[\s,;|]+/', (string) $codes) ?: array()));
            $need   = strtoupper(trim((string) ($type === 'airport' ? $airport : ($type === 'country' ? $country : ''))));

            return $need !== '' && in_array($need, $tokens, true);
        };

        return $side($rule->departure_type, $rule->departure_code, $from, $fromC)
            && $side($rule->arrival_type, $rule->arrival_code, $to, $toC);
    }

    private function withoutBlockRules($markupDB): array
    {
        if (!is_array($markupDB)) { return array(); }

        return array_values(array_filter($markupDB, function ($r) { return ($r['rule_action'] ?? 'discount') !== 'block'; }));
    }

    // Fare-rule switches (config/fare_rules.php). Defaults are used when the file is missing or config is cached.
    private function fareRuleCfg($key, $default)
    {
        try { return config('fare_rules.'.$key, $default); } catch (\Throwable $e) { return $default; }
    }

    public function addReduceMarkupArray($Search_Field,$value_FData,$markupDB,$AllAirline)
    {
        // 1) To Get Where AllAirline is blank
        if($AllAirline=='')
        {
            $blankAirlineCodes  =    array_filter($markupDB, function ($item) 
            {
                return empty($item['airline_code']);
            });

            $markupDB       =   array_values($blankAirlineCodes);
        }
        else
        {
            $NotBlankAirlineCodes = array_filter($markupDB, function ($item)  use ($AllAirline) 
            {
                $airlineCodes = array_map('trim', explode(',', $item['airline_code']));
                return empty($item['airline_code']) || in_array($AllAirline, $airlineCodes);
            });

            $markupDB       = array_values($NotBlankAirlineCodes);
        }

        $temp   =   array();
        $markup =   array();

        if($markupDB)
        {
            $AdultPrice     =   @$value_FData['AdultPrice'];
            $ChildPrice     =   @$value_FData['ChildPrice'];
            $InfantPrice    =   @$value_FData['InfantPrice'];
            
            foreach($markupDB as $markupDBs)
            {
                $amt_per        =   $markupDBs['amt_per'];
                $ruleValue      =   floatval($markupDBs['markup']);
                $minFare        =   floatval(@$markupDBs['fare_min_price']);
                $AdultPrice     =   floatval($AdultPrice);
                $ChildPriceF    =   floatval($ChildPrice);
                $isFixed        =   (($markupDBs['rule_action'] ?? 'discount') === 'fixed');


                // Specificity: airport beats country, airline-specific beats all-airlines.
                $specificity    =   ((@$markupDBs['departure_type']==='airport') ? 30 : 10)
                                +   ((@$markupDBs['arrival_type']==='airport') ? 30 : 10)
                                +   ((trim((string)@$markupDBs['airline_code'])!=='') ? 20 : 0);

                // ONE pricing formula, identical on Website and FareLeader:
                //   percentage : every passenger fare (adult, child) is reduced by the percentage of ITS OWN fare
                //   amount     : every passenger fare (adult, child) is reduced by the same amount
                //   fixed fare : adult = the fixed amount, child scales in proportion
                //   infant     : never adjusted
                if($isFixed)
                {
                    $newAdult       =   $ruleValue;
                    $newChild       =   ($AdultPrice > 0) ? $ChildPriceF*($ruleValue/$AdultPrice) : $ChildPriceF;
                    $discountType   =   'Fixed $'.$markupDBs['markup'];
                }
                elseif($amt_per=='per')
                {
                    $newAdult       =   $AdultPrice-round(($ruleValue/100)*$AdultPrice,2);
                    $newChild       =   $ChildPriceF-round(($ruleValue/100)*$ChildPriceF,2);
                    $discountType   =   $markupDBs['markup'].'%';
                }
                else
                {
                    $newAdult       =   $AdultPrice-$ruleValue;
                    $newChild       =   $ChildPriceF-$ruleValue;
                    // A flat discount bigger than the child fare must not make the child free:
                    // then the child gets the same proportion as the adult.
                    if($ChildPriceF > 0 && $newChild <= 0 && $AdultPrice > 0) { $newChild = $ChildPriceF*(($AdultPrice-$ruleValue)/$AdultPrice); }
                    $discountType   =   '$'.$markupDBs['markup'];
                }

                // Minimum fare floor: a DISCOUNTED passenger fare (adult or child) never goes below the rule's
                // minimum fare. The floor never raises a fare above its original price and is not used by Fixed fare.
                if(!$isFixed && $minFare > 0 && $this->fareRuleCfg('enforce_min_fare',true))
                {
                    if($newAdult < $AdultPrice && $newAdult < $minFare) { $newAdult = min($AdultPrice,$minFare); }
                    if($ChildPriceF > 0 && $newChild < $ChildPriceF && $newChild < $minFare) { $newChild = min($ChildPriceF,$minFare); }
                }

                $newAdult       =   round($newAdult,2);
                $newChild       =   ($ChildPriceF > 0) ? round($newChild,2) : 0;

                // ZERO-FARE GUARD: a rule that would sell the adult below the minimum sell price (default $1)
                // or make a child fare zero/negative is skipped for this offer (never a $0 booking).
                if($newAdult < (float)$this->fareRuleCfg('min_sell_price',1)) { continue; }
                if($ChildPriceF > 0 && $newChild <= 0) { continue; }

                $discount       =   $AdultPrice-$newAdult;

                $temp                           =   array();
                $temp['MarkupId']               =   $markupDBs['id'];
                $temp['MarkupName']             =   @$markupDBs['name'];
                $temp['AdultPrice']             =   $AdultPrice;
                $temp['ChildPrice']             =   $ChildPrice;
                $temp['DiscountType']           =   $discountType;
                $temp['Discount']               =   $discount;
                $temp['Discounted_APrice']      =   $newAdult;
                $temp['Discounted_CPrice']      =   ($ChildPriceF > 0) ? $newChild : NULL;
                $temp['Specificity']            =   $specificity;

                array_push($markup,$temp);
            }

            $minDiscountedPrice = INF;
            $minKey             = null;
            $bestSpec           = -1;
            $useSpecificity     = (bool) $this->fareRuleCfg('most_specific_wins', true);
            $arr                = '';

            if(empty($markup))
            {
                // Every matching rule was skipped by a guard -> this offer keeps the normal supplier price.
                $markupDB = array();
            }

            foreach ($markup as $key => $value) 
            {
                if ($useSpecificity ? ($value['Specificity'] > $bestSpec || ($value['Specificity'] == $bestSpec && $value['Discounted_APrice'] < $minDiscountedPrice)) : ($value['Discounted_APrice'] < $minDiscountedPrice)) 
                {
                    $minDiscountedPrice = $value['Discounted_APrice'];
                    $bestSpec           = $value['Specificity'];

                    $minKey = $key;

                    $arr                            =   array();
                    $arr['MarkupId']                =   $value['MarkupId'];
                    $arr['Specificity']             =   $value['Specificity'];
                    $arr['MarkupName']              =   $value['MarkupName'];
                    $arr['Discount']                =   $value['Discount'];
                   
                    $arr['Old_AdultPrice']          =   $AdultPrice;
                    $arr['New_AdultPrice']          =   @$value['Discounted_APrice'];
                    $arr['Old_ChildPrice']          =   $ChildPrice;
                    $arr['New_ChildPrice']          =   @$value['Discounted_CPrice'];
                    $arr['InfantPrice']             =   $InfantPrice;
                    $arr['New_InfantPrice']         =   $InfantPrice;   // infant never adjusted (was shown as $0 in the breakdown)
                    
                    $arr['Adult']                   =   $Search_Field['Adults'];
                    $arr['Child']                   =   $Search_Field['Childrens'];
                    $arr['Infant']                  =   $Search_Field['Infants'];
                    
                    $arr['Sub_Adult']               =   $Search_Field['Adults']*@$value['Discounted_APrice'];
                    
                    if(@$value['Discounted_CPrice'])
                    {
                        $arr['Sub_Child']               =   $Search_Field['Childrens']*@$value['Discounted_CPrice'];
                    }
                    else
                    {
                        $arr['Sub_Child']               =   NULL;
                    }

                    $arr['Sub_Infant']              =   $Search_Field['Infants']*@$InfantPrice;
                    
                    $arr['TotalPrice']              =   @$arr['Sub_Adult']+@$arr['Sub_Child']+@$arr['Sub_Infant'];
                }
            }
            
        }
        else
        {
            $markup =   ''; 
            $arr    =   '';
        }

        $markupArray['Markup']      = $markup;
        $markupArray['MinMarkup']   = $arr;
        $markupArray['MarkupDB']    = $markupDB;
         
        return $markupArray;
    }

    public function makeFilterArray($filterArray,$resultData)
    {
        $FilterPrice        =   @$resultData['priceInfo']['Adult']['Price'];
        $FilterDptStop      =   @$resultData['DptFlightStop'];
        $FilterRtnStop      =   @$resultData['RtnFlightStop'];

        $FilterDptAirline   =   @$resultData['AllFlight']['Rph_1'][0]['Airline'];
        $FilterRtnAirline   =   @$resultData['AllFlight']['Rph_2'][0]['Airline'];

        $FilterDptAirport   =   @$resultData['AllFlight']['Rph_1'][0]['Departure_Iata'];
        $FilterRtnAirport   =   @$resultData['AllFlight']['Rph_2'][0]['Departure_Iata'];

        if(!isset($filterArray['FilterMinPrice']))
        {
            $filterArray['FilterMinPrice'] = PHP_INT_MAX;
        }

        if(!isset($filterArray['FilterMaxPrice']))
        {
            $filterArray['FilterMaxPrice'] = PHP_INT_MIN;
        }

        if(is_numeric($FilterPrice))
        {
            $FilterPrice = (float)$FilterPrice;

            if($FilterPrice < $filterArray['FilterMinPrice'])
            {
                $filterArray['FilterMinPrice'] = $FilterPrice;
            }

            if($FilterPrice > $filterArray['FilterMaxPrice'])
            {
                $filterArray['FilterMaxPrice'] = $FilterPrice;
            }
        }

        if(!isset($filterArray['FilterDptStop']) || !is_array($filterArray['FilterDptStop']))
        {
            $filterArray['FilterDptStop'] = array();
        }

        if($FilterDptStop !== null && $FilterDptStop !== '')
        {
            if(!in_array($FilterDptStop,$filterArray['FilterDptStop'],true))
            {
                array_push($filterArray['FilterDptStop'],$FilterDptStop);
            }
        }

        if(!isset($filterArray['FilterRtnStop']) || !is_array($filterArray['FilterRtnStop']))
        {
            $filterArray['FilterRtnStop'] = array();
        }

        if($FilterRtnStop !== null && $FilterRtnStop !== '')
        {
            if(!in_array($FilterRtnStop,$filterArray['FilterRtnStop'],true))
            {
                array_push($filterArray['FilterRtnStop'],$FilterRtnStop);
            }
        }

        if(!isset($filterArray['FilterDptAirline']) || !is_array($filterArray['FilterDptAirline']))
        {
            $filterArray['FilterDptAirline'] = array();
        }

        if(!empty($FilterDptAirline))
        {
            if(!in_array($FilterDptAirline,$filterArray['FilterDptAirline'],true))
            {
                array_push($filterArray['FilterDptAirline'],$FilterDptAirline);
            }
        }

        if(!isset($filterArray['FilterRtnAirline']) || !is_array($filterArray['FilterRtnAirline']))
        {
            $filterArray['FilterRtnAirline'] = array();
        }

        if(!empty($FilterRtnAirline))
        {
            if(!in_array($FilterRtnAirline,$filterArray['FilterRtnAirline'],true))
            {
                array_push($filterArray['FilterRtnAirline'],$FilterRtnAirline);
            }
        }

        if(!isset($filterArray['FilterDptAirport']) || !is_array($filterArray['FilterDptAirport']))
        {
            $filterArray['FilterDptAirport'] = array();
        }

        if(!empty($FilterDptAirport))
        {
            if(!in_array($FilterDptAirport,$filterArray['FilterDptAirport'],true))
            {
                array_push($filterArray['FilterDptAirport'],$FilterDptAirport);
            }
        }

        if(!isset($filterArray['FilterRtnAirport']) || !is_array($filterArray['FilterRtnAirport']))
        {
            $filterArray['FilterRtnAirport'] = array();
        }

        if(!empty($FilterRtnAirport))
        {
            if(!in_array($FilterRtnAirport,$filterArray['FilterRtnAirport'],true))
            {
                array_push($filterArray['FilterRtnAirport'],$FilterRtnAirport);
            }
        }

        return $filterArray;
    }

    public function makeFilterArrayLabel($filterArray,$airline,$airport)
    {
        $FilterDptStop_     =   array();
        $FilterRtnStop_     =   array();
        $matchedDptAirline  =   [];
        $matchedRtnAirline  =   [];
        $matchedDptAirport  =   [];
        $matchedRtnAirport  =   [];

        sort($filterArray['FilterDptStop']);
        sort($filterArray['FilterRtnStop']);
        
        $FilterDptStop      =   @$filterArray['FilterDptStop'];
        if($FilterDptStop)
        {
            foreach($FilterDptStop as $FilterDptStops)
            {
                if($FilterDptStops==0) { $label = "Direct"; }
                if($FilterDptStops==1) { $label = "1 Stop"; }
                if($FilterDptStops==2) { $label = "2 Stop"; }
                if($FilterDptStops==3) { $label = "3 Stop"; }

                $val    =  array('Code'=>  $FilterDptStops,'Value'=>  $label);
                array_push($FilterDptStop_,$val);
            }

            $filterArray['FilterDptStop'] = $FilterDptStop_;
        }

        $FilterRtnStop      =   @$filterArray['FilterRtnStop'];
        if($FilterRtnStop)
        {
            foreach($FilterRtnStop as $FilterRtnStops)
            {
                if($FilterRtnStops==0) { $label = "Direct"; }
                if($FilterRtnStops==1) { $label = "1 Stop"; }
                if($FilterRtnStops==2) { $label = "2 Stop"; }
                if($FilterRtnStops==3) { $label = "3 Stop"; }

                $val    =  array('Code'=>  $FilterRtnStops,'Value'=>  $label);
                array_push($FilterRtnStop_,$val);
            }

            $filterArray['FilterRtnStop'] = $FilterRtnStop_;
        }

        $FilterDptAirline   =   @$filterArray['FilterDptAirline'];
        if($FilterDptAirline)
        {
            foreach ($FilterDptAirline as $code) 
            {
                foreach ($airline as $airlines) 
                {
                    if ($airlines['Code'] === $code) 
                    {
                        $matchedDptAirline[] = [
                            'Code'  => $airlines['Code'],
                            'Value' => $airlines['Value']
                        ];
                        break;
                    }
                }
            }

            $filterArray['FilterDptAirline'] = $matchedDptAirline;
        }

        $FilterRtnAirline   =   @$filterArray['FilterRtnAirline'];
        if($FilterRtnAirline)
        {
            foreach ($FilterRtnAirline as $code) 
            {
                foreach ($airline as $airlines) 
                {
                    if ($airlines['Code'] === $code) 
                    {
                        $matchedRtnAirline[] = [
                            'Code'  => $airlines['Code'],
                            'Value' => $airlines['Value']
                        ];
                        break;
                    }
                }
            }

            $filterArray['FilterRtnAirline'] = $matchedRtnAirline;
        }

        $FilterDptAirport   =   @$filterArray['FilterDptAirport'];
        if($FilterDptAirport)
        {
            foreach ($FilterDptAirport as $code) 
            {
                foreach ($airport as $airports) 
                {
                    if ($airports['Iata'] === $code) 
                    {
                        $matchedDptAirport[] = [
                            'Code'  => $airports['Iata'],
                            'Value' => $airports['Name']." (".$airports['Iata'].")"
                        ];
                        break;
                    }
                }
            }

            $filterArray['FilterDptAirport'] = $matchedDptAirport;
        }

        $FilterRtnAirport   =   @$filterArray['FilterRtnAirport'];
        if($FilterRtnAirport)
        {
            foreach ($FilterRtnAirport as $code) 
            {
                foreach ($airport as $airports) 
                {
                    if ($airports['Iata'] === $code) 
                    {
                        $matchedRtnAirport[] = [
                            'Code'  => $airports['Iata'],
                            'Value' => $airports['Name']." (".$airports['Iata'].")"
                        ];
                        break;
                    }
                }
            }

            $filterArray['FilterRtnAirport'] = $matchedRtnAirport;
        }

        @$filterArray['FilterDptTime']   =   array( '0'=> array('Code'=>"00:00-05:59",'Value' => 'Before 6 AM'),
                                                    '1'=> array('Code'=>"06:00-12:00",'Value' => '6 AM to 12 PM'),
                                                    '2'=> array('Code'=>"12:01-18:00",'Value' => '12 PM to 6 PM'),
                                                    '3'=> array('Code'=>"18:01-23:59",'Value' => 'After 6 PM')
                                                    );

        @$filterArray['FilterRtnTime']   =   array( '0'=> array('Code'=>"00:00-05:59",'Value' => 'Before 6 AM'),
                                                    '1'=> array('Code'=>"06:00-12:00",'Value' => '6 AM to 12 PM'),
                                                    '2'=> array('Code'=>"12:01-18:00",'Value' => '12 PM to 6 PM'),
                                                    '3'=> array('Code'=>"18:01-23:59",'Value' => 'After 6 PM')
                                                    );

        // echo "<pre>";
        // print_r($filterArray);
        // die;

        return $filterArray;
    }

    /*  API Function */

    public function get_soap_error($result)
    {
        return isset($result->detail)
            ? ( isset($result->detail->GenericException)
                ? $result->detail->GenericException->Description
                : ( isset($result->detail->ExceptionDetail)
                    ? $result->detail->ExceptionDetail->Message
                    : 'Unknown error' ) )
            :  ( $result->faultstring != ''
                ? ( $result->faultstring == 'Error Fetching http headers'
                    ? 'Network error'
                    : $result->faultstring )
                : 'Unknown error' );
    }

    public function wsdl_request($method = '', $input = array())
    {
        $apiCredential  =   $this->getSettingAPiType($method);

        $return         =   array('status'=>'0','error'=>'','result'=>'');
        $error_text     =   '';

        $wsdl           =   $apiCredential['wsdl'];
        $soap           =   $apiCredential['soap'];
        
        $opts = [
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false
            ]
        ];
        
        $context        =   stream_context_create($opts);
        
        $connect_params = array(
            'soap_version'  => SOAP_1_2,
            'trace'         => true,
            'exceptions'    => true, // get detailed errors
            'cache_wsdl'    => WSDL_CACHE_MEMORY,
            'features'      => SOAP_USE_XSI_ARRAY_TYPE,
            'connection_timeout' => 120,
            'location'      =>  $soap,
            'compression'   => (SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP)
        );

        // L5-1: normal connect takes ~0.2 s; do not wait 120 s on a dead connection for calls that may be retried.
        if (in_array($method, self::FH_RETRY_METHODS, true)) {
            $connect_params['connection_timeout'] = 10;
        }
    
        // $connect_params = array(
        //     'soap_version'      =>  SOAP_1_2,
        //     'trace'             =>  true,
        //     'exceptions'        =>  false,
        //     'cache_wsdl'        =>  WSDL_CACHE_NONE,
        //     'features'          =>  SOAP_USE_XSI_ARRAY_TYPE,
        //     'connection_timeout'=>  120,
        //     'location'          =>  $soap,
        //     'uri'               =>  'http://tempuri.org/',
        //     'compression'       =>  (SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP),
        //     'stream_context'    =>  $context
        // );
        
        $soapRequestStartedAt = microtime(true);
        $soapClientStartedAt = microtime(true);

        try {
            $client     =   $this->fhMakeSoapClient($wsdl, $connect_params);
            $soapClientMs = (int) round((microtime(true) - $soapClientStartedAt) * 1000);
            $exception  =   "";
        } 
        catch (SoapFault $e) 
        {
            $exception  =   $e->getMessage();
        }
        
        if($exception || is_soap_fault($client))
            $error_text = is_object($client)
                            ? ( isset($client->detail) 
                                ? $client->detail->GenericException->Description
                                : $client->faultstring )
                            : '';
        
        if($error_text == '')
        { 
            $credentials = array(
                'ApiLogin'      => $apiCredential['ApiLogin'],
                'ApiPassword'   => $apiCredential['ApiPassword'],
                'DeviceId'      => $apiCredential['DeviceId'],
                'Currency'      => $apiCredential['Currency'],
                'Language'      => 'EN',
            );

            //USD

            $token                      =   $apiCredential['token'];
            $credentials['TokenGuid']   =   $token;

            $soapCallStartedAt = microtime(true);
            if (in_array($method, self::FH_RETRY_METHODS, true)) {
                // L5-1: timeout + one retry (no booking methods only)
                $result = $this->fhSoapCallWithRetry($client, $wsdl, $connect_params, $method, $input + array('credentials' => $credentials), $soapRequestStartedAt);

                if ($result === null) {
                    $return['status']    = '0';
                    $return['error']     = 'Our airline partner is not responding right now. Please try your search again in a minute.';
                    $return['errorCode'] = 'SUPPLIER_UNAVAILABLE';
                    $return['result']    = '';

                    return $return;
                }
            } else {
                $result = $client->$method($input + array('credentials' => $credentials));
            }
            $soapCallMs = (int) round((microtime(true) - $soapCallStartedAt) * 1000);

            Log::info('FARESHUNT_SOAP_PERFORMANCE', [
                'method' => $method,
                'soap_client_ms' => $soapClientMs ?? null,
                'soap_call_ms' => $soapCallMs,
                'soap_total_ms' => (int) round((microtime(true) - $soapRequestStartedAt) * 1000),
            ]);

            // print_r($result);
            // die;

            $return['status']       =   '0';
            $return['result']       =   '';

            if(is_soap_fault($result))
            {
                $error_text = $this->get_soap_error($result);

                $return['error']        =   $error_text;
                $return['errorCode']    =   @$result->ErrorCode;
            
            }
            elseif(!isset($result->{$method.'Result'}) || !isset($result->{$method.'Result'}->Success))
            {
                $error_text = 'Service error';

                $return['error']        =   $error_text;
                $return['errorCode']    =   @$result->ErrorCode;
            }
            else 
            {
                $result = $result->{$method.'Result'};
                // echo "<pre>";
                // print_r($result);
                     //  die;
                if(!$result->Success)
                {
                    $error_text = trim($result->ErrorString) == '' ? 'Unknown error' : htmlspecialchars($result->ErrorString);
                    
                    $return['status']       =   '0';
                    $return['error']        =   $error_text;
                    $return['errorCode']    =   @$result->ErrorCode;
                    $return['result']       =   '';
                }
                else
                {
                    $return['status']       =  '1';
                    $return['error']        =  '';
                    $return['errorCode']    =  '';
                    $return['result']       =  $result;
                }      
            }
        }
        else
        {
            $return['status']       = '0';
            $return['error']        = $error_text;
            $return['errorCode']    = '';
            $return['result']       = '';
        }

        //echo "<pre>";
        // print_r($return);
        // die;
        
        return $return;
        //return $error_text ?: $result;
    }

    public function wsdl_request_auth($method = '', $input = array())
    {
        $apiCredential  =   $this->getSettingAPiType($method);
        
        $error_text     =   '';
        $wsdl           =   $apiCredential['wsdl'];
        $soap           =   $apiCredential['wsdl'];
        
        $connect_params = array(
            'soap_version'      =>  SOAP_1_2,
            'trace'             =>  true,
            'exceptions'        =>  false,
            'cache_wsdl'        =>  WSDL_CACHE_MEMORY,
            'features'          =>  SOAP_USE_XSI_ARRAY_TYPE,
            'connection_timeout'=>  120,
            'location'          =>  $soap,
            'uri'               =>  'http://tempuri.org/',
            'compression'       =>  (SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP)
        );
        
        try {
            $client     =   new SoapClient($wsdl, $connect_params);
            $exception  =   "";
        } 
        catch (SoapFault $e) 
        {
            $exception  =   $e;
        }
        
        if($exception || is_soap_fault($client))
            $error_text = is_object($client)
                            ? ( isset($client->detail) 
                                ? $client->detail->GenericException->Description
                                : $client->faultstring )
                            : '';
        
        if($error_text == '')
        { 
            $credentials = array(
                'ApiLogin'      => $apiCredential['ApiLogin'],
                'ApiPassword'   => $apiCredential['ApiPassword'],
                'DeviceId'      => $apiCredential['DeviceId'],
                'Currency'      => $apiCredential['Currency'],
                'Language'      => 'EN',
            );

            //USD

            $token                      =   $apiCredential['token'];
            $credentials['TokenGuid']   =   $token;

            $result = $client->$method($input + array('authInfo' => $credentials));
            
            if(is_soap_fault($result))
                $error_text = $this->get_soap_error($result);
            
            elseif(!isset($result->{$method.'Result'}) || !isset($result->{$method.'Result'}->Success))
                $error_text = 'Service error';
            
            else 
            {
                
                $result = $result->{$method.'Result'};

                if(!$result->Success)
                    $error_text = trim($result->ErrorString) == ''
                                    ? 'Unknown error'
                                    : htmlspecialchars($result->ErrorString);
            }
        }
        
        return $error_text ?: $result;
    }

    public function SearchApi($code,$fields)
    {
        $params = array(
            'Adults'        => $fields['Adults'],
            'Childs'        => $fields['Childrens'],
            'Infants'       => $fields['Infants'],
            'FlightClass'   => $fields['FlightClass'],
        );

        // Backward compatible: One Way/Round Trip continue to use the same values.
        // Multi-City supplies an ordered SearchLegs array (2-5 legs).
        if (($fields['FlightType'] ?? '') === 'multi_city' && !empty($fields['SearchLegs'])) {
            $flights = array_map(function ($leg) {
                // Be defensive with sessions created before this hotfix.
                $leg = is_object($leg) ? (array) $leg : $leg;

                return [
                    'Date' => $leg['Date'] ?? '',
                    'IATAFrom' => $leg['IATAFrom'] ?? '',
                    'IATATo' => $leg['IATATo'] ?? '',
                ];
            }, (array) $fields['SearchLegs']);
        } else {
            $flights = [[
                'Date' => $fields['DeptDate'],
                'IATAFrom' => $fields['IATAFrom'],
                'IATATo' => $fields['IATATo'],
            ]];
            if (($fields['FlightType'] ?? '') === 'round_trip') {
                $flights[] = [
                    'Date' => $fields['RetnDate'],
                    'IATAFrom' => $fields['IATATo'],
                    'IATATo' => $fields['IATAFrom'],
                ];
            }
        }

        $params['SearchFlights']    =   $flights;

        $isMultiCityDiagnostic = (($fields['FlightType'] ?? '') === 'multi_city');

        if ($isMultiCityDiagnostic) {
            Log::info('FARESHUNT_MULTICITY_REQUEST', [
                'search_code' => $code,
                'flight_type' => $fields['FlightType'] ?? null,
                'adults' => $params['Adults'] ?? null,
                'children' => $params['Childs'] ?? null,
                'infants' => $params['Infants'] ?? null,
                'flight_class' => $params['FlightClass'] ?? null,
                'search_flights' => $params['SearchFlights'] ?? [],
            ]);
        }

        //$params['AeroFilters']['PreferedAirline']    =   'UA';
        //$params['AeroFilters']['MaxTransfers']    =   '0';
        
        $output                     =   $this->wsdl_request('AeroSearch', array('aeroSearchParams' => $params));

        if ($isMultiCityDiagnostic) {
            Log::info('FARESHUNT_MULTICITY_API_STATUS', [
                'search_code' => $code,
                'status' => $output['status'] ?? null,
                'error' => $output['error'] ?? null,
                'result_type' => isset($output['result']) ? gettype($output['result']) : null,
            ]);
        }

        // echo "<pre>";
        // print_r($output);
        // die;
        if(@$output['status']==1)
        {
            $result     =   $output['result'];
            
            if (!is_object($result) || !isset($result->FlightData) || !isset($result->FlightData->FlightData)) 
            {
                $error_text = (!is_object($result) && ($result != '')) ? $result : 'Flights not found';

                $output['status'] = '0';
                $output['error']  = $error_text;
                $output['result'] = '';
            }
            else
            {
                $result = json_decode(json_encode($result), true);

                if ($isMultiCityDiagnostic) {
                    /*
                     * Sanitized supplier-response diagnostic:
                     * only journey/segment routing fields are collected.
                     * No API credentials, card data, passenger data, or payment data.
                     */
                    $segmentSummary = [];

                    $walkSegments = function ($node, $path = 'root') use (&$walkSegments, &$segmentSummary) {
                        if (count($segmentSummary) >= 120 || !is_array($node)) {
                            return;
                        }

                        $hasRph = array_key_exists('Rph', $node);
                        $departure = $node['Departure'] ?? null;
                        $arrival = $node['Arrival'] ?? null;

                        if ($hasRph && is_array($departure) && is_array($arrival)) {
                            $segmentSummary[] = [
                                'path' => $path,
                                'rph' => $node['Rph'] ?? null,
                                'from' => $departure['Iata'] ?? null,
                                'to' => $arrival['Iata'] ?? null,
                                'departure_date' => $departure['Date'] ?? null,
                                'arrival_date' => $arrival['Date'] ?? null,
                                'marketing_airline' => $node['MarketingAirline'] ?? null,
                                'flight_num' => $node['FlightNum'] ?? null,
                            ];
                        }

                        foreach ($node as $key => $value) {
                            if (is_array($value)) {
                                $walkSegments($value, $path.'.'.$key);
                                if (count($segmentSummary) >= 120) {
                                    break;
                                }
                            }
                        }
                    };

                    $walkSegments($result);

                    $rphSummary = [];
                    foreach ($segmentSummary as $segment) {
                        $rphKey = 'Rph_'.(string)($segment['rph'] ?? 'unknown');
                        if (!isset($rphSummary[$rphKey])) {
                            $rphSummary[$rphKey] = [];
                        }
                        $rphSummary[$rphKey][] = [
                            'from' => $segment['from'],
                            'to' => $segment['to'],
                            'departure_date' => $segment['departure_date'],
                            'arrival_date' => $segment['arrival_date'],
                            'airline' => $segment['marketing_airline'],
                            'flight_num' => $segment['flight_num'],
                        ];
                    }

                    Log::info('FARESHUNT_MULTICITY_RESPONSE_SEGMENTS', [
                        'search_code' => $code,
                        'requested_legs' => $params['SearchFlights'] ?? [],
                        'segment_count_logged' => count($segmentSummary),
                        'rph_summary' => $rphSummary,
                    ]);
                }

                $output['result'] = $result;

                session([$code.".Search-Result" =>  $result]);
                session()->save();
            }
        }
        // print_r($result);
        // die;
        return $output;
    }

    public function PreBookApi($OfferCode,$SearchGuid)
    {
        $params['OfferCode']        =   $OfferCode;
        $params['SearchGuid']       =   $SearchGuid;

        $result                     =   $this->wsdl_request('AeroPrebook', array('aeroPrebookParams' => $params));
        
        return $result;
    }

    public function BookApi($params)
    {
        $result      =   $this->wsdl_request('AeroBook', array('aeroBookParams' => $params));
        
        return $result;
    }

    public function ConfirmApi($params)
    {
        $result                     =   $this->wsdl_request_auth('ConfirmBook', array('confirmParams' => $params));

        if (!is_object($result)) 
        {
            $error_text = (!is_object($result) && ($result != '')) ? $result : 'not confirm';
            
            if($error_text=='not confirm')
            {
                return $error_text;
            }
            else
            {
                return $error_text;
                //die;
            }
           
        } 

        if($result)
        {
            $result = json_decode(json_encode($result), true);

            session(['search_confirm_result' =>  $result]);
            session()->save();

            return $result;
        }

    }

    public function OrderInfoApi($params)
    {
        $result      =   $this->wsdl_request('OrderInfo', array('orderInfoParams' => $params));
        
        return $result;
    }

    /*  Check Functions */

    public function getSearchField($code)
    {
        $SearchField         =   Session::get($code.'.Search-Field');

        /*
         * Decode recursively as associative arrays.
         * Multi-City stores SearchLegs as nested JSON; a shallow cast leaves
         * each leg as stdClass, while SearchApi expects array keys.
         * One Way / Round Trip contain the same scalar keys as before.
         */
        $SearchFieldDecode   =   @json_decode($SearchField, true);

        return is_array($SearchFieldDecode) ? $SearchFieldDecode : array();
    }

    public function isSessionCode($code)
    {
        if(!Session::get($code.'.Search-Field'))
        {
            return false;
        }

        return true;
    }

    public function getArrResultLoop($AllAirPorts,$resultData,$key,$OfferSeg,$Rph,$Airlineinfo)
    {
        $tempArray['Rph']                   =  @$Rph; 
        $tempArray['Airline']               =  @$Airlineinfo['Airline']; 
        $tempArray['AirlineName']           =  @$Airlineinfo['AirlineName']; 
        $tempArray['AirlineLogo']           =  @$Airlineinfo['AirlineLogo']; 
        
        $tempArray['AirCraft']              =  @$OfferSeg['AirCraft'];  
        
        $tempArray['Departure_Iata']        =  @$OfferSeg['Departure']['Iata'];   
        $tempArray['Arrival_Iata']          =  @$OfferSeg['Arrival']['Iata'];   

        if(@$AllAirPorts)
        {
            $index1             =   array_search(@$OfferSeg['Departure']['Iata'], array_column($AllAirPorts, 'Iata'));
            if($index1 !== false) $tempArray['Departure_Name']    =  @$AllAirPorts[$index1]['Name'];
            
            $index2             =   array_search(@$OfferSeg['Arrival']['Iata'], array_column($AllAirPorts, 'Iata'));
            if($index2 !== false) $tempArray['Arrival_Name']    =  @$AllAirPorts[$index2]['Name'];      
        }
        
        $tempArray['Departure_Date']        =  @$OfferSeg['Departure']['Date'];   
        $tempArray['Arrival_Date']          =  @$OfferSeg['Arrival']['Date'];   
        
        $tempArray['Departure_Terminal']    =  @$OfferSeg['Departure']['Terminal'];   
        $tempArray['Arrival_Terminal']      =  @$OfferSeg['Arrival']['Terminal'];   
        
        $tempArray['FlightClass']           =  @$OfferSeg['FlightClass'];   
        $tempArray['FlightMinutes']         =  @$OfferSeg['FlightMinutes'];  
        $tempArray['FlightNum']             =  @$OfferSeg['FlightNum'];   
        $tempArray['FlightTime']            =  @$OfferSeg['FlightTime'];  

        $tempArray['Bag_BType']             =  @$OfferSeg['Baggage']['BaggageType'];   
        $tempArray['Bag_Count']             =  @$OfferSeg['Baggage']['Count'];   
        $tempArray['CabinBag_BType']        =  @$OfferSeg['CabinBaggage']['BaggageType'];  
        $tempArray['CabinBag_Count']        =  @$OfferSeg['CabinBaggage']['Count'];  
        
        $tempArray['SelfConnect']           =  @$OfferSeg['SelfConnect'];

        $rphKey = 'Rph_'.(int)$Rph;
        if (!isset($resultData[$key]['AllFlight'][$rphKey])) {
            $resultData[$key]['AllFlight'][$rphKey] = [];
        }
        $resultData[$key]['AllFlight'][$rphKey][] = $tempArray;

        // Keep all legacy filter/sort fields unchanged for legs 1 and 2.
        if ((int)$Rph === 1) {
            $resultData[$key]['DptFlightStop'] = ($resultData[$key]['DptFlightStop'] ?? 0) + 1;
            $resultData[$key]['FtrAirline'] = $resultData[$key]['AllFlight']['Rph_1'][0]['Airline'];
            $resultData[$key]['FtrDptTime'] = @date('H:i', @strtotime($resultData[$key]['AllFlight']['Rph_1'][0]['Departure_Date']));
        } elseif ((int)$Rph === 2) {
            $resultData[$key]['RtnFlightStop'] = ($resultData[$key]['RtnFlightStop'] ?? 0) + 1;
            $resultData[$key]['FtrRtnTime'] = @date('H:i', @strtotime($resultData[$key]['AllFlight']['Rph_2'][0]['Departure_Date']));
        }

        $resultData[$key]['AirlineList'][]          =   @$Airlineinfo['Airline'];

        return $resultData;
    }

    public function getSessionVal($code)
    {
        Session::get([$code.".Search-Field"]);
        Session::get([$code.".Search-Result"]); // Not use Further

        Session::get([$code.".Arrange-Result"]);

        Session::get([$code.".Filter-CheapestPrice"]);
        Session::get([$code.".Filter-HighestPrice"]);
        Session::get([$code.".Filter-ShorestPrice"]);
        Session::get([$code.".Filter-DptStop"]);
        Session::get([$code.".Filter-RtnStop"]);
        Session::get([$code.".Filter-DptTime"]);
        Session::get([$code.".Filter-RtnTime"]);
        Session::get([$code.".Filter-Airline"]);
        Session::get([$code.".Filter-AirPort"]);       
        
        Session::get([$code.".Search-ResultCount"]);
        Session::get([$code.".Search-SearchGuid"]);
        
        Session::get([$code.".Selected-OfferCode"]);
        Session::get([$code.".Selected-ArrayKey"]);
        Session::get([$code.".Selected-Result"]);

        Session::get([$code.".MarkupDB"]);
        Session::get([$code.".Markup"]);
        Session::get([$code.".Min-Markup"]);
        
        Session::get([$code.".Booking-Form"]);
        
        Session::get(["Booking-Error-Msz"]);
    }

    public function getSettingAPiType($method)
    {
        $settinginfo    =   Setting::where('name','api_type')->first();
        $settingApi     =   $settinginfo['value'];

        if($settingApi=='Demo')
        {
            $apiCredential['ApiLogin']       =   'test';
            $apiCredential['ApiPassword']    =   'test';
            $apiCredential['DeviceId']       =   'test';
            $apiCredential['Currency']       =   'EUR';
            $apiCredential['token']          =   '00000000-0000-0000-0000-000000000000';

            $apiCredential['wsdl']           =   'http://test-api.xml.agency/SiteCity?singlewsdl';
            $apiCredential['soap']           =   'http://test-api.xml.agency/SiteCity';
        }

        if($settingApi=='Live')
        {
            $apiCredential['ApiLogin']       =   'TravelVacations';
            $apiCredential['ApiPassword']    =   'jcfh48fhjsid84';
            $apiCredential['DeviceId']       =   'TravelVacations';
            $apiCredential['Currency']       =   'USD';
            $apiCredential['token']          =   '6a682c99-e404-46dd-aeaf-2452fdfdf25a';

            if($method=='AeroSearch')
            {
                $apiCredential['soap']       =   'https://search-api.city.travel/SiteCity';
            }
            else
            {
                $apiCredential['soap']       =   'https://api.city.travel/SiteCity';
            }

            $apiCredential['wsdl']           =   realpath(public_path('wsdl/SiteCity.wsdl'));
        }

        //print_r($apiCredential);
        return $apiCredential;
    }

    /*  Filter before insert in DB   */

    public function filterPassengers($PaxList, $originalPaxData = [])
    {
        $passengerInfo      =   array();

        if(!array_is_list(@$PaxList['PaxData']))
        {
            $temp1                     =   $PaxList['PaxData'];
            $PaxList                   =   array();
            $PaxList['PaxData'][0]     =   $temp1;
        }

        foreach($PaxList['PaxData'] as $keyP=>$o_PaxList_decodes)
        {
            $passengerInfo[$keyP]['AgeType']        =   @$o_PaxList_decodes['AgeType'];
            $passengerInfo[$keyP]['Age']            =   @$o_PaxList_decodes['Age'];
            $passengerInfo[$keyP]['Name']           =   @$o_PaxList_decodes['Name'];
            $passengerInfo[$keyP]['MiddleName']     =   @$o_PaxList_decodes['MiddleName'];
            $passengerInfo[$keyP]['Surname']        =   @$o_PaxList_decodes['Surname'];
            $passengerInfo[$keyP]['Title']          =   strlen(trim((string)($o_PaxList_decodes['Title'] ?? ''))) > 0 ? $o_PaxList_decodes['Title'] : ($originalPaxData[$keyP]['Title'] ?? '');
            //$passengerInfo[$keyP]['FullName']       =   @$o_PaxList_decodes['Name']." ".@$o_PaxList_decodes['MiddleName']." ".@$o_PaxList_decodes['Surname'];
            $passengerInfo[$keyP]['BirthDay']       =   @$o_PaxList_decodes['BirthDay'];
            $passengerInfo[$keyP]['GenderType']     =   @$o_PaxList_decodes['GenderType'];
        }

        // echo "<pre>";
        // print_r($passengerInfo);

        $passengerInfo_ =   @json_encode($passengerInfo);
  
        return $passengerInfo_;
    }

    public function filterSectors($OfferInfo)
    {
        //echo "<pre>";
        //print_r($OfferInfo);

        $sectorInfo = [];

        if($OfferInfo)
        {
            if(@$OfferInfo['Rph'])
            {
                $PNR             =   @$OfferInfo['PNR'];
                $OfferSegment    =   @$OfferInfo['Segments']['OfferSegment'];

                if(is_array($OfferSegment) && !array_is_list($OfferSegment))
                {
                    $sectorInfo =   $this->getArrfilterSectorsResult($sectorInfo,$OfferSegment,$OfferSegment['Rph'],$OfferSegment['MarketingAirline'],$OfferSegment['MarketingAirlineName'],$PNR);  
                }
                else
                {
                    foreach($OfferSegment as $key1=>$OfferSegments)
                    {
                        $sectorInfo =   $this->getArrfilterSectorsResult($sectorInfo,$OfferSegments,$OfferSegments['Rph'],$OfferSegments['MarketingAirline'],$OfferSegments['MarketingAirlineName'],$PNR);   
                    }
                }
            }
            else
            {
                foreach($OfferInfo as $value1)
                {
                    $PNR                =   $value1['PNR'];
                    $OfferSegment       =   $value1['Segments']['OfferSegment'];

                    if(is_array($OfferSegment) && !array_is_list($OfferSegment))
                    {  
                        $sectorInfo =   $this->getArrfilterSectorsResult($sectorInfo,$OfferSegment,$value1['Rph'],$OfferSegment['MarketingAirline'],$OfferSegment['MarketingAirlineName'],$PNR);
                    }
                    else
                    {
                        foreach($OfferSegment as $OfferSegments)
                        {
                            $sectorInfo =   $this->getArrfilterSectorsResult($sectorInfo,$OfferSegments,$value1['Rph'],$OfferSegments['MarketingAirline'],$OfferSegments['MarketingAirlineName'],$PNR);
                        }
                    }
                }
            } 
        }

        // echo "<pre>";
        // print_r($sectorInfo);

        $sectorInfo_ = [];
        foreach ($sectorInfo as $rphKey => $segments) {
            $sectorInfo_[$rphKey] = json_encode($segments);
        }
        $sectorInfo_['Itinerary'] = json_encode($sectorInfo);
        return $sectorInfo_;
    }

    public function filterCredit($request)
    {
        $pan = preg_replace('/\D/', '', (string) $request->creditcard_no);

        $CreditInfo                       =   array();
        $CreditInfo['creditcard_no']      =  $request->creditcard_no;
        $CreditInfo['card_brand']         =  $this->detectCardBrand($pan);
        $CreditInfo['card_last4']         =  $pan === '' ? '' : substr($pan, -4);
        $CreditInfo['cardholder_name']    =  $request->cardholder_name;
        $CreditInfo['expire_month']       =  $request->expire_month;
        $CreditInfo['expire_year']        =  $request->expire_year;
        $CreditInfo['cvv']                =  $request->cvv;

        return @json_encode($CreditInfo);
    }

    private function detectCardBrand(string $pan): string
    {
        if ($pan === '')                              return '';
        if (preg_match('/^4/', $pan))                 return 'Visa';
        if (preg_match('/^(5[1-5]|2[2-7])/', $pan))   return 'Mastercard';
        if (preg_match('/^3[47]/', $pan))             return 'American Express';
        if (preg_match('/^6(?:011|5)/', $pan))        return 'Discover';
        if (preg_match('/^3(?:0[0-5]|[68])/', $pan))  return 'Diners Club';
        if (preg_match('/^35/', $pan))                return 'JCB';
        return 'Card';
    }

    public function filterBilling($request)
    {
        $BillingInfo                            =   array();

        $BillingInfo['billing_address']         =  $request->billing_address;
        $BillingInfo['billing_country']         =  $request->billing_country;
        $BillingInfo['billing_state']           =  $request->billing_state;
        $BillingInfo['billing_state_97']        =  $request->billing_state_97;
        $BillingInfo['billing_city']            =  $request->billing_city;
        $BillingInfo['billing_pincode']         =  $request->billing_pincode;
        $BillingInfo['billing_mobile_telcode']  =  $request->billing_mobile_telcode;
        $BillingInfo['billing_mobile']          =  $request->billing_mobile;

        //echo "<pre>";
        //print_r($BillingInfo);

        $BillingInfo_ =   @json_encode($BillingInfo);
  
        return $BillingInfo_;
    }

    public function filterOther($request,$result,$infoMarkup)
    {
        $OtherInfo                      =   array();

        $OtherInfo['Currency']          =  $result['Currency'];
        $OtherInfo['Code']              =  $request['code'];
        $OtherInfo['OfferCode']         =  $request['OfferCode'];
        $OtherInfo['SearchGuid']        =  $result['SearchGuid'];
        $OtherInfo['BookGuid']          =  $result['BookGuid'];
        $OtherInfo['BookId']            =  $result['BookId'];
        $OtherInfo['ConfirmableTo']     =  $result['ConfirmableTo'];
        
        $infoMarkupDe                   =  @json_decode($infoMarkup,true);
        $OtherInfo['IsMarkup']          =  @$infoMarkupDe['IsMarkup'];
        $OtherInfo['MarkupId']          =  @$infoMarkupDe['MarkupId'];
        $OtherInfo['MarkupName']        =  @$infoMarkupDe['MarkupName'];
        $OtherInfo['MarkupDiscount']    =  @$infoMarkupDe['MarkupDiscount'];
        $OtherInfo['OldAdultPrice']     =  @$result['TariffInfo']['AdultPrice'];
        $OtherInfo['OldChildPrice']     =  @$result['TariffInfo']['ChildPrice'];
        $OtherInfo['OldInfantPrice']    =  @$result['TariffInfo']['InfantPrice'];
        $OtherInfo['OldTotalPrice']     =  @$result['FullPrice'];

        $OtherInfo_ =   @json_encode($OtherInfo);
  
        return $OtherInfo_;
    }

    public function filterPrice($priceMarkup,$service,$o_FullP,$fields=null)
    {
        // SECURITY: only the customer's Yes/No choices are taken from the browser.
        // Every add-on price is recalculated here from Settings / CRM "Booking Add-ons",
        // so editing the hidden "service" field can no longer change what is charged.
        $priceMarkup    =   is_array($priceMarkup) ? $priceMarkup : (array) json_decode((string) $priceMarkup, true);
        $client         =   is_array($service) ? $service : (array) json_decode((string) $service, true);
        $fields         =   is_array($fields) ? $fields : array();
        $setting        =   getAllSetting();

        if(isset($fields['Adults']))
        {
            $pax        =   max(1, (int) $fields['Adults'] + (int) ($fields['Childrens'] ?? 0));
            $way        =   (($fields['FlightType'] ?? '') === 'round_trip') ? 2 : 1;
        }
        else
        {
            // Search session not available: fall back to the counts on the form, bounded.
            $pax        =   min(9, max(1, (int) ($client['Web_Check_In']['Pax'] ?? $client['Cancellation']['Pax'] ?? 1)));
            $way        =   min(2, max(1, (int) ($client['Web_Check_In']['Way'] ?? 1)));
        }

        $wants          =   function ($key) use ($client) { return (($client[$key]['Enable'] ?? 'No') === 'Yes'); };
        $server         =   array();

        $webPrice       =   (float) ($setting['booking_web_checkin'] ?? 0);
        $server['Web_Check_In'] = [
            'Enable'    =>  ($wants('Web_Check_In') && ($setting['is_booking_web_checkin'] ?? 'ON') !== 'OFF') ? 'Yes' : 'No',
            'Price'     =>  $webPrice,
            'Pax'       =>  $pax,
            'Way'       =>  $way,
            'SubTotal'  =>  round($webPrice * $pax * $way, 2),
        ];

        $canPrice       =   (float) ($setting['booking_cancellation_protection'] ?? 0);
        $server['Cancellation'] = [
            'Enable'    =>  ($wants('Cancellation') && ($setting['is_booking_cancellation_protection'] ?? 'ON') !== 'OFF') ? 'Yes' : 'No',
            'Price'     =>  $canPrice,
            'Pax'       =>  $pax,
            'Way'       =>  $way,
            'SubTotal'  =>  round($canPrice * $pax, 2),
        ];

        // Premium add-ons (Travel Care, Lounge ...) - price from CRM master data, flat per booking
        // (same as shown to the customer on the booking page).
        try
        {
            if(\Illuminate\Support\Facades\Schema::hasTable('booking_addons'))
            {
                foreach(DB::table('booking_addons')->where('is_active', 1)->get() as $addon)
                {
                    if(!isset($client[$addon->key])) { continue; }
                    $server[$addon->key] = [
                        'Enable'    =>  $wants($addon->key) ? 'Yes' : 'No',
                        'Price'     =>  (float) $addon->price,
                        'SubTotal'  =>  round((float) $addon->price, 2),
                    ];
                }
            }
        }
        catch (\Throwable $e)
        {
            report($e);
        }

        $addonsTotal    =   0;
        foreach($server as $row)
        {
            if(($row['Enable'] ?? 'No') === 'Yes') { $addonsTotal += (float) $row['SubTotal']; }
        }

        $merged         =   array_merge($priceMarkup, $server);

        if($addonsTotal <= 0)
        {
            $newCost    =   $o_FullP;
        }
        else
        {
            unset($merged['Total']);
            $newCost            =   round((float) $o_FullP + $addonsTotal, 2);
            $merged['Total']    =   $newCost;
        }

        $priceInfo['ServiceP']  = @json_encode($merged);
        $priceInfo['FullP']     = $newCost;

        return $priceInfo;
    }



    public function getArrfilterSectorsResult($sectorInfo,$OfferSeg,$Rph,$Airline,$AirlineName,$PNR=null)
    {
        $airlineinfo                        =  Airline::where('code',$Airline)->first();
        
        $tempArray['PNR']                   =  @$PNR; 
        $tempArray['Rph']                   =  @$Rph; 
        $tempArray['Airline']               =  @$Airline; 
        $tempArray['AirlineName']           =  @$AirlineName; 
        $tempArray['AirlineLogo']           =  @$airlineinfo['logo']; 
        $tempArray['OperatingAirline']      =  @$OfferSeg['OperatingAirline'];
        
        $tempArray['AirCraft']              =  @$OfferSeg['AirCraft'];  
        
        $tempArray['Bag_BType']             =  @$OfferSeg['Baggage']['BaggageType'];   
        $tempArray['Bag_Count']             =  @$OfferSeg['Baggage']['Count'];   
        $tempArray['CabinBag_BType']        =  @$OfferSeg['CabinBaggage']['BaggageType'];  
        $tempArray['CabinBag_Count']        =  @$OfferSeg['CabinBaggage']['Count']; 
        
        $tempArray['Arrival_City']          =  @$OfferSeg['Arrival']['City'];   
        $tempArray['Arrival_Date']          =  @$OfferSeg['Arrival']['Date'];   
        $tempArray['Arrival_Iata']          =  @$OfferSeg['Arrival']['Iata'];   
        $tempArray['Arrival_Name']          =  @$OfferSeg['Arrival']['Name'];   
        $tempArray['Arrival_Terminal']      =  @$OfferSeg['Arrival']['Terminal'];   

        $tempArray['Departure_City']        =  @$OfferSeg['Departure']['City'];   
        $tempArray['Departure_Date']        =  @$OfferSeg['Departure']['Date'];   
        $tempArray['Departure_Iata']        =  @$OfferSeg['Departure']['Iata'];   
        $tempArray['Departure_Name']        =  @$OfferSeg['Departure']['Name'];   
        $tempArray['Departure_Terminal']    =  @$OfferSeg['Departure']['Terminal'];   

        $tempArray['FlightClass']           =  @$OfferSeg['FlightClass'];   
        $tempArray['FlightMinutes']         =  @$OfferSeg['FlightMinutes'];  
        $tempArray['FlightNum']             =  @$OfferSeg['FlightNum'];   
        $tempArray['FlightTime']            =  @$OfferSeg['FlightTime'];  

        $tempArray['SelfConnect']           =  @$OfferSeg['SelfConnect'];

        $rphKey = 'Rph_'.(int)$Rph;
        if (!isset($sectorInfo[$rphKey])) $sectorInfo[$rphKey] = [];
        $sectorInfo[$rphKey][] = $tempArray;

        return $sectorInfo;
    }

    public function DeleteTemp($flight_emailid,$flight_mobile)
    {
        Temp::where('emailid',$flight_emailid)->where('phone',$flight_mobile)->delete();
    }

    public function SendingMail($bookingid, array $context = [])
    {
        $setting = getAllSetting();
        $is_mail = $setting['is_mail'] ?? 'OFF';

        if ($is_mail !== 'ON') {
            return;
        }

        $bookinfo = Booking::find($bookingid);
        if (!$bookinfo || empty($bookinfo->o_Email)) {
            return;
        }

        $sectorGo = (array) json_decode((string) $bookinfo->o_SectorGo, true);
        $sectorBack = (array) json_decode((string) $bookinfo->o_SectorBack, true);
        $passengers = (array) json_decode((string) $bookinfo->o_Passenger, true);
        $price = (array) json_decode((string) $bookinfo->o_Price, true);
        $billing = (array) json_decode((string) $bookinfo->o_Billing, true);
        $other = (array) json_decode((string) $bookinfo->o_Other, true);

        $subject = 'Booking Confirmation - '.fh_booking_ref($bookinfo->id);

        try {
            $template = MailTemplate::find('3');
            if ($template && !empty($template->subject)) {
                $subject = str_replace(
                    '$bookingid',
                    fh_booking_ref($bookinfo->id),
                    $template->subject
                );
            }
        } catch (\Throwable $e) {
            // Premium confirmation email still works if the legacy template is unavailable.
        }

        $message = view('emails.booking-confirmation', [
            'booking' => $bookinfo,
            'setting' => $setting,
            'sectorGo' => $sectorGo,
            'sectorBack' => $sectorBack,
            'passengers' => $passengers,
            'price' => $price,
            'billing' => $billing,
            'other' => $other,
        ])->render();

        $source = $context['source'] ?? 'automatic_booking';
        $adminId = !empty($context['admin_id']) ? (int) $context['admin_id'] : null;

        try {
            // Mail-log fix (6 Nov 2026): sendSmptEmail() returns false instead of throwing,
            // so check the result - a failed email must be logged as failed, not "sent".
            $this->fhLastMailError = null;
            $sent = $this->sendSmptEmail($bookinfo->o_Email, $subject, $message);

            if ($sent !== true) {
                if (Schema::hasTable('crm_email_logs')) {
                    \Illuminate\Support\Facades\DB::table('crm_email_logs')->insert([
                        'booking_id' => $bookinfo->id,
                        'admin_id' => $adminId,
                        'email_type' => 'booking_confirmation',
                        'recipient' => $bookinfo->o_Email,
                        'subject' => $subject,
                        'status' => 'failed',
                        'source' => $source,
                        'error_message' => mb_substr((string) ($this->fhLastMailError ?: 'Email could not be sent.'), 0, 1000),
                        'sent_at' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                \Illuminate\Support\Facades\Log::warning('FARESHUNT_BOOKING_EMAIL_NOT_SENT', ['booking_id' => $bookinfo->id, 'source' => $source]);

                return false;
            }

            if (Schema::hasTable('crm_email_logs')) {
                \Illuminate\Support\Facades\DB::table('crm_email_logs')->insert([
                    'booking_id' => $bookinfo->id,
                    'admin_id' => $adminId,
                    'email_type' => 'booking_confirmation',
                    'recipient' => $bookinfo->o_Email,
                    'subject' => $subject,
                    'status' => 'sent',
                    'source' => $source,
                    'error_message' => null,
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return true;
        } catch (\Throwable $e) {
            if (Schema::hasTable('crm_email_logs')) {
                \Illuminate\Support\Facades\DB::table('crm_email_logs')->insert([
                    'booking_id' => $bookinfo->id,
                    'admin_id' => $adminId,
                    'email_type' => 'booking_confirmation',
                    'recipient' => $bookinfo->o_Email,
                    'subject' => $subject,
                    'status' => 'failed',
                    'source' => $source,
                    'error_message' => mb_substr($e->getMessage(), 0, 1000),
                    'sent_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            throw $e;
        }
    }

    public function sendSmptEmail($to,$subject,$message)
    {
        $mail = new PHPMailer(true);

        try
        {
            $mailer = (string) config('mail.default', 'smtp');
            if ($mailer !== 'smtp') {
                $this->fhLastMailError = 'Mailer is not SMTP ('.$mailer.').';
                return false;
            }

            $smtp = (array) config('mail.mailers.smtp', []);
            $host = (string) ($smtp['host'] ?? '');
            $port = (int) ($smtp['port'] ?? 587);
            $username = (string) ($smtp['username'] ?? '');
            $password = (string) ($smtp['password'] ?? '');
            $encryption = (string) ($smtp['encryption'] ?? 'tls');
            $fromAddress = (string) config('mail.from.address', '');
            $fromName = (string) config('mail.from.name', 'FaresHunt');
            $bcc = trim((string) config('mail.bcc.address', ''));

            if ($host === '' || $fromAddress === '' || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                $this->fhLastMailError = 'SMTP host or from address is not configured.';
                return false;
            }

            $mail->isSMTP();
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->SMTPAuth = ($username !== '');
            if ($mail->SMTPAuth) {
                $mail->Username = $username;
                $mail->Password = $password;
            }

            if ($encryption !== '' && strtolower($encryption) !== 'null') {
                $mail->SMTPSecure = $encryption;
            }

            $mail->setFrom($fromAddress, $fromName ?: 'FaresHunt');
            if ($bcc !== '' && filter_var($bcc, FILTER_VALIDATE_EMAIL)) {
                $mail->addBCC($bcc);
            }
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = trim(html_entity_decode(strip_tags(str_replace(['<br>','<br/>','<br />'], "
", $message))));
            $mail->send();
            return true;
        }
        catch (Exception $e)
        {
            $this->fhLastMailError = 'SMTP: '.mb_substr(trim((string) ($mail->ErrorInfo ?: $e->getMessage())), 0, 500);
            return false;
        }
    }

}