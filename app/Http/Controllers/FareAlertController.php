<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FareAlertController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = validator($request->all(), [
            'email' => ['required','email','max:190'],
            'home_airport' => ['nullable','string','max:10','regex:/^[A-Za-z0-9 -]*$/'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors(['fare_alert_email' => $validator->errors()->first()])
                ->withInput();
        }

        abort_unless(Schema::hasTable('fare_alert_subscribers'), 503, 'Run database migrations first.');

        $email = strtolower(trim((string)$request->email));
        $airport = strtoupper(trim((string)$request->home_airport));
        $airport = $airport !== '' ? $airport : null;

        DB::table('fare_alert_subscribers')->updateOrInsert(
            ['email'=>$email,'home_airport'=>$airport],
            [
                'status'=>'active',
                'source'=>'website_footer',
                'updated_at'=>now(),
                'created_at'=>now(),
            ]
        );

        return back()->with('fare_alert_success','You are subscribed to fare alerts.');
    }
}
