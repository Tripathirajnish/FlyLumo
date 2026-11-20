<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrackingController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }
        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $q = trim((string)$request->query('q'));
        $type = trim((string)$request->query('type'));
        $agent = trim((string)$request->query('agent'));
        $dateFrom = trim((string)$request->query('date_from'));
        $dateTo = trim((string)$request->query('date_to'));

        $events = collect();

        // 1) Existing booking stage history.
        if (Schema::hasTable('booking_stages')) {
            $rows = DB::table('booking_stages as s')
                ->leftJoin('bookings as b','s.booking_id','=','b.id')
                ->leftJoin('admins as a','s.admin_id','=','a.id')
                ->select(
                    's.id','s.booking_id','s.status','s.admin_id','s.created_at',
                    'b.prefix','b.o_Name','b.o_Email','b.o_Phone',
                    'a.name as admin_name'
                )
                ->orderByDesc('s.id')
                ->limit(1000)
                ->get();

            foreach ($rows as $r) {
                $events->push([
                    'source' => 'booking_stage',
                    'source_id' => $r->id,
                    'booking_id' => $r->booking_id,
                    'booking_ref' => ($r->prefix ?: 'FH').$r->booking_id,
                    'customer' => $r->o_Name ?: 'Guest',
                    'email' => $r->o_Email,
                    'phone' => $r->o_Phone,
                    'type' => 'Stage',
                    'activity' => $this->humanize($r->status ?: 'Booking Stage'),
                    'old_value' => null,
                    'new_value' => $r->status,
                    'agent_id' => $r->admin_id,
                    'agent' => $r->admin_name ?: ($r->admin_id ? 'Admin #'.$r->admin_id : 'System'),
                    'created_at' => $r->created_at,
                ]);
            }
        }

        // 2) Universal CRM change log installed by Universal Actions.
        if (Schema::hasTable('crm_change_logs')) {
            $rows = DB::table('crm_change_logs as c')
                ->leftJoin('admins as a','c.admin_id','=','a.id')
                ->where('c.module','booking')
                ->select('c.*','a.name as admin_name')
                ->orderByDesc('c.id')
                ->limit(1500)
                ->get();

            $bookingIds = $rows->pluck('record_key')->filter(fn($v) => ctype_digit((string)$v))->map(fn($v)=>(int)$v)->unique()->values();
            $bookings = collect();
            if ($bookingIds->count() && Schema::hasTable('bookings')) {
                $bookings = DB::table('bookings')->whereIn('id',$bookingIds)->get()->keyBy('id');
            }

            foreach ($rows as $r) {
                $bookingId = ctype_digit((string)$r->record_key) ? (int)$r->record_key : null;
                $b = $bookingId ? $bookings->get($bookingId) : null;

                $events->push([
                    'source' => 'crm_change',
                    'source_id' => $r->id,
                    'booking_id' => $bookingId,
                    'booking_ref' => $b ? (($b->prefix ?: 'FH').$b->id) : (string)$r->record_key,
                    'customer' => $b->o_Name ?? 'Booking',
                    'email' => $b->o_Email ?? null,
                    'phone' => $b->o_Phone ?? null,
                    'type' => $this->classifyField($r->field ?? '', $r->action ?? ''),
                    'activity' => $this->humanize(($r->field ?: 'Field').' '.($r->action ?: 'changed')),
                    'old_value' => $this->compactValue($r->old_value),
                    'new_value' => $this->compactValue($r->new_value),
                    'agent_id' => $r->admin_id,
                    'agent' => $r->admin_name ?: ($r->admin_id ? 'Admin #'.$r->admin_id : 'System'),
                    'created_at' => $r->created_at,
                ]);
            }
        }

        // 3) Actionable follow-ups, kept distinct from generic remarks.
        if (Schema::hasTable('crm_followups')) {
            $rows = DB::table('crm_followups as f')
                ->leftJoin('bookings as b','f.booking_id','=','b.id')
                ->leftJoin('admins as a','f.assigned_to','=','a.id')
                ->select(
                    'f.id','f.booking_id','f.subject','f.followup_type','f.priority','f.status',
                    'f.assigned_to','f.due_at','f.created_at','f.updated_at','f.completed_at',
                    'b.prefix','b.o_Name','b.o_Email','b.o_Phone','a.name as admin_name'
                )
                ->orderByDesc('f.id')
                ->limit(1000)
                ->get();

            foreach ($rows as $r) {
                $events->push([
                    'source' => 'followup',
                    'source_id' => $r->id,
                    'booking_id' => $r->booking_id,
                    'booking_ref' => $r->booking_id ? (($r->prefix ?: 'FH').$r->booking_id) : 'No booking',
                    'customer' => $r->o_Name ?: 'Unlinked follow-up',
                    'email' => $r->o_Email,
                    'phone' => $r->o_Phone,
                    'type' => 'Follow-up',
                    'activity' => $this->humanize($r->followup_type).' Â· '.$r->subject,
                    'old_value' => null,
                    'new_value' => $r->status.' / '.$r->priority,
                    'agent_id' => $r->assigned_to,
                    'agent' => $r->admin_name ?: ($r->assigned_to ? 'Admin #'.$r->assigned_to : 'Unassigned'),
                    'created_at' => $r->updated_at ?: $r->created_at,
                ]);
            }
        }

        // 4) Append-only manual tracking events for corrections/context.
        if (Schema::hasTable('crm_tracking_events')) {
            $rows = DB::table('crm_tracking_events as t')
                ->leftJoin('bookings as b','t.booking_id','=','b.id')
                ->leftJoin('admins as a','t.admin_id','=','a.id')
                ->select('t.*','b.prefix','b.o_Name','b.o_Email','b.o_Phone','a.name as admin_name')
                ->orderByDesc('t.id')
                ->limit(1000)
                ->get();

            foreach ($rows as $r) {
                $events->push([
                    'source' => 'manual',
                    'source_id' => $r->id,
                    'booking_id' => $r->booking_id,
                    'booking_ref' => $r->booking_id ? (($r->prefix ?: 'FH').$r->booking_id) : 'No booking',
                    'customer' => $r->o_Name ?: 'Manual tracking',
                    'email' => $r->o_Email,
                    'phone' => $r->o_Phone,
                    'type' => $r->event_type ?: 'Manual',
                    'activity' => $r->title,
                    'old_value' => null,
                    'new_value' => $r->note,
                    'agent_id' => $r->admin_id,
                    'agent' => $r->admin_name ?: ($r->admin_id ? 'Admin #'.$r->admin_id : 'System'),
                    'created_at' => $r->created_at,
                ]);
            }
        }

        $events = $events
            ->filter(fn($e) => !empty($e['created_at']))
            ->sortByDesc(fn($e) => strtotime((string)$e['created_at']))
            ->values();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $events = $events->filter(function($e) use ($needle) {
                $hay = mb_strtolower(implode(' ',[
                    $e['booking_ref'] ?? '', $e['customer'] ?? '', $e['email'] ?? '',
                    $e['phone'] ?? '', $e['type'] ?? '', $e['activity'] ?? '',
                    $e['old_value'] ?? '', $e['new_value'] ?? '', $e['agent'] ?? ''
                ]));
                return str_contains($hay,$needle);
            })->values();
        }

        if ($type !== '') {
            $events = $events->filter(fn($e) => ($e['type'] ?? '') === $type)->values();
        }

        if ($agent !== '') {
            $events = $events->filter(fn($e) => (string)($e['agent_id'] ?? '') === (string)$agent)->values();
        }

        if ($dateFrom !== '') {
            $from = strtotime($dateFrom.' 00:00:00');
            $events = $events->filter(fn($e) => strtotime((string)$e['created_at']) >= $from)->values();
        }

        if ($dateTo !== '') {
            $to = strtotime($dateTo.' 23:59:59');
            $events = $events->filter(fn($e) => strtotime((string)$e['created_at']) <= $to)->values();
        }

        $stats = [
            'total' => $events->count(),
            'booking_changes' => $events->whereIn('type',['Booking Change','Passenger','Itinerary','Contact/Billing'])->count(),
            'fare_changes' => $events->where('type','Fare')->count(),
            'payments' => $events->where('type','Payment')->count(),
            'followups' => $events->where('type','Follow-up')->count(),
        ];

        $types = $events->pluck('type')->filter()->unique()->sort()->values();

        $admins = Schema::hasTable('admins')
            ? DB::table('admins')->orderBy('name')->get()
            : collect();

        $perPage = 30;
        $page = max(1,(int)$request->query('page',1));
        $slice = $events->slice(($page-1)*$perPage,$perPage)->values();

        $eventsPaginator = new LengthAwarePaginator(
            $slice,
            $events->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('crm.tracking-v2', compact(
            'eventsPaginator','stats','types','admins','q','type','agent','dateFrom','dateTo'
        ));
    }

    public function storeManual(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        abort_unless(Schema::hasTable('crm_tracking_events'),503,'Tracking V2 migration is missing.');

        $data = $request->validate([
            'booking_id' => ['nullable','integer'],
            'event_type' => ['required','string','max:60'],
            'title' => ['required','string','max:255'],
            'note' => ['nullable','string','max:5000'],
        ]);

        DB::table('crm_tracking_events')->insert([
            'booking_id' => $data['booking_id'] ?: null,
            'event_type' => $data['event_type'],
            'title' => $data['title'],
            'note' => $data['note'] ?? null,
            'admin_id' => (int)$request->session()->get('crm_admin_id') ?: null,
            'created_at' => now(),
        ]);

        return back()->with('crm_success','Tracking note added.');
    }

    private function humanize(string $value): string
    {
        $value = preg_replace('/([a-z])([A-Z])/','$1 $2',$value);
        $value = str_replace(['_','-'],' ',$value);
        return trim(ucwords($value));
    }

    private function classifyField(string $field, string $action): string
    {
        $f = mb_strtolower($field.' '.$action);

        if (str_contains($f,'fare') || str_contains($f,'price') || str_contains($f,'markup') || str_contains($f,'total')) return 'Fare';
        if (str_contains($f,'payment') || str_contains($f,'card') || str_contains($f,'refund') || str_contains($f,'transaction')) return 'Payment';
        if (str_contains($f,'flight') || str_contains($f,'segment') || str_contains($f,'airline') || str_contains($f,'departure') || str_contains($f,'arrival')) return 'Itinerary';
        if (str_contains($f,'passenger') || str_contains($f,'name') || str_contains($f,'dob') || str_contains($f,'passport') || str_contains($f,'gender')) return 'Passenger';
        if (str_contains($f,'email') || str_contains($f,'phone') || str_contains($f,'billing') || str_contains($f,'address')) return 'Contact/Billing';
        if (str_contains($f,'ticket') || str_contains($f,'pnr')) return 'Ticketing';

        return 'Booking Change';
    }

    private function compactValue($value): ?string
    {
        if ($value === null || $value === '') return null;

        $value = (string)$value;
        $decoded = json_decode($value,true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $value = json_encode($decoded,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        }

        return mb_strlen($value) > 300 ? mb_substr($value,0,300).'â€¦' : $value;
    }
}

