<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FaresHuntEmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('mail_templates')) {
            $this->command?->error('mail_templates table not found.');
            return;
        }

        /*
         * IMPORTANT:
         * We use neutral tokens such as [[bookingid]] inside PHP HTML strings.
         * At the very end they are converted to the legacy CRM variables
         * such as $bookingid. This prevents PHP from trying to evaluate
         * $sectorgos, $bookingid, $name, etc. while running the seeder.
         */
        $legacyVars = function (string $html): string {
            return strtr($html, [
                '[[bookingid]]'      => '$bookingid',
                '[[name]]'           => '$name',
                '[[c_name]]'         => '$c_name',
                '[[email]]'          => '$email',
                '[[c_emailid]]'      => '$c_emailid',
                '[[phone]]'          => '$phone',
                '[[c_phone]]'        => '$c_phone',
                '[[pnr]]'            => '$pnr',
                '[[price]]'          => '$price',
                '[[totalprice]]'     => '$totalprice',
                '[[passengers]]'     => '$passengers',
                '[[sectorgos]]'      => '$sectorgos',
                '[[sectorreturns]]'  => '$sectorreturns',
                '[[header_contact]]' => '$header_contact',
                '[[content_emailid]]'=> '$content_emailid',
            ]);
        };

        $logo = '{{ asset("mail_images/fareshunt-logo.png") }}';

        $shell = function (
            string $eyebrow,
            string $title,
            string $intro,
            string $accentHtml = '',
            string $bodyHtml = ''
        ) use ($logo): string {
            return <<<HTML
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6f9;margin:0;padding:0;">
<tr>
<td align="center" style="padding:24px 8px;">
<table role="presentation" width="680" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:680px;background:#ffffff;border:1px solid #e4e9f0;border-radius:20px;overflow:hidden;">
<tr>
<td style="padding:20px 28px;border-bottom:1px solid #eef1f5;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td align="left"><img src="$logo" alt="Fares Hunt" width="170" style="display:block;width:170px;max-width:170px;height:auto;border:0;"></td>
<td align="right" style="font-family:Arial,Helvetica,sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;color:#12365f;">PASSENGER UPDATE</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:30px 30px 12px;font-family:Arial,Helvetica,sans-serif;">
<div style="font-size:10px;letter-spacing:.18em;text-transform:uppercase;color:#e51f5a;font-weight:700;">$eyebrow</div>
<div style="font-size:28px;line-height:1.15;font-weight:700;color:#132b4b;margin-top:7px;">$title</div>
<div style="font-size:14px;line-height:1.75;color:#5f6f82;margin-top:14px;">$intro</div>
</td>
</tr>

$accentHtml

<tr>
<td style="padding:12px 30px 26px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.7;color:#33465c;">
$bodyHtml
</td>
</tr>

<tr>
<td style="padding:18px 30px;background:#143d6a;font-family:Arial,Helvetica,sans-serif;color:#ffffff;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td style="font-size:11px;color:#b9cee3;">Need help?</td>
<td align="right" style="font-size:11px;color:#ffffff;">[[header_contact]] &nbsp; · &nbsp; [[content_emailid]]</td>
</tr>
</table>
</td>
</tr>

<tr>
<td style="padding:15px 30px;text-align:center;font-family:Arial,Helvetica,sans-serif;font-size:10px;line-height:1.6;color:#8795a6;">
Fares Hunt · Booking reference [[bookingid]]<br>
This is an automated service communication related to your travel request.
</td>
</tr>
</table>
</td>
</tr>
</table>
HTML;
        };

        $refCard = function (string $label, string $value, string $note = ''): string {
            return <<<HTML
<tr>
<td style="padding:10px 30px 12px;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#143d6a;border-radius:14px;">
<tr>
<td style="padding:18px 20px;font-family:Arial,Helvetica,sans-serif;color:#ffffff;">
<div style="font-size:9px;letter-spacing:.12em;text-transform:uppercase;color:#a9bfd5;">$label</div>
<div style="font-size:23px;font-weight:800;color:#ff4d77;margin-top:6px;">$value</div>
<div style="font-size:11px;color:#c1d2e2;margin-top:7px;">$note</div>
</td>
</tr>
</table>
</td>
</tr>
HTML;
        };

        $notice = function (string $text, string $tone = 'blue'): string {
            $styles = [
                'blue'  => ['#f4f8fd','#dce7f3','#345c86'],
                'green' => ['#eefaf5','#cfeadd','#167a58'],
                'amber' => ['#fff8eb','#f3dfb7','#8d621a'],
                'red'   => ['#fff2f4','#f5d0d8','#a83a50'],
            ];

            [$bg,$border,$color] = $styles[$tone] ?? $styles['blue'];

            return <<<HTML
<div style="padding:13px 15px;border:1px solid $border;background:$bg;border-radius:11px;color:$color;margin:4px 0 15px;">$text</div>
HTML;
        };

        $itinerary = function (): string {
            return <<<'HTML'
<div style="margin-top:18px;border-top:1px solid #e7ecf2;padding-top:16px;">
<div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#e51f5a;font-weight:700;margin-bottom:8px;">Itinerary</div>
<div style="color:#132b4b;font-weight:700;">[[sectorgos]]</div>
<div style="margin-top:7px;color:#5f6f82;">[[sectorreturns]]</div>
</div>
HTML;
        };

        $templates = [
            [
                'type' => 'E-Ticket Issued [CRM]',
                'label' => 'E-Ticket Issued',
                'category' => 'Ticketing',
                'subject' => 'Your E-Ticket Is Ready | Fares Hunt | [[bookingid]]',
                'variable' => 'Passenger e-ticket issued notification. Uses: [[bookingid]], [[name]], [[pnr]], [[sectorgos]], [[sectorreturns]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Ticketing complete',
                    'Your e-ticket is ready',
                    'Hi [[name]], your booking has completed ticketing. Please review your itinerary and keep your airline-issued e-ticket details available for travel.',
                    $refCard('Booking reference', '[[bookingid]]', 'PNR / airline reference: [[pnr]]'),
                    $notice('<b>Ticketing complete.</b> Your final airline ticket details are now available.', 'green')
                    . '<p>Please verify passenger names, travel dates, airports and flight times. If anything looks incorrect, contact Fares Hunt as soon as possible.</p>'
                    . $itinerary()
                    . '<p style="margin-top:18px"><b>Travel reminder:</b> Carry the identification and travel documents required for your itinerary. Airline check-in rules and document requirements remain the passenger’s responsibility.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Payment Received [CRM]',
                'label' => 'Payment Received',
                'category' => 'Payments',
                'subject' => 'Payment Received | Fares Hunt | [[bookingid]]',
                'variable' => 'Payment acknowledgement. Uses: [[bookingid]], [[name]], [[totalprice]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Payment update',
                    'Payment received',
                    'Hi [[name]], we have recorded your payment for booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Recorded amount: [[totalprice]]'),
                    $notice('<b>Payment received.</b> Ticketing or supplier confirmation may still be in progress.', 'green')
                    . '<p>Payment acknowledgement does not by itself represent an airline-issued ticket. We will send the final ticketing information separately when applicable.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Payment Pending [CRM]',
                'label' => 'Payment Pending',
                'category' => 'Payments',
                'subject' => 'Action Needed: Payment Pending | Fares Hunt | [[bookingid]]',
                'variable' => 'Payment pending passenger update. Uses: [[bookingid]], [[name]], [[totalprice]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Action required',
                    'Your payment is pending',
                    'Hi [[name]], payment for booking [[bookingid]] has not yet been completed or confirmed.',
                    $refCard('Booking reference', '[[bookingid]]', 'Booking total: [[totalprice]]'),
                    $notice('<b>Action may be required.</b> Please contact our support team if you need assistance completing the booking.', 'amber')
                    . '<p>Fare and seat availability may change until payment and ticketing are confirmed. Please do not send card details by email.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Payment Declined [CRM]',
                'label' => 'Payment Declined',
                'category' => 'Payments',
                'subject' => 'Payment Could Not Be Completed | Fares Hunt | [[bookingid]]',
                'variable' => 'Payment failure notification. Never include PAN/CVV. Uses: [[bookingid]], [[name]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Payment update',
                    'Payment could not be completed',
                    'Hi [[name]], we were unable to complete the payment associated with booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Payment status: declined / unsuccessful'),
                    $notice('<b>No card details are included in this email.</b> Please contact Fares Hunt for assistance or use the approved payment method provided by our team.', 'red')
                    . '<p>Fares and availability can change until payment and ticketing are successfully completed.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Fare Change [CRM]',
                'label' => 'Fare Change / Recheck Required',
                'category' => 'Booking Update',
                'subject' => 'Fare Recheck Required | Fares Hunt | [[bookingid]]',
                'variable' => 'Fare change/recheck alert. Uses: [[bookingid]], [[name]], [[price]], [[totalprice]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Booking update',
                    'A fare recheck is required',
                    'Hi [[name]], the fare or availability connected to booking [[bookingid]] requires reconfirmation before ticketing can be completed.',
                    $refCard('Booking reference', '[[bookingid]]', 'Current displayed total: [[totalprice]]'),
                    $notice('<b>Please contact us before taking further action.</b> Airline fares and inventory can change before ticket issuance.', 'amber')
                    . '<p>Our travel team will review the available option and explain any applicable fare difference before a change is confirmed.</p>'
                    . $itinerary()
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Schedule Change [CRM]',
                'label' => 'Airline Schedule Change',
                'category' => 'Booking Update',
                'subject' => 'Important Flight Schedule Update | Fares Hunt | [[bookingid]]',
                'variable' => 'Schedule change notification. Uses: [[bookingid]], [[name]], [[pnr]], [[sectorgos]], [[sectorreturns]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Important travel update',
                    'Your flight schedule has changed',
                    'Hi [[name]], the airline has reported a schedule change affecting booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Airline reference: [[pnr]]'),
                    $notice('<b>Please review the updated itinerary carefully.</b> Flight times, flight numbers or connections may have changed.', 'amber')
                    . $itinerary()
                    . '<p>If the revised itinerary does not work for you, contact our support team. Available alternatives remain subject to the airline’s rules and inventory.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Cancellation Requested [CRM]',
                'label' => 'Cancellation Request Received',
                'category' => 'Cancellation',
                'subject' => 'Cancellation Request Received | Fares Hunt | [[bookingid]]',
                'variable' => 'Acknowledges a cancellation request; does not state that cancellation is complete. Uses: [[bookingid]], [[name]], [[pnr]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Cancellation',
                    'We received your cancellation request',
                    'Hi [[name]], we have received a cancellation request for booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Airline reference: [[pnr]]'),
                    $notice('<b>Your booking is not necessarily cancelled yet.</b> Cancellation remains subject to airline/supplier rules and confirmation.', 'amber')
                    . '<p>Our team will review fare rules, airline penalties and any applicable refund eligibility. We will contact you with the outcome.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Cancellation Confirmed [CRM]',
                'label' => 'Cancellation Confirmed',
                'category' => 'Cancellation',
                'subject' => 'Booking Cancellation Confirmed | Fares Hunt | [[bookingid]]',
                'variable' => 'Final cancellation confirmation. Uses: [[bookingid]], [[name]], [[pnr]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Cancellation complete',
                    'Your booking has been cancelled',
                    'Hi [[name]], cancellation has been recorded for booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Airline reference: [[pnr]]'),
                    $notice('<b>Cancellation confirmed.</b> Any refund, credit or residual value is governed by the applicable fare and airline rules.', 'red')
                    . '<p>If a refund or airline credit applies, a separate update can be sent when processing is completed.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Refund Requested [CRM]',
                'label' => 'Refund Request Received',
                'category' => 'Refunds',
                'subject' => 'Refund Request Received | Fares Hunt | [[bookingid]]',
                'variable' => 'Refund request acknowledgement. Uses: [[bookingid]], [[name]], [[totalprice]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Refund update',
                    'Your refund request is under review',
                    'Hi [[name]], we have recorded a refund request connected to booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Original booking total: [[totalprice]]'),
                    $notice('<b>Refund requested.</b> Approval and timing depend on airline/supplier policy and the original form of payment.', 'blue')
                    . '<p>This message confirms receipt of the request only; it does not confirm that a refund has been approved or completed.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Refund Processed [CRM]',
                'label' => 'Refund Processed',
                'category' => 'Refunds',
                'subject' => 'Refund Processed | Fares Hunt | [[bookingid]]',
                'variable' => 'Refund processing update. Uses: [[bookingid]], [[name]], [[totalprice]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Refund complete',
                    'Your refund has been processed',
                    'Hi [[name]], the refund workflow for booking [[bookingid]] has been marked as processed.',
                    $refCard('Booking reference', '[[bookingid]]', 'Booking total: [[totalprice]]'),
                    $notice('<b>Refund processed.</b> Posting time to the customer’s account can vary by bank, card issuer, airline or payment processor.', 'green')
                    . '<p>Please allow the financial institution’s normal processing time before following up on the credit.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Exchange Confirmed [CRM]',
                'label' => 'Flight Change / Exchange Confirmed',
                'category' => 'Booking Update',
                'subject' => 'Your Updated Itinerary | Fares Hunt | [[bookingid]]',
                'variable' => 'Confirmed exchange/change itinerary. Uses: [[bookingid]], [[name]], [[pnr]], [[sectorgos]], [[sectorreturns]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Itinerary updated',
                    'Your flight change is confirmed',
                    'Hi [[name]], the requested itinerary change for booking [[bookingid]] has been completed.',
                    $refCard('Booking reference', '[[bookingid]]', 'Airline reference: [[pnr]]'),
                    $notice('<b>Updated itinerary.</b> Please disregard superseded flight details and review the itinerary below.', 'green')
                    . $itinerary()
                    . '<p>Additional airline restrictions or residual ticket value may apply based on the fare rules used for the exchange.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Information Required [CRM]',
                'label' => 'Passenger Information Required',
                'category' => 'Follow-up',
                'subject' => 'Action Required for Your Booking | Fares Hunt | [[bookingid]]',
                'variable' => 'General passenger follow-up. Never request full card number or CVV by email. Uses: [[bookingid]], [[name]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Action required',
                    'We need information to continue',
                    'Hi [[name]], our team needs additional information to continue processing booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Please contact Fares Hunt using the support details below.'),
                    $notice('<b>For your security:</b> do not email full card numbers, CVV/security codes, passwords or other sensitive authentication data.', 'amber')
                    . '<p>Please contact our travel support team so we can explain exactly what is required for your booking.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Check-In Reminder [CRM]',
                'label' => 'Online Check-In Reminder',
                'category' => 'Travel Reminder',
                'subject' => 'Check-In Reminder | Fares Hunt | [[bookingid]]',
                'variable' => 'Check-in reminder. Uses: [[bookingid]], [[name]], [[pnr]], [[sectorgos]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Travel reminder',
                    'Check-in time is approaching',
                    'Hi [[name]], this is a reminder to review your airline check-in requirements for booking [[bookingid]].',
                    $refCard('Booking reference', '[[bookingid]]', 'Airline reference: [[pnr]]'),
                    $notice('<b>Check in directly with the operating airline.</b> Check-in windows, document checks and seat assignments are controlled by the airline.', 'blue')
                    . $itinerary()
                    . '<p>Arrive at the airport with enough time for security, baggage drop and any required document verification.</p>'
                ),
                'tnc' => '',
            ],
            [
                'type' => 'Departure Reminder [CRM]',
                'label' => 'Departure Reminder',
                'category' => 'Travel Reminder',
                'subject' => 'Your Trip Is Coming Up | Fares Hunt | [[bookingid]]',
                'variable' => 'Pre-departure reminder. Uses: [[bookingid]], [[name]], [[pnr]], [[sectorgos]], [[sectorreturns]], [[header_contact]], [[content_emailid]].',
                'body' => $shell(
                    'Upcoming trip',
                    'Your journey is coming up',
                    'Hi [[name]], your upcoming trip under booking [[bookingid]] is approaching.',
                    $refCard('Booking reference', '[[bookingid]]', 'Airline reference: [[pnr]]'),
                    $notice('<b>Before you travel:</b> reconfirm flight timing, terminal information, baggage rules and travel-document requirements with the airline.', 'blue')
                    . $itinerary()
                ),
                'tnc' => '',
            ],
        ];

        $has = fn (string $column): bool => Schema::hasColumn('mail_templates', $column);

        foreach ($templates as $template) {
            $payload = [
                'type'     => $template['type'],
                'label'    => $template['label'],
                'subject'  => $legacyVars($template['subject']),
                'body'     => $legacyVars($template['body']),
                'tnc'      => $legacyVars($template['tnc']),
                'variable' => $legacyVars($template['variable']),
            ];

            // Email Templates V2 compatibility.
            if ($has('category')) {
                $payload['category'] = $template['category'];
            }

            if ($has('is_active')) {
                // Keep new templates inactive until you review/activate them.
                $payload['is_active'] = 0;
            }

            if ($has('is_system')) {
                $payload['is_system'] = 0;
            }

            if ($has('created_at')) {
                $payload['created_at'] = now();
            }

            if ($has('updated_at')) {
                $payload['updated_at'] = now();
            }

            $existing = DB::table('mail_templates')
                ->where('type', $template['type'])
                ->first();

            if ($existing) {
                unset($payload['created_at']);

                DB::table('mail_templates')
                    ->where('id', $existing->id)
                    ->update($payload);

                $this->command?->line('Updated: '.$template['label']);
            } else {
                DB::table('mail_templates')->insert($payload);
                $this->command?->line('Created: '.$template['label']);
            }
        }

        $this->command?->info('Fares Hunt passenger email template library installed successfully.');
    }
}