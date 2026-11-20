<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Airline;
use App\Models\Markup;

class FareleaderService
{  
    public function getFormField($data)
    {        
        $cabinMap           =   [ 'Y' => 'Econom','W' => 'PremiumEconomy', 'C' => 'Business','F' => 'First' ];

        if(!@$data['adults'])       {  $data['adults']  = 0; }
        if(!@$data['childs'])       {  $data['childs']  = 0; }
        if(!@$data['infants'])      {  $data['infants'] = 0; }
        
        $FlightType         =   in_array(@$data['tripType'], ['roundTrip','round_trip']) ? 'round_trip' : 'oneway';
        $Adults             =   @$data['adults'];
        $Childrens          =   @$data['childs'];
        $Infants            =   @$data['infants'];
        $FlightClass        =   $cabinMap[@$data['classType']];
        $IATAFrom           =   @$data['origin1'];
        $IATATo             =   @$data['destination1'];
        $IATAFromC          =   '';
        $IATAToC            =   '';
        $DeptDate           =   @date('d.m.Y',@strtotime(@$data['departDate1']));
        $RetnDate           =   (@$data['returnDate1'] == '') ? '' : @date('d.m.Y',@strtotime(@$data['returnDate1']));
        
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
        

        $fields             =   array('FlightType'     => $FlightType, 
                                    'Adults'           => $Adults, 
                                    'Childrens'        => $Childrens, 
                                    'Infants'          => $Infants, 
                                    'FlightClass'      => $FlightClass, 
                                    'IATAFrom'         => $IATAFrom, 
                                    'IATATo'           => $IATATo, 
                                    'IATAFromC'        => $IATAFromC, 
                                    'IATAToC'          => $IATAToC, 
                                    'DeptDate'         => $DeptDate, 
                                    'RetnDate'         => $RetnDate,
                                    'Currency'         => $Currency
                                );

        return $fields;
    }

    public function checkValidation($data)
    {
        $secPattern         =   '/^[A-Za-z]{3}$/';
        $datePattern        =    '/^(?:\d{4}-\d{2}-\d{2}|\d{2}-(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-\d{4}|(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{1,2}, \d{4})$/';
        $today              =   @date('Y-m-d');
        $requiredFields     =   ['origin1', 'destination1', 'departDate1', 'tripType', 'adults', 'childs', 'infants', 'classType', 'currency','metaName'];
        
        $departDate         =   @date('Y-m-d', strtotime(@$data['departDate1']));
        $returnDate         =   @date('Y-m-d', strtotime(@$data['returnDate1']));

        // COLLECT 1: Missing fields
        $missingFields = [];
        foreach ($requiredFields as $field) 
        {
            if (!isset($data[$field])) 
            {
                $missingFields[] = $field;
            }
        }

        // COLLECT 2: Fields with invalid types
        $invalidFields = [];

        if (isset($data['origin1'])) 
        {
            if (!preg_match($secPattern, trim($data['origin1']))) 
            {
                $invalidFields['origin1'] = 'IATA is incorrect';
            } 
        }

        if (isset($data['destination1'])) 
        {
            if (!preg_match($secPattern, trim($data['destination1']))) 
            {
                $invalidFields['destination1'] = 'IATA is incorrect';
            } 
        }

        if (isset($data['departDate1'])) 
        {
            if (!preg_match($datePattern, trim($data['departDate1']))) 
            {
                $invalidFields['departDate1'] = 'Invalid Departure date format';
            } 

            if (strtotime($departDate) < strtotime($today)) 
            {
                $invalidFields['departDate1'] = 'Departure date must be today or a future date';
            }
        }

        if (isset($data['tripType'])) 
        {
            if (!in_array($data['tripType'], ['roundTrip', 'round_trip', 'oneway'])) 
            {
                $invalidFields['tripType'] =  "Invalid trip type";
            }
        }

        if (isset($data['returnDate1'])) 
        {
            if (!preg_match($datePattern, trim($data['returnDate1']))) 
            {
                $invalidFields['returnDate1'] = 'Invalid Return date format';
            } 

            if (trim($returnDate) < $today) 
            {
                $invalidFields['returnDate1'] = 'Return date must be today or a future date';
            }

            if (trim($returnDate) < $departDate) 
            {
                $invalidFields['returnDate1'] = 'Return date must be next date from Departure date';
            }
        }
        
        if (isset($data['adults'])) 
        {
            if (!ctype_digit($data['adults']) || (int)$data['adults'] < 1 || (int)$data['adults'] > 9) 
            {
                $invalidFields['adults'] = 'Must be an integer between 1 and 9';
            }
        }
    
        if (isset($data['childs'])) 
        {
            if (!ctype_digit($data['childs']) || (int)$data['childs'] < 0 || (int)$data['childs'] > 9) 
            {
                $invalidFields['childs'] = 'Must be an integer between 0 and 9';
            }
        }
    
        if (isset($data['infants'])) 
        {
            if (!ctype_digit($data['infants'])) 
            {
                $invalidFields['infants'] = 'Must be an integer';
            } 
            else
            if ((int)$data['infants'] > (int)$data['adults']) 
            {
                $invalidFields['infants'] = 'Must be less than or equal to number of adults';
            }
        }

        if(is_numeric(@$data['adults']) && is_numeric(@$data['childs']) && is_numeric(@$data['infants']))
        {
            $totalPassengers    =   $data['adults'] + $data['childs'] + $data['infants'];
        }
        else
        {
            $totalPassengers    =   0;
        }

        if ($totalPassengers > 9) 
        {
            $invalidFields['total_passengers'] = 'Total number of passengers (adults + children + infants) must be 9 or less';
        }

        if (isset($data['classType'])) 
        {
            if (!in_array($data['classType'], ['Y', 'W', 'C', 'F'])) 
            {
                $invalidFields['classType'] =  "Invalid class Type.";
            }
        }

        if (isset($data['currency'])) 
        {
            if (!in_array($data['currency'], ['USD', 'EUR'])) 
            {
                $invalidFields['currency'] =  "Invalid currency.";
            }
        }

        if (isset($data['metaName']) && $data['metaName'] !== 'fareleaders') 
        {
            $invalidFields['source'] = 'meta name is not correct';
        }

        return compact('missingFields','invalidFields');
    }
    
