<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Onward Stops</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterDptStop'])
                @foreach($lFilter['FilterDptStop'] as $FilterDptStops)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dptstop" for="checkbox-filter-dptstop-{{$FilterDptStops}}"
                                    data-dptstop="{{$FilterDptStops}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dptstop-{{@$FilterDptStops}}" name="checkbox-filter-dptstop-{{@$FilterDptStops}}" 
                                        value="{{@$FilterDptStops}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterDptStops}}</span></div>
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
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterRtnStop'])
                @foreach($lFilter['FilterRtnStop'] as $FilterRtnStops)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtnstop" for="checkbox-filter-rtnstop-{{$FilterRtnStops}}"
                                    data-rtnstop="{{$FilterRtnStops}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtnstop-{{@$FilterRtnStops}}" name="checkbox-filter-rtnstop-{{@$FilterRtnStops}}" 
                                        value="{{@$FilterRtnStops}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterRtnStops}}</span></div>
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

<!-- <div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Departure Time</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterDptTime'])
                @foreach($lFilter['FilterDptTime'] as $FilterDptTimes)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dpttime" for="checkbox-filter-dpttime-{{$FilterDptTimes}}"
                                    data-dpttime="{{$FilterDptTimes}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dpttime-{{@$FilterDptTimes}}" name="checkbox-filter-dpttime-{{@$FilterDptTimes}}" 
                                        value="{{@$FilterDptTimes}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterDptTimes}}</span></div>
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
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterRtnTime'])
                @foreach($lFilter['FilterRtnTime'] as $FilterRtnTimes)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtntime" for="checkbox-filter-rtntime-{{$FilterRtnTimes}}"
                                    data-rtntime="{{$FilterRtnTimes}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtntime-{{@$FilterRtnTimes}}" name="checkbox-filter-rtntime-{{@$FilterRtnTimes}}" 
                                        value="{{@$FilterRtnTimes}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterRtnTimes}}</span></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div> 
</div> -->

<div class="searchBar-single px-3 py-3 border-bottom">
    <div class="searchBar-single-title d-flex mb-3">
        <h6 class="sidebar-subTitle fs-6 fw-medium m-0">Departure Airline</h6>
    </div>
    <div class="searchBar-single-wrap">
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterDptAirline'])
                @foreach($lFilter['FilterDptAirline'] as $FilterDptAirlines)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dptairline" for="checkbox-filter-dptairline-{{$FilterDptAirlines}}"
                                        data-dptairline="{{$FilterDptAirlines}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dptairline-{{@$FilterDptAirlines}}" name="checkbox-filter-dptairline-{{@$FilterDptAirlines}}" 
                                        value="{{@$FilterDptAirlines}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterDptAirlines}}</span></div>
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
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterRtnAirline'])
                @foreach($lFilter['FilterRtnAirline'] as $FilterRtnAirlines)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtnairline" for="checkbox-filter-rtnairline-{{$FilterRtnAirlines}}"
                                    data-rtnairline="{{$FilterRtnAirlines}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtnairline-{{@$FilterRtnAirlines}}" name="checkbox-filter-rtnairline-{{@$FilterRtnAirlines}}" 
                                        value="{{@$FilterRtnAirlines}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterRtnAirlines}}</span></div>
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
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterDptAirport'])
                @foreach($lFilter['FilterDptAirport'] as $FilterDptAirports)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-dptairport" for="checkbox-filter-dptairport-{{$FilterDptAirports}}"
                                    data-dptairport="{{$FilterDptAirports}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-dptairport-{{@$FilterDptAirports}}" name="checkbox-filter-dptairport-{{@$FilterDptAirports}}" 
                                        value="{{@$FilterDptAirports}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterDptAirports}}</span></div>
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
        <ul class="row align-items-center justify-content-between p-0 gx-3 gy-2">
            @if($lFilter['FilterRtnAirport'])
                @foreach($lFilter['FilterRtnAirport'] as $FilterRtnAirports)
                    <li class="col-12">
                        <div class="form-check lg">
                            <div class="frm-slicing d-flex align-items-center">
                                <div class="frm-slicing-first">
                                    <label class="form-check-label label-filter-rtnairport" for="checkbox-filter-rtnairport-{{$FilterRtnAirports}}"
                                    data-rtnairport="{{$FilterRtnAirports}}">
                                        <input class="form-check-input" type="checkbox" 
                                        id="checkbox-filter-rtnairport-{{@$FilterRtnAirports}}" name="checkbox-filter-rtnairport-{{@$FilterRtnAirports}}" 
                                        value="{{@$FilterRtnAirports}}">
                                        <div class="frm-slicing-title ps-2"><span class="text-muted-2">{{$FilterRtnAirports}}</span></div>
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
