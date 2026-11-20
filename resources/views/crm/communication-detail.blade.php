@extends('crm.layout')
@section('content')
@php
    $stage = $leadMeta->stage ?: 'New';
    $priority = $leadMeta->priority ?: 'Normal';
    $stageIndex = array_search($stage,$leadStages,true);
    $stageIndex = $stageIndex === false ? 0 : $stageIndex;
    $displayStages = ['New','Follow-up','Qualified','Quoted','Negotiation','Converted'];
    $created = \Carbon\Carbon::parse($lead->created_at);
    $nextFollow = $leadMeta->next_follow_up_at ? \Carbon\Carbon::parse($leadMeta->next_follow_up_at) : null;
    $destination = $leadMeta->travel_to ?: ($lead->going_to ?: 'Not specified');
    $travelDate = $lead->checkin_date ? \Carbon\Carbon::parse($lead->checkin_date) : null;
@endphp

<div class="ld-page">
    <section class="ld-hero">
        <div>
            <a href="{{ route('crm.communications') }}" class="crm-back"><i class="fa-solid fa-arrow-left me-1"></i>Back to communications</a>
            <div class="ld-title-row">
                <h1>Enquiry #{{ $lead->id }}</h1>
                <span class="ld-stage-badge">{{ $stage }}</span>
                <span class="ld-priority-badge priority-{{ strtolower($priority) }}">{{ $priority }}</span>
            </div>
            <p>{{ $lead->name ?: 'New enquiry' }} Â· {{ $destination }} Â· Received {{ $created->diffForHumans() }}</p>
        </div>
        <div class="ld-hero-actions">
            @if($lead->contact)<a class="btn btn-light" href="tel:{{ $lead->contact }}"><i class="fa-solid fa-phone me-1"></i>Call</a>@endif
            @if($lead->emailid)<a class="btn btn-light" href="mailto:{{ $lead->emailid }}?subject=Travel%20N%20Breakz%20-%20Your%20enquiry"><i class="fa-regular fa-envelope me-1"></i>Email</a>@endif
            <a class="btn btn-primary" href="#communication-operations"><i class="fa-regular fa-pen-to-square me-1"></i>Update Enquiry</a>
        </div>
    </section>

    <section class="ld-pipeline">
        @foreach($displayStages as $i=>$s)
            @php $idx=array_search($s,$leadStages,true); @endphp
            <div class="{{ $idx < $stageIndex ? 'done' : ($s===$stage ? 'active' : '') }}">
                <span>{{ $idx < $stageIndex ? 'âœ“' : $loop->iteration }}</span>
                <b>{{ $s }}</b>
            </div>
        @endforeach
    </section>

    <section class="ld-action-center">
        <div><span><i class="fa-solid fa-bolt"></i></span><div><small>Agent Action Center</small><strong>Manage the customer conversation</strong></div></div>
        <nav>
            @if($lead->contact)<a href="tel:{{ $lead->contact }}"><i class="fa-solid fa-phone"></i>Call</a>@endif
            @if($lead->emailid)<a href="mailto:{{ $lead->emailid }}"><i class="fa-regular fa-envelope"></i>Email</a>@endif
            <a href="#activity"><i class="fa-regular fa-note-sticky"></i>Add Note</a>
            <a href="#communication-operations"><i class="fa-regular fa-bell"></i>Set Follow-up</a>
            <a href="#quotation-workspace"><i class="fa-solid fa-file-lines"></i>Quotation</a>
            <a href="#communication-operations"><i class="fa-solid fa-user-check"></i>Assign Agent</a>
        </nav>
    </section>

    <section class="ld-summary">
        <div class="blue"><span><i class="fa-solid fa-location-dot"></i></span><small>Destination</small><strong>{{ $destination }}</strong></div>
        <div class="purple"><span><i class="fa-regular fa-calendar"></i></span><small>Travel Date</small><strong>{{ $travelDate ? $travelDate->format('d M Y') : 'Not specified' }}</strong></div>
        <div class="green"><span><i class="fa-solid fa-user-tie"></i></span><small>Assigned Agent</small><strong>{{ $leadMeta->agent_name ?: 'Unassigned' }}</strong></div>
        <div class="amber"><span><i class="fa-regular fa-bell"></i></span><small>Next Follow-up</small><strong>{{ $nextFollow ? $nextFollow->format('d M, h:i A') : 'Not scheduled' }}</strong></div>
        <div class="rose"><span><i class="fa-solid fa-fire"></i></span><small>Priority</small><strong>{{ $priority }}</strong></div>
        <div class="cyan"><span><i class="fa-solid fa-tag"></i></span><small>Lead Type</small><strong>{{ $lead->type ?: 'Default' }}</strong></div>
    </section>

    <div class="ld-layout">
        <main>
            <section class="ld-card">
                <header><div><span>Communication details</span><h3>Customer & enquiry overview</h3></div></header>
                <div class="ld-body">
                    <div class="ld-info-grid">
                        <div><small>Enquiry ID</small><strong>#{{ $lead->id }}</strong></div>
                        <div><small>Customer</small><strong>{{ $lead->name ?: 'New enquiry' }}</strong></div>
                        <div><small>Email</small><strong>{{ $lead->emailid ?: 'â€”' }}</strong></div>
                        <div><small>Contact</small><strong>{{ $lead->contact ?: 'â€”' }}</strong></div>
                        <div><small>Destination</small><strong>{{ $destination }}</strong></div>
                        <div><small>Check-in / Travel Date</small><strong>{{ $lead->checkin_date ?: 'â€”' }}</strong></div>
                        <div><small>Occupants</small><strong>{{ $lead->occupant ?: 'â€”' }}</strong></div>
                        <div><small>Source</small><strong>{{ $leadMeta->source ?: 'Website enquiry' }}</strong></div>
                        <div><small>Created</small><strong>{{ $created->format('d M Y, h:i A') }}</strong></div>
                    </div>
                </div>
            </section>

            <section class="ld-card">
                <header><div><span>Travel requirement</span><h3>Trip requirements</h3></div></header>
                <div class="ld-body">
                    <div class="ld-travel-grid">
                        <div><small>From</small><strong>{{ $leadMeta->travel_from ?: 'Not specified' }}</strong></div>
                        <div><small>To</small><strong>{{ $destination }}</strong></div>
                        <div><small>Departure</small><strong>{{ $lead->checkin_date ?: 'Not specified' }}</strong></div>
                        <div><small>Return</small><strong>{{ $leadMeta->return_date ?: 'Not specified' }}</strong></div>
                        <div><small>Adults</small><strong>{{ $leadMeta->adults ?? 'â€”' }}</strong></div>
                        <div><small>Children</small><strong>{{ $leadMeta->children ?? 'â€”' }}</strong></div>
                        <div><small>Infants</small><strong>{{ $leadMeta->infants ?? 'â€”' }}</strong></div>
                        <div><small>Cabin</small><strong>{{ $leadMeta->cabin ?: 'Not specified' }}</strong></div>
                        <div><small>Budget</small><strong>{{ $leadMeta->budget !== null ? '$'.number_format((float)$leadMeta->budget,2) : 'Not specified' }}</strong></div>
                    </div>

                    <div class="ld-message-block">
                        <div><small>Subject</small><strong>{{ $lead->subject ?: 'General enquiry' }}</strong></div>
                        <div><small>Customer Message</small><p>{{ $lead->message ?: 'No message provided.' }}</p></div>
                    </div>
                </div>
            </section>

            <section class="ld-card ld-quote-card" id="quotation-workspace">
                <header><div><span>Quotation workspace</span><h3>Create, save or send a customer quotation</h3></div></header>
                <div class="ld-body">
                    @if(!\Illuminate\Support\Facades\Schema::hasTable('lead_quotations'))
                        <div class="alert alert-warning mb-0">Run <code>php artisan migrate</code> once to enable quotation storage.</div>
                    @else
                    @php
                        $quoteFrom = $leadMeta->travel_from ?: 'From';
                        $quoteTo = $destination !== 'Not specified' ? $destination : 'To';
                        $defaultItinerary = ($quoteFrom !== 'From' || $quoteTo !== 'To') ? $quoteFrom.' â†’ '.$quoteTo : '';
                        $draftQuote = $quotations->firstWhere('status','Draft');
                        $editingQuote = $draftQuote;
                        $defaultFare = old('base_fare', $editingQuote->base_fare ?? ($leadMeta->budget ?? ''));
                        $defaultMarkup = old('markup', $editingQuote->markup ?? '0.00');
                        $defaultCurrency = old('currency', $editingQuote->currency ?? 'USD');
                        $defaultValid = old('valid_until', $editingQuote->valid_until ?? now()->addDays(2)->format('Y-m-d'));
                        $defaultItineraryValue = old('itinerary_summary', $editingQuote->itinerary_summary ?? $defaultItinerary);
                        $defaultNotes = old('notes', $editingQuote->notes ?? '');
                    @endphp
                    <div class="ld-mail-readiness {{ $mailReady ? 'ready' : 'warning' }}">
                        <span><i class="fa-solid {{ $mailReady ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i></span>
                        <div>
                            <b>{{ $mailReady ? 'SMTP configuration detected' : 'SMTP setup still required for live delivery' }}</b>
                            <small>{{ $mailReady ? 'Send will mark a quotation Sent only after the mail server accepts it.' : 'Draft, preview and print/PDF work now. Email delivery will remain Draft until SMTP is configured.' }}</small>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('crm.communication_quotation',$lead->id) }}" class="ld-quote-form">
                        @csrf
                        @if($editingQuote)<input type="hidden" name="quote_id" value="{{ $editingQuote->id }}">@endif
                        @if($editingQuote)
                        <div class="alert alert-info d-flex align-items-center justify-content-between gap-3 flex-wrap">
                            <span>Editing draft <b>{{ $editingQuote->quote_number }}</b>. Saving or sending will update this same quotation.</span>
                            <button class="btn btn-sm btn-light" type="button" onclick="startNewQuotation()"><i class="fa-solid fa-plus me-1"></i>New Quotation</button>
                        </div>
                        @endif
                        <div class="ld-quote-grid">
                            <label><span>Base Fare</span><input class="form-control" type="number" min="0" step="0.01" name="base_fare" value="{{ $defaultFare }}" required placeholder="0.00"></label>
                            <label><span>Markup / Service</span><input class="form-control" type="number" min="0" step="0.01" name="markup" value="{{ $defaultMarkup }}" placeholder="0.00"></label>
                            <label><span>Currency</span><select class="form-select" name="currency">@foreach(['USD','EUR','GBP','CAD','AUD','INR'] as $c)<option value="{{ $c }}" @selected($defaultCurrency===$c)>{{ $c }}</option>@endforeach</select></label>
                            <label><span>Valid Until</span><input class="form-control" type="date" name="valid_until" value="{{ $defaultValid }}"></label>
                        </div>
                        <label class="ld-quote-wide"><span>Itinerary Summary</span><textarea class="form-control" rows="3" name="itinerary_summary" placeholder="Route, dates, airline or flight details">{{ $defaultItineraryValue }}</textarea></label>
                        <label class="ld-quote-wide"><span>Customer-facing Notes</span><textarea class="form-control" rows="3" name="notes" placeholder="Fare conditions, baggage, change/cancel notes, or other quote details">{{ $defaultNotes }}</textarea></label>
                        <div class="ld-quote-actions">
                            <div><i class="fa-regular fa-envelope"></i><span>{{ $lead->emailid ?: 'No customer email available' }}</span></div>
                            @if($editingQuote)<button class="btn btn-outline-primary" type="button" onclick="startNewQuotation()"><i class="fa-solid fa-plus me-1"></i>New Quotation</button>@endif
                            <button class="btn btn-outline-primary" type="button" onclick="openQuotationPreview()"><i class="fa-regular fa-eye me-1"></i>Preview</button>
                            <button class="btn btn-light" type="submit" name="submit_action" value="draft"><i class="fa-regular fa-floppy-disk me-1"></i>Save Draft</button>
                            <button class="btn btn-primary" type="submit" name="submit_action" value="send" {{ empty($lead->emailid)?'disabled':'' }}><i class="fa-regular fa-paper-plane me-1"></i>Send Quotation</button>
                        </div>
                    </form>

                    <div class="quote-preview-modal" id="quotePreviewModal" aria-hidden="true">
                        <div class="quote-preview-backdrop" onclick="closeQuotationPreview()"></div>
                        <div class="quote-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="quotePreviewTitle">
                            <div class="quote-preview-toolbar">
                                <div>
                                    <small>Customer quotation preview</small>
                                    <h3 id="quotePreviewTitle">FaresHunt Quotation</h3>
                                </div>
                                <div class="quote-preview-toolbar-actions">
                                    <button type="button" class="btn btn-light" onclick="printQuotationPreview()"><i class="fa-solid fa-print me-1"></i>Print / Save PDF</button>
                                    <button type="button" class="btn btn-primary" onclick="closeQuotationPreview()"><i class="fa-solid fa-xmark me-1"></i>Close</button>
                                </div>
                            </div>
                            <div class="quote-preview-sheet" id="quotePreviewSheet">
                                <div class="quote-preview-brand">
                                    <div>
                                        <span>FARESHUNT</span>
                                        <h2>Flight Quotation</h2>
                                        <p>Prepared for <b>{{ $lead->name ?: 'Customer' }}</b></p>
                                    </div>
                                    <div class="quote-preview-meta">
                                        <small>Quotation</small><b id="previewQuoteNumber">Draft preview</b>
                                        <small>Enquiry</small><b>#{{ $lead->id }}</b>
                                        <small>Prepared</small><b>{{ now()->format('d M Y') }}</b>
                                    </div>
                                </div>

                                <div class="quote-preview-customer">
                                    <div><small>Customer</small><b>{{ $lead->name ?: 'Customer' }}</b></div>
                                    <div><small>Email</small><b>{{ $lead->emailid ?: 'â€”' }}</b></div>
                                    <div><small>Phone</small><b>{{ $lead->contact ?: 'â€”' }}</b></div>
                                </div>

                                <div class="quote-preview-route">
                                    <small>Itinerary</small>
                                    <h3 id="previewItinerary">â€”</h3>
                                </div>

                                <div class="quote-preview-price">
                                    <div>
                                        <small>Total quotation</small>
                                        <strong><span id="previewCurrency">USD</span> <span id="previewTotal">0.00</span></strong>
                                        <p>Final customer-facing amount</p>
                                    </div>
                                    <div>
                                        <small>Valid until</small>
                                        <b id="previewValidUntil">â€”</b>
                                    </div>
                                </div>

                                <div class="quote-preview-notes">
                                    <small>Fare notes & conditions</small>
                                    <p id="previewNotes">No additional notes.</p>
                                </div>

                                <div class="quote-preview-foot">
                                    <span>This preview intentionally shows the final customer price only.</span>
                                    <span>FaresHunt</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ld-quote-history">
                        <div class="ld-quote-history-head"><b>Quotation History</b><span>{{ $quotations->count() }} {{ \Illuminate\Support\Str::plural('quote',$quotations->count()) }}</span></div>
                        @forelse($quotations as $q)
                            <article>
                                <div><b>{{ $q->quote_number }}</b><small>{{ \Carbon\Carbon::parse($q->created_at)->format('d M Y, h:i A') }} Â· {{ $q->admin_name ?: 'CRM' }}</small></div>
                                <div class="ld-quote-amount">{{ $q->currency }} {{ number_format((float)$q->total_amount,2) }}<small>Base {{ number_format((float)$q->base_fare,2) }} + {{ number_format((float)$q->markup,2) }}</small></div>
                                <span class="ld-quote-status {{ strtolower($q->status) }}">{{ $q->status }}</span>
                                <div class="ld-quote-valid"><small>Valid until</small><b>{{ $q->valid_until ? \Carbon\Carbon::parse($q->valid_until)->format('d M Y') : 'â€”' }}</b></div>
                                <div class="ld-quote-row-actions">
                                    <button class="btn btn-sm btn-light" type="button" onclick="previewSavedQuote(@js($q->quote_number), @js($q->currency), @js($q->total_amount), @js($q->valid_until), @js($q->itinerary_summary), @js($q->notes))"><i class="fa-regular fa-eye me-1"></i>Preview</button>
                                    <a class="btn btn-sm btn-light" target="_blank" href="{{ route('crm.communication_quotation_print',[$lead->id,$q->id]) }}"><i class="fa-solid fa-print me-1"></i>Print/PDF</a>
                                    @if($q->status === 'Draft')
                                    <a class="btn btn-sm btn-light" href="#quotation-workspace" onclick="loadQuoteDraft({{ $q->id }}, @js($q->base_fare), @js($q->markup), @js($q->currency), @js($q->valid_until), @js($q->itinerary_summary), @js($q->notes)); return false;">Edit</a>
                                    <form method="POST" action="{{ route('crm.communication_quotation',$lead->id) }}" style="display:inline">@csrf
                                        <input type="hidden" name="quote_id" value="{{ $q->id }}">
                                        <input type="hidden" name="base_fare" value="{{ $q->base_fare }}"><input type="hidden" name="markup" value="{{ $q->markup }}"><input type="hidden" name="currency" value="{{ $q->currency }}"><input type="hidden" name="valid_until" value="{{ $q->valid_until }}"><input type="hidden" name="itinerary_summary" value="{{ $q->itinerary_summary }}"><input type="hidden" name="notes" value="{{ $q->notes }}">
                                        <button class="btn btn-sm btn-primary" name="submit_action" value="send" {{ empty($lead->emailid)?'disabled':'' }}>Send</button>
                                    </form>
                                    @else
                                    <span class="ld-sent-lock"><i class="fa-solid fa-lock me-1"></i>Locked after send</span>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="ld-quote-empty">No quotation has been created for this enquiry yet.</div>
                        @endforelse
                    </div>
                    @endif
                </div>
            </section>

            <section class="ld-card" id="activity">
                <header><div><span>Communication activity</span><h3>Follow-ups, calls, notes & history</h3></div></header>
                <div class="ld-body">
                    <form method="POST" action="{{ route('crm.lead_activity',$lead->id) }}" class="ld-activity-form">
                        @csrf
                        <select class="form-select" name="activity_type">
                            <option value="note">Note</option>
                            <option value="call">Call</option>
                            <option value="email">Email</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="task">Task</option>
                            <option value="quotation">Quotation</option>
                            <option value="meeting">Meeting</option>
                        </select>
                        <input class="form-control" name="title" required placeholder="What happened?">
                        <input class="form-control" name="details" placeholder="Optional detail">
                        <button class="btn btn-primary">Add activity</button>
                    </form>

                    <div class="ld-timeline">
                        <div class="created">
                            <span><i class="fa-solid fa-plus"></i></span>
                            <p><b>Enquiry received</b><small>{{ $created->format('d M Y, h:i A') }} Â· Website enquiry</small></p>
                        </div>
                        @foreach($activity as $a)
                            <div class="{{ $a->activity_type }}">
                                <span>
                                    <i class="fa-solid {{
                                        $a->activity_type==='call'?'fa-phone':
                                        ($a->activity_type==='email'?'fa-envelope':
                                        ($a->activity_type==='quotation'?'fa-file-lines':
                                        ($a->activity_type==='meeting'?'fa-calendar-check':
                                        ($a->activity_type==='stage_change'?'fa-arrows-rotate':'fa-note-sticky'))))
                                    }}"></i>
                                </span>
                                <p><b>{{ $a->title }}</b>@if($a->details)<em>{{ $a->details }}</em>@endif<small>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y, h:i A') }} Â· {{ $a->admin_name ?: 'CRM' }}</small></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <aside>
            <section class="ld-card ld-workspace" id="communication-operations">
                <header><div><span>Agent workspace</span><h3>Communication operations</h3></div></header>
                <div class="ld-body">
                    <div class="ld-work-status">
                        <div><small>Stage</small><strong>{{ $stage }}</strong></div>
                        <div><small>Priority</small><strong>{{ $priority }}</strong></div>
                        <div><small>Owner</small><strong>{{ $leadMeta->agent_name ?: 'Unassigned' }}</strong></div>
                        <div><small>Age</small><strong>{{ $created->diffForHumans() }}</strong></div>
                    </div>

                    <form method="POST" action="{{ route('crm.lead_operations',$lead->id) }}" class="ld-ops-form">
                        @csrf
                        <label><span>Enquiry Status</span><select class="form-select" name="stage">@foreach($leadStages as $s)<option value="{{ $s }}" @selected($stage===$s)>{{ $s }}</option>@endforeach</select></label>
                        <label><span>Priority</span><select class="form-select" name="priority">@foreach($leadPriorities as $p)<option value="{{ $p }}" @selected($priority===$p)>{{ $p }}</option>@endforeach</select></label>
                        <label><span>Assigned Agent</span><select class="form-select" name="assigned_to"><option value="">Unassigned</option>@foreach($admins as $a)<option value="{{ $a->id }}" @selected((string)$leadMeta->assigned_to===(string)$a->id)>{{ $a->name }}</option>@endforeach</select></label>
                        <label><span>Source</span><input class="form-control" name="source" value="{{ old('source',$leadMeta->source) }}" placeholder="Website / PPC / Referral / Call"></label>
                        <label><span>Next Action</span><input class="form-control" name="next_action" value="{{ old('next_action',$leadMeta->next_action) }}" placeholder="Call customer / send quote / follow up"></label>
                        <label><span>Next Follow-up</span><input class="form-control" type="datetime-local" name="next_follow_up_at" value="{{ $leadMeta->next_follow_up_at ? \Carbon\Carbon::parse($leadMeta->next_follow_up_at)->format('Y-m-d\TH:i') : '' }}"></label>

                        <div class="ld-two">
                            <label><span>Travel From</span><input class="form-control" name="travel_from" value="{{ old('travel_from',$leadMeta->travel_from) }}"></label>
                            <label><span>Travel To</span><input class="form-control" name="travel_to" value="{{ old('travel_to',$leadMeta->travel_to ?: $lead->going_to) }}"></label>
                        </div>

                        <div class="ld-two">
                            <label><span>Return Date</span><input class="form-control" type="date" name="return_date" value="{{ $leadMeta->return_date }}"></label>
                            <label><span>Cabin</span><select class="form-select" name="cabin">@foreach(['','Economy','Premium Economy','Business','First'] as $c)<option value="{{ $c }}" @selected($leadMeta->cabin===$c)>{{ $c ?: 'Select cabin' }}</option>@endforeach</select></label>
                        </div>

                        <div class="ld-three">
                            <label><span>Adults</span><input class="form-control" type="number" min="0" name="adults" value="{{ $leadMeta->adults }}"></label>
                            <label><span>Children</span><input class="form-control" type="number" min="0" name="children" value="{{ $leadMeta->children }}"></label>
                            <label><span>Infants</span><input class="form-control" type="number" min="0" name="infants" value="{{ $leadMeta->infants }}"></label>
                        </div>

                        <label><span>Budget</span><input class="form-control" type="number" min="0" step="0.01" name="budget" value="{{ $leadMeta->budget }}" placeholder="0.00"></label>
                        <label><span>Private Note</span><textarea class="form-control" rows="4" name="private_note" placeholder="Internal agent-only note">{{ $leadMeta->private_note }}</textarea></label>

                        <button class="btn btn-primary w-100"><i class="fa-solid fa-floppy-disk me-1"></i>Save Communication Operations</button>
                    </form>
                </div>
            </section>
        </aside>
    </div>
