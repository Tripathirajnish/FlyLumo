<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CrmBookingToolsController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }
        return null;
    }

    private function adminId(Request $request): int
    {
        return (int) $request->session()->get('crm_admin_id');
    }

    private function roleSlug(Request $request): string
    {
        $adminId = $this->adminId($request);
        if (!$adminId) return '';

        if (Schema::hasTable('crm_access_roles') && Schema::hasColumn('admins','crm_access_role_id')) {
            return (string) DB::table('admins as a')
                ->leftJoin('crm_access_roles as r','a.crm_access_role_id','=','r.id')
                ->where('a.id',$adminId)
                ->value('r.slug');
        }

        return '';
    }

    private function canManageAll(Request $request): bool
    {
        return in_array($this->roleSlug($request), ['super-admin','manager'], true);
    }

    private function assertBookingWorkAccess(Request $request, Booking $booking): void
    {
        if ($this->canManageAll($request)) return;

        $adminId = $this->adminId($request);
        abort_unless(
            $adminId && (int)$booking->assign_to === $adminId,
            403,
            'This booking must be assigned to you before you can perform this action.'
        );
    }

    private function money($value): float
    {
        $clean = preg_replace('/[^0-9.\-]/','',(string)$value);
        return is_numeric($clean) ? round((float)$clean,2) : 0.0;
    }

    private function activeAdmins()
    {
        return DB::table('admins')
            ->where('status','1')
            ->when(
                Schema::hasColumn('admins','crm_deleted_at'),
                fn($q) => $q->whereNull('crm_deleted_at')
            )
            ->orderBy('name')
            ->get(['id','name','username']);
    }

    public function manualCreate(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        return view('crm.manual-booking-create', [
            'admins' => $this->canManageAll($request) ? $this->activeAdmins() : collect(),
            'canAssignOthers' => $this->canManageAll($request),
            'currentAdminId' => $this->adminId($request),
        ]);
    }

    public function manualStore(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('crm_manual_booking_meta'),
            503,
            'Run the Manual Booking + Invoice CRM migration first.'
        );

        $canAssignOthers = $this->canManageAll($request);
        $adminId = $this->adminId($request);

        $data = $request->validate([
            'customer_name' => ['required','string','max:190'],
            'customer_email' => ['nullable','email','max:190'],
            'customer_phone' => ['nullable','string','max:80'],
            'trip_type' => ['required', Rule::in(['one_way','round_trip','multi_city'])],
            'channel' => ['required', Rule::in(['Phone','Email','Offline','Walk-in','Other'])],
            'booking_status' => ['required', Rule::in(['Fresh','Pending','Confirmed','Ticketed'])],
            'assigned_to' => ['nullable','integer','exists:admins,id'],
            'pnr' => ['nullable','string','max:100'],
            'ticket_number' => ['nullable','string','max:190'],
            'notes' => ['nullable','string','max:5000'],

            'passengers' => ['required','array','min:1','max:20'],
            'passengers.*.title' => ['nullable','string','max:20'],
            'passengers.*.first_name' => ['required','string','max:100'],
            'passengers.*.middle_name' => ['nullable','string','max:100'],
            'passengers.*.last_name' => ['required','string','max:100'],
            'passengers.*.type' => ['required', Rule::in(['Adult','Child','Infant'])],
            'passengers.*.dob' => ['nullable','date'],
            'passengers.*.nationality' => ['nullable','string','max:100'],

            'segments' => ['required','array','min:1','max:30'],
            'segments.*.leg_no' => ['required','integer','min:1','max:20'],
            'segments.*.from' => ['required','string','max:10'],
            'segments.*.to' => ['required','string','max:10'],
            'segments.*.departure_at' => ['required','date'],
            'segments.*.arrival_at' => ['required','date'],
            'segments.*.airline_name' => ['required','string','max:190'],
            'segments.*.airline_code' => ['nullable','string','max:10'],
            'segments.*.flight_number' => ['nullable','string','max:30'],
            'segments.*.cabin' => ['required','string','max:60'],

            'base_fare' => ['nullable','numeric','min:0','max:99999999'],
            'taxes' => ['nullable','numeric','min:0','max:99999999'],
            'service_fee' => ['nullable','numeric','min:0','max:99999999'],
            'addons' => ['nullable','numeric','min:0','max:99999999'],
            'discount' => ['nullable','numeric','min:0','max:99999999'],
            'paid_amount' => ['nullable','numeric','min:0','max:99999999'],
            'payment_status' => ['required', Rule::in(['Untracked','Pending','Paid','Partially Paid'])],
            'payment_method' => ['nullable','string','max:100'],
            'supplier_cost' => ['nullable','numeric','min:0','max:99999999'],
            'processing_cost' => ['nullable','numeric','min:0','max:99999999'],
            'other_cost' => ['nullable','numeric','min:0','max:99999999'],
        ]);

        foreach ($data['segments'] as $segment) {
            abort_if(
                strtotime($segment['arrival_at']) <= strtotime($segment['departure_at']),
                422,
                'Each segment arrival time must be after its departure time.'
            );
        }

        $assignedTo = $canAssignOthers && !empty($data['assigned_to'])
            ? (int)$data['assigned_to']
            : $adminId;

        $passengers = collect($data['passengers'])->values()->map(function($p) {
            return [
                'Title' => trim((string)($p['title'] ?? '')),
                'FirstName' => trim((string)$p['first_name']),
                'MiddleName' => trim((string)($p['middle_name'] ?? '')),
                'LastName' => trim((string)$p['last_name']),
                'PassengerType' => $p['type'],
                'DOB' => $p['dob'] ?? null,
                'Nationality' => trim((string)($p['nationality'] ?? '')),
            ];
        })->all();

        $segments = collect($data['segments'])
            ->sortBy(fn($s) => sprintf('%03d-%s', (int)$s['leg_no'], $s['departure_at']))
            ->values()
            ->map(function($s) {
                return [
                    'LegNo' => (int)$s['leg_no'],
                    'Departure_Iata' => strtoupper(trim((string)$s['from'])),
                    'Arrival_Iata' => strtoupper(trim((string)$s['to'])),
                    'Departure_Date' => $s['departure_at'],
                    'Arrival_Date' => $s['arrival_at'],
                    'AirlineName' => trim((string)$s['airline_name']),
                    'Airline' => strtoupper(trim((string)($s['airline_code'] ?? ''))),
                    'FlightNum' => trim((string)($s['flight_number'] ?? '')),
                    'FlightClass' => trim((string)$s['cabin']),
                ];
            });

        $grouped = [];
        foreach ($segments as $segment) {
            $leg = max(1, (int)$segment['LegNo']);
            $grouped['Rph_'.$leg][] = $segment;
        }

        $base = $this->money($data['base_fare'] ?? 0);
        $taxes = $this->money($data['taxes'] ?? 0);
        $service = $this->money($data['service_fee'] ?? 0);
        $addons = $this->money($data['addons'] ?? 0);
        $discount = $this->money($data['discount'] ?? 0);
        $total = max(0, round($base + $taxes + $service + $addons - $discount, 2));
        $paid = min($total, $this->money($data['paid_amount'] ?? 0));
        $supplier = $this->money($data['supplier_cost'] ?? 0);
        $processing = $this->money($data['processing_cost'] ?? 0);
        $otherCost = $this->money($data['other_cost'] ?? 0);

        $pricePayload = [
            'BaseFare' => $base,
            'Taxes' => $taxes,
            'ServiceFee' => $service,
            'Addons' => $addons,
            'Discount' => $discount,
            'Total' => $total,
        ];

        $otherPayload = [
            'ManualBooking' => true,
            'TripType' => $data['trip_type'],
            'Channel' => $data['channel'],
            'CreatedByAdminId' => $adminId,
            'Notes' => $data['notes'] ?? null,
        ];

        $booking = DB::transaction(function () use (
            $data,$assignedTo,$adminId,$passengers,$grouped,$pricePayload,$otherPayload,
            $base,$taxes,$service,$addons,$discount,$total,$paid,$supplier,$processing,$otherCost
        ) {
            $b = new Booking();

            if (Schema::hasColumn('bookings','prefix')) $b->prefix = 'FH';
            if (Schema::hasColumn('bookings','i_field_encode')) $b->i_field_encode = '';

            $b->o_Name = $data['customer_name'];
            $b->o_Email = $data['customer_email'] ?? '';
            $b->o_Phone = $data['customer_phone'] ?? '';
            $b->o_Passenger = json_encode($passengers, JSON_UNESCAPED_UNICODE);
            $b->o_SectorGo = json_encode($grouped['Rph_1'] ?? [], JSON_UNESCAPED_UNICODE);
            $b->o_SectorBack = json_encode($grouped['Rph_2'] ?? [], JSON_UNESCAPED_UNICODE);

            if (Schema::hasColumn('bookings','o_Itinerary')) {
                $b->o_Itinerary = json_encode($grouped, JSON_UNESCAPED_UNICODE);
            }

            $b->o_Price = json_encode($pricePayload, JSON_UNESCAPED_UNICODE);
            $b->o_FullPrice = number_format($total,2,'.','');

            if (Schema::hasColumn('bookings','o_Credit')) $b->o_Credit = null;
            if (Schema::hasColumn('bookings','o_Billing')) $b->o_Billing = json_encode([], JSON_UNESCAPED_UNICODE);
            if (Schema::hasColumn('bookings','o_Other')) $b->o_Other = json_encode($otherPayload, JSON_UNESCAPED_UNICODE);
            if (Schema::hasColumn('bookings','is_markup')) $b->is_markup = 'No';

            $b->source = 'Manual';
            $b->status = $data['booking_status'];
            if (Schema::hasColumn('bookings','assign_to')) $b->assign_to = $assignedTo ?: null;
            $b->save();

            DB::table('crm_manual_booking_meta')->insert([
                'booking_id' => $b->id,
                'trip_type' => $data['trip_type'],
                'channel' => $data['channel'],
                'itinerary_json' => json_encode($grouped, JSON_UNESCAPED_UNICODE),
                'financial_json' => json_encode([
                    'base_fare'=>$base,
                    'taxes'=>$taxes,
                    'service_fee'=>$service,
                    'addons'=>$addons,
                    'discount'=>$discount,
                    'total'=>$total,
                    'paid_amount'=>$paid,
                ], JSON_UNESCAPED_UNICODE),
                'notes' => $data['notes'] ?? null,
                'created_by' => $adminId ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasTable('booking_crm_meta')) {
                $payload = [
                    'booking_id'=>$b->id,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ];

                $candidate = [
                    'pnr' => $data['pnr'] ?? null,
                    'ticket_number' => $data['ticket_number'] ?? null,
                    'payment_status' => $data['payment_status'],
                    'ticket_status' => !empty($data['ticket_number']) ? 'Ticketed' : 'Untracked',
                    'via_source' => 'Manual / '.$data['channel'],
                    'operations_note' => $data['notes'] ?? null,
                ];

                foreach ($candidate as $column=>$value) {
                    if (Schema::hasColumn('booking_crm_meta',$column)) {
                        $payload[$column] = $value;
                    }
                }

                DB::table('booking_crm_meta')->insert($payload);
            }

            if (Schema::hasTable('payment_crm_meta')) {
                $payload = [
                    'booking_id'=>$b->id,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ];

                $candidate = [
                    'payment_status'=>$data['payment_status'],
                    'payment_method'=>$data['payment_method'] ?? 'Manual',
                    'paid_amount'=>$paid,
                    'assigned_to'=>$assignedTo ?: null,
                    'processor'=>'Manual / Offline',
                    'operations_note'=>'Manual booking payment record',
                ];

                foreach ($candidate as $column=>$value) {
                    if (Schema::hasColumn('payment_crm_meta',$column)) {
                        $payload[$column] = $value;
                    }
                }

                DB::table('payment_crm_meta')->insert($payload);
            }

            if (Schema::hasTable('booking_financials')) {
                $gross = round($total - $supplier,2);
                $net = round($gross - $processing - $otherCost,2);

                DB::table('booking_financials')->insert([
                    'booking_id'=>$b->id,
                    'selling_amount'=>$total,
                    'supplier_cost'=>$supplier,
                    'processing_cost'=>$processing,
                    'other_cost'=>$otherCost,
                    'gross_profit'=>$gross,
                    'net_profit'=>$net,
                    'updated_by'=>$adminId ?: null,
                    'finance_note'=>'Created with manual booking',
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            if (Schema::hasTable('booking_status_history')) {
                DB::table('booking_status_history')->insert([
                    'booking_id'=>$b->id,
                    'admin_id'=>$adminId ?: null,
                    'old_status'=>'Manual Created',
                    'new_status'=>$b->status,
                    'note'=>'Manual / offline booking created in CRM.',
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            if ($assignedTo && Schema::hasTable('booking_assigns')) {
                $admin = DB::table('admins')->where('id',$assignedTo)->first();

                DB::table('booking_assigns')->insert([
                    'booking_id'=>$b->id,
                    'role_id'=>$admin->role_id ?? null,
                    'assign_to'=>$assignedTo,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }

            return $b;
        });

        return redirect()
            ->route('crm.booking_detail',$booking->id)
            ->with('crm_success','Manual booking #FH'.$booking->id.' created successfully.');
    }

    public function invoicePrepare(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('crm_invoices'),
            503,
            'Run the Manual Booking + Invoice CRM migration first.'
        );

        $booking = Booking::findOrFail($id);
        $this->assertBookingWorkAccess($request,$booking);

        $manual = Schema::hasTable('crm_manual_booking_meta')
            ? DB::table('crm_manual_booking_meta')->where('booking_id',$id)->first()
            : null;

        $manualFinance = [];
        if ($manual && $manual->financial_json) {
            $manualFinance = json_decode($manual->financial_json,true) ?: [];
        }

        $total = $this->money($booking->o_FullPrice);

        $defaults = [
            'base_fare' => $manualFinance['base_fare'] ?? $total,
            'taxes' => $manualFinance['taxes'] ?? 0,
            'service_fee' => $manualFinance['service_fee'] ?? 0,
            'addons' => $manualFinance['addons'] ?? 0,
            'discount' => $manualFinance['discount'] ?? 0,
            'paid_amount' => $manualFinance['paid_amount'] ?? 0,
        ];

        if (Schema::hasTable('payment_crm_meta')) {
            $payment = DB::table('payment_crm_meta')->where('booking_id',$id)->first();

            if ($payment && Schema::hasColumn('payment_crm_meta','paid_amount')) {
                $defaults['paid_amount'] = $this->money($payment->paid_amount);
            }
        }

        $invoices = DB::table('crm_invoices')
            ->where('booking_id',$id)
            ->orderByDesc('id')
            ->get();

        return view('crm.invoice-create', compact('booking','defaults','invoices'));
    }

    public function invoiceStore(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('crm_invoices'),
            503,
            'Run the Manual Booking + Invoice CRM migration first.'
        );

        $booking = Booking::findOrFail($id);
        $this->assertBookingWorkAccess($request,$booking);

        $data = $request->validate([
            'issue_date'=>['required','date'],
            'due_date'=>['nullable','date','after_or_equal:issue_date'],
            'currency'=>['required','string','size:3'],
            'base_fare'=>['required','numeric','min:0'],
            'taxes'=>['nullable','numeric','min:0'],
            'service_fee'=>['nullable','numeric','min:0'],
            'addons'=>['nullable','numeric','min:0'],
            'discount'=>['nullable','numeric','min:0'],
            'paid_amount'=>['nullable','numeric','min:0'],
            'customer_name'=>['required','string','max:190'],
            'customer_email'=>['nullable','email','max:190'],
            'customer_phone'=>['nullable','string','max:80'],
            'notes'=>['nullable','string','max:5000'],
        ]);

        $base = $this->money($data['base_fare']);
        $taxes = $this->money($data['taxes'] ?? 0);
        $service = $this->money($data['service_fee'] ?? 0);
        $addons = $this->money($data['addons'] ?? 0);
        $discount = $this->money($data['discount'] ?? 0);
        $total = max(0, round($base + $taxes + $service + $addons - $discount,2));
        $paid = min($total,$this->money($data['paid_amount'] ?? 0));
        $balance = max(0,round($total-$paid,2));

        $crmMeta = Schema::hasTable('booking_crm_meta')
            ? DB::table('booking_crm_meta')->where('booking_id',$id)->first()
            : null;

        $manual = Schema::hasTable('crm_manual_booking_meta')
            ? DB::table('crm_manual_booking_meta')->where('booking_id',$id)->first()
            : null;

        $snapshot = [
            'booking' => [
                'id'=>$booking->id,
                'prefix'=>$booking->prefix ?? 'FH',
                'status'=>$booking->status,
                'source'=>$booking->source,
                'created_at'=>(string)$booking->created_at,
            ],
            'passengers' => json_decode((string)$booking->o_Passenger,true) ?: [],
            'sector_go' => json_decode((string)$booking->o_SectorGo,true) ?: [],
            'sector_back' => json_decode((string)$booking->o_SectorBack,true) ?: [],
            'itinerary' => $manual && $manual->itinerary_json
                ? (json_decode($manual->itinerary_json,true) ?: [])
                : (Schema::hasColumn('bookings','o_Itinerary')
                    ? (json_decode((string)$booking->o_Itinerary,true) ?: [])
                    : []),
            'pnr' => $crmMeta->pnr ?? null,
            'ticket_number' => $crmMeta->ticket_number ?? null,
        ];

        $invoiceId = DB::transaction(function () use (
            $booking,$data,$base,$taxes,$service,$addons,$discount,$total,$paid,$balance,$snapshot,$request
        ) {
            $id = DB::table('crm_invoices')->insertGetId([
                'booking_id'=>$booking->id,
                'invoice_number'=>'TMP-'.Str::uuid(),
                'status'=>'Issued',
                'currency'=>strtoupper($data['currency']),
                'issue_date'=>$data['issue_date'],
                'due_date'=>$data['due_date'] ?? null,
                'base_fare'=>$base,
                'taxes'=>$taxes,
                'service_fee'=>$service,
                'addons'=>$addons,
                'discount'=>$discount,
                'total_amount'=>$total,
                'paid_amount'=>$paid,
                'balance_due'=>$balance,
                'customer_name'=>$data['customer_name'],
                'customer_email'=>$data['customer_email'] ?? null,
                'customer_phone'=>$data['customer_phone'] ?? null,
                'notes'=>$data['notes'] ?? null,
                'snapshot_json'=>json_encode($snapshot,JSON_UNESCAPED_UNICODE),
                'created_by'=>$this->adminId($request) ?: null,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);

            $invoiceNumber = 'FH-INV-'.date('Y').'-'.str_pad((string)$id,6,'0',STR_PAD_LEFT);

            DB::table('crm_invoices')
                ->where('id',$id)
                ->update([
                    'invoice_number'=>$invoiceNumber,
                    'updated_at'=>now()
                ]);

            return $id;
        });

        return redirect()
            ->route('crm.invoice_show',$invoiceId)
            ->with('crm_success','Invoice generated successfully.');
    }

    public function invoiceShow(Request $request, int $invoice)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(
            Schema::hasTable('crm_invoices'),
            503,
            'Run the Manual Booking + Invoice CRM migration first.'
        );

        $invoiceRow = DB::table('crm_invoices')->where('id',$invoice)->first();
        abort_unless($invoiceRow,404);

        $booking = Booking::findOrFail($invoiceRow->booking_id);
        $this->assertBookingWorkAccess($request,$booking);

        $snapshot = json_decode((string)$invoiceRow->snapshot_json,true) ?: [];
        $setting = getAllSetting();

        return view('crm.invoice', [
            'invoice'=>$invoiceRow,
            'booking'=>$booking,
            'snapshot'=>$snapshot,
            'setting'=>$setting,
        ]);
    }
}