    public function checkMarkup($fields)
    {
        $flight_types   =   @$fields['FlightType'];
        $cabin          =   @$fields['FlightClass'];

        // The meta form sends 'oneway', 'PremiumEconomy' and 'First'; the rule filters below expect
        // 'one_way', 'Peconom' and 'firstclass'. Normalise so journey and cabin filters really apply.
        // Switch: config fare_rules.meta_strict_flags (default true). false = old behaviour (flags ignored).
        if($this->fareRuleCfg('meta_strict_flags', true))
        {
            $flight_types   =   in_array($flight_types,['oneway','one_way'],true) ? 'one_way' : (in_array($flight_types,['roundTrip','round_trip'],true) ? 'round_trip' : $flight_types);
            $cabin          =   ['PremiumEconomy'=>'Peconom','First'=>'firstclass'][$cabin] ?? $cabin;
        }

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

                where('is_facecompare','Yes')->

                where(function ($query) use ($IATAFrom, $IATAFromC) {
                    $query->orWhere(function ($query) use ($IATAFrom) {
                            $query->where('departure_type', 'airport')
                                  ->where('departure_code', 'like', '%' . $IATAFrom . '%');
                          })
                          ->orWhere(function ($query) use ($IATAFromC) {
                            $query->where('departure_type', 'country')
                                  ->where('departure_code', 'like', '%' . $IATAFromC . '%');
                        });
                })->
                
                where(function ($query) use ($IATATo, $IATAToC) {
                    $query->orWhere(function ($query) use ($IATATo) {
                            $query->where('arrival_type', 'airport')
                                  ->where('arrival_code', 'like', '%' . $IATATo . '%');
                            })
                          ->orWhere(function ($query) use ($IATAToC) {
                              $query->where('arrival_type', 'country')
                                    ->where('arrival_code', 'like', '%' . $IATAToC . '%');
                          });
                })->
                
                when($flight_types == 'one_way', function ($query) { return $query->where('is_oneway', 'Yes'); })->
                when($flight_types == 'round_trip', function ($query) { return $query->where('is_roundtrip', 'Yes'); })->
                
                when($cabin == 'Econom', function ($query) { return $query->where('is_economy', 'Yes'); })->
                when($cabin == 'Peconom', function ($query) { return $query->where('is_peconomy', 'Yes'); })->
                when($cabin == 'Business', function ($query) { return $query->where('is_business', 'Yes'); })->
                when($cabin == 'firstclass', function ($query) { return $query->where('is_fclass', 'Yes'); })->

                whereDate('from_date', '<=', $DeptDate_)->
                whereDate('to_date', '>=', $RetnDate_)->

               when($this->markupPhase2Ready(), function ($query) use ($advDays) {
                    return $query->where(function ($w) use ($advDays) { $w->whereNull('adv_min_days')->orWhere('adv_min_days', '<=', $advDays); })
                                 ->where(function ($w) use ($advDays) { $w->whereNull('adv_max_days')->orWhere('adv_max_days', '>=', $advDays); });
                })->

               where('is_active','Yes')->
               get()->filter(function ($m) use ($IATAFrom, $IATAFromC, $IATATo, $IATAToC) {
                    return $this->routeMatchesExactly($m, $IATAFrom, $IATAFromC, $IATATo, $IATAToC);
                });

        // where('is_active','Yes');
        //dd($markup->toSql());
        //dd($this->getRawSql($markup));

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
                $tempArray['airline']           = $markups['airline'];
                $tempArray['fare_min_price']    = $markups['fare_min_price'];
                $tempArray['markup']            = (int) $markups['markup'];
                $tempArray['amt_per']           = $markups['amt_per'];

                array_push($markupSession,$tempArray);
            }
        }

        return $markupSession;
    }

    public function encryptShort($data, $key)
    {
        $cipher = 'aes-256-cbc';
        $ivlen  = openssl_cipher_iv_length($cipher);
        $iv     = openssl_random_pseudo_bytes($ivlen);

        $encrypted  = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $combined   = $iv . $encrypted;

        // Use URL-safe base64
        return rtrim(strtr(base64_encode($combined), '+/', '-_'), '=');
    }

    /* ---- Signed deeplink encryption (AES-256-CBC + HMAC-SHA256, keys derived from APP_KEY) ---- */

    private function deeplinkKeys(): array
    {
        $base = (string) config('app.key');
        return [hash('sha256', $base.'|fareleader-deeplink-enc', true), hash('sha256', $base.'|fareleader-deeplink-mac', true)];
    }

    public function encryptSigned($data): string
    {
        [$encKey, $macKey] = $this->deeplinkKeys();
        $iv     = random_bytes(16);
        $cipher = openssl_encrypt((string) $data, 'aes-256-cbc', $encKey, OPENSSL_RAW_DATA, $iv);
        $mac    = hash_hmac('sha256', $iv.$cipher, $macKey, true);

        return rtrim(strtr(base64_encode($iv.$cipher.$mac), '+/', '-_'), '=');
    }

    /** Returns the plain text, or false when the link was altered / not created by this site. */
    public function decryptSigned($encrypted)
    {
        if (!is_string($encrypted) || $encrypted === '') return false;

        [$encKey, $macKey] = $this->deeplinkKeys();
        $raw = base64_decode(strtr($encrypted, '-_', '+/'), true);
        if ($raw === false || strlen($raw) < 16 + 16 + 32) return false;

        $mac  = substr($raw, -32);
        $body = substr($raw, 0, -32);
        if (!hash_equals(hash_hmac('sha256', $body, $macKey, true), $mac)) return false;

        return openssl_decrypt(substr($body, 16), 'aes-256-cbc', $encKey, OPENSSL_RAW_DATA, substr($body, 0, 16));
    }

    public function decryptShort($encrypted, $key)
    {
        $cipher     =   'aes-256-cbc';
        $data       =   base64_decode(strtr($encrypted, '-_', '+/'));
        $ivlen      =   openssl_cipher_iv_length($cipher);
        $iv         =   substr($data, 0, $ivlen);
        $ciphertext =   substr($data, $ivlen);

        return openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    }


    /*  Filter Function - Search Result */

    public function addMarkupArray($Search_Field,$value_FData,$markupDB)
    {
        $temp   =   array();
        $markup =   array();

        if($markupDB)
        {
            $AdultPrice     =   (float) (@$value_FData['AdultPrice']  ?? 0);
            $ChildPrice     =   (float) (@$value_FData['ChildPrice']  ?? 0);
            $InfantPrice    =   (float) (@$value_FData['InfantPrice'] ?? 0);
            
            foreach($markupDB as $markupDBs)
            {
                $amt_per                        =   @$markupDBs['amt_per'];
                $temp['MarkupId']               =   @$markupDBs['id'];
                $temp['MarkupName']             =   @$markupDBs['name'];
                $temp['AdultPrice']             =   $AdultPrice;
                $temp['ChildPrice']             =   $ChildPrice;
                $temp['InfantPrice']            =   $InfantPrice;
                
                if($amt_per=='per')
                {
                    $temp['DiscountType']           =   $markupDBs['markup'].'%';
                    $temp['Discount']               =   ($markupDBs['markup'] / 100) * $AdultPrice;
                    $temp['Discounted_APrice']      =   $AdultPrice-(($markupDBs['markup'] / 100) * $AdultPrice);
                    $temp['Discounted_CPrice']      =   $ChildPrice-(($markupDBs['markup'] / 100) * $ChildPrice);
                }
                if($amt_per=='amt')
                {
                    $temp['DiscountType']           =   '$'.$markupDBs['markup'];
                    $temp['Discount']               =   $markupDBs['markup'];
                    $temp['Discounted_APrice']      =   $AdultPrice-$markupDBs['markup'];
                    $temp['Discounted_CPrice']      =   $ChildPrice-$markupDBs['markup'];
                }
                
                array_push($markup,$temp);
            }

            // get Minimum Markup
            $minDiscountedPrice = INF;

            foreach ($markup as $key => $value) 
            {
                if ($value['Discounted_APrice'] < $minDiscountedPrice) 
                {
                    $minDiscountedPrice             =   $value['Discounted_APrice'];

                    $arr['MarkupId']                =   @$value['MarkupId'];
                    $arr['MarkupName']              =   @$value['MarkupName'];
                    $arr['Discount']                =   @$value['Discount'];
                   
                    $arr['Old_AdultPrice']          =   @$value['AdultPrice'];
                    $arr['New_AdultPrice']          =   @$value['Discounted_APrice'];
                    $arr['Old_ChildPrice']          =   @$value['ChildPrice'];
                    $arr['New_ChildPrice']          =   @$value['Discounted_CPrice'];
                    $arr['Old_InfantPrice']         =   @$value['InfantPrice'];
                    $arr['New_InfantPrice']         =   @$value['InfantPrice'];
                    
                    $arr['Adult']                   =   @$Search_Field['Adults'];
                    $arr['Child']                   =   @$Search_Field['Childrens'];
                    $arr['Infant']                  =   @$Search_Field['Infants'];
                    
                    $arr['Sub_Adult']               =   @$Search_Field['Adults']*@$value['Discounted_APrice'];
                    
                    if(@$value['Discounted_CPrice'])
                    {
                        $arr['Sub_Child']           =   @$Search_Field['Childrens']*@$value['Discounted_CPrice'];
                    }
                    else
                    {
                        $arr['Sub_Child']           =   0;
                    }

                    $arr['Sub_Infant']              =   @$Search_Field['Infants']*@$InfantPrice;
                    
                    $arr['TotalPrice']              =   @$arr['Sub_Adult']+@$arr['Sub_Child']+@$arr['Sub_Infant'];
                }
            }

            $oldInfo =   "Adult:".@$AdultPrice.", Child:".@$ChildPrice.", Infant:".@$InfantPrice.", ";
            $oldInfo.=   "Passengers:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].", ";
            $oldInfo.=   "Total:".@$value_FData['TotalPrice'];
            
            $newInfo =   "Adult:".@$arr['New_AdultPrice'].", Child:".@$arr['New_ChildPrice'].", Infant:".@$arr['New_InfantPrice'].", ";
            $newInfo.=   "Passengers:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].", ";
            $newInfo.=   "Total:".@$arr['TotalPrice'].", ";
            $newInfo.=   "MarkupID:".@$arr['MarkupId'].", MarkupName:".@$arr['MarkupName'].", Disocunt:".@$arr['Discount']." per person";

            $priceInfo_  =   "A:".@$arr['New_AdultPrice'].",C:".@$arr['New_ChildPrice'].",I:".@$arr['New_InfantPrice'].",";
            $priceInfo_ .=   "Ps:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].",T:".@$arr['TotalPrice'].",";
            $priceInfo_ .=   "MId:".@$arr['MarkupId'].",MN:".str_replace([',',':'],' ',(string)@$arr['MarkupName']).",Dis:".@$arr['Discount'];
        }
        else
        {
            $markup     =   ''; 
            $arr        =   '';
            $oldInfo    =   '';
            $newInfo    =   '';
            $priceInfo_ =   '';
        }

        $markupArray['MinMarkup']   = $arr;
        $markupArray['OldInfo']     = $oldInfo;
        $markupArray['NewInfo']     = $newInfo;
        $markupArray['PriceInfo']   = $priceInfo_;
         
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
            $AdultPrice     =   (float) (@$value_FData['AdultPrice']  ?? 0);
            $ChildPrice     =   (float) (@$value_FData['ChildPrice']  ?? 0);
            $InfantPrice    =   (float) (@$value_FData['InfantPrice'] ?? 0);
            
            foreach($markupDB as $markupDBs)
            {
                $amt_per        =   @$markupDBs['amt_per'];
                $ruleValue      =   (float) @$markupDBs['markup'];
                $minFare        =   (float) @$markupDBs['fare_min_price'];
                $ChildPriceF    =   (float) $ChildPrice;
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
                $temp['MarkupId']               =   @$markupDBs['id'];
                $temp['MarkupName']             =   @$markupDBs['name'];
                $temp['AdultPrice']             =   $AdultPrice;
                $temp['ChildPrice']             =   $ChildPrice;
                $temp['InfantPrice']            =   $InfantPrice;
                $temp['DiscountType']           =   $discountType;
                $temp['Discount']               =   $discount;
                $temp['Discounted_APrice']      =   $newAdult;
                $temp['Discounted_CPrice']      =   $newChild;
                $temp['Specificity']            =   $specificity;

                array_push($markup,$temp);
            }

            // get Minimum Markup
            $minDiscountedPrice = INF;
            $bestSpec           = -1;
            $useSpecificity     = (bool) $this->fareRuleCfg('most_specific_wins', true);
            $arr                = array();

            if(empty($markup))
            {
                // Every matching rule was skipped by a guard -> offer is not shown on meta (same as "no rule").
                $markupDB = array();
            }

            foreach ($markup as $key => $value) 
            {
                if ($useSpecificity ? ($value['Specificity'] > $bestSpec || ($value['Specificity'] == $bestSpec && $value['Discounted_APrice'] < $minDiscountedPrice)) : ($value['Discounted_APrice'] < $minDiscountedPrice)) 
                {
                    $minDiscountedPrice             =   $value['Discounted_APrice'];
                    $bestSpec                       =   $value['Specificity'];

                    $arr                            =   array();
                    $arr['MarkupId']                =   @$value['MarkupId'];
                    $arr['Specificity']             =   @$value['Specificity'];
                    $arr['MarkupName']              =   @$value['MarkupName'];
                    $arr['Discount']                =   @$value['Discount'];
                   
                    $arr['Old_AdultPrice']          =   @$value['AdultPrice'];
                    $arr['New_AdultPrice']          =   @$value['Discounted_APrice'];
                    $arr['Old_ChildPrice']          =   @$value['ChildPrice'];
                    $arr['New_ChildPrice']          =   @$value['Discounted_CPrice'];
                    $arr['Old_InfantPrice']         =   @$value['InfantPrice'];
                    $arr['New_InfantPrice']         =   @$value['InfantPrice'];
                    
                    $arr['Adult']                   =   @$Search_Field['Adults'];
                    $arr['Child']                   =   @$Search_Field['Childrens'];
                    $arr['Infant']                  =   @$Search_Field['Infants'];
                    
                    $arr['Sub_Adult']               =   @$Search_Field['Adults']*@$value['Discounted_APrice'];
                    
                    if(@$value['Discounted_CPrice'])
                    {
                        $arr['Sub_Child']           =   @$Search_Field['Childrens']*@$value['Discounted_CPrice'];
                    }
                    else
                    {
                        $arr['Sub_Child']           =   0;
                    }

                    $arr['Sub_Infant']              =   @$Search_Field['Infants']*@$InfantPrice;
                    
                    $arr['TotalPrice']              =   @$arr['Sub_Adult']+@$arr['Sub_Child']+@$arr['Sub_Infant'];
                }
            }

            $oldInfo =   "Adult:".@$AdultPrice.", Child:".@$ChildPrice.", Infant:".@$InfantPrice.", ";
            $oldInfo.=   "Passengers:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].", ";
            $oldInfo.=   "Total:".@$value_FData['TotalPrice'];
            
            $newInfo =   "Adult:".@$arr['New_AdultPrice'].", Child:".@$arr['New_ChildPrice'].", Infant:".@$arr['New_InfantPrice'].", ";
            $newInfo.=   "Passengers:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].", ";
            $newInfo.=   "Total:".@$arr['TotalPrice'].", ";
            $newInfo.=   "MarkupID:".@$arr['MarkupId'].", MarkupName:".@$arr['MarkupName'].", Disocunt:".@$arr['Discount']." per person";

            $priceInfo_  =   "A:".@$arr['New_AdultPrice'].",C:".@$arr['New_ChildPrice'].",I:".@$arr['New_InfantPrice'].",";
            $priceInfo_ .=   "Ps:".@$Search_Field['Adults']."+".@$Search_Field['Childrens']."+".@$Search_Field['Infants'].",T:".@$arr['TotalPrice'].",";
            $priceInfo_ .=   "MId:".@$arr['MarkupId'].",MN:".str_replace([',',':'],' ',(string)@$arr['MarkupName']).",Dis:".@$arr['Discount'];
        }
        else
        {
            $markup     =   ''; 
            $arr        =   '';
            $oldInfo    =   '';
            $newInfo    =   '';
            $priceInfo_ =   '';
        }

        $markupArray['Markup']      = $markup;      // delete may later
        $markupArray['MinMarkup']   = $arr;
        $markupArray['MarkupDB']    = $markupDB;    // delete may later
        $markupArray['OldInfo']     = $oldInfo;
        $markupArray['NewInfo']     = $newInfo;
        $markupArray['PriceInfo']   = $priceInfo_;
         
        return $markupArray;
        
    }

    public function getArrResultLoop($resultData,$key,$OfferSeg,$Rph)
    {
        $airlineInfo                         =   @explode('-',@$OfferSeg['FlightNum']);

        $tempArray['cabin']                  =   @$OfferSeg['FlightClass'];
        $tempArray['flightNumber']           =   @$OfferSeg['FlightNum'];
        $tempArray['airlineCode']            =   @$airlineInfo[0];
        $tempArray['operatingAirlineCode']   =   @$OfferSeg['OperatingAirline'];
        $tempArray['aircraftCode']           =   @$OfferSeg['AirCraft'];
        $tempArray['departureDateTime']      =   @$OfferSeg['Departure']['Date'];  
        $tempArray['arrivalDateTime']        =   @$OfferSeg['Arrival']['Date'];  
        $tempArray['departureAirportCode']   =   @$OfferSeg['Departure']['Iata'];   
        $tempArray['arrivalAirportCode']     =   @$OfferSeg['Arrival']['Iata']; 
        //$tempArray['tripduration']           =   @$OfferSeg['FlightMinutes']; 
        
        if($Rph==1)
        {           
            array_push($resultData[$key]['leg1']['segments'],$tempArray); 
            
            // Trip Duration
            $segmentCount  =  count($resultData[$key]['leg1']['segments']);

            if($segmentCount)
            {
                if($segmentCount=='1')
                {
                    $previousValue  =   @$OfferSeg['Arrival']['Date'];
                    array_push($resultData[$key]['leg1']['previousValue_'],$previousValue);

                    array_push($resultData[$key]['leg1']['tripduration_'],$OfferSeg['FlightMinutes']);
                }

                if($segmentCount>'1')
                {
                    $previousValue  =   end($resultData[$key]['leg1']['previousValue_']);
                    
                    $date1          =   new \DateTime($previousValue);
                    $date2          =   new \DateTime(@$OfferSeg['Departure']['Date']);

                    $interval       =   $date1->diff($date2);
                    $overlay        =   ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                    $previousValue  =   @$OfferSeg['Arrival']['Date'];
                    array_push($resultData[$key]['leg1']['previousValue_'],$previousValue);

                    array_push($resultData[$key]['leg1']['tripduration_'],$overlay);
                    array_push($resultData[$key]['leg1']['tripduration_'],$OfferSeg['FlightMinutes']);
                }
            }
        }
        if($Rph==2)
        {           
            array_push($resultData[$key]['leg2']['segments'],$tempArray); 

            // Trip Duration
            $segmentCount  =  count($resultData[$key]['leg2']['segments']);

            if($segmentCount)
            {
                if($segmentCount=='1')
                {
                    $previousValue  =   @$OfferSeg['Arrival']['Date'];
                    array_push($resultData[$key]['leg2']['previousValue_'],$previousValue);

                    array_push($resultData[$key]['leg2']['tripduration_'],$OfferSeg['FlightMinutes']);
                }

                if($segmentCount>'1')
                {
                    $previousValue  =   end($resultData[$key]['leg2']['previousValue_']);
                    
                    $date1          =   new \DateTime($previousValue);
                    $date2          =   new \DateTime(@$OfferSeg['Departure']['Date']);

                    $interval       =   $date1->diff($date2);
                    $overlay        =   ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                    $previousValue  =   @$OfferSeg['Arrival']['Date'];
                    array_push($resultData[$key]['leg2']['previousValue_'],$previousValue);

                    array_push($resultData[$key]['leg2']['tripduration_'],$overlay);
                    array_push($resultData[$key]['leg2']['tripduration_'],$OfferSeg['FlightMinutes']);
                }
            }
        }

        $resultData[$key]['airlinelist'][]     =   @$airlineInfo[0];

        return $resultData;
    }

    public function arrayRearrange($Search_Field,$markupDB,$result,$code,$isOther=null)
    {
        $ResultCount            =   @$result['ResultCount'];
        $SearchGuid             =   @$result['SearchGuid'];
        $FlightData             =   @$result['FlightData']['FlightData'];
        if (is_array($FlightData) && $FlightData !== [] && !array_key_exists(0, $FlightData)) { $FlightData = [$FlightData]; } // L4 fix 16 Nov 2026: supplier sends ONE flight as an object, not a list
        
        if(@$FlightData)
        {
            foreach($FlightData as $key=>$value_FData)
            {
                $OfferInfo                                      =   @$value_FData['Offers']['OfferInfo'];
                
                $resultData[$key]['airlinelist']                =    array();
                $resultData[$key]['allairline']                 =    '';
               
                $resultData[$key]['leg1']['segments']           =   array();
                $resultData[$key]['leg1']['tripduration']       =   '';
                $resultData[$key]['leg1']['tripduration_']      =   array();
                $resultData[$key]['leg1']['previousValue_']     =   array();
                
                $resultData[$key]['leg2']['segments']           =   array();
                $resultData[$key]['leg2']['tripduration']       =   '';
                $resultData[$key]['leg2']['tripduration_']      =   array();
                $resultData[$key]['leg2']['previousValue_']     =   array();

                if($OfferInfo)
                {                   
                    if(@$OfferInfo['Rph'])
                    {
                        $OfferSegment       =   @$OfferInfo['Segments']['OfferSegment'];

                        if(@$OfferSegment['AirCraft'] || isset($OfferSegment['MarketingAirline']))
                        {                       
                            $resultData     =   $this->getArrResultLoop($resultData,$key,$OfferSegment,$OfferSegment['Rph']);  
                        }
                        else
                        {
                            foreach($OfferSegment as $key1=>$OfferSegments)
                            {                           
                                $resultData =   $this->getArrResultLoop($resultData,$key,$OfferSegments,$OfferSegments['Rph']);   
                            }
                        }
                    }
                    else
                    {
                        foreach($OfferInfo as $value1)
                        {
                            $OfferSegment               =   @$value1['Segments']['OfferSegment'];

                            if(@$OfferSegment['AirCraft'] || isset($OfferSegment['MarketingAirline']))
                            {  
                                $resultData =   $this->getArrResultLoop($resultData,$key,$OfferSegment,$value1['Rph']);
                            }
                            else
                            {
                                foreach($OfferSegment as $OfferSegments)
                                {
                                    $resultData =   $this->getArrResultLoop($resultData,$key,$OfferSegments,$value1['Rph']);
                                }
                            }
                        }
                    }  
                    
                    // Check All Airlines are same or not
                    if (count(array_unique($resultData[$key]['airlinelist'])) === 1) 
                    {
                        $resultData[$key]['airlinelist'] = $resultData[$key]['airlinelist'][0];
                    } 
                    
                    // Airline block rules: a blocked airline removes the offer (no markup => offer is dropped below)
                    $markupDBUse      =   $this->isAirlineBlocked($markupDB,$resultData[$key]['airlinelist']) ? array() : $this->withoutBlockRules($markupDB);

                    $markupArray      =   $this->addReduceMarkupArray($Search_Field,$value_FData,$markupDBUse,$resultData[$key]['airlinelist']);
                    
                    $reduceMarkupDB   =   $markupArray['MarkupDB'];

                    if($reduceMarkupDB)
                    {
                        $resultData[$key]['price']['currencyCode']      =   @$Search_Field['Currency'];
                        $resultData[$key]['price']['pricePerAdult']     =   @$markupArray['MinMarkup']['New_AdultPrice'];
                        $resultData[$key]['price']['pricePerChild']     =   @$markupArray['MinMarkup']['New_ChildPrice'];
                        $resultData[$key]['price']['pricePerInfant']    =   @$markupArray['MinMarkup']['New_InfantPrice'];
                        $resultData[$key]['price']['totalAmount']       =   @$markupArray['MinMarkup']['TotalPrice'];
                        $resultData[$key]['price']['isRefundable']      =   true;

                        $signedSearchField = $Search_Field;
                        if ($isOther === "No") {
                            $verifiedSource = request()->attributes->get("fareleaders_verified_source");
                            $signedSearchField["FareLeadersSource"] = $verifiedSource === "FareLeaders Core" ? "FareLeaders Core" : "FareLeaders Compare";
                        }
                        $Search_Field_Decode    =   @json_encode($signedSearchField);
                        $Offer_Code_Decode      =   @$value_FData['OfferCode'];
                        $Price_Info_Decode      =   @json_encode($resultData[$key]['price']);
                        
                        // SECURITY: deeplink data is encrypted AND signed with a key derived from APP_KEY,
                        // so nobody can create or alter a price link.
                        $PriceInfo              =   @$markupArray['PriceInfo'];
                        $encryptedPriceinfo     =   $this->encryptSigned($PriceInfo);
                        $encryptedSearchField   =   $this->encryptSigned($Search_Field_Decode);

                        $domain =   request()->getSchemeAndHttpHost();
                        
                        $url =   $domain."/flights-payment?";
                        $url.=   "priceinfo=".@$encryptedPriceinfo."&";
                        $url.=   "field=".@$encryptedSearchField."&";
                        // FIX (deeplink, FareLeaders CORE only): encode supplier values so '+', '/', '=' or '&'
                        // can never break the link. Compare and the UI search keep the original link format.
                        if (($signedSearchField["FareLeadersSource"] ?? null) === "FareLeaders Core") {
                            $url.=   "searchguid=".rawurlencode((string) @$SearchGuid)."&";
                            $url.=   "offercode=".rawurlencode((string) @$Offer_Code_Decode);
                        } else {
                            $url.=   "searchguid=".@$SearchGuid."&";
                            $url.=   "offercode=".@$Offer_Code_Decode;
                        }
                        
                        $offerCode                                      =   @$value_FData['OfferCode'];
                        $deeplinkUrl                                    =   @$url;

                        $resultData[$key]['deeplinkUrl']                =   @$deeplinkUrl;
                        
                        if($isOther=='Yes') // show in only UI Search, not in Json Search
                        {
                            $resultData[$key]['other']['oldInfo']           =   @$markupArray['OldInfo'];
                            $resultData[$key]['other']['newInfo']           =   @$markupArray['NewInfo'];
                        }
                        
                        // Trip Duration Calculation
                        $totalDurationLeg1              =   @array_sum(@$resultData[$key]['leg1']['tripduration_']);
                        $totalDurationLeg1_hours        =   @floor($totalDurationLeg1 / 60);
                        $totalDurationLeg1_minutes      =   $totalDurationLeg1 % 60;
                        $formattedDurationLeg1          =   sprintf("%02d:%02d", $totalDurationLeg1_hours, $totalDurationLeg1_minutes);

                        $resultData[$key]['leg1']['tripduration']   =   $formattedDurationLeg1;
                        unset($resultData[$key]['leg1']['tripduration_']);
                        unset($resultData[$key]['leg1']['previousValue_']);
                        
                        $totalDurationLeg2              =   @array_sum(@$resultData[$key]['leg2']['tripduration_']);
                        $totalDurationLeg2_hours        =   @floor($totalDurationLeg2 / 60);
                        $totalDurationLeg2_minutes      =   $totalDurationLeg2 % 60;
                        $formattedDurationLeg2          =   sprintf("%02d:%02d", $totalDurationLeg2_hours, $totalDurationLeg2_minutes);

                        $resultData[$key]['leg2']['tripduration']   =   $formattedDurationLeg2;
                        unset($resultData[$key]['leg2']['tripduration_']);
                        unset($resultData[$key]['leg2']['previousValue_']);                         
                    }
                    else
                    {
                        unset($resultData[$key]);
                    }

                }
                
                // End Single Single Array
            }
        }
 
        $newArray['flightItineraries'] = @$resultData;
        
        //echo count(@$resultData);
        return $newArray;
    }

    public function matchBothAirports($fields,$filterResult)
    {
        $FlightType =   @$fields['FlightType'];
        $IATAFrom   =   @strtoupper(@$fields['IATAFrom']);
        $IATATo     =   @strtoupper(@$fields['IATATo']);

        foreach ($filterResult['flightItineraries'] as $key => $flight) 
        {
            if($FlightType=='round_trip')
            {
                $total_leg1segment      = count(@$flight['leg1']['segments']);
                $total_leg2segment      = count(@$flight['leg2']['segments']);

                $leg1_laststop_segment  = $total_leg1segment-1; if(!$leg1_laststop_segment) { $leg1_laststop_segment = '0'; }
                $leg2_laststop_segment  = $total_leg2segment-1; if(!$leg2_laststop_segment) { $leg2_laststop_segment = '0'; }

                if ($flight['leg1']['segments'][0]['departureAirportCode']!= $IATAFrom || 
                    $flight['leg1']['segments'][$leg1_laststop_segment]['arrivalAirportCode']!= $IATATo || 
                    $flight['leg2']['segments'][0]['departureAirportCode']!= $IATATo ||
                    $flight['leg2']['segments'][$leg2_laststop_segment]['arrivalAirportCode']!= $IATAFrom) 
                {
                    //@$filterResult['flightItineraries'][$key]['match'] = 'No';
                    unset($filterResult['flightItineraries'][$key]);
                } 
            }
            else
            {
                $total_leg1segment      = count($flight['leg1']['segments']);
                $leg1_laststop_segment  = $total_leg1segment-1;

                if ($flight['leg1']['segments'][0]['departureAirportCode'] != $IATAFrom || 
                    $flight['leg1']['segments'][$leg1_laststop_segment]['arrivalAirportCode']!= $IATATo) 
                {
                    //@$filterResult['flightItineraries'][$key]['match'] = 'No';
                    unset($filterResult['flightItineraries'][$key]);
                } 
            }
        }

        return $filterResult;
    }


    /*  Filter Function - Payment Page */

    public function getArrResultLoopAfterPreBook($resultData,$OfferSeg,$Rph)
    {
        $matchedRecords                     =  Airline::WHERE('code', @$OfferSeg['MarketingAirline'])->first();

        if($matchedRecords->logo)
        {
            $tempArray['AirlineLogo']       =  $matchedRecords->logo;
        }
        else
        {
            $tempArray['AirlineLogo']       =  ''; 
        }

        $tempArray['Rph']                   =  @$Rph; 
        $tempArray['Airline']               =  @$OfferSeg['MarketingAirline']; 
        $tempArray['AirlineName']           =  @$OfferSeg['MarketingAirlineName']; 
        
        $tempArray['AirCraft']              =  @$OfferSeg['AirCraft'];  

        $tempArray['Departure_Name']        =  @$OfferSeg['Departure']['Name'];   
        $tempArray['Arrival_Name']          =  @$OfferSeg['Arrival']['Name'];   

        $tempArray['Departure_Iata']        =  @$OfferSeg['Departure']['Iata'];   
        $tempArray['Arrival_Iata']          =  @$OfferSeg['Arrival']['Iata'];   
        
        $tempArray['Departure_Date']        =  @$OfferSeg['Departure']['Date'];   
        $tempArray['Arrival_Date']          =  @$OfferSeg['Arrival']['Date'];   
        
        $tempArray['FlightClass']           =  @$OfferSeg['FlightClass'];   
        $tempArray['FlightMinutes']         =  @$OfferSeg['FlightMinutes'];  
        $tempArray['FlightNum']             =  @$OfferSeg['FlightNum'];   
        $tempArray['FlightTime']            =  @$OfferSeg['FlightTime'];  

        $tempArray['Bag_BType']             =  @$OfferSeg['Baggage']['BaggageType'];   
        $tempArray['Bag_Count']             =  @$OfferSeg['Baggage']['Count'];   
        $tempArray['CabinBag_BType']        =  @$OfferSeg['CabinBaggage']['BaggageType'];  
        $tempArray['CabinBag_Count']        =  @$OfferSeg['CabinBaggage']['Count'];  
        
        $tempArray['SelfConnect']           =  @$OfferSeg['SelfConnect'];
        
        if($Rph==1)
        {
            array_push($resultData['AllFlight']['Rph_1'],$tempArray);
            $resultData['DptFlightStop']      =   ($resultData['DptFlightStop'])+1;
        }
        if($Rph==2)
        {
            array_push($resultData['AllFlight']['Rph_2'],$tempArray);
            $resultData['RtnFlightStop']      =   ($resultData['RtnFlightStop'])+1;
        }

        return $resultData;
    }

    public function arrayRearrangePreBookingResult($OfferInfo)
    {
        $resultData['AllFlight']['Rph_1'] = [];
        $resultData['AllFlight']['Rph_2'] = [];

        $resultData['DptFlightStop']      =    0;
        $resultData['RtnFlightStop']      =    0;

        if($OfferInfo)
        {
            if(@$OfferInfo['Rph'])
            {
                $OfferSegment    =   @$OfferInfo['Segments']['OfferSegment'];

                if(@$OfferSegment['AirCraft'] || isset($OfferSegment['MarketingAirline']))
                {
                    $resultData     =   $this->getArrResultLoopAfterPreBook($resultData,$OfferSegment,$OfferSegment['Rph']);  
                }
                else
                {
                    foreach($OfferSegment as $key1=>$OfferSegments)
                    {                   
                        $resultData =   $this->getArrResultLoopAfterPreBook($resultData,$OfferSegments,$OfferSegments['Rph']);   
                    }
                }
            }
            else
            {
                foreach($OfferInfo as $value1)
                {
                    $OfferSegment               =   @$value1['Segments']['OfferSegment'];

                    if(@$OfferSegment['AirCraft'] || isset($OfferSegment['MarketingAirline']))
                    {  
                        $resultData =   $this->getArrResultLoopAfterPreBook($resultData,$OfferSegment,$value1['Rph']);
                    }
                    else
                    {
                        foreach($OfferSegment as $OfferSegments)
                        {
                            $resultData =   $this->getArrResultLoopAfterPreBook($resultData,$OfferSegments,$value1['Rph']);
                        }
                    }
                }
            }

            $resultData['DptFlightStop']      =   ($resultData['DptFlightStop'])-1;
            $resultData['RtnFlightStop']      =   ($resultData['RtnFlightStop'])-1;
        }

        return $resultData;
    }

}

?>