</div>

<style>
.quote-preview-modal{display:none;position:fixed;inset:0;z-index:9999}.quote-preview-modal.open{display:block}.quote-preview-backdrop{position:absolute;inset:0;background:rgba(8,28,58,.68);backdrop-filter:blur(5px)}.quote-preview-dialog{position:relative;width:min(980px,calc(100% - 40px));max-height:92vh;overflow:auto;margin:4vh auto;background:#f5f8fc;border-radius:22px;box-shadow:0 28px 80px rgba(5,24,51,.28)}.quote-preview-toolbar{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 22px;background:#123b66;color:#fff;position:sticky;top:0;z-index:2}.quote-preview-toolbar small{display:block;text-transform:uppercase;letter-spacing:.08em;opacity:.72}.quote-preview-toolbar h3{margin:3px 0 0;color:#fff}.quote-preview-toolbar-actions{display:flex;gap:10px}.quote-preview-sheet{margin:24px;background:#fff;border:1px solid #dce6f2;border-radius:18px;padding:34px;box-shadow:0 12px 34px rgba(23,55,91,.08)}.quote-preview-brand,.quote-preview-customer,.quote-preview-price,.quote-preview-foot{display:flex;justify-content:space-between;gap:24px}.quote-preview-brand{padding-bottom:24px;border-bottom:1px solid #e6edf5}.quote-preview-brand span{font-size:12px;letter-spacing:.14em;color:#2b63e5;font-weight:800}.quote-preview-brand h2{font-size:32px;margin:6px 0 8px}.quote-preview-brand p{margin:0;color:#61748a}.quote-preview-meta{display:grid;grid-template-columns:auto auto;gap:6px 14px;text-align:right}.quote-preview-meta small,.quote-preview-customer small,.quote-preview-route small,.quote-preview-price small,.quote-preview-notes small{color:#7c8da2;text-transform:uppercase;font-size:11px;font-weight:800;letter-spacing:.06em}.quote-preview-customer{margin:24px 0}.quote-preview-customer>div{flex:1;background:#f7f9fc;border:1px solid #e4ebf4;border-radius:13px;padding:15px}.quote-preview-customer b{display:block;margin-top:5px}.quote-preview-route{background:#eef5ff;border:1px solid #d8e7ff;border-radius:16px;padding:22px}.quote-preview-route h3{margin:7px 0 0;white-space:pre-line}.quote-preview-price{align-items:center;margin:22px 0;background:linear-gradient(135deg,#11385f,#1e578b);color:#fff;border-radius:18px;padding:24px}.quote-preview-price strong{display:block;font-size:34px;margin-top:4px}.quote-preview-price p{margin:4px 0 0;opacity:.72}.quote-preview-price small{color:#b9d1e8}.quote-preview-price>div:last-child{text-align:right}.quote-preview-notes{border:1px solid #e4ebf4;border-radius:16px;padding:20px;min-height:110px}.quote-preview-notes p{white-space:pre-line;margin:8px 0 0;color:#32465c}.quote-preview-foot{margin-top:26px;padding-top:16px;border-top:1px solid #e8eef5;color:#789;font-size:12px}@media(max-width:720px){.quote-preview-dialog{width:calc(100% - 20px);margin:2vh auto}.quote-preview-sheet{margin:12px;padding:20px}.quote-preview-brand,.quote-preview-customer,.quote-preview-price{flex-direction:column}.quote-preview-price>div:last-child{text-align:left}}
@media print{body *{visibility:hidden!important}.quote-preview-modal,.quote-preview-modal *{visibility:visible!important}.quote-preview-modal{display:block!important;position:absolute!important;inset:0!important}.quote-preview-backdrop,.quote-preview-toolbar{display:none!important}.quote-preview-dialog{width:100%!important;max-height:none!important;margin:0!important;box-shadow:none!important;background:#fff!important}.quote-preview-sheet{margin:0!important;border:0!important;box-shadow:none!important}}
</style>
@endsection

<script>
function loadQuoteDraft(id, base, markup, currency, validUntil, itinerary, notes) {
  const f=document.querySelector('.ld-quote-form'); if(!f) return;
  let q=f.querySelector('input[name=quote_id]'); if(!q){q=document.createElement('input');q.type='hidden';q.name='quote_id';f.appendChild(q)} q.value=id;
  f.elements.base_fare.value=base ?? ''; f.elements.markup.value=markup ?? '0.00'; f.elements.currency.value=currency ?? 'USD';
  f.elements.valid_until.value=validUntil ?? ''; f.elements.itinerary_summary.value=itinerary ?? ''; f.elements.notes.value=notes ?? '';
  const banner=f.querySelector('.alert-info');
  if(banner){ const label=banner.querySelector('span'); if(label) label.innerHTML='Editing selected draft. Saving or sending will update this same quotation.'; }
  document.getElementById('quotation-workspace').scrollIntoView({behavior:'smooth',block:'start'});
}

function previewSavedQuote(quoteNumber, currency, total, validUntil, itinerary, notes) {
  const modal=document.getElementById('quotePreviewModal'); if(!modal) return;
  const set=(id,value)=>{ const el=document.getElementById(id); if(el) el.textContent=value; };
  set('previewQuoteNumber', quoteNumber || 'Quotation');
  set('previewCurrency', currency || 'USD');
  set('previewTotal', Number(total || 0).toFixed(2));
  set('previewItinerary', itinerary || 'Travel itinerary to be confirmed.');
  set('previewNotes', notes || 'No additional notes.');
  set('previewValidUntil', validUntil ? new Date(validUntil+'T00:00:00').toLocaleDateString(undefined,{day:'2-digit',month:'short',year:'numeric'}) : 'Subject to availability');
  modal.classList.add('open'); modal.setAttribute('aria-hidden','false'); document.body.classList.add('quote-preview-open');
}

function openQuotationPreview() {
  const f=document.querySelector('.ld-quote-form'); if(!f) return;
  const base=parseFloat(f.elements.base_fare.value || '0') || 0;
  const markup=parseFloat(f.elements.markup.value || '0') || 0;
  const currency=f.elements.currency.value || 'USD';
  const total=base+markup;
  const itinerary=(f.elements.itinerary_summary.value || '').trim() || 'Itinerary not specified';
  const notes=(f.elements.notes.value || '').trim() || 'No additional notes.';
  const valid=f.elements.valid_until.value || '';
  document.getElementById('previewCurrency').textContent=currency;
  document.getElementById('previewTotal').textContent=total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
  document.getElementById('previewItinerary').textContent=itinerary;
  document.getElementById('previewNotes').textContent=notes;
  document.getElementById('previewValidUntil').textContent=valid ? new Date(valid+'T00:00:00').toLocaleDateString(undefined,{day:'2-digit',month:'short',year:'numeric'}) : 'Not specified';
  const m=document.getElementById('quotePreviewModal'); if(!m) return;
  m.classList.add('open'); m.setAttribute('aria-hidden','false');
  document.body.style.overflow='hidden';
}

function closeQuotationPreview() {
  const m=document.getElementById('quotePreviewModal'); if(!m) return;
  m.classList.remove('open'); m.setAttribute('aria-hidden','true');
  document.body.style.overflow='';
}

function printQuotationPreview() {
  window.print();
}

document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeQuotationPreview(); });

function startNewQuotation() {
  const f=document.querySelector('.ld-quote-form'); if(!f) return;
  const q=f.querySelector('input[name=quote_id]'); if(q) q.remove();
  f.elements.base_fare.value='';
  f.elements.markup.value='0.00';
  f.elements.currency.value='USD';
  const d=new Date(); d.setDate(d.getDate()+2);
  f.elements.valid_until.value=d.toISOString().slice(0,10);
  f.elements.itinerary_summary.value=@js($defaultItinerary);
  f.elements.notes.value='';
  const banner=f.querySelector('.alert-info');
  if(banner){ banner.classList.remove('alert-info'); banner.classList.add('alert-success'); banner.innerHTML='<span><b>New quotation mode.</b> Saving will create a new quotation number and will not change existing drafts.</span>'; }
  const first=f.elements.base_fare; if(first) first.focus();
  document.getElementById('quotation-workspace').scrollIntoView({behavior:'smooth',block:'start'});
}
</script>
