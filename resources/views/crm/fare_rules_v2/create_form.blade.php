<section class="panel" id="create-rule">
<div class="header">
<div>
<h2>Create Fare Rule</h2>
<p class="subtitle">Configure a new pricing rule. All new rules remain drafts.</p>
</div>
<span class="tag">DRAFT ONLY</span>
</div>

<form method="POST" action="{{ route('crm.fare_rules_v2.drafts.store') }}" id="v2-rule-form">
@csrf
<input type="hidden" name="conditions[0][field]" value="airline">
<input type="hidden" name="conditions[0][operator]" value="equals">
<input type="hidden" name="conditions[0][condition_group]" value="1">

<input type="hidden" name="conditions[1][field]" value="origin">
<input type="hidden" name="conditions[1][operator]" value="equals">
<input type="hidden" name="conditions[1][condition_group]" value="1">

<input type="hidden" name="conditions[2][field]" value="destination">
<input type="hidden" name="conditions[2][operator]" value="equals">
<input type="hidden" name="conditions[2][condition_group]" value="1">

<input type="hidden" name="conditions[3][field]" value="cabin">
<input type="hidden" name="conditions[3][operator]" value="equals">
<input type="hidden" name="conditions[3][condition_group]" value="1">

<input type="hidden" name="stackable" value="0">
<input type="hidden" name="stop_processing" value="0">


<div class="v2-grid">
<label>Rule Code
<input name="rule_code" required maxlength="80" pattern="[A-Za-z0-9_-]+" placeholder="FH-V2-NEW-001">
</label>

<label>Rule Name
<input name="name" required maxlength="200" placeholder="United Economy Markup">
</label>

<label>Priority
<input type="number" name="priority" min="1" max="10000" value="100" required>
</label>

<label>Airline Code
<input name="conditions[0][value]" required placeholder="UA" maxlength="10">
</label>

<label>Origin Airport
<input name="conditions[1][value]" required placeholder="JFK" maxlength="3">
</label>

<label>Destination Airport
<input name="conditions[2][value]" required placeholder="LAX" maxlength="3">
</label>

<label>Cabin
<select name="conditions[3][value]" required>
<option value="Economy">Economy</option>
<option value="Premium Economy">Premium Economy</option>
<option value="Business">Business</option>
<option value="First">First</option>
</select>
</label>

<label>Adjustment Type
<select name="actions[0][action_type]" required>
<option value="percentage_markup">Percentage Markup</option>
<option value="fixed_markup">Fixed Markup</option>
<option value="percentage_discount">Percentage Discount</option>
<option value="fixed_discount">Fixed Discount</option>
</select>
</label>

<label>Adjustment Amount
<input type="number" name="actions[0][amount]" min="0" max="1000000" step="0.0001" value="5" required>
</label>
</div>

<label class="v2-check">
<input type="checkbox" name="stackable" value="1">
Allow rule stacking
</label>

<label class="v2-check">
<input type="checkbox" name="stop_processing" value="1">
Stop processing after this rule
</label>

<p class="note">Saving creates a draft only. It does not activate pricing or modify customer fares.</p>

<button type="submit" class="v2-submit">Save Draft Rule</button>
</form>
</section>

<style>
.v2-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;margin-top:24px}
.v2-grid label{display:flex;flex-direction:column;gap:9px;font-size:13px;font-weight:700;color:#34445e}
.v2-grid input,.v2-grid select{width:100%;border:1px solid #dce4f0;border-radius:10px;padding:13px;font-size:14px;background:#fff;color:#17243b}
.v2-grid input:focus,.v2-grid select:focus{outline:2px solid #a8c6ff;border-color:#4c7dd9}
.v2-check{display:flex;align-items:center;gap:9px;margin-top:20px;font-size:14px}
.v2-submit{margin-top:24px;border:0;border-radius:11px;background:#234c94;color:white;padding:15px 25px;font-size:14px;font-weight:700;cursor:pointer}
.v2-submit:hover{background:#183b79}
@media(max-width:850px){.v2-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:520px){.v2-grid{grid-template-columns:1fr}}
</style>
