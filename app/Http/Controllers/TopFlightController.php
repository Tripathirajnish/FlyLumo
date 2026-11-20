<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SoapService;
use App\Models\TopDeal;
use Illuminate\Support\Facades\Cache;

class TopFlightController extends Controller
{
    protected $soapService;

    public function __construct(SoapService $soapService)
    {
        $this->soapService = $soapService;
    }

    public function ajaxGetFlightData(Request $request)
    {
        $id = $request->id;

        if (@$id) {
            $topdeal = TopDeal::where('id', $id)->first();

            if (!$topdeal) {
                return response()->json('--');
            }

            $departure_code = $topdeal->departure_code;
            $arrival_code = $topdeal->arrival_code;
            $days = $topdeal->days;
            $today = @date('m/d/Y');

            $fields['FlightType'] = 'one_way';
            $fields['IATAFrom'] = $departure_code;
            $fields['IATATo'] = $arrival_code;
            $fields['DeptDate'] = @date('d.m.Y', @strtotime("+$days day", strtotime($today)));
            $fields['Adults'] = '1';
            $fields['Childrens'] = '0';
            $fields['Infants'] = '0';
            $fields['FlightClass'] = 'Econom';

            $output = $this->soapService->SearchApi($fields);

            if (@$output['status'] == '0') {
                return response()->json('--');
            }

            $result = @$output['result'];
            $FlightData = @$result['FlightData']['FlightData'];
            $firstPrice = @$FlightData[0]['AdultPrice'];

            return response()->json('$'.$firstPrice);
        }

        return response()->json('--');
    }

    public function ajaxRouteFare(Request $request)
    {
        $data = $request->validate([
            'from' => ['required','string','size:3'],
            'to' => ['required','string','size:3'],
            'cabin' => ['nullable','in:Econom,Peconom,Business,firstclass'],
            'days' => ['nullable','integer','min:1','max:330'],
            'trip_type' => ['nullable','in:one_way,round_trip'],
            'return_days' => ['nullable','integer','min:1','max:60'],
        ]);

        $from = strtoupper($data['from']);
        $to = strtoupper($data['to']);
        $cabin = $data['cabin'] ?? 'Econom';
        $days = (int)($data['days'] ?? 30);
        $tripType = $data['trip_type'] ?? 'round_trip';
        $returnDays = max(1, (int)($data['return_days'] ?? 7));

        $departure = now()->startOfDay()->addDays($days);
        $return = $departure->copy()->addDays($returnDays);

        $cacheKey = implode(':', [
            'home_route_fare',
            $from,
            $to,
            $cabin,
            $days,
            $tripType,
            $tripType === 'round_trip' ? $returnDays : 0,
        ]);

        $price = Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            function () use ($from, $to, $cabin, $tripType, $departure, $return) {
                $fields = [
                    'FlightType' => $tripType,
                    'IATAFrom' => $from,
                    'IATATo' => $to,
                    'DeptDate' => $departure->format('d.m.Y'),
                    'Adults' => '1',
                    'Childrens' => '0',
                    'Infants' => '0',
                    'FlightClass' => $cabin,
                ];

                if ($tripType === 'round_trip') {
                    $fields['RetnDate'] = $return->format('d.m.Y');
                }

                $output = $this->soapService->SearchApi($fields);

                if (($output['status'] ?? '0') === '0') {
                    return null;
                }

                $rows = $output['result']['FlightData']['FlightData'] ?? [];
                if (!$rows) {
                    return null;
                }

                $prices = collect($rows)
                    ->pluck('AdultPrice')
                    ->filter(fn ($value) => is_numeric($value))
                    ->map(fn ($value) => (float)$value);

                return $prices->count() ? $prices->min() : null;
            }
        );

        return response()->json([
            'ok' => $price !== null,
            'price' => $price !== null ? round((float)$price, 2) : null,
            'label' => $price !== null ? '$'.number_format((float)$price, 2) : 'Check fare',
            'trip_type' => $tripType,
            'trip_label' => $tripType === 'round_trip' ? 'Round Trip' : 'One Way',
            'departure_date' => $departure->format('Y-m-d'),
            'return_date' => $tripType === 'round_trip' ? $return->format('Y-m-d') : null,
        ]);
    }
}
