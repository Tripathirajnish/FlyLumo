<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    private function guard(Request $request)
    {
        if (!$request->session()->has('crm_admin_id')) {
            return redirect()->route('crm.login');
        }
        return null;
    }

    private function categories(): array
    {
        return [
            'Booking Confirmation',
            'Booking Update',
            'Cancellation',
            'Follow-up',
            'Payments',
            'Refunds',
            'Ticketing',
            'Travel Reminder',
        ];
    }

    private function sampleVariables(): array
    {
        return [
            '$bookingid' => 'HTB123456',
            '$name' => 'Guest Traveller',
            '$c_name' => 'Guest Traveller',
            '$email' => 'guest@example.com',
            '$c_emailid' => 'guest@example.com',
            '$phone' => '+1 800 555 0123',
            '$c_phone' => '+1 800 555 0123',
            '$pnr' => 'ABC123',
            '$price' => '$699.99',
            '$totalprice' => '$699.99',
            '$passengers' => '1 Adult',
            '$sectorgos' => 'JFK → LHR · Example Airline · 10:25 AM',
            '$sectorreturns' => 'LHR → JFK · Example Airline · 06:15 PM',
            '$header_contact' => '+1 800 555 0123',
            '$content_emailid' => 'support@example.com',
            '$contact' => '+1 800 555 0123',
            '$emailid' => 'support@example.com',
            '$created_at' => now()->format('d M Y, h:i A'),
            '$creditcard_no' => '4242',
            '$adult_no' => '1',
            '$adult_price' => '699.99',
            '$adult_subtotal' => '699.99',
            '$child_no' => '0',
            '$child_price' => '0.00',
            '$child_subtotal' => '0.00',
            '$infant_no' => '0',
            '$infant_price' => '0.00',
            '$infant_subtotal' => '0.00',
            '$total' => '699.99',
            '$webcheckin' => '',
            '$cancellation' => '',
            '$tnc' => '<p>Sample terms and conditions.</p>',
            '{{ asset("mail_images/fareshunt-logo.png") }}' => asset('mail_images/fareshunt-logo.png'),
            "{{ asset('mail_images/fareshunt-logo.png') }}" => asset('mail_images/fareshunt-logo.png'),
        ];
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;

        $query = DB::table('mail_templates');
        $hasCategory = Schema::hasColumn('mail_templates', 'category');
        $hasActive = Schema::hasColumn('mail_templates', 'is_active');

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($sub) use ($q, $hasCategory) {
                $sub->where('label', 'like', '%'.$q.'%')
                    ->orWhere('type', 'like', '%'.$q.'%')
                    ->orWhere('subject', 'like', '%'.$q.'%');
                if ($hasCategory) $sub->orWhere('category', 'like', '%'.$q.'%');
            });
        }

        if ($hasCategory && $request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }
        if ($hasActive && in_array($request->input('status'), ['active','inactive'], true)) {
            $query->where('is_active', $request->status === 'active');
        }

        $templates = $query->orderBy('type')->orderBy('label')->get();
        $total = DB::table('mail_templates')->count();
        $active = $hasActive ? DB::table('mail_templates')->where('is_active', 1)->count() : $total;
        $inactive = max(0, $total - $active);
        $system = Schema::hasColumn('mail_templates', 'is_system') ? DB::table('mail_templates')->where('is_system',1)->count() : 0;

        return view('crm.email-templates-v2', [
            'templates' => $templates,
            'categories' => $this->categories(),
            'stats' => compact('total','active','inactive','system'),
            'hasV2Schema' => $hasCategory && $hasActive,
        ]);
    }

    public function create(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        return view('crm.email-template-form-v2', [
            'template' => null,
            'categories' => $this->categories(),
            'variables' => array_keys($this->sampleVariables()),
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $data = $this->validated($request);
        $payload = $this->payload($data, true);
        $id = DB::table('mail_templates')->insertGetId($payload);
        return redirect()->route('crm.email_template_edit', $id)->with('crm_success','Email template created successfully.');
    }

    public function edit(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $template = DB::table('mail_templates')->where('id',$id)->first();
        abort_unless($template,404);
        return view('crm.email-template-form-v2', [
            'template' => $template,
            'categories' => $this->categories(),
            'variables' => array_keys($this->sampleVariables()),
        ]);
    }

    public function update(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(DB::table('mail_templates')->where('id',$id)->exists(),404);
        $data = $this->validated($request);
        DB::table('mail_templates')->where('id',$id)->update($this->payload($data, false));
        return redirect()->route('crm.email_template_edit',$id)->with('crm_success','Email template updated successfully.');
    }

    public function preview(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $template = DB::table('mail_templates')->where('id',$id)->first();
        abort_unless($template,404);
        $isSystemTicketBooking =
            !empty($template->is_system)
            && strcasecmp(trim((string) ($template->label ?? '')), 'Ticket Booking') === 0
            && strcasecmp(trim((string) ($template->category ?? '')), 'Booking Confirmation') === 0;

        if ($isSystemTicketBooking) {
            $booking = (object) [
                'id' => 123456,
                'status' => 'New Booking',
                'created_at' => now(),
                'o_Name' => 'Guest Traveller',
                'o_Email' => 'guest@example.com',
                'o_Phone' => '+1 800 555 0123',
                'o_FullPrice' => 699.99,
                'o_Price' => '{}',
            ];

            return view('emails.booking-confirmation', [
                'booking' => $booking,
                'setting' => getAllSetting(),
                'sectorGo' => [[
                    'Departure_Iata' => 'DEN',
                    'Departure_City' => 'Denver',
                    'Departure_Date' => now()->addDays(14)->setTime(10, 30)->format('d.m.Y H:i'),
                    'Arrival_Iata' => 'LAX',
                    'Arrival_City' => 'Los Angeles',
                    'Arrival_Date' => now()->addDays(14)->setTime(12, 5)->format('d.m.Y H:i'),
                    'AirlineName' => 'Delta Air Lines',
                    'AirlineCode' => 'DL',
                    'FlightNum' => 'DL-378',
                    'FlightMinutes' => 155,
                    'Stops' => 0,
                    'FlightClass' => 'Economy',
                    'Bag_Count' => 1,
                    'BookingClass' => 'Y',
                ]],
                'sectorBack' => [],
                'passengers' => [[
                    'Title' => 'Mr',
                    'Name' => 'Guest',
                    'Surname' => 'Traveller',
                    'AgeType' => 'Adult',
                    'GenderType' => 'Male',
                    'BirthDay' => '01.01.1990',
                ]],
                'price' => [],
                'billing' => [],
                'other' => [],
            ]);
        }

        $sample = $this->sampleVariables();
        $subject = strtr((string) $template->subject, $sample);
        $body = strtr((string) ($template->body ?? ''), $sample);
        $tnc = strtr((string) ($template->tnc ?? ''), $sample);
        return view('crm.email-template-preview-v2', compact('template','subject','body','tnc'));
    }

    public function toggle(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        abort_unless(Schema::hasColumn('mail_templates','is_active'), 409, 'Run the Email Templates V2 migration first.');
        $template = DB::table('mail_templates')->where('id',$id)->first();
        abort_unless($template,404);
        DB::table('mail_templates')->where('id',$id)->update(['is_active' => !$template->is_active, 'updated_at' => now()]);
        return back()->with('crm_success', 'Template status updated.');
    }

    public function duplicate(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $template = DB::table('mail_templates')->where('id',$id)->first();
        abort_unless($template,404);
        $row = (array) $template;
        unset($row['id']);
        $row['label'] = Str::limit(($row['label'] ?? 'Email Template').' Copy', 255, '');
        if (array_key_exists('slug',$row)) $row['slug'] = null;
        if (array_key_exists('is_system',$row)) $row['is_system'] = 0;
        if (array_key_exists('is_active',$row)) $row['is_active'] = 0;
        if (array_key_exists('created_at',$row)) $row['created_at'] = now();
        if (array_key_exists('updated_at',$row)) $row['updated_at'] = now();
        $newId = DB::table('mail_templates')->insertGetId($row);
        return redirect()->route('crm.email_template_edit',$newId)->with('crm_success','Template duplicated as inactive copy.');
    }

    public function destroy(Request $request, int $id)
    {
        if ($redirect = $this->guard($request)) return $redirect;
        $template = DB::table('mail_templates')->where('id',$id)->first();
        abort_unless($template,404);
        if (Schema::hasColumn('mail_templates','is_system') && (int) $template->is_system === 1) {
            return back()->withErrors(['template' => 'System templates cannot be deleted. Duplicate or edit them instead.']);
        }
        DB::table('mail_templates')->where('id',$id)->delete();
        return redirect()->route('crm.email_templates')->with('crm_success','Email template deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['required','string','max:255'],
            'type' => ['required','string','max:100'],
            'category' => ['nullable','string','max:100'],
            'subject' => ['required','string','max:255'],
            'variable' => ['nullable','string'],
            'body' => ['nullable','string'],
            'tnc' => ['nullable','string'],
            'is_active' => ['nullable'],
        ]);
    }

    private function payload(array $data, bool $creating): array
    {
        $payload = [
            'label' => $data['label'],
            'type' => $data['type'],
            'subject' => $data['subject'],
            'variable' => $data['variable'] ?? '',
            'body' => $data['body'] ?? '',
            'tnc' => $data['tnc'] ?? '',
        ];
        if (Schema::hasColumn('mail_templates','category')) $payload['category'] = $data['category'] ?? 'Custom';
        if (Schema::hasColumn('mail_templates','is_active')) $payload['is_active'] = !empty($data['is_active']);
        if (Schema::hasColumn('mail_templates','updated_at')) $payload['updated_at'] = now();
        if ($creating && Schema::hasColumn('mail_templates','created_at')) $payload['created_at'] = now();
        return $payload;
    }
}
