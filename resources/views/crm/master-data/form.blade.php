@extends('crm.layout')

@section('content')
@php
    $labels = [
        'countries' => 'Country',
        'cities' => 'City',
        'airports' => 'Airport',
        'airlines' => 'Airline',
        'booking_addons' => 'Booking Add-on',
    ];
    $label = $labels[$section] ?? 'Record';
    $isEdit = isset($record) && $record;
    $value = fn($field, $default = '') => old($field, $isEdit ? ($record->{$field} ?? $default) : $default);
@endphp

<style>
.tmf-page{max-width:980px;padding-bottom:30px}
.tmf-head{margin-bottom:16px}
.tmf-head h1{font-size:32px;color:#082d59;margin:8px 0 4px}
.tmf-head p{color:#718299;font-size:13px;margin:0}
.tmf-card{background:#fff;border:1px solid #dfe7ef;border-radius:16px;padding:20px}
.tmf-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.tmf-field label{display:block;font-size:10px;font-weight:850;text-transform:uppercase;color:#526981;margin-bottom:6px}
.tmf-field .form-control,.tmf-field .form-select{height:46px;border-radius:10px}
.tmf-help{font-size:10px;color:#7a8ba0;margin-top:5px}
.tmf-note{padding:12px 14px;border:1px solid #f0d9a4;background:#fff9eb;border-radius:12px;color:#775b18;font-size:11px;margin-bottom:15px}
.tmf-actions{display:flex;gap:10px;margin-top:20px}
@media(max-width:760px){.tmf-grid{grid-template-columns:1fr}}
</style>

<div class="tmf-page">
    <div class="tmf-head">
        <a href="{{ route('crm.master_data',['section'=>$section]) }}" class="crm-back">
            <i class="fa-solid fa-arrow-left me-1"></i>Travel Master Data
        </a>
        <h1>{{ $isEdit ? 'Edit' : 'Add' }} {{ $label }}</h1>
        <p>{{ $isEdit ? 'Update this '.strtolower($label).' master-data record safely.' : 'Prepare a new '.strtolower($label).' master-data record.' }}</p>
    </div>

    <div class="tmf-note">
        @if($isEdit)
            <b>Safe edit enabled:</b> changes are validated and audited. Protected codes and referenced records cannot be changed unsafely; historical booking data remains untouched.
        @else
            <b>{{ $label }} creation enabled:</b> new records are validated and audited. Existing master data and historical booking/search references remain untouched.
        @endif
    </div>

    <div class="tmf-card">
        @if($isEdit)
            <form method="POST" action="{{ route('crm.master_data_update',['section'=>$section,'id'=>$record->id]) }}" @if($section === 'airlines') enctype="multipart/form-data" @endif>
                @csrf
                @method('PUT')
        @elseif($section === 'countries')
            <form method="POST" action="{{ route('crm.master_data_country_store') }}">
                @csrf
        @elseif($section === 'cities')
            <form method="POST" action="{{ route('crm.master_data_city_store') }}">
                @csrf
        @elseif($section === 'airports')
            <form method="POST" action="{{ route('crm.master_data_airport_store') }}">
                @csrf
        @elseif($section === 'airlines')
            <form method="POST" action="{{ route('crm.master_data_airline_store') }}" enctype="multipart/form-data">
                @csrf
        @elseif($section === 'booking_addons')
            <form method="POST" action="{{ route('crm.master_data_addon_store') }}">
                @csrf
        @endif

        <div class="tmf-grid">
            @if($section === 'countries')
                <div class="tmf-field">
                    <label>Country name</label>
                    <input class="form-control" type="text" name="name" value="{{ $value('name') }}" placeholder="Example: United States" maxlength="200" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>Country code</label>
                    <input class="form-control text-uppercase" type="text" name="code" value="{{ $value('code') }}" placeholder="Example: US" maxlength="2" required>
                    <div class="tmf-help">New standard countries will use ISO alpha-2 codes. Existing legacy codes are preserved.</div>
                    @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

            @elseif($section === 'cities')
                <div class="tmf-field">
                    <label>City name</label>
                    <input class="form-control" type="text" name="name" value="{{ $value('name') }}" placeholder="Example: New York" maxlength="200" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>Country</label>
                    <select class="form-select" name="country_id" required>
                        <option value="">Select country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" @selected((string)$value('country_id') === (string)$country->id)>
                                {{ $country->name }} @if($country->code)({{ $country->code }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

            @elseif($section === 'airports')
                <div class="tmf-field">
                    <label>Airport name</label>
                    <input class="form-control" name="name" value="{{ $value('name') }}" placeholder="Example: John F. Kennedy International Airport" maxlength="255" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>Airport code</label>
                    <input class="form-control text-uppercase" name="code" value="{{ $value('code') }}" placeholder="Example: JFK" maxlength="3" required>
                    <div class="tmf-help">New standard airport records use a 3-letter IATA code.</div>
                    @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>Country</label>
                    <select class="form-select" name="country_id" id="airport-country" required>
                        <option value="">Select country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ (string) $value('country_id') === (string) $country->id ? 'selected' : '' }}>
                                {{ $country->name }} @if($country->code)({{ $country->code }})@endif
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>City</label>
                    <select class="form-select" name="city_id" id="airport-city" data-old-city="{{ $value('city_id') }}" required disabled>
                        <option value="">Select a country first</option>
                    </select>
                    <div class="tmf-help">Only cities belonging to the selected country are shown.</div>
                    @error('city_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const country = document.getElementById('airport-country');
                    const city = document.getElementById('airport-city');
                    if (!country || !city) return;

                    const baseUrl = @json(url('/travel-master-data/countries'));
                    const oldCity = city.dataset.oldCity || '';

                    async function loadCities(countryId) {
                        city.disabled = true;
                        city.innerHTML = '<option value="">Loading cities...</option>';

                        if (!countryId) {
                            city.innerHTML = '<option value="">Select a country first</option>';
                            return;
                        }

                        try {
                            const response = await fetch(baseUrl + '/' + encodeURIComponent(countryId) + '/cities', {
                                headers: { 'Accept': 'application/json' },
                                credentials: 'same-origin'
                            });

                            if (!response.ok) throw new Error('City lookup failed');

                            const rows = await response.json();
                            city.innerHTML = '<option value="">Select city</option>';

                            rows.forEach(function (row) {
                                const option = document.createElement('option');
                                option.value = row.id;
                                option.textContent = row.name;
                                if (String(row.id) === String(oldCity)) option.selected = true;
                                city.appendChild(option);
                            });

                            city.disabled = false;
                        } catch (e) {
                            city.innerHTML = '<option value="">Unable to load cities</option>';
                        }
                    }

                    country.addEventListener('change', function () {
                        city.dataset.oldCity = '';
                        loadCities(this.value);
                    });

                    if (country.value) loadCities(country.value);
                });
                </script>

            @elseif($section === 'airlines')
                <div class="tmf-field">
                    <label>Airline name</label>
                    <input class="form-control" name="name" value="{{ $value('name') }}" placeholder="Example: United Airlines" maxlength="255" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>Airline code</label>
                    <input class="form-control text-uppercase" name="code" value="{{ $value('code') }}" placeholder="Example: UA" maxlength="100" required>
                    <div class="tmf-help">Existing legacy airline codes remain untouched.</div>
                    @error('code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="tmf-field">
                    <label>Popular</label>
                    <select class="form-select" name="popular" required>
                        <option value="0" {{ (string)$value('popular','0')==='0'?'selected':'' }}>No (0)</option>
                        <option value="1" {{ (string)$value('popular')==='1'?'selected':'' }}>Yes (1)</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>Flight type</label>
                    <select class="form-select" name="flight_type" required>
                        <option value="No" {{ (string)$value('flight_type','No')==='No'?'selected':'' }}>No</option>
                        <option value="Yes" {{ (string)$value('flight_type')==='Yes'?'selected':'' }}>Yes</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>SFC</label>
                    <select class="form-select" name="sfc" required>
                        <option value="0" {{ (string)$value('sfc','0')==='0'?'selected':'' }}>0</option>
                        <option value="1" {{ (string)$value('sfc')==='1'?'selected':'' }}>1</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>ATOL</label>
                    <select class="form-select" name="atol" required>
                        <option value="1" {{ (string)$value('atol','1')==='1'?'selected':'' }}>1</option>
                        <option value="0" {{ (string)$value('atol')==='0'?'selected':'' }}>0</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>Show generic</label>
                    <select class="form-select" name="show_generic" required>
                        <option value="0" {{ (string)$value('show_generic','0')==='0'?'selected':'' }}>0</option>
                        <option value="1" {{ (string)$value('show_generic')==='1'?'selected':'' }}>1</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>Deposit</label>
                    <select class="form-select" name="deposit" required>
                        <option value="0" {{ (string)$value('deposit','0')==='0'?'selected':'' }}>0</option>
                        <option value="1" {{ (string)$value('deposit')==='1'?'selected':'' }}>1</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>Meal</label>
                    <select class="form-select" name="meal" required>
                        <option value="0" {{ (string)$value('meal','0')==='0'?'selected':'' }}>0</option>
                        <option value="1" {{ (string)$value('meal')==='1'?'selected':'' }}>1</option>
                    </select>
                </div>
                <div class="tmf-field">
                    <label>Bag</label>
                    <select class="form-select" name="bag" required>
                        <option value="0" {{ (string)$value('bag','0')==='0'?'selected':'' }}>0</option>
                        <option value="1" {{ (string)$value('bag')==='1'?'selected':'' }}>1</option>
                    </select>
                </div>
                <div class="tmf-field" style="grid-column:1/-1">
                    <label>Airline logo</label>
                    <input class="form-control" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
                    @if($isEdit && !empty($record->logo))
                        <div class="mt-2 mb-2">
                            <img src="{{ $record->logo }}" alt="{{ $record->name }}" style="max-width:120px;max-height:60px;object-fit:contain">
                        </div>
                        <label class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1">
                            <span class="form-check-label">Remove current logo</span>
                        </label>
                    @endif
                    <div class="tmf-help">PNG/JPG/WebP, maximum 5 MB. Existing external logo URLs remain supported.</div>
                    @error('logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

            @elseif($section === 'booking_addons')
                <div class="tmf-field">
                    <label>Add-on key</label>
                    @if($isEdit)
                        <input class="form-control" value="{{ $record->key }}" readonly>
                        <div class="tmf-help">Key is locked after creation to preserve historical booking references.</div>
                    @else
                        <input class="form-control" type="text" name="key" value="{{ $value('key') }}" placeholder="Example: Travel_Care" maxlength="100" required>
                        <div class="tmf-help">Use letters, numbers and underscores only. This key cannot be changed later.</div>
                        @error('key')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @endif
                </div>

                <div class="tmf-field">
                    <label>Add-on name</label>
                    <input class="form-control" type="text" name="name" value="{{ $value('name') }}" placeholder="Example: Travel Care" maxlength="150" required>
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="tmf-field" style="grid-column:1/-1">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="4" maxlength="2000" style="height:auto" placeholder="Describe what this add-on includes.">{{ $value('description') }}</textarea>
                    @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="tmf-field">
                    <label>Price ($)</label>
                    <input class="form-control" type="number" name="price" value="{{ $value('price','0.00') }}" min="0" max="99999999.99" step="0.01" required>
                    @error('price')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="tmf-field">
                    <label>Pricing type</label>
                    <select class="form-select" name="pricing_type" required>
                        <option value="flat" selected>Flat</option>
                    </select>
                    <div class="tmf-help">Current checkout supports flat-price premium add-ons only.</div>
                </div>

                <div class="tmf-field">
                    <label>Status</label>
                    <select class="form-select" name="is_active" required>
                        <option value="1" {{ (string)$value('is_active','1') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (string)$value('is_active','1') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="tmf-field">
                    <label>Display order</label>
                    <input class="form-control" type="number" name="display_order" value="{{ $value('display_order','0') }}" min="0" max="999999" step="1" required>
                    @error('display_order')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

            @endif
        </div>

        <div class="tmf-actions">
            <a class="btn btn-light" href="{{ route('crm.master_data',['section'=>$section]) }}">Cancel</a>
            <button class="btn btn-primary" type="submit">
                <i class="fa-solid {{ $isEdit ? 'fa-floppy-disk' : 'fa-plus' }} me-1"></i>
                {{ $isEdit ? 'Save '.$label : 'Create '.$label }}
            </button>
        </div>

        @if(in_array($section, ['countries','cities','airports','airlines','booking_addons'], true))
            </form>
        @endif
    </div>
</div>
@endsection
