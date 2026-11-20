<?php

use Illuminate\Support\Str;
use App\Models\Destination;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Content;
use App\Models\Setting;
use App\Models\Result;

use App\Services\SoapService;

if(!function_exists('getAllSetting'))
{
    function getAllSetting()
    {
        $setting =   Setting::get();
        if($setting)
        {
            $data = array();

            foreach($setting as $settings)
            {
                $data[$settings['name']] = $settings['value'];
            }
            return $data;
        }
    }
}

if(!function_exists('getTopAirline'))
{
    function getTopAirline()
    {
        $f_airline  =   Airline::where('flight_type','Yes')->get();
        if($f_airline)
        {
            foreach($f_airline as $f_airlines)
            {
                echo "<img src='".$f_airlines['logo']."' alt='".$f_airlines['name']."' />";
            }
        }
    }
}

if(!function_exists('getTopDestination'))
{
    function getTopDestination()
    {
        $f_destination  =   Destination::where('is_visible','1')->inRandomOrder()->limit(6)->get();
        if($f_destination)
        {
            foreach($f_destination as $f_destinations)
            {
                echo "<li><a href='javaScript:void(0);'>".$f_destinations['title']."</a></li>";
            }
        }
    }
}

if(!function_exists('getContent'))
{
    function getContent()
    {
        $f_content  =   Content::where('is_visible','1')->inRandomOrder()->limit(6)->get();
        if($f_content)
        {
            foreach($f_content as $f_contents)
            {
                echo "<li class='ms-3'><a href='/content/".$f_contents['slug']."'>".$f_contents['title']."</a></li>";
            }
        }
    }
}

if(!function_exists('getContentBasic'))
{
    function getContentBasic()
    {
        $f_content  =   Content::where('is_visible','1')->where('type','Basic')->get();
        if($f_content)
        {
            foreach($f_content as $f_contents)
            {
                echo "<li><a href='/content/".$f_contents['slug']."'>".$f_contents['title']."</a></li>";
            }
        }
    }
}

if(!function_exists('getContentLegal'))
{
    function getContentLegal()
    {
        $f_content  =   Content::where('is_visible','1')->where('type','Legal')->get();
        if($f_content)
        {
            foreach($f_content as $f_contents)
            {
                echo "<li style='margin-right:10px !important;'><a href='/content/".$f_contents['slug']."'>".$f_contents['title']."</a></li>";
            }
        }
    }
}

if(!function_exists('getContentLegalPaymentPage'))
{
    function getContentLegalPaymentPage()
    {
        $f_content  =   Content::where('is_visible','1')->where('type','Legal')->get();
        if($f_content)
        {
            foreach($f_content as $keyy=>$f_contents)
            {
                if($keyy!=0) { echo " & "; }
                echo "<a class='text-info' onclick=\"window.open('/content/".$f_contents['slug']."', 'info', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=800,height=600, screenX=50,screenY=50')\" href='javascript: void(0);'>".$f_contents['title']."</a>";
            }
        }
    }
}

if(!function_exists('getAirport'))
{
    function getAirport()
    {
        $airport  =   Airport::get();
        {
            foreach($airport as $airports)
            {
                if($airports->city) { $cname = $airports->city->name; } else {$cname = ''; }
                echo "<option value='".$airports->code."'>".$airports->code." - ".$airports->name.",".$cname."</option>";
            }
        }
    }
}

if(!function_exists('getDateFormat'))
{
    function getDateFormat($date,$method)
    {
        if($method=='d M')
        {
            echo date('d M',strtotime($date));
        }
        if($method=='H:i')
        {
            echo date('H:i',strtotime($date));
        }

        if($method=='d M H:i')
        {
            echo date('d M H:i',strtotime($date));
        }
        
    }
}

if(!function_exists('fields_decode'))
{
    function fields_decode($code)
    {
        $field_decode           =   @base64_decode($code);
        $field_exp              =   @explode(',',$field_decode);
        
        $flight_types           =   @$field_exp[0];
        $adult                  =   @$field_exp[1];
        $children               =   @$field_exp[2];
        $infant                 =   @$field_exp[3];
        $cabin_classes          =   @$field_exp[4];
        $location_from          =   @$field_exp[5];
        $location_to            =   @$field_exp[6];

        $depature_date_format   =   @date('d.m.Y',@strtotime($field_exp[7]));
        $return_date_format     =   @date('d.m.Y',@strtotime($field_exp[8]));

        $field      =   array('flight_types'            => $flight_types, 
                              'adult'                   => $adult, 
                              'children'                => $children, 
                              'infant'                  => $infant, 
                              'cabin_classes'           => $cabin_classes, 
                              'location_from'           => $location_from, 
                              'location_to'             => $location_to, 
                              'depature_date_format'    => $depature_date_format, 
                              'return_date_format'      => $return_date_format);

        return $field;
    }
}

if(!function_exists('addInputResult'))
{
    function addInputResult($field_encode)
    {
        $sessionId  =   Session::getId();
        
        $info       =   Result::where('sessionid',$sessionId)->first();
        if(@$info)
        {
            $info->i_journey_type       =   $request->flight_types;
            $info->i_adult              =   $request->adult;
            $info->i_children           =   $request->children;
            $info->i_infant             =   $request->infant;
            $info->i_cabin_class        =   $request->cabin_classes;
            $info->i_departure_airport  =   $request->location_from;
            $info->i_arrival_airport    =   $request->location_to;
            $info->i_departure_date     =   $request->depature_date;
            $info->i_arrival_date       =   $request->return_date;
            $info->i_field_encode       =   $field_encode;
            
            $info->save();
        }
        else
        {
            $infoN   =   new Result();

            $infoN->sessionid            =   $sessionId;
            $infoN->i_journey_type       =   $request->flight_types;
            $infoN->i_adult              =   $request->adult;
            $infoN->i_children           =   $request->children;
            $infoN->i_infant             =   $request->infant;
            $infoN->i_cabin_class        =   $request->cabin_classes;
            $infoN->i_departure_airport  =   $request->location_from;
            $infoN->i_arrival_airport    =   $request->location_to;
            $infoN->i_departure_date     =   $request->depature_date;
            $infoN->i_arrival_date       =   $request->return_date;
            
            $infoN->save();
        }
        
    }
}

// FaresHunt public CRM references. Database primary keys/prefixes remain untouched.
if(!function_exists('fh_booking_ref')) {
    function fh_booking_ref($id) { return 'FH'.(int)$id; }
}
if(!function_exists('fh_lead_ref')) {
    function fh_lead_ref($id) { return 'FH-L'.(int)$id; }
}
if(!function_exists('fh_customer_ref')) {
    function fh_customer_ref($id) { return 'FH-C'.(int)$id; }
}

?>
