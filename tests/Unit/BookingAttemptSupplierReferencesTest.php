<?php

namespace Tests\Unit;

use App\Models\BookingAttempt;
use App\Services\BookingAttemptService;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BookingAttemptSupplierReferencesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once database_path(
            'migrations/2026_10_10_032658_create_booking_attempts_table.php'
        );

        $migration = require database_path(
            'migrations/2026_10_10_032658_create_booking_attempts_table.php'
        );

        $migration->up();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('booking_attempts');
        parent::tearDown();
    }

    public function test_supplier_references_are_saved_only_once(): void
    {
        $service = new BookingAttemptService();

        $attempt = BookingAttempt::create([
            'attempt_key' => str_repeat('a', 64),
            'status' => 'processing',
            'started_at' => now(),
        ]);

        $this->assertTrue(
            $service->recordSupplierReferences($attempt, 'BOOK-123', 'GUID-123')
        );

        $attempt->refresh();

        $this->assertSame('BOOK-123', $attempt->supplier_book_id);
        $this->assertSame('GUID-123', $attempt->supplier_book_guid);

        $this->assertFalse(
            $service->recordSupplierReferences($attempt, 'BOOK-999', 'GUID-999')
        );

        $attempt->refresh();

        $this->assertSame('BOOK-123', $attempt->supplier_book_id);
        $this->assertSame('GUID-123', $attempt->supplier_book_guid);
    }

    public function test_completed_attempt_cannot_be_modified(): void
    {
        $attempt = BookingAttempt::create([
            'attempt_key' => str_repeat('b', 64),
            'status' => 'completed',
        ]);

        $this->assertFalse(
            (new BookingAttemptService())->recordSupplierReferences(
                $attempt, 'BOOK-123', 'GUID-123'
            )
        );
    }

    public function test_invalid_references_are_rejected(): void
    {
        $attempt = BookingAttempt::create([
            'attempt_key' => str_repeat('c', 64),
            'status' => 'processing',
        ]);

        $service = new BookingAttemptService();

        $this->assertFalse(
            $service->recordSupplierReferences($attempt, '', 'GUID-123')
        );

        $this->assertFalse(
            $service->recordSupplierReferences($attempt, 'BOOK-123', '')
        );

        $this->assertFalse(
            $service->recordSupplierReferences($attempt, str_repeat('X', 101), 'GUID-123')
        );

        $this->assertFalse(
            $service->recordSupplierReferences($attempt, 'BOOK-123', str_repeat('Y', 151))
        );
    }
}
