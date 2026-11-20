<?php

namespace Tests\Unit;

use App\Models\BookingAttempt;
use App\Services\BookingAttemptService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingAttemptRegistrationTest extends TestCase
{
    public function test_duplicate_registration_reuses_existing_attempt(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $migration = require base_path(
            'database/migrations/2026_10_10_032658_create_booking_attempts_table.php'
        );

        try {
            $migration->up();

            $service = new BookingAttemptService();
            $key = hash('sha256', 'isolated-test-booking');

            $first = $service->register($key);
            $second = $service->register($key);

            $this->assertTrue($first['created']);
            $this->assertFalse($second['created']);
            $this->assertSame($first['attempt']->id, $second['attempt']->id);
            $this->assertSame(1, BookingAttempt::count());
        } finally {
            Schema::dropIfExists('booking_attempts');
        }
    }
}
