<?php

namespace App\Support;

use Illuminate\Http\Request;

class BookingAttemptKey
{
    public static function fromRequest(Request $request): string
    {
        $passengers = [];
        $count = max(0, min(20, (int) $request->input('loop', 0)));

        for ($i = 0; $i <= $count; $i++) {
            $passengers[] = [
                'age_type' => trim((string) $request->input("AgeType_$i", '')),
                'title' => mb_strtolower(trim((string) $request->input("adult_title_$i", ''))),
                'gender' => mb_strtolower(trim((string) $request->input("adult_gender_$i", ''))),
                'first' => mb_strtolower(trim((string) $request->input("adult_fname_$i", ''))),
                'middle' => mb_strtolower(trim((string) $request->input("adult_mname_$i", ''))),
                'last' => mb_strtolower(trim((string) $request->input("adult_lname_$i", ''))),
                'dob' => trim((string) $request->input("adult_dob_$i", '')),
            ];
        }

        $payload = [
            'search_guid' => (string) $request->input('SearchGuid', ''),
            'offer_code' => (string) $request->input('OfferCode', ''),
            'email' => mb_strtolower(trim((string) $request->input('flight_emailid', ''))),
            'mobile' => trim((string) $request->input('flight_mobile', '')),
            'passengers' => $passengers,
        ];

        return hash_hmac('sha256', json_encode($payload, JSON_THROW_ON_ERROR), config('app.key'));
    }
}
