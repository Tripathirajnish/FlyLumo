@extends('crm.layout')
@section('content')
@php
    $groupLabels = [
        'theme'=>'Theme & Branding',
        'global'=>'Header, Navigation & Footer',
        'hero'=>'Homepage Hero',
        'search'=>'Search Bar',
        'hero_trust'=>'Homepage Search Trust Strip',
        'trust'=>'Trust Strip',
        'home_trust_strip'=>'Homepage Trust Strip',
        'featured'=>'Featured Flight Deals',
        'international'=>'International Destinations',
        'domestic'=>'US Domestic Routes',
        'india'=>'USA → India Deals',
        'premium'=>'Business & First Class',
        'holiday'=>'Holiday Packages',
        'last_minute'=>'Last Minute Deals',
    ];
    $groupIcons = [
        'theme'=>'fa-palette','global'=>'fa-window-maximize','hero'=>'fa-image',
        'search'=>'fa-magnifying-glass','hero_trust'=>'fa-shield-halved','trust'=>'fa-shield-halved','home_trust_strip'=>'fa-award','featured'=>'fa-fire',
        'international'=>'fa-earth-americas','domestic'=>'fa-flag-usa','india'=>'fa-plane-departure',
        'premium'=>'fa-gem','holiday'=>'fa-umbrella-beach','last_minute'=>'fa-bolt'
    ];
    $cardSectionLabels = [
        'featured'=>'Featured Flight Deals',
        'international'=>'International Destinations',
        'domestic'=>'US Domestic Routes',
        'india'=>'USA → India Deals',
        'business'=>'Business Class',
        'first'=>'First Class',
        'holiday'=>'Holiday Packages',
        'last_minute'=>'Last Minute Deals',
    ];
    $cardSectionIcons = [
        'featured'=>'fa-fire','international'=>'fa-earth-americas','domestic'=>'fa-flag-usa',
        'india'=>'fa-plane-departure','business'=>'fa-briefcase','first'=>'fa-crown',
        'holiday'=>'fa-umbrella-beach','last_minute'=>'fa-bolt'
    ];
    $settingCount = $cmsSettings->flatten()->count();
    $cardCount = $cmsCards->flatten()->count();
    $activeCardCount = $cmsCards->flatten()->where('is_active',1)->count();
@endphp

