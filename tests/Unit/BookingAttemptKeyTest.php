<?php

namespace Tests\Unit;

use App\Support\BookingAttemptKey;
use Illuminate\Http\Request;
use Tests\TestCase;

class BookingAttemptKeyTest extends TestCase
{
    private function bookingRequest(array $changes = []): Request
    {
        return Request::create('/submit-flight', 'POST', array_merge([
            'SearchGuid' => 'test-search-123',
            'OfferCode' => 'test-offer-456',
            'flight_emailid' => 'customer@example.com',
            'flight_mobile' => '1234567890',
            'loop' => 0,
            'adult_fname_0' => 'John',
            'adult_mname_0' => '',
            'adult_lname_0' => 'Smith',
            'adult_dob_0' => '1990-01-01',
        ], $changes));
    }

    public function test_same_booking_produces_same_key(): void
    {
        $first = $this->bookingRequest();
        $second = $this->bookingRequest();

        $this->assertSame(
            BookingAttemptKey::fromRequest($first),
            BookingAttemptKey::fromRequest($second)
        );
    }

    public function test_different_passenger_produces_different_key(): void
    {
        $first = $this->bookingRequest();
        $second = $this->bookingRequest([
            'adult_lname_0' => 'Johnson',
        ]);

        $this->assertNotSame(
            BookingAttemptKey::fromRequest($first),
            BookingAttemptKey::fromRequest($second)
        );
    }
}
