<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /*
    Session Vary:

    session(['search_code'           =>  $field_encode]);
    session(['search_flight_result'  =>  $result])
    session(['search_flight_arrange' =>  $resultArrange])            // $resultArrange['resultData'], $resultArrange['resultSummary']

    session(['choose_result_key'     =>  $arraykey]);
    session(['choose_result'         =>  $resultSelected]);

    */
}