<div class="cms2-shell">
    <section class="cms2-hero">
        <div class="cms2-hero-copy">
            <span class="cms2-eyebrow"><i class="fa-solid fa-wand-magic-sparkles"></i> Website Experience Manager</span>
            <h1>Website CMS</h1>
            <p>Manage branding, homepage sections, route cards and website content from one premium workspace.</p>
        </div>
        <div class="cms2-hero-actions">
            <a class="cms2-btn cms2-btn-ghost" href="{{ route('crm.pages_manager') }}"><i class="fa-regular fa-file-lines"></i> Pages</a>
            <a class="cms2-btn cms2-btn-ghost" href="{{ route('crm.content_manager') }}"><i class="fa-regular fa-pen-to-square"></i> Content</a>
            <a class="cms2-btn cms2-btn-ghost" href="{{ route('crm.footer_manager') }}"><i class="fa-solid fa-bars-staggered"></i> Footer</a>
            <a class="cms2-btn cms2-btn-primary" href="/" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open Website</a>
        </div>
    </section>

    <section class="cms2-kpis">
        <article><span class="cms2-kpi-icon blue"><i class="fa-solid fa-sliders"></i></span><div><small>Editable settings</small><strong>{{ $settingCount }}</strong><em>Live website controls</em></div></article>
        <article><span class="cms2-kpi-icon pink"><i class="fa-regular fa-rectangle-list"></i></span><div><small>Website cards</small><strong>{{ $cardCount }}</strong><em>{{ $activeCardCount }} active</em></div></article>
        <article><span class="cms2-kpi-icon violet"><i class="fa-regular fa-file-lines"></i></span><div><small>Existing pages</small><strong>{{ $contents->count() }}</strong><em>Database-driven pages</em></div></article>
        <article><span class="cms2-kpi-icon green"><i class="fa-solid fa-layer-group"></i></span><div><small>Content library</small><strong>{{ $blogs->count()+$destinations->count()+$offers->count() }}</strong><em>Blogs, destinations & offers</em></div></article>
    </section>

    @if($cmsSettings->isEmpty())
    <div class="alert alert-warning">Website CMS tables are not available yet. Run <code>php artisan migrate</code>.</div>
    @endif

    <div class="cms2-tabs" role="tablist">
        <button type="button" class="active" data-cms2-tab="settings"><i class="fa-solid fa-sliders"></i> Website Settings</button>
        <button type="button" data-cms2-tab="cards"><i class="fa-regular fa-rectangle-list"></i> Deal & Route Cards <span>{{ $cardCount }}</span></button>
        <button type="button" data-cms2-tab="content"><i class="fa-solid fa-database"></i> Content Overview</button>
    </div>

    <div class="cms2-panel active" data-cms2-panel="settings">
        <form method="POST" action="{{ route('crm.cms_settings_update') }}" enctype="multipart/form-data" id="cms2-settings-form">
            @csrf
            <div class="cms2-settings-layout">
                <main>
                    <div class="cms2-section-jump">
                        <div class="cms2-settings-toolbar">
                            <div class="cms2-settings-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" id="cms2-settings-search" placeholder="Search website settings..."></div>
                            <label class="cms2-dev-toggle"><input type="checkbox" id="cms2-dev-toggle"><span>Show developer keys</span></label>
                        </div>
                    </div>

                    @foreach($groupLabels as $group=>$label)
                        @if(isset($cmsSettings[$group]))
                        <details class="cms2-setting-card" id="cms-setting-{{ $group }}" {{ in_array($group,['theme','hero']) ? 'open' : '' }} data-settings-group>
                            <summary>
                                <span class="cms2-setting-title"><i class="fa-solid {{ $groupIcons[$group] ?? 'fa-circle' }}"></i><span><b>{{ $label }}</b><small>{{ $cmsSettings[$group]->count() }} controls</small></span></span>
                                <span class="cms2-summary-meta"><em class="cms2-section-status">Saved</em><i class="fa-solid fa-chevron-down"></i></span>
                            </summary>

                            @if($group === 'global')
                                @php
                                    $globalSets = [
                                        'navigation' => ['label'=>'Navigation','icon'=>'fa-compass','items'=>$cmsSettings[$group]->filter(fn($x)=>str_starts_with($x->key,'site.nav.'))],
                                        'branding' => ['label'=>'Footer Branding','icon'=>'fa-copyright','items'=>$cmsSettings[$group]->filter(fn($x)=>str_starts_with($x->key,'site.footer.') && !str_contains($x->key,'.alerts.') && !str_contains($x->key,'.trust.') && !preg_match('/site\.footer\.col[1-4]\./',$x->key) && !str_starts_with($x->key,'site.footer.bottom.'))],
                                        'alerts' => ['label'=>'Fare Alerts','icon'=>'fa-bell','items'=>$cmsSettings[$group]->filter(fn($x)=>str_contains($x->key,'site.footer.alerts.'))],
                                        'trust' => ['label'=>'Trust & Security','icon'=>'fa-shield-halved','items'=>$cmsSettings[$group]->filter(fn($x)=>str_contains($x->key,'site.footer.trust.'))],
                                        'columns' => ['label'=>'Footer Columns','icon'=>'fa-table-columns','items'=>$cmsSettings[$group]->filter(fn($x)=>preg_match('/site\.footer\.col[1-4]\./',$x->key))],
                                        'legal' => ['label'=>'Legal & Bottom Links','icon'=>'fa-scale-balanced','items'=>$cmsSettings[$group]->filter(fn($x)=>str_starts_with($x->key,'site.footer.bottom.'))],
                                    ];
                                    $claimed = $globalSets['navigation']['items']->concat($globalSets['branding']['items'])->concat($globalSets['alerts']['items'])->concat($globalSets['trust']['items'])->concat($globalSets['columns']['items'])->concat($globalSets['legal']['items'])->pluck('id');
                                    $globalSets['other'] = ['label'=>'Other Global Settings','icon'=>'fa-sliders','items'=>$cmsSettings[$group]->reject(fn($x)=>$claimed->contains($x->id))];
                                @endphp
                                <div class="cms2-subgroups">
                                @foreach($globalSets as $subKey=>$sub)
                                    @if($sub['items']->count())
                                    <details class="cms2-subgroup" data-settings-subgroup>
                                        <summary><span><i class="fa-solid {{ $sub['icon'] }}"></i><b>{{ $sub['label'] }}</b><small>{{ $sub['items']->count() }} controls</small></span><i class="fa-solid fa-chevron-down"></i></summary>
                                        @if($subKey === 'columns')
                                            <div class="cms2-footer-columns">
                                            @for($col=1;$col<=4;$col++)
                                                @php $title=$sub['items']->firstWhere('key','site.footer.col'.$col.'.title'); @endphp
                                                <section class="cms2-footer-column" data-setting-search="footer column {{ $col }} {{ strtolower($title->value ?? '') }}">
                                                    <div class="cms2-footer-column-head"><span><i class="fa-solid fa-grip-vertical"></i> Column {{ $col }}</span>@if($title)<input class="form-control cms2-input" name="settings[{{ $title->key }}]" value="{{ $title->value }}" aria-label="Column {{ $col }} title">@endif</div>
                                                    <div class="cms2-link-table">
                                                    @for($i=1;$i<=6;$i++)
                                                        @php $ls=$sub['items']->firstWhere('key','site.footer.col'.$col.'.l'.$i); $us=$sub['items']->firstWhere('key','site.footer.col'.$col.'.u'.$i); @endphp
                                                        @if($ls || $us)<div class="cms2-link-row" data-setting-search="{{ strtolower(($ls->value ?? '').' '.($us->value ?? '')) }}"><span class="cms2-row-grip"><i class="fa-solid fa-grip-vertical"></i></span>@if($ls)<input class="form-control cms2-input" name="settings[{{ $ls->key }}]" value="{{ $ls->value }}" placeholder="Link label">@endif @if($us)<input class="form-control cms2-input" name="settings[{{ $us->key }}]" value="{{ $us->value }}" placeholder="/url">@endif</div>@endif
                                                    @endfor
                                                    </div>
                                                </section>
                                            @endfor
                                            </div>
                                        @else
                                            <div class="cms2-setting-grid">@foreach($sub['items'] as $s) @include('crm.partials.cms-setting-field',['s'=>$s]) @endforeach</div>
                                        @endif
                                    </details>
                                    @endif
                                @endforeach
                                </div>
                            @else
                                <div class="cms2-setting-grid">@foreach($cmsSettings[$group] as $s) @include('crm.partials.cms-setting-field',['s'=>$s]) @endforeach</div>
                            @endif
                        </details>
                        @endif
                    @endforeach
                </main>

                <aside class="cms2-publish">
                    <div class="cms2-publish-card">
                        <span class="cms2-publish-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                        <h3>Publish Website Settings</h3>
                        <p>Save branding, visibility, navigation, homepage text and global website settings together.</p>
                        <div class="cms2-publish-note" id="cms2-publish-state"><i class="fa-solid fa-circle-check"></i><span>Everything is saved.</span></div>
                        <button class="cms2-btn cms2-btn-primary cms2-btn-block" id="cms2-save-settings" disabled><i class="fa-solid fa-cloud-arrow-up"></i> Save Website Settings</button>
                        <a href="/" target="_blank" class="cms2-btn cms2-btn-soft cms2-btn-block"><i class="fa-regular fa-eye"></i> Preview Website</a>
                    </div>
                </aside>
            </div>
        </form>
    </div>

    <div class="cms2-panel" data-cms2-panel="cards">
        <section class="cms2-card-manager">
            <div class="cms2-manager-head">
                <div>
                    <span class="cms2-eyebrow">Homepage Inventory</span>
                    <h2>Deal & Route Cards</h2>
                    <p>Create, edit, hide, reorder and update route cards without touching code.</p>
                </div>
                <button type="button" class="cms2-btn cms2-btn-primary" data-cms2-open-add><i class="fa-solid fa-plus"></i> Add Website Card</button>
            </div>

            <div class="cms2-card-toolbar">
                <div class="cms2-search"><i class="fa-solid fa-magnifying-glass"></i><input id="cms2-card-search" placeholder="Search route, city, airport or airline..."></div>
                <div class="cms2-filter-pills">
                    <button type="button" class="active" data-card-filter="all">All <span>{{ $cardCount }}</span></button>
                    @foreach($cardSectionLabels as $section=>$label)
                    <button type="button" data-card-filter="{{ $section }}">{{ $label }} <span>{{ ($cmsCards[$section] ?? collect())->count() }}</span></button>
                    @endforeach
                </div>
            </div>

            <details class="cms2-add-card" id="cms2-add-card">
                <summary><span><i class="fa-solid fa-circle-plus"></i> Create a new homepage card</span><i class="fa-solid fa-chevron-down"></i></summary>
                <form method="POST" action="{{ route('crm.cms_card_store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="cms2-form-section">
                        <h4>Basic information</h4>
                        <div class="cms2-form-grid">
                            <label><span>Section</span><select class="form-select" name="section" required>@foreach($cardSectionLabels as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></label>
                            <label><span>Card title</span><input class="form-control" name="title" placeholder="e.g. New York to Los Angeles" required></label>
                            <label class="span-2"><span>Subtitle</span><input class="form-control" name="subtitle" placeholder="Optional supporting text"></label>
                        </div>
                    </div>
                    <div class="cms2-form-section">
                        <h4>Route & display</h4>
                        <div class="cms2-form-grid">
                            <label><span>From</span><input class="form-control" name="from_code" placeholder="JFK"></label>
                            <label><span>To</span><input class="form-control" name="to_code" placeholder="LAX"></label>
                            <label><span>City / destination</span><input class="form-control" name="city" placeholder="Los Angeles"></label>
                            <label><span>Badge</span><input class="form-control" name="badge" placeholder="Hot Deal"></label>
                            <label><span>Cabin</span><select class="form-select" name="cabin"><option value="Econom">Economy</option><option value="Peconom">Premium Economy</option><option value="Business">Business</option><option value="firstclass">First Class</option></select></label>
                            <label><span>Search days</span><input class="form-control" type="number" name="days" value="30" min="1" max="330"></label>
                            <label><span>Trip Type</span><select class="form-select" name="trip_type" data-deal-trip-type required><option value="round_trip" selected>Round Trip</option><option value="one_way">One Way</option></select></label>
                            <label data-return-days-wrap><span>Return after (days)</span><input class="form-control" type="number" name="return_days" value="7" min="1" max="60"><small class="cms2-field-help">Return date = departure date + these days.</small></label>
                            <label><span>Fare Mode</span><select class="form-select" name="fare_mode" data-fare-mode required><option value="manual" selected>Manual Starting Fare</option><option value="live">Live Fare</option></select></label>
                            <label data-display-fare-wrap><span>Starting Fare ($)</span><input class="form-control" type="number" step="0.01" min="0" max="99999.99" name="display_fare" value="399" placeholder="399"><small class="cms2-field-help">Shown instantly on homepage without calling the fare API.</small></label>
                            <label><span>Airline</span><input class="form-control" name="airline" placeholder="Optional airline"></label>
                            <label><span>Duration</span><input class="form-control" name="duration" placeholder="e.g. 7h 05m"></label>
                        </div>
                    </div>
                    <div class="cms2-form-section">
                        <h4>Image & publishing</h4>
                        <div class="cms2-form-grid">
                            <label class="span-2"><span>Upload image</span><input class="form-control" type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp"><small>JPG, JPEG, PNG or WEBP · Max 5 MB</small></label>
                            <label><span>Sort order</span><input class="form-control" type="number" name="sort_order" value="0" min="0"></label>
                        </div>
                        <div class="cms2-create-footer">
                            <label class="cms2-check"><input type="checkbox" name="is_active" value="1" checked><span>Publish as active</span></label>
                            <button class="cms2-btn cms2-btn-primary"><i class="fa-solid fa-plus"></i> Add Card</button>
                        </div>
                    </div>
                </form>
            </details>

            <div class="cms2-groups">
                @foreach($cardSectionLabels as $section=>$label)
                @php $sectionCards = ($cmsCards[$section] ?? collect()); @endphp
                <details class="cms2-group" data-card-group="{{ $section }}" open>
                    <summary>
                        <span class="cms2-group-title"><i class="fa-solid {{ $cardSectionIcons[$section] ?? 'fa-rectangle-list' }}"></i><span><b>{{ $label }}</b><small>{{ $sectionCards->count() }} cards</small></span></span>
                        <span class="cms2-group-summary"><em>{{ $sectionCards->where('is_active',1)->count() }} active</em><i class="fa-solid fa-chevron-down"></i></span>
                    </summary>
                    <div class="cms2-card-grid">
                        @forelse($sectionCards as $c)
                        <article class="cms2-route-card" data-search="{{ strtolower(trim($c->title.' '.$c->subtitle.' '.$c->from_code.' '.$c->to_code.' '.$c->city.' '.$c->airline)) }}">
                            <div class="cms2-route-media" style="background-image:url('{{ $c->image ?: '/assets/img/banner-8.jpg' }}')">
                                <div class="cms2-media-top">
                                    <span class="cms2-id">#{{ $c->id }}</span>
                                    <span class="cms2-status {{ $c->is_active ? 'active' : 'inactive' }}"><i class="fa-solid fa-circle"></i>{{ $c->is_active ? 'Active' : 'Hidden' }}</span>
                                    <span class="cms2-trip-badge">{{ ($c->trip_type ?? 'round_trip') === 'one_way' ? 'One Way' : 'Round Trip' }}</span>
                                    <span class="cms2-fare-badge {{ ($c->fare_mode ?? 'manual') === 'live' ? 'live' : 'manual' }}">{{ ($c->fare_mode ?? 'manual') === 'live' ? 'Live Fare' : (($c->display_fare ?? null) !== null ? 'From $'.number_format((float)$c->display_fare,0) : 'Manual Fare') }}</span>
                                </div>
                                <div class="cms2-route-overlay">
                                    <strong>{{ $c->from_code ?: 'FROM' }} <i class="fa-solid fa-arrow-right-long"></i> {{ $c->to_code ?: 'TO' }}</strong>
                                    <small>{{ $c->city ?: $label }}</small>
                                </div>
                            </div>

                            <details class="cms2-route-editor">
                                <summary><span><i class="fa-regular fa-pen-to-square"></i> Edit deal</span><span class="cms2-summary-route">{{ $c->from_code }} → {{ $c->to_code }}</span><i class="fa-solid fa-chevron-down"></i></summary>
                                <form method="POST" action="{{ route('crm.cms_card_update',$c->id) }}" enctype="multipart/form-data" class="cms2-route-form">
                                @csrf @method('PUT')
                                <div class="cms2-route-body">
                                    <label class="span-2"><span>Title</span><input class="form-control" name="title" value="{{ $c->title }}" required></label>
                                    <label class="span-2"><span>Subtitle</span><input class="form-control" name="subtitle" value="{{ $c->subtitle }}" placeholder="Optional subtitle"></label>
                                    <label><span>From</span><input class="form-control" name="from_code" value="{{ $c->from_code }}" placeholder="JFK"></label>
                                    <label><span>To</span><input class="form-control" name="to_code" value="{{ $c->to_code }}" placeholder="LAX"></label>
                                    <label><span>City</span><input class="form-control" name="city" value="{{ $c->city }}" placeholder="City"></label>
                                    <label><span>Badge</span><input class="form-control" name="badge" value="{{ $c->badge }}" placeholder="Badge"></label>
                                    <label><span>Cabin</span><select class="form-select" name="cabin">@foreach(['Econom'=>'Economy','Peconom'=>'Premium Economy','Business'=>'Business','firstclass'=>'First Class'] as $k=>$v)<option value="{{ $k }}" @selected($c->cabin===$k)>{{ $v }}</option>@endforeach</select></label>
                                    <label><span>Search days</span><input class="form-control" type="number" name="days" value="{{ $c->days }}" min="1" max="330"></label>
                                    <label><span>Trip Type</span><select class="form-select" name="trip_type" data-deal-trip-type required><option value="round_trip" @selected(($c->trip_type ?? 'round_trip')==='round_trip')>Round Trip</option><option value="one_way" @selected(($c->trip_type ?? 'round_trip')==='one_way')>One Way</option></select></label>
                                    <label data-return-days-wrap><span>Return after (days)</span><input class="form-control" type="number" name="return_days" value="{{ $c->return_days ?? 7 }}" min="1" max="60"><small class="cms2-field-help">Used only for Round Trip deals.</small></label>
                                    <label><span>Fare Mode</span><select class="form-select" name="fare_mode" data-fare-mode required><option value="manual" @selected(($c->fare_mode ?? 'manual')==='manual')>Manual Starting Fare</option><option value="live" @selected(($c->fare_mode ?? 'manual')==='live')>Live Fare</option></select></label>
                                    <label data-display-fare-wrap><span>Starting Fare ($)</span><input class="form-control" type="number" step="0.01" min="0" max="99999.99" name="display_fare" value="{{ $c->display_fare ?? '' }}" placeholder="399"><small class="cms2-field-help">Used only in Manual Starting Fare mode.</small></label>
                                    <label><span>Airline</span><input class="form-control" name="airline" value="{{ $c->airline }}" placeholder="Optional"></label>
                                    <label><span>Duration</span><input class="form-control" name="duration" value="{{ $c->duration }}" placeholder="Optional"></label>
                                    <label class="span-2"><span>Replace image</span><input class="form-control" type="file" name="image_file" accept=".jpg,.jpeg,.png,.webp"><small>Leave empty to keep the current image · JPG, JPEG, PNG or WEBP · Max 5 MB</small></label>
                                </div>
                                <div class="cms2-route-footer">
                                    <label class="cms2-check"><input type="checkbox" name="is_active" value="1" @checked($c->is_active)><span>Active</span></label>
                                    <label class="cms2-order"><span>Order</span><input class="form-control" type="number" name="sort_order" value="{{ $c->sort_order }}" min="0"></label>
                                    <button class="cms2-btn cms2-btn-primary"><i class="fa-regular fa-floppy-disk"></i> Save</button>
                                </div>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('crm.cms_card_delete',$c->id) }}" onsubmit="return confirm('Delete this website card?')" class="cms2-delete">
                                @csrf @method('DELETE')
                                <button title="Delete card"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </article>
                        @empty
                        <div class="cms2-empty">No cards in this section.</div>
                        @endforelse
                    </div>
                </details>
                @endforeach
            </div>
        </section>
    </div>

    <div class="cms2-panel" data-cms2-panel="content">
        <section class="cms2-content-overview">
            <div class="cms2-manager-head">
                <div><span class="cms2-eyebrow">Existing Website Content</span><h2>Content Overview</h2><p>Your current database-driven content remains available and untouched.</p></div>
            </div>
            <div class="cms2-content-grid">
                <a href="{{ route('crm.pages_manager') }}"><span class="cms2-kpi-icon blue"><i class="fa-regular fa-file-lines"></i></span><div><small>Pages</small><strong>{{ $contents->count() }}</strong><em>Manage pages</em></div><i class="fa-solid fa-arrow-right"></i></a>
                <a href="{{ route('crm.travel_guides_manager') }}"><span class="cms2-kpi-icon violet"><i class="fa-regular fa-newspaper"></i></span><div><small>Travel Guides</small><strong>{{ $blogs->count() }}</strong><em>Manage travel guides</em></div><i class="fa-solid fa-arrow-right"></i></a>
                <a href="{{ route('crm.destinations_manager') }}"><span class="cms2-kpi-icon pink"><i class="fa-solid fa-location-dot"></i></span><div><small>Destinations</small><strong>{{ $destinations->count() }}</strong><em>Manage destinations</em></div><i class="fa-solid fa-arrow-right"></i></a>
                <a href="{{ route('crm.content_manager') }}"><span class="cms2-kpi-icon green"><i class="fa-solid fa-tags"></i></span><div><small>Offers</small><strong>{{ $offers->count() }}</strong><em>Manage offers</em></div><i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </section>
    </div>
