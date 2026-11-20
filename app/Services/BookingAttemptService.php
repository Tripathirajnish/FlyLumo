<?php

namespace App\Services;

use App\Models\BookingAttempt;
use Illuminate\Database\QueryException;

class BookingAttemptService
{

    public function retryDecision(BookingAttempt $attempt): string
    {
        if ($attempt->status === 'completed' && $attempt->booking_id) {
            return 'show_existing_booking';
        }

        if ($attempt->status === 'processing') {
            return 'wait';
        }

        return 'manual_verification_required';
    }


    public function recordSupplierReferences(
        BookingAttempt $attempt,
        string $bookId,
        string $bookGuid
    ): bool {
        $bookId = trim($bookId);
        $bookGuid = trim($bookGuid);

        if ($bookId === '' || $bookGuid === ''
            || strlen($bookId) > 100
            || strlen($bookGuid) > 150) {
            return false;
        }

        return BookingAttempt::whereKey($attempt->id)
            ->where('status', 'processing')
            ->whereNull('supplier_book_id')
            ->whereNull('supplier_book_guid')
            ->update([
                'supplier_book_id' => $bookId,
                'supplier_book_guid' => $bookGuid,
                'updated_at' => now(),
            ]) === 1;
    }

    public function markCompleted(BookingAttempt $attempt, int $bookingId): bool
    {
        if ($bookingId <= 0) {
            return false;
        }

        return BookingAttempt::whereKey($attempt->id)
            ->where('status', 'processing')
            ->update([
                'status' => 'completed',
                'booking_id' => $bookingId,
                'completed_at' => now(),
                'last_error_code' => null,
                'updated_at' => now(),
            ]) === 1;
    }

    public function markPendingVerification(BookingAttempt $attempt, string $errorCode): bool
    {
        return BookingAttempt::whereKey($attempt->id)
            ->where('status', 'processing')
            ->update([
                'status' => 'pending_verification',
                'last_error_code' => substr($errorCode, 0, 100),
                'updated_at' => now(),
            ]) === 1;
    }

    public function register(string $key): array
    {
        try {
            $attempt = BookingAttempt::create([
                'attempt_key' => $key,
                'status' => 'processing',
                'started_at' => now(),
            ]);

            return [
                'created' => true,
                'attempt' => $attempt,
            ];
        } catch (QueryException $e) {
            // Only a duplicate-key error may be treated as an existing attempt.
            $driverCode = (int) ($e->errorInfo[1] ?? 0);
            $sqlState = (string) ($e->errorInfo[0] ?? '');

            if ($driverCode !== 1062 && $sqlState !== '23505'
                && !($sqlState === '23000' && $driverCode === 19)) {
                throw $e;
            }

            $existing = BookingAttempt::where('attempt_key', $key)->first();

            if (!$existing) {
                throw $e;
            }

            return [
                'created' => false,
                'attempt' => $existing,
            ];
        }
    }
}
