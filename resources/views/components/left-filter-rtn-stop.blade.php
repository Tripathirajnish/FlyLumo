<li class="col-6">

    @php
    if(@$selected['f_rtn_stop'] || @$selected['f_rtn_stop']=='0')
    {
        $valuesArray = explode(',', $selected['f_rtn_stop']);
    }
    else
    {
        $valuesArray = array();
    }
    @endphp

    <input type="checkbox" class="btn-check" id="f_rtn_stop_{{$lFilterRtnStop['Code']}}" name="f_rtn_stop[]" value="{{$lFilterRtnStop['Code']}}"
     onclick="submitForm()" @if(in_array($lFilterRtnStop['Code'], $valuesArray)) checked @endif >
    <label class="btn btn-sm btn-secondary rounded-1 fw-medium px-2 full-width"
        for="f_rtn_stop_{{$lFilterRtnStop['Code']}}">{{$lFilterRtnStop['Value']}}</label>
</li>