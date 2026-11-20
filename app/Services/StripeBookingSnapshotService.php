<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Step F2a: encrypted booking snapshot per Stripe attempt (owner-approved D1).
 *  - Payload is JSON encrypted with the app key (Crypt). Never readable in the DB.
 *  - Card data is refused: any card-like key anywhere in the payload throws.
 *  - consume() is compare-and-set so the booking executor can use a snapshot once.
 */
class StripeBookingSnapshotService
{
    public const VERSION = 1;
    public const KEEP_DAYS = 30;
    public const REQUIRED = ['search_code', 'offer_code', 'search_guid', 'contact', 'passengers', 'billing', 'price'];
    private const CARD_KEY = '/(creditcard|cardholder|card_?num|card_?no$|^pan$|cvv|cvc|expire_month|expire_year|card_?exp)/i';

    public function store(int $attemptId, array $data): void
    {
        if ($attemptId <= 0) {
            throw new RuntimeException('Invalid attempt.');
        }
        foreach (self::REQUIRED as $k) {
            if (!array_key_exists($k, $data)) {
                throw new RuntimeException('Booking snapshot is missing '.$k.'.');
            }
        }
        $this->refuseCardData($data);

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Booking snapshot could not be encoded.');
        }

        $now = now();
        $existing = DB::table('stripe_booking_snapshots')->where('attempt_id', $attemptId)->first();
        if ($existing && $existing->consumed_at !== null) {
            throw new RuntimeException('Booking snapshot already used.');
        }

        $row = [
            'payload_version' => self::VERSION,
            'payload' => Crypt::encryptString($json),
            'purge_after' => $now->copy()->addDays(self::KEEP_DAYS),
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('stripe_booking_snapshots')->where('id', $existing->id)->whereNull('consumed_at')->update($row);
        } else {
            DB::table('stripe_booking_snapshots')->insert($row + ['attempt_id' => $attemptId, 'created_at' => $now]);
        }
    }

    public function load(int $attemptId): ?array
    {
        $row = DB::table('stripe_booking_snapshots')->where('attempt_id', $attemptId)->first();
        if (!$row) {
            return null;
        }
        if ((int) $row->payload_version !== self::VERSION) {
            throw new RuntimeException('Unsupported booking snapshot version.');
        }
        $data = json_decode(Crypt::decryptString((string) $row->payload), true);
        if (!is_array($data)) {
            throw new RuntimeException('Booking snapshot is unreadable.');
        }

        return $data;
    }

    /** true only for the first caller. */
    public function consume(int $attemptId): bool
    {
        return DB::table('stripe_booking_snapshots')
            ->where('attempt_id', $attemptId)->whereNull('consumed_at')
            ->update(['consumed_at' => now(), 'updated_at' => now()]) === 1;
    }

    private function refuseCardData(array $data): void
    {
        array_walk_recursive($data, function ($v, $k) {
            if (is_string($k) && preg_match(self::CARD_KEY, $k)) {
                throw new RuntimeException('Card data must never be stored in a booking snapshot.');
            }
        });
        foreach ($data as $k => $v) {
            if (is_string($k) && preg_match(self::CARD_KEY, $k)) {
                throw new RuntimeException('Card data must never be stored in a booking snapshot.');
            }
            if (is_array($v)) {
                $this->refuseCardData($v);
            }
        }
    }
}
