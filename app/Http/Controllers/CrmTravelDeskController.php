<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CrmTravelDeskController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) return redirect()->route('crm.login');
        abort_unless(Schema::hasTable('crm_travel_requests'), 503, 'Run php artisan migrate to enable Travel Desk.');
        return null;
    }

    private function adminId(Request $request): int
    {
        return (int) $request->session()->get('crm_admin_id');
    }

    private function typeLabels(): array
    {
        return [
            'flight' => 'Flight',
            'hotel' => 'Hotel',
            'car' => 'Car Rental',
            'package' => 'Holiday Package',
            'custom' => 'Custom Trip',
        ];
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $type = $request->query('type');
        $q = trim((string)$request->query('q',''));

        $query = DB::table('crm_travel_requests')->latest();
        if ($type && array_key_exists($type, $this->typeLabels())) $query->where('type',$type);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('reference','like',"%{$q}%")
                  ->orWhere('customer_name','like',"%{$q}%")
                  ->orWhere('customer_email','like',"%{$q}%")
                  ->orWhere('destination','like',"%{$q}%");
            });
        }

        $requests = $query->paginate(25)->withQueryString();
        $counts = collect($this->typeLabels())->mapWithKeys(fn($label,$key)=>[$key=>DB::table('crm_travel_requests')->where('type',$key)->count()]);
        return view('crm.travel-desk.index', ['requests'=>$requests,'counts'=>$counts,'types'=>$this->typeLabels(),'activeType'=>$type]);
    }

    public function create(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $type = $request->query('type','package');
        if (!array_key_exists($type,$this->typeLabels())) $type='package';
        $admins = DB::table('admins')->where('status','1')->orderBy('name')->get(['id','name','username']);
        return view('crm.travel-desk.create', ['types'=>$this->typeLabels(),'selectedType'=>$type,'admins'=>$admins]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $types = array_keys($this->typeLabels());
        $data = $request->validate([
            'type'=>['required',Rule::in($types)],
            'customer_name'=>['required','string','max:190'],
            'customer_email'=>['nullable','email','max:190'],
            'customer_phone'=>['nullable','string','max:80'],
            'destination'=>['nullable','string','max:190'],
            'start_date'=>['nullable','date'],
            'end_date'=>['nullable','date','after_or_equal:start_date'],
            'travelers'=>['required','integer','min:1','max:99'],
            'currency'=>['required','string','max:8'],
            'service_fee'=>['nullable','numeric','min:0'],
            'discount'=>['nullable','numeric','min:0'],
            'payment_status'=>['required',Rule::in(['Unpaid','Partial','Paid','Refunded'])],
            'status'=>['required',Rule::in(['Draft','Quoted','Pending','Confirmed','Completed','Cancelled'])],
            'assigned_to'=>['nullable','integer'],
            'notes'=>['nullable','string','max:10000'],
            'items'=>['required','array','min:1','max:30'],
            'items.*.item_type'=>['required',Rule::in(['flight','hotel','car','activity','transfer','fee','other'])],
            'items.*.title'=>['required','string','max:190'],
            'items.*.description'=>['nullable','string','max:2000'],
            'items.*.quantity'=>['required','numeric','min:0.01','max:9999'],
            'items.*.unit_price'=>['required','numeric','min:0','max:9999999'],
        ]);

        $subtotal = collect($data['items'])->sum(fn($i)=>(float)$i['quantity']*(float)$i['unit_price']);
        $service = (float)($data['service_fee'] ?? 0);
        $discount = (float)($data['discount'] ?? 0);
        $total = max(0,$subtotal+$service-$discount);
        $adminId = $this->adminId($request);

        $id = DB::transaction(function() use ($data,$subtotal,$service,$discount,$total,$adminId) {
            $ref = 'FH-' . strtoupper(substr($data['type'],0,3)) . '-' . now()->format('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)),0,6));
            $id = DB::table('crm_travel_requests')->insertGetId([
                'reference'=>$ref,'type'=>$data['type'],'status'=>$data['status'],'source'=>'CRM',
                'customer_name'=>$data['customer_name'],'customer_email'=>$data['customer_email'] ?? null,'customer_phone'=>$data['customer_phone'] ?? null,
                'destination'=>$data['destination'] ?? null,'start_date'=>$data['start_date'] ?? null,'end_date'=>$data['end_date'] ?? null,
                'travelers'=>$data['travelers'],'currency'=>strtoupper($data['currency']),'subtotal'=>$subtotal,'service_fee'=>$service,'discount'=>$discount,'total'=>$total,
                'payment_status'=>$data['payment_status'],'notes'=>$data['notes'] ?? null,'assigned_to'=>$data['assigned_to'] ?? $adminId,'created_by'=>$adminId,
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            foreach ($data['items'] as $item) {
                $line=(float)$item['quantity']*(float)$item['unit_price'];
                DB::table('crm_travel_request_items')->insert([
                    'travel_request_id'=>$id,'item_type'=>$item['item_type'],'title'=>$item['title'],'description'=>$item['description'] ?? null,
                    'quantity'=>$item['quantity'],'unit_price'=>$item['unit_price'],'total'=>$line,'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
            return $id;
        });
        return redirect()->route('crm.travel_desk.show',$id)->with('crm_success','Travel request created successfully.');
    }

    public function show(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $travel = DB::table('crm_travel_requests')->where('id',$id)->first();
        abort_unless($travel,404);
        $items = DB::table('crm_travel_request_items')->where('travel_request_id',$id)->orderBy('id')->get();
        $agent = $travel->assigned_to ? DB::table('admins')->where('id',$travel->assigned_to)->first(['id','name','username']) : null;
        return view('crm.travel-desk.show', compact('travel','items','agent'));
    }
}
