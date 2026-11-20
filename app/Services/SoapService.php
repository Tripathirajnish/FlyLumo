<?php

namespace App\Services;

use SoapClient;
use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SoapService
{
    /**
     * L5-1b (17 Nov 2026): FareLeaders Core supplier calls that create NO booking
     * (AeroSearch, AeroPrebook) get a read timeout and ONE fresh retry on a network
     * failure, all inside ~55 s (Apache answers 504 at 60 s). This service has no
     * booking method, so nothing here can create a booking twice.
     */
    private const FH_RETRY_METHODS = ['AeroSearch', 'AeroPrebook'];

    /** Separate method so the retry can be tested without the real supplier. */
    protected function fhMakeSoapClient($wsdl, array $connectParams)
    {
        return new SoapClient($wsdl, $connectParams);
    }

    /** A transport problem (no answer, reset, cannot connect) - not a supplier business error. */
    protected function fhIsNetworkFault($result): bool
    {
        return is_soap_fault($result) && !isset($result->detail) && (($result->faultcode ?? '') === 'HTTP');
    }

    /**
     * Returns [result, lastThrowable]. result is the supplier answer (it can be a SoapFault
     * object, because this client runs with exceptions => false). lastThrowable is set only
     * when the final attempt threw (e.g. "SSL: Connection reset by peer", "gzdecode(): data error").
     */
    protected function fhCallWithRetry($client, $wsdl, array $connectParams, string $method, array $args): array
    {
        $startedAt  = microtime(true);
        $budget     = 55.0;
        $firstLimit = ($method === 'AeroPrebook') ? 30 : 25;
        $oldTimeout = ini_get('default_socket_timeout');
        $result     = null;
        $thrown     = null;

        try {
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                $left = $budget - (microtime(true) - $startedAt);

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

                    $result = $client->$method($args);
                    $thrown = null;

                    if (!$this->fhIsNetworkFault($result)) {
                        if ($attempt === 2) {
                            \Log::info('FL_CORE_SOAP_RETRY', ['method' => $method, 'outcome' => 'success_on_retry', 'ms' => (int) round((microtime(true) - $t0) * 1000)]);
                        }

                        return [$result, null];
                    }

                    $err = 'SoapFault: ' . mb_substr((string) ($result->faultstring ?? ''), 0, 120);
                } catch (\Throwable $e) {
                    $thrown = $e;
                    $result = null;
                    $err    = get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 120);
                }

                \Log::warning('FL_CORE_SOAP_ATTEMPT_FAILED', [
                    'method'    => $method,
                    'attempt'   => $attempt,
                    'ms'        => (int) round((microtime(true) - $t0) * 1000),
                    'timeout_s' => $timeout,
                    'error'     => $err,
                ]);
            }
        } finally {
            ini_set('default_socket_timeout', (string) $oldTimeout);
        }

        \Log::error('FL_CORE_SUPPLIER_UNAVAILABLE', [
            'method'   => $method,
            'total_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);

        return [$result, $thrown];
    }

    public function get_soap_error($result)
    {
        return isset($result->detail)
            ? ( isset($result->detail->GenericException)
                ? $result->detail->GenericException->Description
                : ( isset($result->detail->ExceptionDetail)
                    ? $result->detail->ExceptionDetail->Message
                    : 'Unknown error' ) )
            :  ( ($result->faultstring ?? '') != ''
                ? ( $result->faultstring == 'Error Fetching http headers'
                    ? 'Network error'
                    : $result->faultstring )
                : 'Unknown error' );
    }

    public function wsdl_request($method = '', $input = array())
    {
        $apiCredential = $this->getSettingAPiType($method);

        $return = array('status'=>'0','error'=>'','result'=>'');
        $error_text = '';

        $wsdl = $apiCredential['wsdl'] ?? '';
        $soap = $apiCredential['soap'] ?? '';

        if (!$wsdl) {
            return ['status'=>'0','error'=>'WSDL is not configured for the active API environment.','result'=>''];
        }
        if (!$soap) {
            return ['status'=>'0','error'=>'SOAP endpoint is not configured for the active API environment.','result'=>''];
        }

        $opts = [
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ];

        $context = stream_context_create($opts);

        $connect_params = array(
            'soap_version' => SOAP_1_2,
            'trace' => true,
            'exceptions' => false,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'features' => SOAP_USE_XSI_ARRAY_TYPE,
            'connection_timeout' => 120,
            'location' => $soap,
            'uri' => 'http://tempuri.org/',
            'compression' => (SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP),
            'stream_context' => $context
        );

        // L5-1b: a normal connect takes ~0.2 s; do not wait 120 s on a dead connection.
        if (in_array($method, self::FH_RETRY_METHODS, true)) {
            $connect_params['connection_timeout'] = 10;
        }

        $client = null;
        try {
            $client = $this->fhMakeSoapClient($wsdl, $connect_params);
            $exception = "";
        } catch (\SoapFault $e) {
            $exception = $e;
        }

        if ($exception || ($client && is_soap_fault($client))) {
            $error_text = is_object($client)
                ? ( isset($client->detail)
                    ? ($client->detail->GenericException->Description ?? 'SOAP error')
                    : ($client->faultstring ?? 'SOAP error') )
                : ($exception ? $exception->getMessage() : 'SOAP error');
        }

        if($error_text == '')
        {
            $credentials = array(
                'ApiLogin' => $apiCredential['ApiLogin'],
                'ApiPassword' => $apiCredential['ApiPassword'],
                'DeviceId' => $apiCredential['DeviceId'],
                'Currency' => $apiCredential['Currency'],
                'Language' => 'EN',
                'TokenGuid' => $apiCredential['token'],
            );

            if ($method === 'AeroPrebook') {
                $prebookStart = microtime(true);
                \Log::info('FL_CORE_AEROPREBOOK_START');
            }

            $fhThrown = null;

            try {
                if (in_array($method, self::FH_RETRY_METHODS, true)) {
                    // L5-1b: timeout + one retry; a thrown network error no longer becomes a 500
                    [$result, $fhThrown] = $this->fhCallWithRetry($client, $wsdl, $connect_params, $method, $input + array('credentials' => $credentials));
                } else {
                    $result = $client->$method($input + array('credentials' => $credentials));
                }
            } finally {
                if ($method === 'AeroPrebook') {
                    \Log::info('FL_CORE_AEROPREBOOK_FINISH', [
                        'duration_ms' => round((microtime(true) - $prebookStart) * 1000)
                    ]);
                }
            }

            if ($fhThrown !== null) {
                $return['error'] = 'Network error';     // same text the API already returns for "Error Fetching http headers"
            } elseif(is_soap_fault($result)) {
                $return['error'] = $this->get_soap_error($result);
            } elseif(!isset($result->{$method.'Result'}) || !isset($result->{$method.'Result'}->Success)) {
                $return['error'] = 'Service error';
            } else {
                $result = $result->{$method.'Result'};
                if(!$result->Success) {
                    $return['error'] = trim($result->ErrorString) == ''
                        ? 'Unknown error'
                        : htmlspecialchars($result->ErrorString);
                } else {
                    $return['status'] = '1';
                    $return['error'] = '';
                    $return['result'] = $result;
                }
            }
        } else {
            $return['error'] = $error_text;
        }

        if ($method === 'AeroPrebook') {
            \Log::info('FL_CORE_AEROPREBOOK_OUTCOME', [
                'status' => $return['status'],
                'error_type' => $return['error'] === ''
                    ? 'none'
                    : 'supplier_or_service_error'
            ]);
        }

        return $return;
    }

    public function getSettingAPiType($method)
    {
        $settingApi = Setting::where('name','api_type')->value('value') ?: 'Demo';
        $env = strtolower($settingApi === 'Live' ? 'live' : 'demo');

        $defaults = $env === 'demo'
            ? [
                'ApiLogin' => 'test',
                'ApiPassword' => 'test',
                'DeviceId' => 'test',
                'Currency' => 'EUR',
                'token' => '00000000-0000-0000-0000-000000000000',
                'wsdl' => 'http://test-api.xml.agency/SiteCity?singlewsdl',
                'search_soap' => 'http://test-api.xml.agency/SiteCity',
                'general_soap' => 'http://test-api.xml.agency/SiteCity',
            ]
            : [
                'ApiLogin' => '',
                'ApiPassword' => '',
                'DeviceId' => '',
                'Currency' => 'USD',
                'token' => '',
                'wsdl' => '',
                'search_soap' => '',
                'general_soap' => '',
            ];

        $cfg = $this->getSecureEnvironmentConfig($env, $defaults);

        // Portable Live WSDL fallback: use the project copy if present.
        if ($env === 'live' && empty($cfg['wsdl'])) {
            $projectWsdl = public_path('wsdl/SiteCity.wsdl');
            if (is_file($projectWsdl)) {
                $cfg['wsdl'] = $projectWsdl;
            }
        }

        $cfg['soap'] = ($method === 'AeroSearch')
            ? $cfg['search_soap']
            : $cfg['general_soap'];

        return $cfg;
    }

    private function getSecureEnvironmentConfig(string $env, array $defaults): array
    {
        $names = [
            "api_{$env}_login",
            "api_{$env}_password",
            "api_{$env}_device_id",
            "api_{$env}_token",
            "api_{$env}_currency",
            "api_{$env}_wsdl",
            "api_{$env}_search_soap",
            "api_{$env}_general_soap",
            "api_{$env}_soap",
        ];

        $rows = Setting::whereIn('name',$names)->pluck('value','name');

        $decrypt = function ($value) {
            if (!$value) return '';
            try { return Crypt::decryptString($value); }
            catch (\Throwable $e) { return ''; }
        };

        $legacySoap = $rows["api_{$env}_soap"] ?? '';

        return [
            'ApiLogin' => !empty($rows["api_{$env}_login"]) ? $decrypt($rows["api_{$env}_login"]) : $defaults['ApiLogin'],
            'ApiPassword' => !empty($rows["api_{$env}_password"]) ? $decrypt($rows["api_{$env}_password"]) : $defaults['ApiPassword'],
            'DeviceId' => !empty($rows["api_{$env}_device_id"]) ? $decrypt($rows["api_{$env}_device_id"]) : $defaults['DeviceId'],
            'Currency' => $rows["api_{$env}_currency"] ?? $defaults['Currency'],
            'token' => !empty($rows["api_{$env}_token"]) ? $decrypt($rows["api_{$env}_token"]) : $defaults['token'],
            'wsdl' => $rows["api_{$env}_wsdl"] ?? $defaults['wsdl'],
            'search_soap' => $rows["api_{$env}_search_soap"] ?? ($legacySoap ?: $defaults['search_soap']),
            'general_soap' => $rows["api_{$env}_general_soap"] ?? ($legacySoap ?: $defaults['general_soap']),
        ];
    }

    public function SearchApi($fields)
    {
        $params = array(
            'Adults' => @$fields['Adults'],
            'Childs' => @$fields['Childrens'],
            'Infants' => @$fields['Infants'],
            'FlightClass' => @$fields['FlightClass'],
        );

        $flights = array(
            array(
                'Date' => @$fields['DeptDate'],
                'IATAFrom' => @$fields['IATAFrom'],
                'IATATo' => @$fields['IATATo'],
            )
        );

        if(@$fields['FlightType']=='round_trip') {
            $flights[] = array(
                'Date' => @$fields['RetnDate'],
                'IATAFrom' => @$fields['IATATo'],
                'IATATo' => @$fields['IATAFrom'],
            );
        }

        $params['SearchFlights'] = $flights;
        $output = $this->wsdl_request('AeroSearch', array('aeroSearchParams' => $params));

        if(@$output['status']==1)
        {
            $result = $output['result'];

            if (!is_object($result) || !isset($result->FlightData) || !isset($result->FlightData->FlightData))
            {
                $error_text = (!is_object(@$result) && (@$result != '')) ? $result : 'Flights not found';
                $output['status'] = '0';
                $output['error'] = $error_text;
                $output['result'] = '';
            }
            else
            {
                $result = json_decode(json_encode(@$result), true);
                $output['result'] = $result;
            }
        }

        return $output;
    }

    public function PreBookApi($OfferCode,$SearchGuid)
    {
        $params['OfferCode'] = $OfferCode;
        $params['SearchGuid'] = $SearchGuid;

        return $this->wsdl_request('AeroPrebook', array('aeroPrebookParams' => $params));
    }
}
