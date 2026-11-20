<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class WebsiteAnalyticsController extends Controller{
 private function guard(Request $r){return $r->session()->has('crm_admin_id')?null:redirect()->route('crm.login');}
 public function index(Request $r){if($x=$this->guard($r))return $x;$days=max(1,min(365,(int)$r->query('days',30)));$from=now()->subDays($days);
  $q=DB::table('website_analytics_events')->where('created_at','>=',$from);
  $stats=['searches'=>(clone $q)->whereIn('event_type',['search','fareleader_search'])->count(),'results'=>(clone $q)->where('event_type','results')->count(),'checkouts'=>(clone $q)->whereIn('event_type',['checkout','fareleader_checkout'])->count(),'bookings'=>(clone $q)->where('event_type','booking')->count()];
  $routes=(clone $q)->whereIn('event_type',['search','fareleader_search'])->whereNotNull('origin')->whereNotNull('destination')->select('origin','destination',DB::raw('COUNT(*) searches'))->groupBy('origin','destination')->orderByDesc('searches')->limit(25)->get();
  $sources=(clone $q)->select('source',DB::raw('COUNT(*) events'))->groupBy('source')->orderByDesc('events')->get();
  $devices=(clone $q)->select('device',DB::raw('COUNT(*) events'))->groupBy('device')->orderByDesc('events')->get();
  $noResults=(clone $q)->where('event_type','results')->where('result_count',0)->select('origin','destination',DB::raw('COUNT(*) hits'))->groupBy('origin','destination')->orderByDesc('hits')->limit(20)->get();
  $recent=(clone $q)->orderByDesc('id')->limit(100)->get();

  // FareLeaders conversion funnel: reporting only; no booking/search logic is changed.
  $flBase = DB::table('website_analytics_events')
      ->where('created_at','>=',$from)
      ->where('source','fareleaders');

  $flSessionCount = function(array $types) use ($flBase) {
      return (clone $flBase)
          ->whereIn('event_type',$types)
          ->whereNotNull('session_key')
          ->distinct()
          ->count('session_key');
  };

  $fareleaderFunnel = [
      'results'          => $flSessionCount(['results']),
      'checkout'         => $flSessionCount(['checkout','fareleader_checkout']),
      'contact_entered'  => $flSessionCount(['contact_entered']),
      'payment_section'  => $flSessionCount(['payment_section']),
      'confirm_clicked'  => $flSessionCount(['confirm_clicked']),
      'book_api_success' => $flSessionCount(['book_api_success']),
      'book_api_failed'  => $flSessionCount(['book_api_failed']),
      'booking'          => $flSessionCount(['booking']),
  ];

  return view('crm.website-analytics',compact(
      'days','stats','routes','sources','devices','noResults','recent','fareleaderFunnel'
  ));}
}
