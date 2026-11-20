<li class="col-6">

    @php
    if(@$selected['f_dtp_stop'] || @$selected['f_dtp_stop']=='0')
    {
        $valuesArray = explode(',', $selected['f_dtp_stop']);
    }
    else
    {
        $valuesArray = array();
    }
    @endphp

    <input type="checkbox" class="btn-check" id="f_dtp_stop_{{$lFilterDptStop['Code']}}" name="f_dtp_stop[]" 
    value="{{$lFilterDptStop['Code']}}" onclick="submitForm()" 
    @if(in_array($lFilterDptStop['Code'], $valuesArray)) checked @endif >
    <label class="btn btn-sm btn-secondary rounded-1 fw-medium px-2 full-width" for="f_dtp_stop_{{$lFilterDptStop['Code']}}">{{$lFilterDptStop['Value']}}</label>

</li>