<?php

namespace Tests\Unit;

use App\Models\BookingAttempt;
use App\Services\BookingAttemptService;
use PHPUnit\Framework\TestCase;

class BookingAttemptDecisionTest extends TestCase
{
    public function test_retry_decisions(): void
    {
        $service = new BookingAttemptService();

        $cases = [
            ['processing', null, 'wait'],
            ['pending_verification', null, 'manual_verification_required'],
            ['completed', 123, 'show_existing_booking'],
            ['completed', null, 'manual_verification_required'],
            ['failed', null, 'manual_verification_required'],
        ];

        foreach ($cases as [$status, $bookingId, $expected]) {
            $attempt = new BookingAttempt();
            $attempt->status = $status;
            $attempt->booking_id = $bookingId;

            $this->assertSame(
                $expected,
                $service->retryDecision($attempt),
                "Unexpected decision for status: {$status}"
            );
        }
    }
}
