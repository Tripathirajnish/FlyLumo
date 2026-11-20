<?php

namespace Tests\Unit;

use App\Models\BookingAttempt;
use App\Services\BookingAttemptService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingAttemptCompletionTest extends TestCase
{
    public function test_only_processing_attempt_can_be_completed(): void
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
                'attempt_key' => hash('sha256', 'completion-test'),
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $this->assertTrue($service->markCompleted($processing, 12345));

            $processing->refresh();

            $this->assertSame('completed', $processing->status);
            $this->assertSame(12345, $processing->booking_id);
            $this->assertNotNull($processing->completed_at);

            $pending = BookingAttempt::create([
                'attempt_key' => hash('sha256', 'pending-completion-test'),
                'status' => 'pending_verification',
                'started_at' => now(),
            ]);

            $this->assertFalse($service->markCompleted($pending, 54321));

            $pending->refresh();

            $this->assertSame('pending_verification', $pending->status);
            $this->assertNull($pending->booking_id);

            $this->assertFalse($service->markCompleted($processing, 99999));
            $this->assertFalse($service->markCompleted($pending, 0));
        } finally {
            Schema::dropIfExists('booking_attempts');
        }
    }
}
