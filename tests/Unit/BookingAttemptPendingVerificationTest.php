<?php

namespace Tests\Unit;

use App\Models\BookingAttempt;
use App\Services\BookingAttemptService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingAttemptPendingVerificationTest extends TestCase
{
    public function test_only_processing_attempt_can_be_marked_pending(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $migration = require base_path(
            'database/migrations/2026_10_10_032658_create_booking_attempts_table.php'
        );

        try {
            $migration->up();

            $service = new BookingAttemptService();

            $processing = BookingAttempt::create([
                'attempt_key' => hash('sha256', 'processing-test'),
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $this->assertTrue(
                $service->markPendingVerification($processing, 'SOAP_TIMEOUT')
            );

            $processing->refresh();

            $this->assertSame('pending_verification', $processing->status);
            $this->assertSame('SOAP_TIMEOUT', $processing->last_error_code);

            $completed = BookingAttempt::create([
                'attempt_key' => hash('sha256', 'completed-test'),
                'status' => 'completed',
                'booking_id' => 12345,
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            $this->assertFalse(
                $service->markPendingVerification($completed, 'SOAP_TIMEOUT')
            );

            $completed->refresh();

            $this->assertSame('completed', $completed->status);
            $this->assertSame(12345, $completed->booking_id);
            $this->assertNull($completed->last_error_code);
        } finally {
            Schema::dropIfExists('booking_attempts');
        }
    }
}
