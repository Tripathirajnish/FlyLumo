<li class="col-12">
    @php
    if(@$selected['f_airline'])
    {
        $valuesArray = explode(',', $selected['f_airline']);
    }
    else
    {
        $valuesArray = array();
    }
    @endphp

    <div class="form-check lg">
        <div class="frm-slicing d-flex align-items-center">
            <div class="frm-slicing-first">
                <input class="form-check-input" type="checkbox" id="f_airline" name="f_airline[]" value="{{@$lFilterAirl['Code']}}"
                onclick="submitForm()" @if(in_array($lFilterAirl['Code'], $valuesArray)) checked @endif >
                <label class="form-check-label" for="f_airline"></label>
            </div>
            <div
                class="frm-slicing-end d-flex align-items-center justify-content-between full-width ps-1">
                <div class="frms-flex d-flex align-items-center">
                    <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$lFilterAirl['Value']}}</span></div>
                </div>
                <div class="text-end"><span class="text-md text-muted-2 opacity-75"></span></div>
            </div>
        </div>
    </div>
</li>