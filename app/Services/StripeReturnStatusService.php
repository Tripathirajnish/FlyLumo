<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Step F5: what the customer sees after paying on Stripe, derived ONLY from our own
 * attempt row (never from the browser). Read-only: no Stripe call, no DB write.
 */
class StripeReturnStatusService
{
    public const FINAL = ['confirmed', 'review', 'fare_changed', 'not_completed'];

    /** null when the reference is unknown. */
    public function forReference(string $reference): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $reference)) {
            return null;
        }
        $a = DB::table('stripe_payment_attempts')->where('reference_hash', $reference)
            ->first(['id', 'status', 'booking_status', 'booking_id']);
        if (!$a) {
            return null;
        }

        $status = (string) $a->status;
        $booking = (string) ($a->booking_status ?? '');

        if ($booking === 'booked' && $a->booking_id) {
            $b = DB::table('bookings')->where('id', $a->booking_id)->first(['id', 'confirmation_token']);
            $ref = $b ? (function_exists('fh_booking_ref') ? fh_booking_ref($b->id) : 'FH'.$b->id) : null;

            return $this->out('confirmed', 'Your booking is confirmed',
                'Thank you! Your booking'.($ref ? ' '.$ref : '').' is confirmed and the confirmation has been sent to your email. '
                .'Your e-ticket will follow once airline ticketing is completed.',
                $ref, $b && $b->confirmation_token ? url('booking-successful/'.$b->confirmation_token) : null);
        }
        if ($booking === 'manual_review') {
            return $this->out('review', 'We are finishing your booking',
                'Your payment is on hold and our team is completing your booking with the airline. '
                .'We will email you shortly. Please do not pay again.');
        }
        if (in_array($booking, ['fare_changed', 'fare_unavailable'], true)) {
            return $this->out('fare_changed', 'We could not confirm this fare',
                'The airline fare '.($booking === 'fare_changed' ? 'changed' : 'is no longer available').' while we were booking it. '
                .'The hold on your card has been released, so no money has been taken. Please search again.');
        }
        if (in_array($status, ['expired', 'failed', 'cancelled'], true)) {
            return $this->out('not_completed', 'Payment not completed',
                'This payment was not completed and no money has been taken. You can search again and book.');
        }
        if ($status === 'authorized') {
            return $this->out('processing', 'Payment received - confirming your seats',
                'Your payment is on hold and we are now confirming your seats with the airline. This usually takes under a minute. Please do not pay again.');
        }

        return $this->out('processing', 'Thank you - we are confirming your payment',
            'We are waiting for the payment confirmation from Stripe. This page updates by itself. Please do not pay again.');
    }

    private function out(string $state, string $title, string $message, ?string $ref = null, ?string $url = null): array
    {
        return [
            'state' => $state,
            'final' => in_array($state, self::FINAL, true),
            'title' => $title,
            'message' => $message,
            'booking_ref' => $ref,
            'booking_url' => $url,
        ];
    }
}
