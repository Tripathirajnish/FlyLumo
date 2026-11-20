<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Services\SoapService;
use App\Services\FareleaderService;

use App\Models\MailTemplate;
use App\Models\Booking;
use App\Models\TelephoneCountryCode;
use App\Models\Country;
use App\Models\State;
use App\Models\Airport;

class FareleaderController extends Controller
{   
    protected $soapService;
    protected $fareleaderService;
    
    public function __construct(SoapService $soapService, FareleaderService $fareleaderService)
    {
        $this->soapService          =   $soapService;
        $this->fareleaderService    =   $fareleaderService;
    }
    
    public function coreJsonSearch(Request $request)
    {
        $expectedKey = (string) config('services.fareleaders_core.api_key', '');
        $providedKey = (string) $request->header('X-API-Key', '');

        if ($expectedKey === '' || $providedKey === '' ||
            !hash_equals($expectedKey, $providedKey)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->attributes->set("fareleaders_verified_source", "FareLeaders Core");

        return $this->jsonSearch($request);
    }

    public function uiSearch()
    {
        return view('fareleader.ui_search_form');
    }

    public function uiSearchSubmitForm(Request $request)
    {
        $data           =   $request->only(['origin1', 'destination1', 'departDate1', 'tripType', 'returnDate1', 'adults', 'childs', 'infants', 'classType', 'currency','metaName']);     
        $validate       =   $this->fareleaderService->checkValidation($data);
       
        // Validate 1: Required field
        if (!empty($validate['missingFields'])) 
        {
            $missing_fields =   json_encode([
                                    'error'             => 'Missing required fields',
                                    'missing_fields'    => $validate['missingFields']
                                ]);
            
            echo $missing_fields;
        }
        
        // Validate 2: Invalid types
        if (!empty($validate['invalidFields'])) 
        {
            $invalidFields =   json_encode([
                                    'error'             => 'Invalid data types',
                                    'invalid_fields'    => $validate['invalidFields']
                                ]);

            echo $invalidFields;
        }

        if (empty($validate['missingFields']) && empty($validate['invalidFields']) ) 
        {
            $fields             =   $this->fareleaderService->getFormField($data);  // DB Airport 2 query
            //dd($fields);
            $markup             =   $this->fareleaderService->checkMarkup($fields); // DB Markup 1 query
            // echo "<pre>";
            // var_dump($markup);
            // dd($markup);

            if($markup)
            {
                $output         =   $this->soapService->SearchApi($fields);
    
                if(@$output['status']=='0')
                {
                    echo json_encode(['error'    =>   @$output['error']]);
                    $filterResult   =   '';
                }
                else
                {
                    $result                 =   @$output['result'];
                    // echo "<pre>";
                    // print_r($result);
                    // die;
                    
                    $code                   =   @md5(@mt_rand(10000,99999).time() . 'holidaytrips.us');
                    $filterResult           =   $this->fareleaderService->arrayRearrange($fields,$markup,$result,$code,'Yes');
                    // echo "<pre>";
                    // print_r($filterResult);
                    // die;
                    $filterResult           =   $this->fareleaderService->matchBothAirports($fields,$filterResult);

                    if(@$filterResult['flightItineraries'])
                    {
                        @$filterResult['flightItineraries']  =   @array_slice(@$filterResult['flightItineraries'], 0, 100);    // Get only 100 Result
                    }
                    // echo "<pre>";
                    // print_r($filterResult);
                    // die;
                    $filterResult['flightItineraries'] = array_values($filterResult['flightItineraries']);
                }
            }
            else
            {
                echo json_encode(['error'    =>   'No Markup is found']);
                $filterResult   =   '';
            }
        }
        else
        {
            $filterResult   =   '';
        }

        $form_fields     =   array('tripType'        => @$request->tripType, 
                                'adults'            => @$request->adults, 
                                'childs'            => @$request->childs, 
                                'infants'           => @$request->infants, 
                                'classType'         => @$request->classType, 
                                'origin1'           => @$request->origin1, 
                                'destination1'      => @$request->destination1, 
                                'IATAFromC'         => @$request->IATAFromC, 
                                'IATAToC'           => @$request->IATAToC, 
                                'departDate1'       => @$request->departDate1, 
                                'returnDate1'       => @$request->returnDate1,
                                'currency'          => @$request->currency
                            );
        
        $info               =   compact('form_fields','filterResult');
        
        return view('fareleader.ui_search_form')->with($info);
    }

    public function jsonSearch(Request $request)
    {
        $data               =   $request->only(['origin1', 'destination1', 'departDate1', 'tripType', 'returnDate1', 'adults', 'childs', 'infants', 'classType', 'currency','metaName']);     
        $validate           =   $this->fareleaderService->checkValidation($data);
       
        // Validate 1: Required field
        if (!empty($validate['missingFields'])) 
        {
            return response()->json([
                'error'             => 'Missing required fields',
                'missing_fields'    => $validate['missingFields']
            ], 400);
        }
        
        // Validate 2: Invalid types
        if (!empty($validate['invalidFields'])) 
        {
            return response()->json([
                'error'             => 'Invalid data types',
                'invalid_fields'    => $validate['invalidFields']
            ], 400);
        }
        
        $fields             =   $this->fareleaderService->getFormField($data);
        $markup             =   $this->fareleaderService->checkMarkup($fields);

        if($markup)
        {
            $output         =   $this->soapService->SearchApi($fields);

            if(@$output['status']=='0')
            {
                return response()->json([
                    'error'    =>   @$output['error']
                ], 400);
            }
            else
            {
                $result                 =   @$output['result'];

                $code                   =   @md5(@mt_rand(10000,99999).time() . 'holidaytrip.in');
                $filterResult           =   $this->fareleaderService->arrayRearrange($fields,$markup,$result,$code,'No');
                $filterResult           =   $this->fareleaderService->matchBothAirports($fields,$filterResult);
                
                if(@$filterResult['flightItineraries'])
                {
                    @$filterResult['flightItineraries']  =   @array_slice(@$filterResult['flightItineraries'], 0, 50);    // Get only 100 Result
                }
                
                $filterResult['flightItineraries'] = array_values($filterResult['flightItineraries']);

                return response()->json([
                    'flightItineraries'    => $filterResult['flightItineraries']
                ], 200, [], JSON_UNESCAPED_SLASHES);
            }
        }
        else
        {
            return response()->json(['flightItineraries' =>  ""], 200);
        }
    }

    public function listSearch(Request $request)
    {
        $data               =   $request->only(['origin1', 'destination1', 'departDate1', 'tripType', 'returnDate1', 'adults', 'childs', 'infants', 'classType', 'currency','metaName']);     
        
        // echo "<pre>";
        // print_r($data);

        $source             =   'FareLeader';
        $cabinMap           =   [ 'Y' => 'Econom','W' => 'PremiumEconomy', 'C' => 'Business','F' => 'First' ];

        $data['adults']     =   (int) @$data['adults'];
        $data['childs']     =   (int) @$data['childs'];
        $data['infants']    =   (int) @$data['infants'];

        if(!@$data['adults'] || !is_int(@$data['adults']))       {  $data['adults']  = 0; }
        if(!@$data['childs'] || !is_int(@$data['childs']))       {  $data['childs']  = 0; }
        if(!@$data['infants'] || !is_int(@$data['infants']))     {  $data['infants'] = 0; }
        
        $FlightType         =   (@$data['tripType'] == 'roundTrip' || @$data['tripType'] == 'round_trip') ? 'round_trip' : 'one_way';
        $Adults             =   @$data['adults'];
        $Childrens          =   @$data['childs'];
        $Infants            =   @$data['infants'];
        $FlightClass        =   @$cabinMap[@$data['classType']];
        $IATAFrom           =   @$data['origin1'];
        $IATATo             =   @$data['destination1'];
        $IATAFromC          =   '';
        $IATAToC            =   '';
        $DeptDate           =   @date('d.m.Y',@strtotime(@$data['departDate1']));
        $RetnDate           =   (@$data['returnDate1'] == '') ? '' : @date('d.m.Y',@strtotime(@$data['returnDate1']));

        $IATAFrom           =   @strtoupper(@substr($IATAFrom,0,3));
        $IATATo             =   @strtoupper(@substr($IATATo,0,3));
        
        $IATAFromInfo       =   Airport::where('code',$IATAFrom)->first();
        $IATAToInfo         =   Airport::where('code',$IATATo)->first();
        
        if($IATAFromInfo)
        {
            if(@$IATAFromInfo->country) { $IATAFromC =  $IATAFromInfo->country->code; }
        }

        if($IATAToInfo)
        {
            if(@$IATAToInfo->country) { $IATAToC    =  $IATAToInfo->country->code; }
        }

        $Currency           =   @$data['currency'];


        $field             =   array(
                                'Source'           => $source, 
                                'FlightType'       => $FlightType, 
                                'Adults'           => $Adults, 
                                'Childrens'        => $Childrens, 
                                'Infants'          => $Infants, 
                                'FlightClass'      => $FlightClass, 
                                'IATAFromFull'     => $IATAFrom, 
                                'IATAToFull'       => $IATATo, 
                                'IATAFrom'         => $IATAFrom, 
                                'IATATo'           => $IATATo, 
                                'IATAFromC'        => $IATAFromC, 
                                'IATAToC'          => $IATAToC, 
                                'DeptDate'         => $DeptDate, 
                                'RetnDate'         => $RetnDate,
                                'Currency'         => $Currency
                            );

        //print_r($field);

        $fieldEncoded   =   @json_encode($field, true);
            
        $code           =   @md5(@mt_rand(10000,99999).time() . 'holidaytrip.in');
            
        session([$code.".Search-Field" =>  $fieldEncoded]);
        session()->save();

        //echo request()->getSchemeAndHttpHost()."/flight-listing/$code";

        return redirect("flight-listing/$code");
    }

    public function flightBooking(Request $request)
    {
        // $bookingid = 11005;
        // $this->__SendingMail($bookingid);
        // echo "Testing Mode for Email";
        // die;
        
        $SearchGuid         =   $request->query('searchguid');
        $OfferCode          =   $request->query('offercode');
        $encryptedPriceinfo =   $request->query('priceinfo');
        $encryptedFieldInfo =   $request->query('field');
        $code               =   bin2hex(random_bytes(16));
        $key                =   '1';
        
        // SECURITY: only links created (and signed) by this site are accepted. An edited or forged
        // link fails the signature check and is rejected, so the price cannot be changed via the URL.
        $PriceInfo          =   $this->fareleaderService->decryptSigned($encryptedPriceinfo);
        $FieldInfo          =   $this->fareleaderService->decryptSigned($encryptedFieldInfo);

        if($PriceInfo === false || $FieldInfo === false)
        {
            \Illuminate\Support\Facades\Log::warning('FARESHUNT_FARELEADER_DEEPLINK_REJECTED', ['ip_hash' => hash('sha256', (string) $request->ip())]);
            return redirect('/not-found-page');
        }

        $PriceInfos         =   @explode(',',$PriceInfo);
        $assocArray         =   [];

        foreach ($PriceInfos as $pair) 
        {
            $parts = @explode(':', $pair, 2);

            if (isset($parts[1])) 
            {
                $keyy               = @$parts[0];
                $value              = @$parts[1];
                $PriceInfoss[$keyy] = @$value;
            }
            else
            {
                // stop the code.
            }
        }

        if(@$PriceInfoss)
        {
            $Ps                                  =   @explode('+',@$PriceInfoss['Ps']);

            $priceMarkup['Adult']['Price']       =   @$PriceInfoss['A'];
            $priceMarkup['Adult']['Number']      =   @$Ps[0];
            $priceMarkup['Adult']['SubTotal']    =   @$PriceInfoss['A']*@$Ps[0];
    
            $priceMarkup['Child']['Price']       =   @$PriceInfoss['C'];
            $priceMarkup['Child']['Number']      =   @$Ps[1];
            $priceMarkup['Child']['SubTotal']    =   @$PriceInfoss['C']*@$Ps[1];
    
            $priceMarkup['Infant']['Price']      =   @$PriceInfoss['I'];
            $priceMarkup['Infant']['Number']     =   @$Ps[2];
            $priceMarkup['Infant']['SubTotal']   =   @$PriceInfoss['I']*@$Ps[2];
    
            $priceMarkup['Total']                =   @$PriceInfoss['T'];
    
            $infoMarkup['OldAdultPrice']         =   '';
            $infoMarkup['OldChildPrice']         =   '';
            $infoMarkup['OldInfantPrice']        =   '';
            
            $infoMarkup['IsMarkup']              =   "Yes";
            $infoMarkup['MarkupId']              =   @$PriceInfoss['MId'];
            $infoMarkup['MarkupName']            =   @$PriceInfoss['MN'];
            $infoMarkup['MarkupDiscount']        =   @$PriceInfoss['Dis'];
        }

        if(@$SearchGuid!='' && @$OfferCode!='' && @$PriceInfo!='' && @$FieldInfo!='')
        {
            $fields         =   @json_decode(@$FieldInfo, true);

            $output         =   $this->soapService->PreBookApi($OfferCode,$SearchGuid); 
            $output         =   @json_decode(@json_encode($output), true);

            $status         =   @$output['status'];
            $error          =   @$output['error'];
            $result         =   @$output['result'];

            if(@$status=='1') 
            {
                if(@$result['Success']=='1')
                {
                    $Offer          =   $result['Offers']['OfferInfo'];
                    $resultSelected =   $this->fareleaderService->arrayRearrangePreBookingResult($Offer);
                    
                    session(["Fareleader.infoMarkup"        =>  @$infoMarkup]);
                    session(["Fareleader.priceMarkup"       =>  @$priceMarkup]);
                    // Per-booking copy (keyed by this page's code) - the booking submit reads the price from here.
                    session([$code.".FL-infoMarkup"         =>  @$infoMarkup]);
                    session([$code.".FL-priceMarkup"        =>  @$priceMarkup]);
                    session([$code.".FL-OfferCode"          =>  $OfferCode]);
                    session([$code.".FL-SearchGuid"         =>  $SearchGuid]);
                    // Stripe (FL-B): raw supplier FullPrice of this PreBook. Stripe checkout only
                    // starts if a fresh PreBook still returns the same price. Legacy flow ignores it.
                    session([$code.".FL-SupplierFullPrice"  =>  @$result['FullPrice']]);
                    session([$code.".Search-Field"          =>  $FieldInfo]);
                    $partnerSource = $fields["FareLeadersSource"] ?? null;
                    if (in_array($partnerSource, ["FareLeaders Core", "FareLeaders Compare"], true)) {
                        session([$code.".FL-partner-source" => $partnerSource]);
                    }
                    
                    $Booking_Form       =   Session::get("Fareleader.Booking-Form");
                    // $info            =   compact('Booking_Form','code','OfferCode','SearchGuid','key','fields','resultSelected');
                    
                    $telcode            =   TelephoneCountryCode::get();
                    $country            =   Country::get();
                    $state              =   State::get();
                    
                    $key = 1;
                    $info               =   compact('Booking_Form','resultSelected','key','OfferCode','SearchGuid','encryptedPriceinfo','encryptedFieldInfo','code','key','fields','priceMarkup','infoMarkup','telcode','country','state');
                    
                    return view('fareleader_flight_booking')->with($info);
                }

                // FIX (deeplink, FareLeaders CORE only): supplier said the offer is no longer valid.
                if ($this->isCoreDeeplink($fields)) {
                    return $this->deeplinkFallback('prebook_not_success', $fields, $OfferCode, $SearchGuid, @$result['ErrorString'] ?? null);
                }
                echo 'Something went wrong.';
            }
            else
            {
                // FIX (deeplink, FareLeaders CORE only): PreBook call itself failed.
                if ($this->isCoreDeeplink($fields)) {
                    return $this->deeplinkFallback('prebook_failed', $fields, $OfferCode, $SearchGuid, $error);
                }
                echo 'Something went wrong.';
            }
        }
        else
        {
            // FIX (deeplink, FareLeaders CORE only): searchguid / offercode missing from the link.
            $coreFields = @json_decode((string) @$FieldInfo, true);
            if ($this->isCoreDeeplink($coreFields)) {
                return $this->deeplinkFallback('missing_params', $coreFields, $OfferCode, $SearchGuid, null);
            }
            echo "Something went wrong.";
        }
    }

    /**
     * Core vs Compare: the signed "field" parameter carries FareLeadersSource, set at search time
     * (Core = authenticated /api/core/jsonsearch). Compare links and older links without this
     * value return false, so they keep the original behaviour untouched.
     */
    private function coreRecoverySearchData(array $fields): ?array
    {
        if (!$this->isCoreDeeplink($fields)) {
            return null;
        }

        $cabinMap = [
            'Econom' => 'Y',
            'PremiumEconomy' => 'W',
            'Business' => 'C',
            'First' => 'F',
        ];

        $flightType = $fields['FlightType'] ?? '';
        $cabin = $fields['FlightClass'] ?? '';

        if (!in_array($flightType, ['oneway', 'one_way', 'round_trip'], true)
            || !isset($cabinMap[$cabin])) {
            return null;
        }

        $departure = \DateTimeImmutable::createFromFormat(
            '!d.m.Y',
            (string) ($fields['DeptDate'] ?? '')
        );

        if (!$departure || $departure->format('d.m.Y') !== ($fields['DeptDate'] ?? '')) {
            return null;
        }

        $data = [
            'origin1' => strtoupper((string) ($fields['IATAFrom'] ?? '')),
            'destination1' => strtoupper((string) ($fields['IATATo'] ?? '')),
            'departDate1' => $departure->format('d-M-Y'),
            'tripType' => $flightType === 'round_trip' ? 'round_trip' : 'oneway',
            'adults' => (string) ($fields['Adults'] ?? ''),
            'childs' => (string) ($fields['Childrens'] ?? ''),
            'infants' => (string) ($fields['Infants'] ?? ''),
            'classType' => $cabinMap[$cabin],
            'currency' => (string) ($fields['Currency'] ?? ''),
            'metaName' => 'fareleaders',
        ];

        if ($flightType === 'round_trip') {
            $return = \DateTimeImmutable::createFromFormat(
                '!d.m.Y',
                (string) ($fields['RetnDate'] ?? '')
            );

            if (!$return || $return->format('d.m.Y') !== ($fields['RetnDate'] ?? '')) {
                return null;
            }

            $data['returnDate1'] = $return->format('d-M-Y');
        }

        $validation = $this->fareleaderService->checkValidation($data);

        if (!empty($validation['missingFields']) || !empty($validation['invalidFields'])) {
            return null;
        }

        return $data;
    }

    private function coreRecoveryFreshOffers(array $signedFields): array
    {
        $data = $this->coreRecoverySearchData($signedFields);

        if ($data === null) {
            return [];
        }

        $fields = $this->fareleaderService->getFormField($data);
        $markup = $this->fareleaderService->checkMarkup($fields);

        if (!$markup) {
            return [];
        }

        $output = $this->soapService->SearchApi($fields);

        if (!is_array($output) || (string) ($output['status'] ?? '0') !== '1') {
            return [];
        }

        $result = $output['result'] ?? null;

        if (!is_array($result)
            || empty($result['FlightData'])
            || empty($result['FlightData']['FlightData'])) {
            return [];
        }

        $code = bin2hex(random_bytes(16));

        // The source was verified from the original signed Core field.
        // This attribute is needed by the existing signed-link generator.
        $request = request();
        $previousSource = $request->attributes->get('fareleaders_verified_source');

        try {
            $request->attributes->set('fareleaders_verified_source', 'FareLeaders Core');

            $offers = $this->fareleaderService->arrayRearrange(
                $fields, $markup, $result, $code, 'No'
            );
        } finally {
            if ($previousSource === null) {
                $request->attributes->remove('fareleaders_verified_source');
            } else {
                $request->attributes->set('fareleaders_verified_source', $previousSource);
            }
        }

        $offers = $this->fareleaderService->matchBothAirports($fields, $offers);

        return array_slice(
            array_values($offers['flightItineraries'] ?? []),
            0,
            20
        );
    }

    private function isCoreDeeplink($fields): bool
    {
        return is_array($fields) && (($fields['FareLeadersSource'] ?? null) === 'FareLeaders Core');
    }

    /**
     * FIX (deeplink): dedicated log file storage/logs/fareleader-deeplink.log.
     * Independent of LOG_CHANNEL / LOG_LEVEL in .env, so these lines are always written.
     */
    private function deeplinkLog()
    {
        return \Illuminate\Support\Facades\Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/fareleader-deeplink.log'),
            'level'  => 'debug',
        ]);
    }

    /**
     * FIX (deeplink): when a valid signed link can no longer be booked (expired SearchGuid, sold-out
     * offer, missing params), log the reason and send the customer to a FRESH search for the same
     * route/dates/passengers instead of a blank "Something went wrong." page.
     */
    private function deeplinkFallback(string $reason, $fields, $offerCode, $searchGuid, $supplierError)
    {
        // Core recovery rollout: keep disabled until fresh-search and selection tests pass.
        $coreRecoveryEnabled = false;

        if ($coreRecoveryEnabled && $this->isCoreDeeplink($fields)) {
            $recoveryKey = 'fareleader_core_recovery_' . hash(
                'sha256',
                (string) $searchGuid
            );

            if (session()->has($recoveryKey)) {
                return response()
                    ->view('fareleader_core_recovery', [
                        'offers' => [],
                    ])
                    ->header('Cache-Control', 'no-store, private');
            }

            session()->put($recoveryKey, true);
            session()->save();

            $offers = $this->coreRecoveryFreshOffers($fields);

            return response()
                ->view('fareleader_core_recovery', [
                    'offers' => $offers,
                ])
                ->header('Cache-Control', 'no-store, private');
        }

        $this->deeplinkLog()->warning('FARESHUNT_FARELEADER_DEEPLINK_PREBOOK_FAILED', [
            'reason'         => $reason,
            'supplier_error' => is_scalar($supplierError) ? mb_substr((string) $supplierError, 0, 300) : null,
            'searchguid'     => is_string($searchGuid) ? $searchGuid : null,
            'offercode_len'  => is_string($offerCode) ? strlen($offerCode) : 0,
            'offercode_has_space' => is_string($offerCode) && str_contains($offerCode, ' '),
            'route'          => is_array($fields) ? (($fields['IATAFrom'] ?? '').'-'.($fields['IATATo'] ?? '')) : null,
        ]);

        if (!is_array($fields) || empty($fields['IATAFrom']) || empty($fields['IATATo']) || empty($fields['DeptDate'])) {
            return redirect('/not-found-page');
        }

        // Departure already passed -> nothing to re-search.
        $dept = strtotime((string) $fields['DeptDate']);
        if ($dept === false || $dept < strtotime(date('Y-m-d'))) {
            return redirect('/not-found-page');
        }

        // Normalise the meta values to the website listing values.
        $flightType = in_array($fields['FlightType'] ?? '', ['round_trip', 'roundTrip'], true) ? 'round_trip' : 'one_way';
        $cabinMap   = ['PremiumEconomy' => 'Peconom', 'First' => 'firstclass'];
        $cabin      = $cabinMap[$fields['FlightClass'] ?? ''] ?? ($fields['FlightClass'] ?? 'Econom');

        $search = [
            'Source'       => 'FareLeader',
            'FlightType'   => $flightType,
            'Adults'       => (int) ($fields['Adults'] ?? 1) ?: 1,
            'Childrens'    => (int) ($fields['Childrens'] ?? 0),
            'Infants'      => (int) ($fields['Infants'] ?? 0),
            'FlightClass'  => $cabin,
            'IATAFromFull' => $fields['IATAFrom'],
            'IATAToFull'   => $fields['IATATo'],
            'IATAFrom'     => $fields['IATAFrom'],
            'IATATo'       => $fields['IATATo'],
            'IATAFromC'    => $fields['IATAFromC'] ?? '',
            'IATAToC'      => $fields['IATAToC'] ?? '',
            'DeptDate'     => $fields['DeptDate'],
            'RetnDate'     => $flightType === 'round_trip' ? ($fields['RetnDate'] ?? '') : '',
            'Currency'     => $fields['Currency'] ?? 'USD',
        ];

        $code = bin2hex(random_bytes(16));
        session([$code.'.Search-Field' => json_encode($search)]);
        session([$code.'.FL-fare-expired' => true]);
        session()->save();

        return redirect('flight-listing/'.$code)
            ->with('error', 'The fare you selected is no longer available. Here are the latest fares for the same trip.');
    }

}
