<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB; use Illuminate\Support\Facades\Schema;
class WebsiteAnalytics {
 public function handle(Request $r, Closure $next){ return $next($r); }
 public function terminate(Request $r,$response): void {
  try{
   if(!Schema::hasTable('website_analytics_events')) return;
   $name=(string)optional($r->route())->getName();
   $map=['submit_search'=>'search','flight_listing'=>'results','flight_booking'=>'checkout','booking_successful'=>'booking','fareleader_ui_search'=>'fareleader_search','fareleader_flight_booking'=>'fareleader_checkout'];
   if(!isset($map[$name])) return;
   if($name==='fareleader_ui_search' && !$r->isMethod('post')) return;
   $event=$map[$name]; $route=$r->route(); $code=$route?($route->parameter('code')??null):null;
   $f=[];
   if($name==='submit_search') $f=['IATAFrom'=>$r->input('location_from'),'IATATo'=>$r->input('location_to'),'FlightType'=>$r->input('flight_types'),'DeptDate'=>$r->input('departure_date'),'RetnDate'=>$r->input('return_date'),'Adults'=>$r->input('adult'),'Childrens'=>$r->input('children'),'Infants'=>$r->input('infant'),'FlightClass'=>$r->input('cabin_classes')];
   elseif($name==='fareleader_ui_search') $f=['IATAFrom'=>$r->input('origin1'),'IATATo'=>$r->input('destination1'),'FlightType'=>$r->input('tripType'),'DeptDate'=>$r->input('departDate1'),'RetnDate'=>$r->input('returnDate1'),'Adults'=>$r->input('adults'),'Childrens'=>$r->input('childs'),'Infants'=>$r->input('infants'),'FlightClass'=>$r->input('classType')];
   elseif($code){$raw=session($code.'.Search-Field');$f=is_string($raw)?(json_decode($raw,true)?:[]):[];}
   $iata = function ($value) {
    $value = strtoupper(trim((string) $value));

    if ($value === '') {
        return null;
    }

    // Exact IATA code, e.g. JFK
    if (preg_match('/^[A-Z]{3}$/', $value)) {
        return $value;
    }

    // Autocomplete format: JFK - Airport Name
    if (preg_match('/^([A-Z]{3})\s*[-–—]\s*.+$/', $value, $matches)) {
        return $matches[1];
    }

    // Do not guess an IATA code from a city or airport name.
    return null;
};
   $ua=strtolower((string)$r->userAgent()); $device=str_contains($ua,'mobile')?'mobile':(str_contains($ua,'tablet')||str_contains($ua,'ipad')?'tablet':'desktop');
   $ref=(string)$r->headers->get('referer',''); $source=$r->input('metaName') ?: (str_contains(strtolower($ref),'fareleader')?'fareleaders':'');
   if(!$source && isset($f['Source']) && strcasecmp((string)$f['Source'],'FareLeader')===0)$source='fareleaders';
   if(str_starts_with($event,'fareleader'))$source='fareleaders'; elseif(!$source)$source='direct';
   $count=null;if($event==='results'&&$code)$count=session($code.'.Search-ResultCount');
   $bookingId=$route?($route->parameter('bookingid')??null):null;
   DB::table('website_analytics_events')->insert(['session_key'=>hash('sha256',(string)$r->session()->getId()),'event_type'=>$event,'source'=>substr((string)$source,0,80),'origin'=>$iata($f['IATAFrom']??null),'destination'=>$iata($f['IATATo']??null),'trip_type'=>$f['FlightType']??null,'depart_date'=>$this->date($f['DeptDate']??null),'return_date'=>$this->date($f['RetnDate']??null),'adults'=>$f['Adults']??null,'children'=>$f['Childrens']??null,'infants'=>$f['Infants']??null,'cabin'=>$f['FlightClass']??null,'result_count'=>is_numeric($count)?(int)$count:null,'booking_id'=>is_numeric($bookingId)?(int)$bookingId:null,'device'=>$device,'ip_hash'=>hash('sha256',(string)$r->ip().config('app.key')),'referrer'=>substr($ref,0,1000),'meta_json'=>json_encode(['route'=>$name]),'created_at'=>now(),'updated_at'=>now()]);
  }catch(\Throwable $e){ report($e); }
 }
 private function date($v){if(!$v)return null;try{return \Carbon\Carbon::parse($v)->format('Y-m-d');}catch(\Throwable $e){return null;}}
}
