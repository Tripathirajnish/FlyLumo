<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingServicingController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) return redirect()->route('crm.login');
        return null;
    }

    // FH card visibility (31 Oct 2026): true only for the Super Admin CRM role (fails closed).
    private function isCardAdmin(Request $request): bool
    {
        if (!Schema::hasTable('crm_access_roles')) return false;
        return (string) DB::table('admins as a')
            ->leftJoin('crm_access_roles as r', 'a.crm_access_role_id', '=', 'r.id')
            ->where('a.id', (int) $request->session()->get('crm_admin_id'))
            ->value('r.slug') === 'super-admin';
    }

    private function decode($value): array
    {
        if (!$value) return [];
        $v = json_decode((string)$value, true);
        return is_array($v) ? $v : [];
    }

    private function log(Request $request, Booking $booking, string $area, string $action, $before, $after, ?string $note = null): void
    {
        if (!Schema::hasTable('booking_service_history')) return;
        DB::table('booking_service_history')->insert([
            'booking_id'=>$booking->id,'admin_id'=>(int)$request->session()->get('crm_admin_id'),
            'area'=>$area,'action'=>$action,'before_json'=>json_encode($before, JSON_UNESCAPED_UNICODE),
            'after_json'=>json_encode($after, JSON_UNESCAPED_UNICODE),'note'=>$note,'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    public function passengerSave(Request $request, int $id, ?int $index = null)
    {
        if ($r=$this->guard($request)) return $r;
        $b=Booking::findOrFail($id);
        $d=$request->validate([
            'AgeType'=>'required|string|max:20','Name'=>'required|string|max:100','MiddleName'=>'nullable|string|max:100',
            'Surname'=>'required|string|max:100','BirthDay'=>'nullable|string|max:40','GenderType'=>'nullable|string|max:20',
            'Nationality'=>'nullable|string|max:80','PassportNumber'=>'nullable|string|max:80','PassportExpiry'=>'nullable|string|max:40',
        ]);
        $p=$this->decode($b->o_Passenger); $before=$p;
        if ($index === null) $p[]=$d; else { abort_unless(array_key_exists($index,$p),404); $p[$index]=array_merge((array)$p[$index],$d); }
        $b->o_Passenger=json_encode(array_values($p),JSON_UNESCAPED_UNICODE);
        if (!empty($p[0])) $b->o_Name=trim(($p[0]['Name']??'').' '.($p[0]['MiddleName']??'').' '.($p[0]['Surname']??''));
        $b->save(); $this->log($request,$b,'Passenger',$index===null?'Passenger added':'Passenger updated',$before,$p);
        return back()->with('crm_success',$index===null?'Passenger added.':'Passenger updated.');
    }

    public function passengerDelete(Request $request, int $id, int $index)
    {
        if ($r=$this->guard($request)) return $r; $b=Booking::findOrFail($id); $p=$this->decode($b->o_Passenger); $before=$p;
        abort_unless(array_key_exists($index,$p),404); array_splice($p,$index,1); $b->o_Passenger=json_encode(array_values($p),JSON_UNESCAPED_UNICODE);
        if (!empty($p[0])) $b->o_Name=trim(($p[0]['Name']??'').' '.($p[0]['MiddleName']??'').' '.($p[0]['Surname']??'')); $b->save();
        $this->log($request,$b,'Passenger','Passenger removed',$before,$p); return back()->with('crm_success','Passenger removed from CRM booking.');
    }

    public function segmentSave(Request $request, int $id, string $trip, ?int $index = null)
    {
        if ($r=$this->guard($request)) return $r; abort_unless(in_array($trip,['outbound','return'],true),404); $b=Booking::findOrFail($id);
        $d=$request->validate([
            'Departure_Iata'=>'required|string|max:10','Departure_City'=>'nullable|string|max:100','Departure_Name'=>'nullable|string|max:150','Departure_Date'=>'required|string|max:50',
            'Arrival_Iata'=>'required|string|max:10','Arrival_City'=>'nullable|string|max:100','Arrival_Name'=>'nullable|string|max:150','Arrival_Date'=>'required|string|max:50',
            'Airline'=>'nullable|string|max:10','AirlineName'=>'required|string|max:150','FlightNum'=>'nullable|string|max:30','FlightClass'=>'nullable|string|max:50',
            'BookingClass'=>'nullable|string|max:20','Bag_Count'=>'nullable|string|max:30','PNR'=>'nullable|string|max:100',
        ]);
        $field=$trip==='outbound'?'o_SectorGo':'o_SectorBack'; $segments=$this->decode($b->{$field}); $before=$segments;
        $d['Rph']=$trip==='outbound'?1:2;
        if ($index===null) $segments[]=$d; else { abort_unless(array_key_exists($index,$segments),404); $segments[$index]=array_merge((array)$segments[$index],$d); }
        $b->{$field}=json_encode(array_values($segments),JSON_UNESCAPED_UNICODE); $b->save();
        $this->log($request,$b,'Itinerary',$index===null?'Segment added':'Segment updated',$before,$segments,ucfirst($trip));
        return back()->with('crm_success',$index===null?'Flight segment added.':'Flight segment updated.');
    }

    public function segmentDelete(Request $request, int $id, string $trip, int $index)
    {
        if ($r=$this->guard($request)) return $r; abort_unless(in_array($trip,['outbound','return'],true),404); $b=Booking::findOrFail($id); $field=$trip==='outbound'?'o_SectorGo':'o_SectorBack';
        $segments=$this->decode($b->{$field}); $before=$segments; abort_unless(array_key_exists($index,$segments),404); array_splice($segments,$index,1);
        $b->{$field}=json_encode(array_values($segments),JSON_UNESCAPED_UNICODE); $b->save(); $this->log($request,$b,'Itinerary','Segment removed',$before,$segments,ucfirst($trip));
        return back()->with('crm_success','Flight segment removed.');
    }

    public function contactBilling(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r; $b=Booking::findOrFail($id);
        $d=$request->validate(['email'=>'required|email|max:190','phone'=>'nullable|string|max:60','billing_address'=>'nullable|string|max:255','billing_city'=>'nullable|string|max:100','billing_state'=>'nullable|string|max:100','billing_pincode'=>'nullable|string|max:30','billing_country'=>'nullable|string|max:100','billing_mobile_telcode'=>'nullable|string|max:20','billing_mobile'=>'nullable|string|max:50']);
        $before=['email'=>$b->o_Email,'phone'=>$b->o_Phone,'billing'=>$this->decode($b->o_Billing)];
        $billing=$this->decode($b->o_Billing); foreach(['billing_address','billing_city','billing_state','billing_pincode','billing_country','billing_mobile_telcode','billing_mobile'] as $k) $billing[$k]=$d[$k]??null;
        $b->o_Email=$d['email']; $b->o_Phone=$d['phone']??null; $b->o_Billing=json_encode($billing,JSON_UNESCAPED_UNICODE); $b->save();
        $this->log($request,$b,'Contact & Billing','Contact/billing updated',$before,['email'=>$b->o_Email,'phone'=>$b->o_Phone,'billing'=>$billing]); return back()->with('crm_success','Contact and billing updated.');
    }

    public function reprice(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r; $b=Booking::findOrFail($id); $d=$request->validate(['new_total'=>'required|numeric|min:0','markup_amount'=>'nullable|numeric|min:0','reason'=>'required|string|max:500']);
        $old=(float)$b->o_FullPrice; $b->o_FullPrice=$d['new_total']; $b->save();
        if (Schema::hasTable('booking_crm_meta')) DB::table('booking_crm_meta')->updateOrInsert(['booking_id'=>$b->id],['markup_amount'=>$d['markup_amount']??null,'updated_at'=>now(),'created_at'=>now()]);
        if (Schema::hasTable('booking_fare_history')) DB::table('booking_fare_history')->insert(['booking_id'=>$b->id,'admin_id'=>(int)$request->session()->get('crm_admin_id'),'old_total'=>$old,'new_total'=>$d['new_total'],'markup_amount'=>$d['markup_amount']??null,'reason'=>$d['reason'],'created_at'=>now(),'updated_at'=>now()]);
        $this->log($request,$b,'Fare','Booking repriced',['total'=>$old],['total'=>(float)$d['new_total'],'markup'=>$d['markup_amount']??null],$d['reason']); return back()->with('crm_success','Current booking fare updated. Previous fare preserved in history.');
    }

    public function cardMetadata(Request $request, int $id)
    {
        if ($r=$this->guard($request)) return $r; $b=Booking::findOrFail($id); abort_unless(Schema::hasTable('booking_card_metadata'),503);
        // FH card visibility (31 Oct 2026): card details may be edited by Super Admin only.
        abort_unless($this->isCardAdmin($request), 403, 'Only Super Admin can view or edit card details.');
        $d=$request->validate(['cardholder_name'=>'nullable|string|max:150','brand'=>'nullable|string|max:50','last4'=>'nullable|digits:4','expire_month'=>'nullable|integer|min:1|max:12','expire_year'=>'nullable|integer|min:2020|max:2200','note'=>'nullable|string|max:500']);
        $before=DB::table('booking_card_metadata')->where('booking_id',$b->id)->first();
        DB::table('booking_card_metadata')->updateOrInsert(['booking_id'=>$b->id],array_merge($d,['updated_at'=>now(),'created_at'=>$before->created_at??now()]));
        $after=DB::table('booking_card_metadata')->where('booking_id',$b->id)->first(); $this->log($request,$b,'Payment card','Safe card metadata updated',$before,$after,'No PAN/CVV stored by this CRM editor.');
        return back()->with('crm_success','Safe card metadata updated. Full card number/CVV were not stored.');
    }
}
