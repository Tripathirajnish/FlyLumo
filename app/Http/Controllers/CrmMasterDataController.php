<?php

namespace App\Http\Controllers;

use App\Services\CrmAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrmMasterDataController extends Controller
{
    public function __construct(
        private CrmAccessService $access
    ) {
    }

    private function guard(Request $request, string $permission = 'master_data.view')
    {
        $adminId = (int) $request->session()->get('crm_admin_id');

        if (!$adminId) {
            return redirect()->route('crm.login');
        }

        if (!$this->access->can($adminId, $permission)) {
            abort(403, 'You do not have permission to access Travel Master Data.');
        }

        return null;
    }


    public function create(Request $request)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        $section = strtolower((string) $request->input('section', 'countries'));

        if (!in_array($section, ['countries', 'cities', 'airports', 'airlines', 'booking_addons'], true)) {
            abort(404);
        }

        $countries = in_array($section, ['cities', 'airports'], true)
            ? DB::table('countries')->select('id', 'name', 'code')->orderBy('name')->get()
            : collect();

        return view('crm.master-data.form', [
            'section' => $section,
            'record' => null,
            'countries' => $countries,
        ]);
    }

    public function citiesByCountry(Request $request, int $countryId)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        if (!DB::table('countries')->where('id', $countryId)->exists()) {
            abort(404);
        }

        $cities = DB::table('cities')
            ->where('country_id', $countryId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    public function storeCountry(Request $request)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'size:2', 'regex:/^[A-Za-z]{2}$/'],
        ]);

        $name = trim($validated['name']);
        $code = strtoupper(trim($validated['code']));

        if (DB::table('countries')->whereRaw('UPPER(code) = ?', [$code])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'This country code already exists.']);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        $countryId = DB::transaction(function () use ($name, $code, $adminId, $request) {
            $now = now();

            $id = DB::table('countries')->insertGetId([
                'name' => $name,
                'code' => $code,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $after = DB::table('countries')->where('id', $id)->first();

            DB::table('travel_master_data_audit')->insert([
                'actor_admin_id' => $adminId ?: null,
                'entity_type' => 'country',
                'entity_id' => $id,
                'action' => 'create',
                'summary' => "Created country {$name} ({$code})",
                'before_json' => null,
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => $request->ip(),
                'created_at' => $now,
            ]);

            return $id;
        });

        return redirect()
            ->route('crm.master_data', ['section' => 'countries', 'q' => $countryId])
            ->with('success', 'Country created successfully.');
    }

    public function storeCity(Request $request)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'country_id' => ['required', 'integer'],
        ]);

        $name = trim($validated['name']);
        $countryId = (int) $validated['country_id'];

        $country = DB::table('countries')->where('id', $countryId)->first();

        if (!$country) {
            return back()
                ->withInput()
                ->withErrors(['country_id' => 'Selected country does not exist.']);
        }

        if (
            DB::table('cities')
                ->where('country_id', $countryId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['name' => 'This city already exists in the selected country.']);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        $cityId = DB::transaction(function () use ($name, $countryId, $country, $adminId, $request) {
            $now = now();

            $id = DB::table('cities')->insertGetId([
                'name' => $name,
                'country_id' => $countryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $after = DB::table('cities')->where('id', $id)->first();

            DB::table('travel_master_data_audit')->insert([
                'actor_admin_id' => $adminId ?: null,
                'entity_type' => 'city',
                'entity_id' => $id,
                'action' => 'create',
                'summary' => "Created city {$name} in {$country->name}",
                'before_json' => null,
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => $request->ip(),
                'created_at' => $now,
            ]);

            return $id;
        });

        return redirect()
            ->route('crm.master_data', ['section' => 'cities', 'q' => $cityId])
            ->with('success', 'City created successfully.');
    }

    public function storeAirport(Request $request)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'country_id' => ['required', 'integer'],
            'city_id' => ['required', 'integer'],
        ]);

        $name = trim($validated['name']);
        $code = strtoupper(trim($validated['code']));
        $countryId = (int) $validated['country_id'];
        $cityId = (int) $validated['city_id'];

        $country = DB::table('countries')->where('id', $countryId)->first();

        if (!$country) {
            return back()
                ->withInput()
                ->withErrors(['country_id' => 'Selected country does not exist.']);
        }

        $city = DB::table('cities')
            ->where('id', $cityId)
            ->where('country_id', $countryId)
            ->first();

        if (!$city) {
            return back()
                ->withInput()
                ->withErrors(['city_id' => 'Selected city does not belong to the selected country.']);
        }

        if (
            DB::table('airports')
                ->whereRaw('UPPER(TRIM(code)) = ?', [$code])
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'This airport code already exists.']);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        $airportId = DB::transaction(function () use ($name, $code, $countryId, $cityId, $country, $city, $adminId, $request) {
            $now = now();

            $id = DB::table('airports')->insertGetId([
                'name' => $name,
                'code' => $code,
                'city_id' => $cityId,
                'country_id' => $countryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $after = DB::table('airports')->where('id', $id)->first();

            DB::table('travel_master_data_audit')->insert([
                'actor_admin_id' => $adminId ?: null,
                'entity_type' => 'airport',
                'entity_id' => $id,
                'action' => 'create',
                'summary' => "Created airport {$name} ({$code}) in {$city->name}, {$country->name}",
                'before_json' => null,
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => $request->ip(),
                'created_at' => $now,
            ]);

            return $id;
        });

        return redirect()
            ->route('crm.master_data', ['section' => 'airports', 'q' => $airportId])
            ->with('success', 'Airport created successfully.');
    }



    public function storeAirline(Request $request)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9-]+$/'],
            'popular' => ['required', 'in:0,1'],
            'flight_type' => ['required', 'in:Yes,No'],
            'sfc' => ['required', 'in:0,1'],
            'atol' => ['required', 'in:0,1'],
            'show_generic' => ['required', 'in:0,1'],
            'deposit' => ['required', 'numeric', 'min:0', 'max:1'],
            'meal' => ['required', 'in:0,1'],
            'bag' => ['required', 'in:0,1'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $name = trim($validated['name']);
        $code = strtoupper(trim($validated['code']));

        if (
            DB::table('airlines')
                ->whereRaw('UPPER(TRIM(code)) = ?', [$code])
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['code' => 'This airline code already exists.']);
        }

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $dir = public_path('uploads/airlines');

            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                return back()
                    ->withInput()
                    ->withErrors(['logo' => 'Unable to prepare airline logo storage.']);
            }

            $filename = bin2hex(random_bytes(16)) . '.' . strtolower($file->getClientOriginalExtension());
            $file->move($dir, $filename);
            $logoPath = '/uploads/airlines/' . $filename;
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        try {
            $airlineId = DB::transaction(function () use ($validated, $name, $code, $logoPath, $adminId, $request) {
                $now = now();

                $id = DB::table('airlines')->insertGetId([
                    'name' => $name,
                    'code' => $code,
                    'popular' => (string) $validated['popular'],
                    'flight_type' => $validated['flight_type'],
                    'sfc' => (int) $validated['sfc'],
                    'atol' => (int) $validated['atol'],
                    'show_generic' => (int) $validated['show_generic'],
                    'deposit' => (float) $validated['deposit'],
                    'meal' => (int) $validated['meal'],
                    'bag' => (int) $validated['bag'],
                    'logo' => $logoPath,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $after = DB::table('airlines')->where('id', $id)->first();

                DB::table('travel_master_data_audit')->insert([
                    'actor_admin_id' => $adminId ?: null,
                    'entity_type' => 'airline',
                    'entity_id' => $id,
                    'action' => 'create',
                    'summary' => "Created airline {$name} ({$code})",
                    'before_json' => null,
                    'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip_address' => $request->ip(),
                    'created_at' => $now,
                ]);

                return $id;
            });
        } catch (\Throwable $e) {
            if ($logoPath && str_starts_with($logoPath, '/uploads/airlines/')) {
                $absolute = public_path(ltrim($logoPath, '/'));
                if (is_file($absolute)) {
                    @unlink($absolute);
                }
            }

            throw $e;
        }

        return redirect()
            ->route('crm.master_data', ['section' => 'airlines', 'q' => $airlineId])
            ->with('success', 'Airline created successfully.');
    }


    public function storeAddon(Request $request)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        $validated = $request->validate([
            'key' => ['required','string','max:100','regex:/^[A-Za-z0-9_]+$/'],
            'name' => ['required','string','max:150'],
            'description' => ['nullable','string','max:2000'],
            'price' => ['required','numeric','min:0','max:99999999.99'],
            'pricing_type' => ['required','in:flat'],
            'is_active' => ['required','in:0,1'],
            'display_order' => ['required','integer','min:0','max:999999'],
        ]);

        $key = trim($validated['key']);
        $name = trim($validated['name']);

        if (DB::table('booking_addons')->where('key', $key)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['key' => 'This add-on key already exists.']);
        }

        $adminId = (int) $request->session()->get('crm_admin_id');

        $addonId = DB::transaction(function () use ($validated, $key, $name, $adminId, $request) {
            $now = now();

            $id = DB::table('booking_addons')->insertGetId([
                'key' => $key,
                'name' => $name,
                'description' => isset($validated['description']) ? trim((string)$validated['description']) : null,
                'price' => (float)$validated['price'],
                'pricing_type' => $validated['pricing_type'],
                'is_active' => (int)$validated['is_active'],
                'display_order' => (int)$validated['display_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $after = DB::table('booking_addons')->where('id', $id)->first();

            DB::table('travel_master_data_audit')->insert([
                'actor_admin_id' => $adminId ?: null,
                'entity_type' => 'booking_addon',
                'entity_id' => $id,
                'action' => 'create',
                'summary' => "Created booking add-on {$name} ({$key})",
                'before_json' => null,
                'after_json' => json_encode($after, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => $request->ip(),
                'created_at' => $now,
            ]);

            return $id;
        });

        return redirect()
            ->route('crm.master_data', ['section' => 'booking_addons', 'q' => $addonId])
            ->with('success', 'Booking add-on created successfully.');
    }


    public function edit(Request $request, string $section, int $id)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        if (!in_array($section, ['countries','cities','airports','airlines','booking_addons'], true)) {
            abort(404);
        }

        $table = $section;
        $record = DB::table($table)->where('id', $id)->first();
        abort_unless($record, 404);

        $countries = collect();
        $cities = collect();

        if (in_array($section, ['cities','airports'], true)) {
            $countries = DB::table('countries')->orderBy('name')->get();
        }

        if ($section === 'airports') {
            $cities = DB::table('cities')
                ->where('country_id', (int)$record->country_id)
                ->orderBy('name')
                ->get();
        }

        return view('crm.master-data.form', compact('section','record','countries','cities'));
    }

    public function update(Request $request, string $section, int $id)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        if (!in_array($section, ['countries','cities','airports','airlines','booking_addons'], true)) {
            abort(404);
        }

        $record = DB::table($section)->where('id', $id)->first();
        abort_unless($record, 404);

        $before = (array)$record;
        $now = now();

        if ($section === 'countries') {
            $request->validate([
                'name' => ['required','string','max:200'],
                'code' => ['required','string','max:50'],
            ]);

            $name = trim($request->name);
            $code = strtoupper(trim($request->code));

            if (strcasecmp(trim((string)$record->code), $code) !== 0) {
                if (!preg_match('/^[A-Z]{2}$/', $code)) {
                    return back()->withErrors(['code' => 'New country code must be a 2-letter ISO-style code.'])->withInput();
                }
                $exists = DB::table('countries')->where('id','<>',$id)->whereRaw('UPPER(TRIM(code)) = ?', [$code])->exists();
                if ($exists) {
                    return back()->withErrors(['code' => 'This country code already exists.'])->withInput();
                }
            }

            $after = ['name'=>$name,'code'=>$code,'updated_at'=>$now];

        } elseif ($section === 'cities') {
            $request->validate([
                'name' => ['required','string','max:200'],
                'country_id' => ['required','integer'],
            ]);

            $countryId = (int)$request->country_id;
            abort_unless(DB::table('countries')->where('id',$countryId)->exists(), 422);

            if ($countryId !== (int)$record->country_id &&
                DB::table('airports')->where('city_id',$id)->exists()) {
                return back()->withErrors(['country_id' => 'Country cannot be changed because this city is already used by airports.'])->withInput();
            }

            $after = [
                'name'=>trim($request->name),
                'country_id'=>$countryId,
                'updated_at'=>$now,
            ];

        } elseif ($section === 'airports') {
            $request->validate([
                'name' => ['required','string','max:255'],
                'code' => ['required','string','max:100'],
                'country_id' => ['required','integer'],
                'city_id' => ['required','integer'],
            ]);

            $countryId = (int)$request->country_id;
            $cityId = (int)$request->city_id;
            $code = strtoupper(trim($request->code));

            $city = DB::table('cities')->where('id',$cityId)->where('country_id',$countryId)->first();
            if (!$city) {
                return back()->withErrors(['city_id' => 'Selected city does not belong to the selected country.'])->withInput();
            }

            $oldCode = strtoupper(trim((string)$record->code));

            if ($oldCode !== $code) {
                if (!preg_match('/^[A-Z]{3}$/', $code)) {
                    return back()->withErrors(['code' => 'New airport code must be a 3-letter IATA-style code.'])->withInput();
                }

                if (DB::table('airports')->where('id','<>',$id)->whereRaw('UPPER(TRIM(code)) = ?',[$code])->exists()) {
                    return back()->withErrors(['code' => 'This airport code already exists.'])->withInput();
                }

                if ($this->airportCodeInUse($oldCode)) {
                    return back()->withErrors(['code' => 'Airport code cannot be changed because the current code is used in operational or historical data.'])->withInput();
                }
            }

            $after = [
                'name'=>trim($request->name),
                'code'=>$code,
                'country_id'=>$countryId,
                'city_id'=>$cityId,
                'updated_at'=>$now,
            ];

        } elseif ($section === 'airlines') {
            $request->validate([
                'name' => ['required','string','max:255'],
                'code' => ['required','string','max:100'],
                'popular' => ['required','in:0,1'],
                'flight_type' => ['required','in:Yes,No'],
                'sfc' => ['required','in:0,1'],
                'atol' => ['required','in:0,1'],
                'show_generic' => ['required','in:0,1'],
                'deposit' => ['required','numeric','min:0','max:1'],
                'meal' => ['required','in:0,1'],
                'bag' => ['required','in:0,1'],
                'logo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
                'remove_logo' => ['nullable','in:0,1'],
            ]);

            $code = strtoupper(trim($request->code));
            $oldCode = strtoupper(trim((string)$record->code));

            if ($oldCode !== $code) {
                if (!preg_match('/^[A-Z0-9]{2}$/', $code)) {
                    return back()->withErrors(['code' => 'New airline code must be 2 alphanumeric characters.'])->withInput();
                }

                if (DB::table('airlines')->where('id','<>',$id)->whereRaw('UPPER(TRIM(code)) = ?',[$code])->exists()) {
                    return back()->withErrors(['code' => 'This airline code already exists.'])->withInput();
                }

                if ($this->airlineCodeInUse($oldCode)) {
                    return back()->withErrors(['code' => 'Airline code cannot be changed because the current code is used in operational or historical data.'])->withInput();
                }
            }

            $oldLogo = $record->logo;
            $logo = $oldLogo;
            $newUploadedLogo = null;

            if ($request->input('remove_logo') === '1') {
                $logo = null;
            }

            if ($request->hasFile('logo')) {
                $dir = public_path('uploads/airlines');
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }

                $file = $request->file('logo');
                $filename = bin2hex(random_bytes(16)).'.'.$file->getClientOriginalExtension();
                $file->move($dir, $filename);
                $newUploadedLogo = '/uploads/airlines/'.$filename;
                $logo = $newUploadedLogo;
            }

            $after = [
                'name'=>trim($request->name),
                'code'=>$code,
                'popular'=>$request->popular,
                'flight_type'=>$request->flight_type,
                'sfc'=>(int)$request->sfc,
                'atol'=>(int)$request->atol,
                'show_generic'=>(int)$request->show_generic,
                'deposit'=>(float)$request->deposit,
                'meal'=>(int)$request->meal,
                'bag'=>(int)$request->bag,
                'logo'=>$logo,
                'updated_at'=>$now,
            ];
        } elseif ($section === 'booking_addons') {
            $request->validate([
                'name' => ['required','string','max:150'],
                'description' => ['nullable','string','max:2000'],
                'price' => ['required','numeric','min:0','max:99999999.99'],
                'pricing_type' => ['required','in:flat'],
                'is_active' => ['required','in:0,1'],
                'display_order' => ['required','integer','min:0','max:999999'],
            ]);

            $after = [
                'name' => trim($request->name),
                'description' => $request->filled('description') ? trim((string)$request->description) : null,
                'price' => (float)$request->price,
                'pricing_type' => $request->pricing_type,
                'is_active' => (int)$request->is_active,
                'display_order' => (int)$request->display_order,
                'updated_at' => $now,
            ];
        }

        $entityType = [
            'countries' => 'country',
            'cities' => 'city',
            'airports' => 'airport',
            'airlines' => 'airline',
            'booking_addons' => 'booking_addon',
        ][$section];

        try {
            DB::transaction(function () use ($section,$id,$before,$after,$request,$entityType) {
                DB::table($section)->where('id',$id)->update($after);

                DB::table('travel_master_data_audit')->insert([
                'actor_admin_id' => session('crm_admin_id') ?: null,
                'entity_type' => $entityType,
                'entity_id' => $id,
                'action' => 'update',
                'summary' => "Updated {$entityType} #{$id}",
                'before_json' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'after_json' => json_encode(array_merge($before,$after), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ip_address' => $request->ip(),
                    'created_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            if ($section === 'airlines' && !empty($newUploadedLogo)) {
                $this->deleteLocalAirlineLogo($newUploadedLogo);
            }
            throw $e;
        }

        if ($section === 'airlines' && isset($oldLogo) && $oldLogo !== ($after['logo'] ?? null)) {
            $this->deleteLocalAirlineLogo($oldLogo);
        }

        return redirect()->route('crm.master_data', ['section'=>$section,'q'=>$id])
            ->with('success', ucfirst(rtrim($section,'s')).' updated successfully.');
    }

    public function destroy(Request $request, string $section, int $id)
    {
        if ($redirect = $this->guard($request, 'master_data.manage')) {
            return $redirect;
        }

        if (!in_array($section, ['countries','cities','airports','airlines','booking_addons'], true)) {
            abort(404);
        }

        $record = DB::table($section)->where('id',$id)->first();
        abort_unless($record, 404);

        if ($section === 'countries') {
            $deps = [
                'cities' => DB::table('cities')->where('country_id',$id)->count(),
                'airports' => DB::table('airports')->where('country_id',$id)->count(),
                'states' => DB::table('states')->where('country_id',$id)->count(),
                'billings' => DB::table('billings')->where('country_id',$id)->count(),
                'telephone codes' => DB::table('telephone_country_codes')->where('country_id',$id)->count(),
            ];
            if (array_sum($deps) > 0) {
                return back()->withErrors(['delete' => 'Country cannot be deleted because it is referenced by existing records.']);
            }

        } elseif ($section === 'cities') {
            if (DB::table('airports')->where('city_id',$id)->exists()) {
                return back()->withErrors(['delete' => 'City cannot be deleted because it is used by one or more airports.']);
            }

        } elseif ($section === 'airports') {
            if ($this->airportCodeInUse(strtoupper(trim((string)$record->code)))) {
                return back()->withErrors(['delete' => 'Airport cannot be deleted because its code is used in operational or historical data.']);
            }

        } elseif ($section === 'airlines') {
            if ($this->airlineCodeInUse(strtoupper(trim((string)$record->code)))) {
                return back()->withErrors(['delete' => 'Airline cannot be deleted because its code is used in operational or historical data.']);
            }
        } elseif ($section === 'booking_addons') {
            $pattern = '%"' . addcslashes((string)$record->key, '%_\\') . '":%';
            if (DB::table('bookings')->where('o_Price', 'like', $pattern)->exists()) {
                return back()->withErrors(['delete' => 'This add-on cannot be deleted because it exists in historical booking data. Disable it instead.']);
            }
        }

        $before = (array)$record;

        $entityType = [
            'countries' => 'country',
            'cities' => 'city',
            'airports' => 'airport',
            'airlines' => 'airline',
            'booking_addons' => 'booking_addon',
        ][$section];

        DB::transaction(function () use ($section,$id,$before,$request,$record,$entityType) {
            DB::table($section)->where('id',$id)->delete();

            DB::table('travel_master_data_audit')->insert([
                'actor_admin_id' => session('crm_admin_id') ?: null,
                'entity_type' => $entityType,
                'entity_id' => $id,
                'action' => 'delete',
                'summary' => "Deleted {$entityType} #{$id}",
                'before_json' => json_encode($before, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'after_json' => null,
                'ip_address' => $request->ip(),
                'created_at' => now(),
            ]);

        });

        if ($section === 'airlines') {
            $this->deleteLocalAirlineLogo($record->logo);
        }

        return redirect()->route('crm.master_data', ['section'=>$section])
            ->with('success', ucfirst(rtrim($section,'s')).' deleted successfully.');
    }

    private function airportCodeInUse(string $code): bool
    {
        if ($code === '') return false;

        foreach ([
            ['markups','departure_code'],
            ['markups','arrival_code'],
            ['top_deals','departure_code'],
            ['top_deals','arrival_code'],
            ['customer_crm_meta','home_airport'],
            ['fare_alert_subscribers','home_airport'],
        ] as [$table,$column]) {
            if (DB::table($table)->whereRaw('UPPER(TRIM('.$column.')) = ?',[$code])->exists()) {
                return true;
            }
        }

        foreach (['o_SectorGo','o_SectorBack','o_Itinerary'] as $column) {
            if (DB::table('bookings')->where($column,'like','%'.$code.'%')->exists()) {
                return true;
            }
        }

        return false;
    }

    private function airlineCodeInUse(string $code): bool
    {
        if ($code === '') return false;

        foreach ([
            ['markups','airline_code'],
            ['customer_crm_meta','preferred_airline'],
        ] as [$table,$column]) {
            if (DB::table($table)->whereRaw('UPPER(TRIM('.$column.')) = ?',[$code])->exists()) {
                return true;
            }
        }

        if (DB::table('markups')->where('airline_value','like','%'.$code.'%')->exists()) {
            return true;
        }

        foreach (['o_SectorGo','o_SectorBack','o_Itinerary'] as $column) {
            if (DB::table('bookings')->where($column,'like','%'.$code.'%')->exists()) {
                return true;
            }
        }

        return false;
    }

    private function deleteLocalAirlineLogo(?string $logo): void
    {
        if (!$logo || !str_starts_with($logo, '/uploads/airlines/')) {
            return;
        }

        $relative = ltrim($logo, '/');
        $full = public_path($relative);
        $base = realpath(public_path('uploads/airlines'));

        if (!$base || !file_exists($full)) {
            return;
        }

        $real = realpath($full);
        if ($real && str_starts_with($real, $base.DIRECTORY_SEPARATOR)) {
            @unlink($real);
        }
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $section = strtolower((string) $request->input('section', 'countries'));

        if (!in_array($section, ['countries', 'cities', 'airports', 'airlines', 'booking_addons'], true)) {
            $section = 'countries';
        }

        $term = trim((string) $request->input('q', ''));

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $query = match ($section) {
            'cities' => DB::table('cities as c')
                ->leftJoin('countries as co', 'co.id', '=', 'c.country_id')
                ->select(
                    'c.id',
                    'c.name',
                    'c.country_id',
                    'c.created_at',
                    'c.updated_at',
                    'co.name as country_name',
                    'co.code as country_code'
                ),

            'airports' => DB::table('airports as a')
                ->leftJoin('cities as ci', 'ci.id', '=', 'a.city_id')
                ->leftJoin('countries as co', 'co.id', '=', 'a.country_id')
                ->select(
                    'a.id',
                    'a.name',
                    'a.code',
                    'a.city_id',
                    'a.country_id',
                    'a.created_at',
                    'a.updated_at',
                    'ci.name as city_name',
                    'co.name as country_name',
                    'co.code as country_code'
                ),

            'airlines' => DB::table('airlines')
                ->select(
                    'id',
                    'name',
                    'code',
                    'popular',
                    'flight_type',
                    'sfc',
                    'atol',
                    'show_generic',
                    'deposit',
                    'meal',
                    'bag',
                    'logo',
                    'created_at',
                    'updated_at'
                ),

            'booking_addons' => DB::table('booking_addons')
                ->select(
                    'id',
                    'key',
                    'name',
                    'description',
                    'price',
                    'pricing_type',
                    'is_active',
                    'display_order',
                    'created_at',
                    'updated_at'
                ),

            default => DB::table('countries')
                ->select('id', 'name', 'code', 'created_at', 'updated_at'),
        };

        if ($term !== '') {
            $query->where(function ($q) use ($section, $term) {
                if ($section === 'countries') {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                } elseif ($section === 'cities') {
                    $q->where('c.name', 'like', "%{$term}%")
                        ->orWhere('co.name', 'like', "%{$term}%")
                        ->orWhere('co.code', 'like', "%{$term}%");
                } elseif ($section === 'airports') {
                    $q->where('a.name', 'like', "%{$term}%")
                        ->orWhere('a.code', 'like', "%{$term}%")
                        ->orWhere('ci.name', 'like', "%{$term}%")
                        ->orWhere('co.name', 'like', "%{$term}%")
                        ->orWhere('co.code', 'like', "%{$term}%");
                } elseif ($section === 'airlines') {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%");
                } elseif ($section === 'booking_addons') {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('key', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                }

                if (ctype_digit($term)) {
                    $idColumn = match ($section) {
                        'cities' => 'c.id',
                        'airports' => 'a.id',
                        default => 'id',
                    };

                    $q->orWhere($idColumn, (int) $term);
                }
            });
        }

        $records = $query
            ->orderBy(
                $section === 'cities'
                    ? 'c.name'
                    : ($section === 'airports'
                        ? 'a.code'
                        : ($section === 'booking_addons' ? 'display_order' : 'name'))
            )
            ->paginate($perPage)
            ->withQueryString();

        $counts = [
            'countries' => DB::table('countries')->count(),
            'cities' => DB::table('cities')->count(),
            'airports' => DB::table('airports')->count(),
            'airlines' => DB::table('airlines')->count(),
            'booking_addons' => DB::table('booking_addons')->count(),
        ];

        $canManage = $this->access->can(
            (int) $request->session()->get('crm_admin_id'),
            'master_data.manage'
        );

        return view('crm.master-data.index', compact(
            'section',
            'records',
            'counts',
            'canManage'
        ));
    }
}