</div>


<style id="cms2-deal-trip-control-v1">
.cms2-field-help{display:block;margin-top:5px;color:#8290a4;font-size:10px;line-height:1.35}
.cms2-trip-badge{display:inline-flex;align-items:center;min-height:25px;padding:0 9px;border-radius:999px;background:rgba(7,27,52,.78);color:#fff;font-size:9px;font-weight:700;letter-spacing:.03em}
[data-return-days-wrap].is-disabled{opacity:.45}
[data-return-days-wrap].is-disabled input{pointer-events:none;background:#f3f5f8!important}
</style>


<style id="cms2-compact-fare-mode-v1">
.cms2-fare-badge{display:inline-flex;align-items:center;min-height:25px;padding:0 9px;border-radius:999px;font-size:9px;font-weight:800;letter-spacing:.02em}
.cms2-fare-badge.manual{background:#eef7f0;color:#187a43;border:1px solid #d7eadc}
.cms2-fare-badge.live{background:#fff0f3;color:#c91737;border:1px solid #ffd4dd}
[data-display-fare-wrap].is-disabled{opacity:.45}
[data-display-fare-wrap].is-disabled input{pointer-events:none;background:#f3f5f8!important}
.cms2-route-card{align-self:start!important}
.cms2-route-media{min-height:148px!important}
.cms2-route-editor{margin:0;border-top:1px solid #e8edf4}
.cms2-route-editor>summary{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:10px;min-height:48px;padding:0 15px;cursor:pointer;list-style:none;background:#fbfcfe;color:#17304f;font-size:11px;font-weight:700}
.cms2-route-editor>summary::-webkit-details-marker{display:none}
.cms2-route-editor>summary>span:first-child{display:inline-flex;align-items:center;gap:7px}
.cms2-route-editor>summary .cms2-summary-route{justify-self:end;color:#7b8da6;font-size:10px;font-weight:600}
.cms2-route-editor>summary>i{font-size:9px;color:#8290a5;transition:transform .18s ease}
.cms2-route-editor[open]>summary>i{transform:rotate(180deg)}
.cms2-route-editor .cms2-route-form{border-top:1px solid #edf1f6}
.cms2-card-grid{align-items:start!important}
</style>

<style id="cms2-hero-contrast-polish-v3">
/* Visual-only CMS hero contrast polish. No CMS logic, forms, routes or card behavior changed. */
.cms2-hero{position:relative;isolation:isolate;overflow:hidden}
.cms2-hero::before,.cms2-hero::after{opacity:.38!important}
.cms2-hero-copy{position:relative;z-index:2}
.cms2-hero-copy h1{color:#fff!important;text-shadow:0 1px 1px rgba(0,0,0,.08)}
.cms2-hero-copy p{color:rgba(235,244,255,.82)!important}
.cms2-hero-copy .cms2-eyebrow{color:#4d9cff!important}
.cms2-hero-actions{position:relative;z-index:2}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('[data-cms2-tab]');
    const panels = document.querySelectorAll('[data-cms2-panel]');

    tabButtons.forEach(btn => btn.addEventListener('click', function () {
        const key = this.dataset.cms2Tab;
        tabButtons.forEach(b => b.classList.toggle('active', b === this));
        panels.forEach(p => p.classList.toggle('active', p.dataset.cms2Panel === key));
        window.scrollTo({top:0, behavior:'smooth'});
    }));

    const addButton = document.querySelector('[data-cms2-open-add]');
    const addPanel = document.getElementById('cms2-add-card');
    if (addButton && addPanel) addButton.addEventListener('click', () => {
        addPanel.open = true;
        addPanel.scrollIntoView({behavior:'smooth', block:'start'});
    });

    function syncDealTripFields(scope=document) {
        scope.querySelectorAll('form').forEach(form => {
            const trip = form.querySelector('[data-deal-trip-type]');
            const wrap = form.querySelector('[data-return-days-wrap]');
            const input = wrap?.querySelector('input[name="return_days"]');
            if (!trip || !wrap || !input) return;

            const apply = () => {
                const roundTrip = trip.value === 'round_trip';
                wrap.classList.toggle('is-disabled', !roundTrip);
                input.disabled = !roundTrip;
                if (roundTrip && !input.value) input.value = '7';
            };

            trip.addEventListener('change', apply);
            apply();
        });
    }
    syncDealTripFields();

    function syncFareModeFields(scope=document) {
        scope.querySelectorAll('form').forEach(form => {
            const mode = form.querySelector('[data-fare-mode]');
            const wrap = form.querySelector('[data-display-fare-wrap]');
            const input = wrap?.querySelector('input[name="display_fare"]');
            if (!mode || !wrap || !input) return;
            const apply = () => {
                const manual = mode.value === 'manual';
                wrap.classList.toggle('is-disabled', !manual);
                input.disabled = !manual;
                if (manual && !input.value) input.value = '399';
            };
            mode.addEventListener('change', apply);
            apply();
        });
    }
    syncFareModeFields();

    const search = document.getElementById('cms2-card-search');
    const filterButtons = document.querySelectorAll('[data-card-filter]');
    let activeFilter = 'all';

    function applyCardFilter() {
        const q = (search?.value || '').trim().toLowerCase();
        document.querySelectorAll('[data-card-group]').forEach(group => {
            const section = group.dataset.cardGroup;
            const sectionAllowed = activeFilter === 'all' || activeFilter === section;
            let visible = 0;

            group.querySelectorAll('.cms2-route-card').forEach(card => {
                const matches = !q || (card.dataset.search || '').includes(q);
                const show = sectionAllowed && matches;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            group.style.display = sectionAllowed && (visible > 0 || !q) ? '' : 'none';
            if (q && visible > 0) group.open = true;
        });
    }

    filterButtons.forEach(btn => btn.addEventListener('click', function () {
        activeFilter = this.dataset.cardFilter;
        filterButtons.forEach(b => b.classList.toggle('active', b === this));
        applyCardFilter();
    }));
    if (search) search.addEventListener('input', applyCardFilter);

    document.querySelectorAll('.cms2-toggle input').forEach(input => {
        input.addEventListener('change', function () {
            const label = this.closest('.cms2-toggle')?.querySelector('b');
            if (label) label.textContent = this.checked ? 'Visible' : 'Hidden';
        });
    });


    // Premium settings UX: search, developer keys, dirty state and compact copy actions.
    const settingsForm = document.getElementById('cms2-settings-form');
    const settingsSearch = document.getElementById('cms2-settings-search');
    const devToggle = document.getElementById('cms2-dev-toggle');
    const saveButton = document.getElementById('cms2-save-settings');
    const publishState = document.getElementById('cms2-publish-state');
    let settingsDirty = false;

    function markSettingsDirty(el) {
        if (!settingsForm) return;
        settingsDirty = true;
        if (saveButton) saveButton.disabled = false;
        if (publishState) publishState.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i><span>Unsaved changes ready to publish.</span>';
        const card = el?.closest('[data-settings-group]');
        const status = card?.querySelector('.cms2-section-status');
        if (status) { status.textContent = 'Unsaved'; status.classList.add('dirty'); }
    }
    settingsForm?.querySelectorAll('input,textarea,select').forEach(el => {
        if (el.id !== 'cms2-settings-search' && el.id !== 'cms2-dev-toggle') el.addEventListener('input', () => markSettingsDirty(el));
        if (el.type === 'checkbox' || el.type === 'file') el.addEventListener('change', () => markSettingsDirty(el));
    });
    settingsForm?.addEventListener('submit', () => {
        settingsDirty = false;
        if (saveButton) { saveButton.disabled = true; saveButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...'; }
    });
    window.addEventListener('beforeunload', e => { if (settingsDirty) { e.preventDefault(); e.returnValue = ''; } });

    devToggle?.addEventListener('change', function(){ document.querySelector('.cms2-shell')?.classList.toggle('show-dev-keys', this.checked); });
    document.querySelectorAll('.cms2-copy').forEach(btn => btn.addEventListener('click', async function(){
        const input = this.parentElement?.querySelector('input'); if (!input) return;
        try { await navigator.clipboard.writeText(input.value); this.innerHTML='<i class="fa-solid fa-check"></i>'; setTimeout(()=>this.innerHTML='<i class="fa-regular fa-copy"></i>',900); } catch(e) {}
    }));

    settingsSearch?.addEventListener('input', function(){
        const q=this.value.trim().toLowerCase();
        document.querySelectorAll('[data-settings-group]').forEach(card => {
            let hits=0;
            card.querySelectorAll('[data-setting-search]').forEach(field => { const show=!q || (field.dataset.settingSearch||'').includes(q); field.style.display=show?'':'none'; if(show) hits++; });
            card.style.display=(!q || hits)?'':'none'; if(q && hits){card.open=true; card.querySelectorAll('[data-settings-subgroup]').forEach(sub=>{if([...sub.querySelectorAll('[data-setting-search]')].some(x=>x.style.display!== 'none')) sub.open=true;});}
        });
    });
});
</script>
@endsection
