@extends('crm.layout')
@section('content')

<style id="fareshunt-settings-team-delete-v1">
.settings-v3-actions-head{width:120px!important;text-align:right!important}
.settings-v3-team-actions{text-align:right!important;white-space:nowrap!important}
.settings-v3-team-actions form{display:inline!important;margin:0!important}
.settings-v3-remove-user{
    display:inline-flex!important;align-items:center!important;justify-content:center!important;gap:6px!important;
    min-height:32px!important;padding:6px 10px!important;border:1px solid #f0c6cd!important;border-radius:9px!important;
    background:#fff7f8!important;color:#bd3047!important;font-size:10px!important;font-weight:800!important;
    line-height:1!important;cursor:pointer!important;transition:.15s ease!important;
}
.settings-v3-remove-user:hover{background:#fff0f2!important;border-color:#e7a9b4!important;color:#a91f38!important}
.settings-v3-you-badge{
    display:inline-flex!important;align-items:center!important;gap:5px!important;padding:6px 9px!important;
    border-radius:999px!important;background:#eef4ff!important;color:#275fd4!important;font-size:9px!important;font-weight:900!important;
}
@media(max-width:760px){
    .settings-v3-remove-user span{display:none!important}
    .settings-v3-remove-user{width:32px!important;padding:0!important}
    .settings-v3-actions-head{width:54px!important}
}
</style>


<style id="fareshunt-settings-branding-pass3">
.settings-v3-brand-preview{padding-top:18px!important}
.settings-v3-brand-box{
    display:flex!important;align-items:center!important;justify-content:center!important;
    min-height:82px!important;margin-top:10px!important;padding:14px 18px!important;
    border:1px solid #dce6f2!important;border-radius:14px!important;background:#fff!important;
    overflow:hidden!important;
}
.settings-v3-brand-box img{
    display:block!important;width:auto!important;height:auto!important;
    max-width:190px!important;max-height:52px!important;object-fit:contain!important;
}
.settings-v3-brand-meta{display:flex!important;flex-direction:column!important;gap:2px!important;margin-top:11px!important}
.settings-v3-brand-meta strong{color:#0b315f!important;font-size:14px!important;font-weight:800!important}
.settings-v3-brand-meta small{color:#7c8ca3!important;font-size:11px!important}
.settings-v3-brand-note{
    display:inline-flex!important;align-items:center!important;gap:6px!important;width:max-content!important;
    margin-top:10px!important;padding:6px 9px!important;border-radius:999px!important;
    background:#ecf8f1!important;color:#15804d!important;font-size:10px!important;font-weight:800!important;
}
</style>


<div class="settings-v2-page">
    <div class="settings-v2-hero">
        <div>
            <span class="settings-v2-kicker"><i class="fa-solid fa-gear"></i> CRM CONFIGURATION</span>
            <h1>Settings</h1>
            <p>Manage public website contact details and review your agency and CRM account information.</p>
        </div>
        <a class="settings-v2-open-site" href="/" target="_blank">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            Open Website
        </a>
    </div>

    <div class="settings-v2-summary">
        <div class="settings-v2-stat">
            <div class="settings-v2-stat-icon blue"><i class="fa-solid fa-phone-volume"></i></div>
            <div><span>Website Contact</span><strong>{{ $agency['header_contact'] ?? 'Not set' }}</strong><small>Public support number</small></div>
        </div>
        <div class="settings-v2-stat">
            <div class="settings-v2-stat-icon green"><i class="fa-brands fa-whatsapp"></i></div>
            <div><span>WhatsApp</span><strong>{{ $agency['content_whatsapp'] ?? 'Not set' }}</strong><small>Customer messaging</small></div>
        </div>
        <div class="settings-v2-stat">
            <div class="settings-v2-stat-icon purple"><i class="fa-solid fa-satellite-dish"></i></div>
            <div><span>API Environment</span><strong>{{ $agency['api_type'] ?? '—' }}</strong><small>Current supplier mode</small></div>
        </div>
        <div class="settings-v2-stat">
            <div class="settings-v2-stat-icon amber"><i class="fa-solid fa-users"></i></div>
            <div><span>CRM Team</span><strong>{{ ($canViewTeam ?? false) ? $team->count() : '—' }}</strong><small>Configured users</small></div>
        </div>
    </div>

    <div class="settings-v2-main-grid">
        <section class="settings-v2-card settings-v2-contact-card">
            <header class="settings-v2-card-head">
                <div>
                    <span class="settings-v2-eyebrow">PUBLIC WEBSITE</span>
                    <h2>Website Contact Details</h2>
                    <p>These values are used by the public website header, top bar, footer and support links.</p>
                </div>
                <span class="settings-v2-badge green"><i class="fa-solid fa-globe"></i> Website</span>
            </header>

            <form method="POST" action="{{ route('crm.settings_contact_update') }}" class="settings-v2-form" id="settings-v2-form">
                @csrf

                <div class="settings-v2-form-grid">
                    <div class="settings-v2-field">
                        <label for="settings_toll_free">Toll Free Number</label>
                        <div class="settings-v2-input-wrap">
                            <span class="settings-v2-input-icon red"><i class="fa-solid fa-phone"></i></span>
                            <input id="settings_toll_free" class="form-control" name="header_contact"
                                   value="{{ old('header_contact', $agency['header_contact'] ?? '') }}"
                                   placeholder="+1 800 000 0000">
                        </div>
                        <small>Shown in the top bar, main header and footer.</small>
                    </div>

                    <div class="settings-v2-field">
                        <label for="settings_whatsapp">WhatsApp Number</label>
                        <div class="settings-v2-input-wrap">
                            <span class="settings-v2-input-icon green"><i class="fa-brands fa-whatsapp"></i></span>
                            <input id="settings_whatsapp" class="form-control" name="content_whatsapp"
                                   value="{{ old('content_whatsapp', $agency['content_whatsapp'] ?? '') }}"
                                   placeholder="+1 800 000 0000">
                        </div>
                        <small>Use the full international number including country code.</small>
                    </div>

                    <div class="settings-v2-field settings-v2-span-2">
                        <label for="settings_email">Support Email</label>
                        <div class="settings-v2-input-wrap">
                            <span class="settings-v2-input-icon blue"><i class="fa-regular fa-envelope"></i></span>
                            <input id="settings_email" class="form-control" type="email" name="content_emailid"
                                   value="{{ old('content_emailid', $agency['content_emailid'] ?? '') }}"
                                   placeholder="support@example.com">
                        </div>
                        <small>Used in footer and customer-facing support areas.</small>
                    </div>

                    <div class="settings-v2-field settings-v2-span-2">
                        <label for="settings_address">Office / Footer Address</label>
                        <div class="settings-v2-textarea-wrap">
                            <textarea id="settings_address" class="form-control" name="footer_address" rows="3"
                                      placeholder="Office address">{{ old('footer_address', $agency['footer_address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="settings-v2-live-preview">
                    <div class="settings-v2-preview-label"><i class="fa-solid fa-eye"></i> Public contact preview</div>
                    <div class="settings-v2-preview-grid">
                        <div><span>Toll Free</span><strong id="preview_toll">{{ $agency['header_contact'] ?? 'Not set' }}</strong></div>
                        <div><span>WhatsApp</span><strong id="preview_whatsapp">{{ $agency['content_whatsapp'] ?? 'Not set' }}</strong></div>
                        <div><span>Support Email</span><strong id="preview_email">{{ $agency['content_emailid'] ?? 'Not set' }}</strong></div>
                    </div>
                </div>

                <div class="settings-v2-form-actions">
                    <div id="settings-v2-save-state" class="settings-v2-save-state saved"><i class="fa-solid fa-circle-check"></i> Everything is saved.</div>
                    <button class="settings-v2-save-btn" id="settings-v2-save-btn" disabled>
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save Website Contact Details</span>
                    </button>
                </div>
            </form>
        </section>

        <aside class="settings-v2-side">
            <section class="settings-v2-card settings-v2-info-card">
                <header class="settings-v2-card-head compact">
                    <div>
                        <span class="settings-v2-eyebrow">AGENCY</span>
                        <h3>Agency Overview</h3>
                    </div>
                    <i class="fa-solid fa-building settings-v2-head-icon"></i>
                </header>
                <div class="settings-v2-info-list">
                    <div class="settings-v2-info-row">
                        <span>API Mode</span>
                        <strong><span class="settings-v2-mode-badge {{ strtolower($agency['api_type'] ?? '') }}">{{ $agency['api_type'] ?? '—' }}</span></strong>
                    </div>
                    <div class="settings-v2-logo-preview settings-v3-brand-preview">
                        <span>Agency Brand</span>
                        <div class="settings-v3-brand-box">
                            <img src="{{ asset('assets/img/fareshunt-logo-transparent.png') }}" alt="FaresHunt">
                        </div>
                        <div class="settings-v3-brand-meta">
                            <strong>FaresHunt</strong>
                            <small>Aeronza Travel Group LLC</small>
                        </div>
                        <div class="settings-v3-brand-note">
                            <i class="fa-solid fa-circle-check"></i>
                            Official CRM branding
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-v2-card settings-v2-info-card">
                <header class="settings-v2-card-head compact">
                    <div>
                        <span class="settings-v2-eyebrow">SESSION</span>
                        <h3>Signed In User</h3>
                    </div>
                    <i class="fa-solid fa-user-shield settings-v2-head-icon"></i>
                </header>
                <div class="settings-v2-user-profile">
                    <div class="settings-v2-avatar">{{ strtoupper(substr($admin->name ?? 'A',0,1)) }}</div>
                    <div>
                        <strong>{{ $admin->name ?? 'Administrator' }}</strong>
                        <span>{{ $admin->username ?? '—' }}</span>
                    </div>
                </div>
                <div class="settings-v2-info-list">
                    <div class="settings-v2-info-row"><span>Username</span><strong>{{ $admin->username ?? '—' }}</strong></div>
                    <div class="settings-v2-info-row"><span>Role ID</span><strong>{{ $admin->role_id ?? '—' }}</strong></div>
                </div>
            </section>
        </aside>
    </div>

    @if($canViewTeam ?? false)
    <section class="settings-v2-card settings-v2-team">
        <header class="settings-v2-card-head">
            <div>
                <span class="settings-v2-eyebrow">CRM ACCESS</span>
                <h2>Team</h2>
                <p>CRM users currently configured.</p>
            </div>
            <span class="settings-v2-badge blue">{{ $team->count() }} users</span>
        </header>

        <div class="table-responsive">
            <table class="crm-table settings-v2-team-table">
                <thead>
                    <tr>
                        <th>Team Member</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Status</th>
                        <th class="settings-v3-actions-head">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($team as $a)
                    <tr>
                        <td>
                            <div class="settings-v2-team-user">
                                <span class="settings-v2-mini-avatar">{{ strtoupper(substr($a->name ?: 'U',0,1)) }}</span>
                                <div><b>{{ $a->name }}</b><small>CRM user</small></div>
                            </div>
                        </td>
                        <td><span class="settings-v2-username">{{ $a->username }}</span></td>
                        <td><a class="settings-v2-email" href="mailto:{{ $a->emailid }}">{{ $a->emailid }}</a></td>
                        <td>{{ $a->mobile ?: '—' }}</td>
                        <td><span class="settings-v2-status {{ $a->status==='1'?'active':'inactive' }}"><i class="fa-solid fa-circle"></i>{{ $a->status==='1'?'Active':'Inactive' }}</span></td>
                        <td class="settings-v3-team-actions">
                            @if((int)session('crm_admin_id') === (int)$a->id)
                                <span class="settings-v3-you-badge"><i class="fa-solid fa-user-shield"></i> You</span>
                            @elseif($canManageTeam ?? false)
                                <form method="POST"
                                      action="{{ action([\App\Http\Controllers\TeamAccessController::class,'deleteUser'], $a->id) }}"
                                      onsubmit="return confirm('Remove {{ addslashes($a->name ?: $a->username) }} from CRM access? Historical assignments and activity will be preserved.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="settings-v3-remove-user" title="Remove CRM access">
                                        <i class="fa-regular fa-trash-can"></i>
                                        <span>Remove</span>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('settings-v2-form');
    if (!form) return;

    const saveBtn = document.getElementById('settings-v2-save-btn');
    const state = document.getElementById('settings-v2-save-state');
    const fields = Array.from(form.querySelectorAll('input:not([type="hidden"]), textarea'));
    const initial = new Map(fields.map(el => [el.name, el.value]));

    function updateDirty() {
        const dirty = fields.some(el => initial.get(el.name) !== el.value);
        saveBtn.disabled = !dirty;
        state.classList.toggle('saved', !dirty);
        state.classList.toggle('dirty', dirty);
        state.innerHTML = dirty
            ? '<i class="fa-solid fa-circle-exclamation"></i> Unsaved changes'
            : '<i class="fa-solid fa-circle-check"></i> Everything is saved.';
    }

    fields.forEach(el => el.addEventListener('input', function () {
        updateDirty();
        if (el.name === 'header_contact') document.getElementById('preview_toll').textContent = el.value || 'Not set';
        if (el.name === 'content_whatsapp') document.getElementById('preview_whatsapp').textContent = el.value || 'Not set';
        if (el.name === 'content_emailid') document.getElementById('preview_email').textContent = el.value || 'Not set';
    }));
    });

    form.addEventListener('submit', function () {
        saveBtn.disabled = true;
        saveBtn.querySelector('span').textContent = 'Saving...';
        state.className = 'settings-v2-save-state saving';
        state.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving changes...';
    });

    updateDirty();
});
</script>
@endsection
