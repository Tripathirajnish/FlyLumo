<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Onward Stops</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterDptStop'])
                @foreach($lFilter['FilterDptStop'] as $FilterDptStops)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dptstop" for="checkbox-filter-dptstop-{{$FilterDptStops['Code']}}"
                                    data-dptstop="{{$FilterDptStops['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dptstop-{{@$FilterDptStops['Code']}}" name="checkbox-filter-dptstop-{{@$FilterDptStops['Code']}}" 
                                        value="{{@$FilterDptStops['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{ preg_replace('/^([2-9]) Stop$/', '$1 Stops', (string) $FilterDptStops['Value']) }}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>  
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Return Stops</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterRtnStop'])
                @foreach($lFilter['FilterRtnStop'] as $FilterRtnStops)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtnstop" for="checkbox-filter-rtnstop-{{$FilterRtnStops['Code']}}"
                                    data-rtnstop="{{$FilterRtnStops['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtnstop-{{@$FilterRtnStops['Code']}}" name="checkbox-filter-rtnstop-{{@$FilterRtnStops['Code']}}" 
                                        value="{{@$FilterRtnStops['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{ preg_replace('/^([2-9]) Stop$/', '$1 Stops', (string) $FilterRtnStops['Value']) }}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>  
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Departure Time</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterDptTime'])
                @foreach($lFilter['FilterDptTime'] as $FilterDptTimes)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dpttime" for="checkbox-filter-dpttime-{{$FilterDptTimes['Code']}}"
                                    data-dpttime="{{$FilterDptTimes['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dpttime-{{@$FilterDptTimes['Code']}}" name="checkbox-filter-dpttime-{{@$FilterDptTimes['Code']}}" 
                                        value="{{@$FilterDptTimes['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{$FilterDptTimes['Value']}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif           
        </ul>
    </div>  
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Return Time</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterRtnTime'])
                @foreach($lFilter['FilterRtnTime'] as $FilterRtnTimes)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtntime" for="checkbox-filter-rtntime-{{$FilterRtnTimes['Code']}}"
                                    data-rtntime="{{$FilterRtnTimes['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtntime-{{@$FilterRtnTimes['Code']}}" name="checkbox-filter-rtntime-{{@$FilterRtnTimes['Code']}}" 
                                        value="{{@$FilterRtnTimes['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{$FilterRtnTimes['Value']}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div> 
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Departure Airline</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterDptAirline'])
                @foreach($lFilter['FilterDptAirline'] as $FilterDptAirlines)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dptairline" for="checkbox-filter-dptairline-{{$FilterDptAirlines['Code']}}"
                                        data-dptairline="{{$FilterDptAirlines['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dptairline-{{@$FilterDptAirlines['Code']}}" name="checkbox-filter-dptairline-{{@$FilterDptAirlines['Code']}}" 
                                        value="{{@$FilterDptAirlines['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{$FilterDptAirlines['Value']}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div> 
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Return Airline</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterRtnAirline'])
                @foreach($lFilter['FilterRtnAirline'] as $FilterRtnAirlines)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtnairline" for="checkbox-filter-rtnairline-{{$FilterRtnAirlines['Code']}}"
                                    data-rtnairline="{{$FilterRtnAirlines['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtnairline-{{@$FilterRtnAirlines['Code']}}" name="checkbox-filter-rtnairline-{{@$FilterRtnAirlines['Code']}}" 
                                        value="{{@$FilterRtnAirlines['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{$FilterRtnAirlines['Value']}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div> 
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Departure Airport</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterDptAirport'])
                @foreach($lFilter['FilterDptAirport'] as $FilterDptAirports)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dptairport" for="checkbox-filter-dptairport-{{$FilterDptAirports['Code']}}"
                                    data-dptairport="{{$FilterDptAirports['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dptairport-{{@$FilterDptAirports['Code']}}" name="checkbox-filter-dptairport-{{@$FilterDptAirports['Code']}}" 
                                        value="{{@$FilterDptAirports['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{$FilterDptAirports['Value']}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div> 
</div>

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Return Airport</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-0">
            @if($lFilter['FilterRtnAirport'])
                @foreach($lFilter['FilterRtnAirport'] as $FilterRtnAirports)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtnairport" for="checkbox-filter-rtnairport-{{$FilterRtnAirports['Code']}}"
                                    data-rtnairport="{{$FilterRtnAirports['Code']}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtnairport-{{@$FilterRtnAirports['Code']}}" name="checkbox-filter-rtnairport-{{@$FilterRtnAirports['Code']}}" 
                                        value="{{@$FilterRtnAirports['Code']}}">
                                        <div class="frm-slicing-title"><span class="text-muted-2">{{$FilterRtnAirports['Value']}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div> 
</div>
