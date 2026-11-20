<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DefaultController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\TopFlightController;
use App\Http\Controllers\FareleaderController;

Route::get('/',[DefaultController::class,'home']);

// Public airline information pages.
Route::get('/airlines/{slug}', function ($slug) {
    $airlines = [
        'united-airlines' => [
            'name' => 'United Airlines',
            'code' => 'UA',
            'tagline' => 'A major U.S. carrier connecting domestic cities with an extensive international network.',
            'overview' => 'United Airlines is one of the largest U.S. airlines, serving a broad network across the United States and international destinations. Its network is especially useful for travelers connecting through major U.S. gateway airports to Europe, Asia, Latin America and other global regions.',
            'network' => 'United operates a large domestic and international network, with significant operations through airports such as Chicago O’Hare, Denver, Houston, Newark, San Francisco and Washington Dulles.',
            'program' => 'MileagePlus',
            'alliance' => 'Star Alliance',
            'cabins' => ['Economy','Economy Plus','Premium Plus','United Polaris Business'],
            'search_code' => 'UA',
        ],
        'american-airlines' => [
            'name' => 'American Airlines',
            'code' => 'AA',
            'tagline' => 'A major U.S. airline with extensive domestic service and international connections.',
            'overview' => 'American Airlines serves a wide range of destinations throughout the United States and internationally. Its network is particularly strong for domestic travel, the Caribbean, Latin America and transatlantic journeys.',
            'network' => 'American’s network is supported by major connecting airports including Dallas/Fort Worth, Charlotte, Miami, Phoenix, Chicago O’Hare and Philadelphia.',
            'program' => 'AAdvantage',
            'alliance' => 'oneworld',
            'cabins' => ['Main Cabin','Main Cabin Extra','Premium Economy','Business','First'],
            'search_code' => 'AA',
        ],
        'delta-air-lines' => [
            'name' => 'Delta Air Lines',
            'code' => 'DL',
            'tagline' => 'A major U.S. global airline serving domestic and international destinations.',
            'overview' => 'Delta Air Lines operates an extensive U.S. and international network, with service across North America and major destinations in Europe, Asia, Latin America and other regions.',
            'network' => 'Delta has significant operations through airports such as Atlanta, Detroit, Minneapolis–St. Paul, Salt Lake City, Seattle, Los Angeles, Boston and New York.',
            'program' => 'SkyMiles',
            'alliance' => 'SkyTeam',
            'cabins' => ['Main Cabin','Comfort+','Premium Select','Delta One','First Class'],
            'search_code' => 'DL',
        ],
        'british-airways' => [
            'name' => 'British Airways',
            'code' => 'BA',
            'tagline' => 'A leading UK airline with a broad international network centered on London.',
            'overview' => 'British Airways connects the United Kingdom with destinations across Europe, North America, the Middle East, Asia, Africa and other international markets. It is a common choice for travelers connecting between the United States and the United Kingdom or onward through London.',
            'network' => 'British Airways operates a global network centered primarily on London, with London Heathrow serving as its principal hub.',
            'program' => 'The British Airways Club',
            'alliance' => 'oneworld',
            'cabins' => ['World Traveller','World Traveller Plus','Club World','First'],
            'search_code' => 'BA',
        ],
        'emirates' => [
            'name' => 'Emirates',
            'code' => 'EK',
            'tagline' => 'A Dubai-based international airline connecting travelers across a wide global network.',
            'overview' => 'Emirates is a major international carrier based in Dubai. Its network is well suited to travelers connecting between the United States and destinations across the Middle East, South Asia, Africa, Asia and other international regions.',
            'network' => 'Emirates operates its global network through Dubai International Airport, allowing many long-haul itineraries to connect through Dubai.',
            'program' => 'Emirates Skywards',
            'alliance' => 'Independent global carrier',
            'cabins' => ['Economy','Premium Economy','Business Class','First Class'],
            'search_code' => 'EK',
        ],
        'qatar-airways' => [
            'name' => 'Qatar Airways',
            'code' => 'QR',
            'tagline' => 'A Doha-based international airline with extensive long-haul connections.',
            'overview' => 'Qatar Airways serves an extensive international network through Doha. It is frequently used for long-haul travel between the United States and destinations across the Middle East, India, South Asia, Africa, Southeast Asia and other global regions.',
            'network' => 'Qatar Airways operates its network through Hamad International Airport in Doha, providing connections to destinations around the world.',
            'program' => 'Qatar Airways Privilege Club',
            'alliance' => 'oneworld',
            'cabins' => ['Economy','Business Class','First Class on selected services'],
            'search_code' => 'QR',
        ],
    ];

    abort_unless(isset($airlines[$slug]), 404);

    return view('airline', ['airline' => $airlines[$slug], 'slug' => $slug]);
})->name('airline_detail');

// Human-readable site map page linked from the footer.
Route::get('/sitemap', function () {
    return view('sitemap');
})->name('sitemap');


// XML sitemap for search engines.
Route::get('/sitemap.xml', function () {
    $urls = [
        url('/'),
        url('/deals'),
        url('/terms-and-conditions'),
        url('/about-us'),
        url('/privacy-policy'),
        url('/cookie-policy'),
        url('/disclaimer'),
        url('/contact-us'),
        url('/travel-guide'),
        url('/blog/navigating-baggage-rules-and-unexpected-flight-delays'),
        url('/blog/understanding-real-time-airfares-and-promotional-tickets'),
        url('/blog/the-essential-international-travel-checklist:-passports,-visas,-and-name-accuracy'),
        url('/blog/from-sun-and-surf-to-urban-escapes:-planning-your-next-great-adventure'),
        url('/refund-and-dispute-policy'),
        url('/help-center'),
        url('/destinations'),

        url('/airlines/united-airlines'),
        url('/airlines/american-airlines'),
        url('/airlines/delta-air-lines'),
        url('/airlines/british-airways'),
        url('/airlines/emirates'),
        url('/airlines/qatar-airways'),

        url('/offer/usa-to-india-flight-deals-from-$699'),
        url('/offer/usa-to-europe-flight-deals'),
    ];

    $destinationUrls = \Illuminate\Support\Facades\DB::table('destinations')
        ->where('is_visible', '1')
        ->orderByRaw('CASE WHEN sequence IS NULL THEN 1 ELSE 0 END')
        ->orderBy('sequence')
        ->orderBy('title')
        ->pluck('slug');

    foreach ($destinationUrls as $destinationSlug) {
        $urls[] = url('/destination/' . $destinationSlug);
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    foreach ($urls as $url) {
        $xml .= "  <url><loc>" .
            htmlspecialchars($url, ENT_XML1, 'UTF-8') .
            "</loc></url>\n";
    }

    $xml .= '</urlset>';

    return response($xml, 200)
        ->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('xml_sitemap');


// Public Terms & Conditions URL.
// Keep the existing CMS slug `terms-of-use` internally so existing content remains intact.
Route::get('/terms-and-conditions', function () {
    return app(\App\Http\Controllers\DefaultController::class)
        ->single_content('terms-and-conditions');
})->name('terms_conditions');

// Preserve old links/bookmarks and send them to the new public URL.
Route::redirect('/terms-of-use', '/terms-and-conditions', 301);

Route::get('/content/{slug}',[DefaultController::class,'single_content'])->name('single_content');
Route::get('/page/{slug}',[DefaultController::class,'single_content'])->name('public_page');
Route::get('/{slug}',[DefaultController::class,'single_content'])
    ->where('slug','about-us|why-choose-us|privacy-policy|cookie-policy|terms-of-use|return-policy|shipping-policy|cancellation-policy|refund-and-dispute-policy|disclaimer')
    ->name('friendly_content');

Route::get('/travel-guide',[DefaultController::class,'all_blogs'])->name('all_blogs');
Route::get('/category/{catslug}',[DefaultController::class,'blogs_bycat'])->name('blogs_bycat');
Route::get('/blog/{slug}',[DefaultController::class,'single_blog'])->name('single_blog');

Route::domain('crm.fareshunt.com')->get('/destinations', [App\Http\Controllers\CrmController::class, 'destinationsManager'])->name('crm.destinations_manager');
Route::get('/destinations',[DefaultController::class,'all_destinations'])->name('all_destinations');
Route::get('/destination/{slug}',[DefaultController::class,'single_destination'])->name('single_destination');

Route::get('/travel-offer/{version}', [App\Http\Controllers\CrmTravelOfferController::class, 'customerView'])
    ->whereNumber('version')
    ->middleware('signed')
    ->name('travel_offer.customer');

Route::get('/travel-offer/{version}/book', [App\Http\Controllers\CrmTravelOfferController::class, 'bookSlot'])
    ->whereNumber('version')
    ->middleware('signed')
    ->name('travel_offer.book');

Route::post('/travel-offer/{version}/review', [App\Http\Controllers\CrmTravelOfferController::class, 'reviewSlot'])
    ->whereNumber('version')
    ->middleware('signed')
    ->name('travel_offer.review');

Route::post('/travel-offer/{version}/confirm', [App\Http\Controllers\CrmTravelOfferController::class, 'confirmSlot'])
    ->whereNumber('version')
    ->middleware('signed')
    ->name('travel_offer.confirm');

Route::get('/deals', [DefaultController::class, 'all_deals'])->name('all_deals');
Route::get('/offers', [DefaultController::class, 'all_offers'])->name('all_offers');
Route::get('/offer/{slug}',[DefaultController::class,'single_offer'])->name('single_offer');

Route::get('/contact-us',[DefaultController::class,'contact_us'])->name('contact_us');
Route::get('/help-center',[DefaultController::class,'help_center'])->name('help_center');
Route::POST('/add-ticket',[DefaultController::class,'submitTicket'])->name('add_ticket');
Route::get('/thank-you',[DefaultController::class,'thankyou'])->name('thankyou');

Route::POST('/submit-addholiday',[EnquiryController::class,'submit_holiday'])->name('submit_holiday');
Route::POST('/submit-search',[EnquiryController::class,'submit_search'])->middleware('throttle:flight-search')->name('submit_search');
Route::POST('/submit-contact',[EnquiryController::class,'submit_contact'])->middleware('throttle:contact')->name('submit_contact');

// Flight Related Routes

Route::GET('/flight-listing/{code}',[FlightController::class,'flight_listing'])->middleware('throttle:flight-search')->name('flight_listing');
Route::GET('/flight-payment/{code}/{arraykey}',[FlightController::class,'flight_booking'])->name('flight_booking');
Route::GET('/search-flight',[FlightController::class,'flight_search'])->name('search_flight');

Route::GET('/submit-prebook/ajax/{code}/{OfferCode}/{SearchGuid}/{key}',[FlightController::class,'submitPreBook'])->name('submit_prebook_ajax');
Route::GET('/ajax/get-airport',[FlightController::class,'ajaxGetAirport'])->name('ajax_get_airport');
Route::GET('/ajax/get-flight-results',[FlightController::class,'ajaxGetFlightResults'])->name('get_flight_results');
Route::GET('/ajax/get-flight-detail',[FlightController::class,'ajaxGetFlightDetail'])->name('get_flight_detail');
Route::GET('/ajax/get-markup-detail',[FlightController::class,'ajaxGetMarkupDetail'])->name('get_markup_detail');
Route::get('/ajax/get-topflight-price', [TopFlightController::class, 'ajaxGetFlightData'])->middleware('throttle:route-fare');
Route::get('/ajax/route-fare', [TopFlightController::class, 'ajaxRouteFare'])->middleware('throttle:route-fare')->name('ajax_route_fare');
Route::POST('/ajax/store-temp-booking', [EnquiryController::class, 'submit_tempbooking']);
Route::POST('/ajax/track-fareleader-event', [EnquiryController::class, 'trackFareleaderEvent'])->name('fareleader_track_event');
Route::POST('/ajax/get-service-info', [EnquiryController::class, 'getServiceInfo']);

Route::POST('/submit-filtering',[FlightController::class,'submitLeftFiltering'])->name('submit_filtering');
Route::POST('/submit-flight',[FlightController::class,'submitBooking'])->name('submit_flight');
Route::POST('/submit-confirm',[FlightController::class,'submitConfirm'])->name('submit_confirm');
Route::POST('/submit-orderinfo',[FlightController::class,'submitOrderInfo'])->name('submit_orderinfo');

Route::GET('/booking-successful/{bookingid}',[FlightController::class,'booking_successful'])->name('booking_successful');
Route::get('/not-found-page',[DefaultController::class,'no_found'])->name('not-found-page');

// Fareleader MetaSite
Route::GET('/fareleader_ui_search', [FareleaderController::class, 'uiSearch'])->name('fareleader_ui_search');
Route::POST('/fareleader_ui_search', [FareleaderController::class, 'uiSearchSubmitForm'])->middleware('throttle:flight-search')->name('fareleader_ui_search_submit');
Route::GET('/api/search', [FareleaderController::class, 'listSearch'])->middleware('throttle:flight-search');
Route::GET('/api/jsonsearch', [FareleaderController::class, 'jsonSearch'])->middleware('throttle:meta-search');
Route::GET('/api/core/jsonsearch', [FareleaderController::class, 'coreJsonSearch'])->middleware('throttle:meta-search');
Route::GET('/flights-payment',[FareleaderController::class,'flightBooking'])->name('fareleader_flight_booking');
Route::POST('/submit-flights-payment',[FlightController::class,'submitBookingFLeader'])->name('fareleader_flight_booking_submit');

// Redirect GET to Home or 404
Route::GET('/submit-addholiday', function () { return redirect('/not-found-page'); });
Route::GET('/submit-search', function () { return redirect('/not-found-page'); });
Route::GET('/submit-contact', function () { return redirect('/not-found-page'); });
Route::GET('/submit-filtering', function () { return redirect('/not-found-page'); });
Route::GET('/submit-flight', function () { return redirect('/not-found-page'); });
Route::GET('/submit-confirm', function () { return redirect('/not-found-page'); });
Route::GET('/submit-orderinfo', function () { return redirect('/not-found-page'); });
#Route::GET('/submit-flight-fleader', function () { return redirect('/not-found-page'); });
Route::GET('/submit-flights-payment', function () { return redirect('/not-found-page'); });


// CRM workspace - presentation/operations layer only. Existing flight APIs and fare rules remain untouched.
Route::post('/fare-alerts/subscribe', [App\Http\Controllers\FareAlertController::class, 'subscribe'])->name('fare_alert.subscribe');


// Legacy /crm compatibility routes
Route::get('/crm/login', [App\Http\Controllers\CrmController::class, 'login'])->name('legacy.crm.login');
Route::post('/crm/login', [App\Http\Controllers\CrmController::class, 'authenticate'])->middleware('throttle:crm-login')->name('legacy.crm.authenticate');
Route::post('/crm/logout', [App\Http\Controllers\CrmController::class, 'logout'])->name('legacy.crm.logout');
Route::get('/crm', [App\Http\Controllers\CrmController::class, 'dashboard'])->name('legacy.crm.dashboard');
Route::get('/crm/bookings', [App\Http\Controllers\CrmController::class, 'bookings'])->name('legacy.crm.bookings');
Route::get('/crm/incomplete-bookings', [App\Http\Controllers\CrmController::class, 'incompleteBookings'])->name('legacy.crm.incomplete_bookings');
Route::get('/crm/incomplete-bookings/{id}', [App\Http\Controllers\CrmController::class, 'incompleteBookingDetail'])->whereNumber('id')->name('legacy.crm.incomplete_booking_detail');
Route::post('/crm/incomplete-bookings/{id}/send-email', [App\Http\Controllers\CrmController::class, 'sendIncompleteBookingEmail'])->whereNumber('id')->name('legacy.crm.incomplete_booking_email_send');
Route::delete('/crm/incomplete-bookings/{id}', [App\Http\Controllers\CrmController::class, 'incompleteBookingDelete'])->whereNumber('id')->name('legacy.crm.incomplete_booking_delete');
Route::get('/crm/travel-desk', [App\Http\Controllers\CrmTravelDeskController::class, 'index'])->name('legacy.crm.travel_desk');
Route::get('/crm/travel-desk/create', [App\Http\Controllers\CrmTravelDeskController::class, 'create'])->name('legacy.crm.travel_desk.create');
Route::post('/crm/travel-desk', [App\Http\Controllers\CrmTravelDeskController::class, 'store'])->name('legacy.crm.travel_desk.store');
Route::get('/crm/travel-desk/{id}', [App\Http\Controllers\CrmTravelDeskController::class, 'show'])->whereNumber('id')->name('legacy.crm.travel_desk.show');

Route::get('/crm/travel-plans', [App\Http\Controllers\CrmTravelPlanController::class, 'index'])->name('legacy.crm.travel_plans');
Route::get('/crm/travel-plans/{id}', [App\Http\Controllers\CrmTravelPlanController::class, 'show'])->whereNumber('id')->name('legacy.crm.travel_plans.show');

Route::get('/crm/travel-offers', [App\Http\Controllers\CrmTravelOfferController::class, 'index'])->name('legacy.crm.travel_offers');
Route::get('/crm/travel-offers/create', [App\Http\Controllers\CrmTravelOfferController::class, 'create'])->name('legacy.crm.travel_offers.create');
Route::post('/crm/travel-offers', [App\Http\Controllers\CrmTravelOfferController::class, 'store'])->name('legacy.crm.travel_offers.store');
Route::get('/crm/travel-offers/{id}/edit', [App\Http\Controllers\CrmTravelOfferController::class, 'edit'])->whereNumber('id')->name('legacy.crm.travel_offers.edit');
Route::put('/crm/travel-offers/{id}', [App\Http\Controllers\CrmTravelOfferController::class, 'update'])->whereNumber('id')->name('legacy.crm.travel_offers.update');
Route::get('/crm/travel-offers/{id}/preview', [App\Http\Controllers\CrmTravelOfferController::class, 'preview'])->whereNumber('id')->name('legacy.crm.travel_offers.preview');
Route::get('/crm/travel-offers/{id}/send', [App\Http\Controllers\CrmTravelOfferController::class, 'sendForm'])->whereNumber('id')->name('legacy.crm.travel_offers.send');
Route::post('/crm/travel-offers/{id}/send', [App\Http\Controllers\CrmTravelOfferController::class, 'send'])->whereNumber('id')->name('legacy.crm.travel_offers.send.submit');
Route::get('/crm/integrations', [App\Http\Controllers\CrmIntegrationController::class, 'index'])->name('legacy.crm.integrations');
Route::post('/crm/integrations', [App\Http\Controllers\CrmIntegrationController::class, 'store'])->name('legacy.crm.integrations.store');
Route::get('/crm/bookings/manual/create', [App\Http\Controllers\CrmBookingToolsController::class, 'manualCreate'])->name('legacy.crm.manual_booking_create');
Route::post('/crm/bookings/manual', [App\Http\Controllers\CrmBookingToolsController::class, 'manualStore'])->name('legacy.crm.manual_booking_store');
Route::get('/crm/bookings/{id}/invoice/create', [App\Http\Controllers\CrmBookingToolsController::class, 'invoicePrepare'])->whereNumber('id')->name('legacy.crm.invoice_create');
Route::post('/crm/bookings/{id}/invoice', [App\Http\Controllers\CrmBookingToolsController::class, 'invoiceStore'])->whereNumber('id')->name('legacy.crm.invoice_store');
Route::get('/crm/invoices/{invoice}', [App\Http\Controllers\CrmBookingToolsController::class, 'invoiceShow'])->whereNumber('invoice')->name('legacy.crm.invoice_show');
Route::get('/crm/bookings/{id}', [App\Http\Controllers\CrmController::class, 'bookingDetail'])->whereNumber('id')->name('legacy.crm.booking_detail');
Route::get('/crm/customers', [App\Http\Controllers\CrmController::class, 'customers'])->name('legacy.crm.customers');
Route::get('/crm/customer-detail', [App\Http\Controllers\CrmController::class, 'customerDetail'])->name('legacy.crm.customer_detail');
Route::post('/crm/customers/profile', [App\Http\Controllers\CrmController::class, 'customerProfileUpdate'])->name('legacy.crm.customer_profile_update');
Route::post('/crm/customers/activity', [App\Http\Controllers\CrmController::class, 'customerActivityStore'])->name('legacy.crm.customer_activity_store');
Route::get('/crm/leads', [App\Http\Controllers\CrmController::class, 'leads'])->name('legacy.crm.leads');
Route::get('/crm/leads/create', [App\Http\Controllers\CrmController::class, 'leadCreate'])->name('legacy.crm.lead_create');
Route::post('/crm/leads', [App\Http\Controllers\CrmController::class, 'leadStore'])->name('legacy.crm.lead_store');
Route::get('/crm/leads/import', [App\Http\Controllers\CrmController::class, 'leadImport'])->name('legacy.crm.lead_import');
Route::post('/crm/leads/import', [App\Http\Controllers\CrmController::class, 'leadImportStore'])->name('legacy.crm.lead_import_store');
Route::get('/crm/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('legacy.crm.reports');
Route::get('/crm/reports/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('legacy.crm.reports_export');
Route::get('/crm/payments', [App\Http\Controllers\CrmController::class, 'payments'])->name('legacy.crm.payments');
Route::get('/crm/payments/{id}', [App\Http\Controllers\CrmController::class, 'paymentDetail'])->whereNumber('id')->name('legacy.crm.payment_detail');
Route::post('/crm/payments/{id}/operations', [App\Http\Controllers\CrmController::class, 'paymentOperationsUpdate'])->whereNumber('id')->name('legacy.crm.payment_operations');
Route::post('/crm/payments/{id}/activity', [App\Http\Controllers\CrmController::class, 'paymentActivityStore'])->whereNumber('id')->name('legacy.crm.payment_activity');
Route::get('/crm/refunds', [App\Http\Controllers\CrmController::class, 'refunds'])->name('legacy.crm.refunds');
Route::get('/crm/refunds/create', [App\Http\Controllers\CrmController::class, 'refundCreate'])->name('legacy.crm.refund_create');
Route::post('/crm/refunds', [App\Http\Controllers\CrmController::class, 'refundStore'])->name('legacy.crm.refund_store');
Route::get('/crm/refunds/{id}', [App\Http\Controllers\CrmController::class, 'refundDetail'])->whereNumber('id')->name('legacy.crm.refund_detail');
Route::post('/crm/refunds/{id}/review', [App\Http\Controllers\CrmController::class, 'refundReview'])->whereNumber('id')->name('legacy.crm.refund_review');
Route::post('/crm/refunds/{id}/approve', [App\Http\Controllers\CrmController::class, 'refundApprove'])->whereNumber('id')->name('legacy.crm.refund_approve');
Route::post('/crm/refunds/{id}/process', [App\Http\Controllers\CrmController::class, 'refundProcess'])->whereNumber('id')->name('legacy.crm.refund_process');
Route::post('/crm/refunds/{id}/refund', [App\Http\Controllers\CrmController::class, 'refundComplete'])->whereNumber('id')->name('legacy.crm.refund_complete');
Route::post('/crm/refunds/{id}/close', [App\Http\Controllers\CrmController::class, 'refundClose'])->whereNumber('id')->name('legacy.crm.refund_close');
Route::post('/crm/refunds/{id}/reject', [App\Http\Controllers\CrmController::class, 'refundReject'])->whereNumber('id')->name('legacy.crm.refund_reject');
Route::post('/crm/refunds/{id}/cancel', [App\Http\Controllers\CrmController::class, 'refundCancel'])->whereNumber('id')->name('legacy.crm.refund_cancel');
Route::get('/crm/chargebacks', [App\Http\Controllers\CrmController::class, 'chargebacks'])->name('legacy.crm.chargebacks');
Route::get('/crm/chargebacks/create', [App\Http\Controllers\CrmController::class, 'chargebackCreate'])->name('legacy.crm.chargeback_create');
Route::post('/crm/chargebacks', [App\Http\Controllers\CrmController::class, 'chargebackStore'])->name('legacy.crm.chargeback_store');
Route::get('/crm/chargebacks/{id}', [App\Http\Controllers\CrmController::class, 'chargebackDetail'])->whereNumber('id')->name('legacy.crm.chargeback_detail');
Route::post('/crm/chargebacks/{id}/evidence-required', [App\Http\Controllers\CrmController::class, 'chargebackEvidenceRequired'])->whereNumber('id')->name('legacy.crm.chargeback_evidence_required');
Route::post('/crm/chargebacks/{id}/evidence-ready', [App\Http\Controllers\CrmController::class, 'chargebackEvidenceReady'])->whereNumber('id')->name('legacy.crm.chargeback_evidence_ready');
Route::post('/crm/chargebacks/{id}/submit', [App\Http\Controllers\CrmController::class, 'chargebackSubmit'])->whereNumber('id')->name('legacy.crm.chargeback_submit');
Route::post('/crm/chargebacks/{id}/review', [App\Http\Controllers\CrmController::class, 'chargebackReview'])->whereNumber('id')->name('legacy.crm.chargeback_review');
Route::post('/crm/chargebacks/{id}/resolve', [App\Http\Controllers\CrmController::class, 'chargebackResolve'])->whereNumber('id')->name('legacy.crm.chargeback_resolve');
Route::post('/crm/chargebacks/{id}/close', [App\Http\Controllers\CrmController::class, 'chargebackClose'])->whereNumber('id')->name('legacy.crm.chargeback_close');
Route::get('/crm/notifications', [App\Http\Controllers\CrmController::class, 'notifications'])->name('legacy.crm.notifications');
Route::post('/crm/notifications/read', [App\Http\Controllers\CrmController::class, 'notificationRead'])->name('legacy.crm.notification_read');
Route::post('/crm/notifications/read-all', [App\Http\Controllers\CrmController::class, 'notificationsReadAll'])->name('legacy.crm.notifications_read_all');
Route::get('/crm/leads/{id}', [App\Http\Controllers\CrmController::class, 'leadDetail'])
    ->whereNumber('id')
    ->name('legacy.crm.lead_detail');

Route::post('/crm/leads/{id}/operations', [App\Http\Controllers\CrmController::class, 'leadOperationsUpdate'])
    ->whereNumber('id')
    ->name('legacy.crm.lead_operations');

Route::post('/crm/leads/{id}/activity', [App\Http\Controllers\CrmController::class, 'leadActivityStore'])
    ->whereNumber('id')
    ->name('legacy.crm.lead_activity');

Route::post('/crm/leads/{id}/send-email', [App\Http\Controllers\CrmController::class, 'sendLeadEmail'])
    ->whereNumber('id')
    ->name('legacy.crm.lead_email_send');

Route::post('/crm/bookings/{id}/status', [App\Http\Controllers\CrmController::class, 'bookingStatus'])
    ->whereNumber('id')
    ->name('legacy.crm.booking_status');

Route::get('/crm/bookings/{id}/email-preview', [App\Http\Controllers\CrmController::class, 'bookingEmailPreview'])
    ->whereNumber('id')
    ->name('legacy.crm.booking_email_preview');

Route::post('/crm/bookings/{id}/email-resend', [App\Http\Controllers\CrmController::class, 'resendBookingConfirmation'])
    ->whereNumber('id')
    ->name('legacy.crm.booking_email_resend');
Route::get('/crm/bookings/{id}/template-email/preview', [App\Http\Controllers\CrmController::class, 'bookingTemplateEmailPreview'])->whereNumber('id')->name('legacy.crm.booking_template_email_preview');
Route::post('/crm/bookings/{id}/template-email/send', [App\Http\Controllers\CrmController::class, 'sendBookingTemplateEmail'])->whereNumber('id')->name('legacy.crm.booking_template_email_send');

Route::post('/crm/bookings/{id}/remark', [App\Http\Controllers\CrmController::class, 'bookingRemark'])
    ->whereNumber('id')
    ->name('legacy.crm.booking_remark');

Route::post('/crm/bookings/{id}/assign', [App\Http\Controllers\CrmController::class, 'bookingAssign'])
    ->whereNumber('id')
    ->name('legacy.crm.booking_assign');

Route::post('/crm/bookings/{id}/operations', [App\Http\Controllers\CrmController::class, 'bookingOperations'])
    ->whereNumber('id')
    ->name('legacy.crm.booking_operations');

Route::get('/crm/export/{type}', [App\Http\Controllers\CrmController::class, 'export'])
    ->where('type', 'bookings|customers|payments|leads')
    ->name('legacy.crm.export');


Route::post('/crm/universal/field', [App\Http\Controllers\CrmController::class, 'universalFieldUpdate'])->name('legacy.crm.universal_field_update');
Route::post('/crm/universal/archive', [App\Http\Controllers\CrmController::class, 'universalArchive'])->name('legacy.crm.universal_archive');
Route::get('/crm/trash', [App\Http\Controllers\CrmController::class, 'universalTrash'])->name('legacy.crm.universal_trash');
Route::post('/crm/trash/{archiveId}/restore', [App\Http\Controllers\CrmController::class, 'universalRestore'])
    ->whereNumber('archiveId')
    ->name('legacy.crm.universal_restore');
Route::post('/crm/trash/{archiveId}/permanent-delete', [App\Http\Controllers\CrmController::class, 'universalPermanentDelete'])
    ->whereNumber('archiveId')
    ->name('legacy.crm.universal_permanent_delete');

Route::get('/crm/fare-rules', [App\Http\Controllers\FareRuleController::class, 'index'])->name('legacy.crm.fare_rules');
Route::get('/crm/fare-rules/create', [App\Http\Controllers\FareRuleController::class, 'create'])->name('legacy.crm.fare_rule_create');
Route::post('/crm/fare-rules', [App\Http\Controllers\FareRuleController::class, 'store'])->name('legacy.crm.fare_rule_store');
Route::get('/crm/fare-rules/test', [App\Http\Controllers\FareRuleController::class, 'simulator'])->name('legacy.crm.fare_rule_test');
Route::get('/crm/fare-rules/{id}/edit', [App\Http\Controllers\FareRuleController::class, 'edit'])->whereNumber('id')->name('legacy.crm.fare_rule_edit');
Route::put('/crm/fare-rules/{id}', [App\Http\Controllers\FareRuleController::class, 'update'])->whereNumber('id')->name('legacy.crm.fare_rule_update');
Route::post('/crm/fare-rules/{id}/toggle', [App\Http\Controllers\FareRuleController::class, 'toggle'])->whereNumber('id')->name('legacy.crm.fare_rule_toggle');
Route::post('/crm/fare-rules/{id}/duplicate', [App\Http\Controllers\FareRuleController::class, 'duplicate'])->whereNumber('id')->name('legacy.crm.fare_rule_duplicate');
Route::delete('/crm/fare-rules/{id}', [App\Http\Controllers\FareRuleController::class, 'destroy'])->whereNumber('id')->name('legacy.crm.fare_rule_delete');
Route::get('/crm/fare-rules/{id}/history', [App\Http\Controllers\FareRuleController::class, 'history'])->whereNumber('id')->name('legacy.crm.fare_rule_history');
Route::get('/crm/tasks', [App\Http\Controllers\CrmController::class, 'followUps'])->name('legacy.crm.followups');
Route::get('/crm/api-control', [App\Http\Controllers\CrmController::class, 'apiControl'])->name('legacy.crm.api_control');
Route::get('/crm/tracking', [App\Http\Controllers\TrackingController::class, 'index'])->name('legacy.crm.tracking');
Route::get('/crm/cms', [App\Http\Controllers\CrmController::class, 'cms'])->name('legacy.crm.cms');
Route::get('/crm/pages', [App\Http\Controllers\CrmController::class, 'pagesManager'])->name('legacy.crm.pages_manager');
Route::get('/crm/pages/create', [App\Http\Controllers\CrmController::class, 'pageCreate'])->name('legacy.crm.page_create');
Route::post('/crm/pages', [App\Http\Controllers\CrmController::class, 'pageStore'])->name('legacy.crm.page_store');
Route::get('/crm/pages/{id}/edit', [App\Http\Controllers\CrmController::class, 'pageEdit'])->whereNumber('id')->name('legacy.crm.page_edit');
Route::put('/crm/pages/{id}', [App\Http\Controllers\CrmController::class, 'pageUpdate'])->whereNumber('id')->name('legacy.crm.page_update');
Route::delete('/crm/pages/{id}', [App\Http\Controllers\CrmController::class, 'pageDelete'])->whereNumber('id')->name('legacy.crm.page_delete');
Route::get('/crm/destinations', [App\Http\Controllers\CrmController::class, 'destinationsManager'])->name('legacy.crm.destinations_manager');
Route::get('/crm/destinations/create', [App\Http\Controllers\CrmController::class, 'destinationCreate'])->name('legacy.crm.destination_create');
Route::post('/crm/destinations', [App\Http\Controllers\CrmController::class, 'destinationStore'])->name('legacy.crm.destination_store');
Route::get('/crm/destinations/{id}/edit', [App\Http\Controllers\CrmController::class, 'destinationEdit'])->whereNumber('id')->name('legacy.crm.destination_edit');
Route::put('/crm/destinations/{id}', [App\Http\Controllers\CrmController::class, 'destinationUpdate'])->whereNumber('id')->name('legacy.crm.destination_update');
Route::delete('/crm/destinations/{id}', [App\Http\Controllers\CrmController::class, 'destinationDelete'])->whereNumber('id')->name('legacy.crm.destination_delete');
Route::get('/crm/travel-guides', [App\Http\Controllers\CrmController::class, 'travelGuidesManager'])->name('legacy.crm.travel_guides_manager');
Route::get('/crm/travel-guides/create', [App\Http\Controllers\CrmController::class, 'travelGuideCreate'])->name('legacy.crm.travel_guide_create');
Route::post('/crm/travel-guides', [App\Http\Controllers\CrmController::class, 'travelGuideStore'])->name('legacy.crm.travel_guide_store');
Route::get('/crm/travel-guides/{id}/edit', [App\Http\Controllers\CrmController::class, 'travelGuideEdit'])->whereNumber('id')->name('legacy.crm.travel_guide_edit');
Route::put('/crm/travel-guides/{id}', [App\Http\Controllers\CrmController::class, 'travelGuideUpdate'])->whereNumber('id')->name('legacy.crm.travel_guide_update');
Route::delete('/crm/travel-guides/{id}', [App\Http\Controllers\CrmController::class, 'travelGuideDelete'])->whereNumber('id')->name('legacy.crm.travel_guide_delete');
Route::get('/crm/content-manager', [App\Http\Controllers\CrmController::class, 'contentManager'])->name('legacy.crm.content_manager');
Route::post('/crm/content-manager', [App\Http\Controllers\CrmController::class, 'contentManagerUpdate'])->name('legacy.crm.content_manager_update');
Route::get('/crm/footer-manager', [App\Http\Controllers\CrmController::class, 'footerManager'])->name('legacy.crm.footer_manager');
Route::post('/crm/footer-manager', [App\Http\Controllers\CrmController::class, 'footerManagerUpdate'])->name('legacy.crm.footer_manager_update');
Route::post('/crm/cms/settings', [App\Http\Controllers\CrmController::class, 'cmsSettingsUpdate'])->name('legacy.crm.cms_settings_update');
Route::post('/crm/cms/cards', [App\Http\Controllers\CrmController::class, 'cmsCardStore'])->name('legacy.crm.cms_card_store');
Route::put('/crm/cms/cards/{id}', [App\Http\Controllers\CrmController::class, 'cmsCardUpdate'])->whereNumber('id')->name('legacy.crm.cms_card_update');
Route::delete('/crm/cms/cards/{id}', [App\Http\Controllers\CrmController::class, 'cmsCardDelete'])->whereNumber('id')->name('legacy.crm.cms_card_delete');
Route::get('/crm/communications', [App\Http\Controllers\CrmController::class, 'communications'])->name('legacy.crm.communications');
Route::get('/crm/communications/{id}', [App\Http\Controllers\CrmController::class, 'communicationDetail'])->whereNumber('id')->name('legacy.crm.communication_detail');
Route::post('/crm/communications/{id}/quotation', [App\Http\Controllers\CrmController::class, 'communicationQuotationStore'])->whereNumber('id')->name('legacy.crm.communication_quotation');
Route::get('/crm/communications/{id}/quotation/{quoteId}/print', [App\Http\Controllers\CrmController::class, 'communicationQuotationPrint'])->whereNumber(['id','quoteId'])->name('legacy.crm.communication_quotation_print');
Route::get('/crm/settings', [App\Http\Controllers\CrmController::class, 'settings'])->name('legacy.crm.settings');
Route::post('/crm/settings/contact', [App\Http\Controllers\CrmController::class, 'settingsContactUpdate'])->name('legacy.crm.settings_contact_update');

// Email Templates V2 - template management only; SMTP/booking/fare logic unchanged.
Route::get('/crm/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'index'])->name('legacy.crm.email_templates');
Route::get('/crm/email-templates/create', [App\Http\Controllers\EmailTemplateController::class, 'create'])->name('legacy.crm.email_template_create');
Route::post('/crm/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'store'])->name('legacy.crm.email_template_store');
Route::get('/crm/email-templates/{id}/edit', [App\Http\Controllers\EmailTemplateController::class, 'edit'])->whereNumber('id')->name('legacy.crm.email_template_edit');
Route::put('/crm/email-templates/{id}', [App\Http\Controllers\EmailTemplateController::class, 'update'])->whereNumber('id')->name('legacy.crm.email_template_update');
Route::get('/crm/email-templates/{id}/preview', [App\Http\Controllers\EmailTemplateController::class, 'preview'])->whereNumber('id')->name('legacy.crm.email_template_preview');
Route::post('/crm/email-templates/{id}/toggle', [App\Http\Controllers\EmailTemplateController::class, 'toggle'])->whereNumber('id')->name('legacy.crm.email_template_toggle');
Route::post('/crm/email-templates/{id}/duplicate', [App\Http\Controllers\EmailTemplateController::class, 'duplicate'])->whereNumber('id')->name('legacy.crm.email_template_duplicate');
Route::delete('/crm/email-templates/{id}', [App\Http\Controllers\EmailTemplateController::class, 'destroy'])->whereNumber('id')->name('legacy.crm.email_template_delete');



// CRM Booking Servicing V2
Route::post('/crm/bookings/{id}/passengers', [App\Http\Controllers\BookingServicingController::class, 'passengerSave'])->whereNumber('id')->name('legacy.crm.booking_passenger_add');
Route::post('/crm/bookings/{id}/passengers/{index}', [App\Http\Controllers\BookingServicingController::class, 'passengerSave'])->whereNumber('id')->whereNumber('index')->name('legacy.crm.booking_passenger_update');
Route::delete('/crm/bookings/{id}/passengers/{index}', [App\Http\Controllers\BookingServicingController::class, 'passengerDelete'])->whereNumber('id')->whereNumber('index')->name('legacy.crm.booking_passenger_delete');
Route::post('/crm/bookings/{id}/segments/{trip}', [App\Http\Controllers\BookingServicingController::class, 'segmentSave'])->whereNumber('id')->name('legacy.crm.booking_segment_add');
Route::post('/crm/bookings/{id}/segments/{trip}/{index}', [App\Http\Controllers\BookingServicingController::class, 'segmentSave'])->whereNumber('id')->whereNumber('index')->name('legacy.crm.booking_segment_update');
Route::delete('/crm/bookings/{id}/segments/{trip}/{index}', [App\Http\Controllers\BookingServicingController::class, 'segmentDelete'])->whereNumber('id')->whereNumber('index')->name('legacy.crm.booking_segment_delete');
Route::post('/crm/bookings/{id}/contact-billing', [App\Http\Controllers\BookingServicingController::class, 'contactBilling'])->whereNumber('id')->name('legacy.crm.booking_contact_billing');
Route::post('/crm/bookings/{id}/reprice', [App\Http\Controllers\BookingServicingController::class, 'reprice'])->whereNumber('id')->name('legacy.crm.booking_reprice');
Route::post('/crm/bookings/{id}/card-metadata', [App\Http\Controllers\BookingServicingController::class, 'cardMetadata'])->whereNumber('id')->name('legacy.crm.booking_card_metadata');

// CRM Follow-ups V2
Route::get('/crm/follow-ups-v2',[App\Http\Controllers\FollowUpController::class,'index'])->name('legacy.crm.followups_v2');
Route::post('/crm/follow-ups-v2',[App\Http\Controllers\FollowUpController::class,'store'])->name('legacy.crm.followup_store');
Route::post('/crm/follow-ups-v2/{id}/update',[App\Http\Controllers\FollowUpController::class,'update'])->whereNumber('id')->name('legacy.crm.followup_update');
Route::post('/crm/follow-ups-v2/{id}/complete',[App\Http\Controllers\FollowUpController::class,'complete'])->whereNumber('id')->name('legacy.crm.followup_complete');
Route::post('/crm/follow-ups-v2/{id}/duplicate',[App\Http\Controllers\FollowUpController::class,'duplicate'])->whereNumber('id')->name('legacy.crm.followup_duplicate');
Route::post('/crm/follow-ups-v2/{id}/delete',[App\Http\Controllers\FollowUpController::class,'destroy'])->whereNumber('id')->name('legacy.crm.followup_delete');

// CRM API Control V2
Route::get('/crm/api-control-v2', [App\Http\Controllers\ApiControlController::class, 'index'])->name('legacy.crm.api_control_v2');
Route::post('/crm/api-control-v2/switch', [App\Http\Controllers\ApiControlController::class, 'switchMode'])->name('legacy.crm.api_control_v2_switch');
Route::post('/crm/api-control-v2/test', [App\Http\Controllers\ApiControlController::class, 'testConnection'])->name('legacy.crm.api_control_v2_test');

// CRM API Control V3
Route::get('/crm/api-control-v3',[App\Http\Controllers\ApiControlController::class,'index'])->name('legacy.crm.api_control_v3');
Route::post('/crm/api-control-v3/switch',[App\Http\Controllers\ApiControlController::class,'switchMode'])->name('legacy.crm.api_control_v3_switch');
Route::post('/crm/api-control-v3/test',[App\Http\Controllers\ApiControlController::class,'testConnection'])->name('legacy.crm.api_control_v3_test');
Route::post('/crm/api-control-v3/credentials',[App\Http\Controllers\ApiControlController::class,'saveCredentials'])->name('legacy.crm.api_control_v3_credentials');

// CRM API Control V3.1
Route::get('/crm/api-control-v3-1',[App\Http\Controllers\ApiControlController::class,'index'])->name('legacy.crm.api_control_v3_1');

// CRM Booking Tracking — canonical URL is /crm/tracking.
// Keep the old V2 GET URL only as a compatibility redirect for old bookmarks.
Route::redirect('/crm/tracking-v2', '/crm/tracking', 301)->name('legacy.crm.tracking_v2');
Route::post('/crm/tracking/manual',[App\Http\Controllers\TrackingController::class,'storeManual'])->name('legacy.crm.tracking_v2_manual');


// Canonical clean CRM subdomain routes
Route::domain('crm.fareshunt.com')->get('/login', [App\Http\Controllers\CrmController::class, 'login'])->name('crm.login');
Route::domain('crm.fareshunt.com')->post('/login', [App\Http\Controllers\CrmController::class, 'authenticate'])->middleware('throttle:crm-login')->name('crm.authenticate');
Route::domain('crm.fareshunt.com')->post('/logout', [App\Http\Controllers\CrmController::class, 'logout'])->name('crm.logout');
Route::domain('crm.fareshunt.com')->get('/dashboard', [App\Http\Controllers\CrmController::class, 'dashboard'])->name('crm.dashboard');
Route::domain('crm.fareshunt.com')->get('/bookings', [App\Http\Controllers\CrmController::class, 'bookings'])->name('crm.bookings');
Route::domain('crm.fareshunt.com')->get('/incomplete-bookings', [App\Http\Controllers\CrmController::class, 'incompleteBookings'])->name('crm.incomplete_bookings');
Route::domain('crm.fareshunt.com')->get('/incomplete-bookings/{id}', [App\Http\Controllers\CrmController::class, 'incompleteBookingDetail'])->whereNumber('id')->name('crm.incomplete_booking_detail');
Route::domain('crm.fareshunt.com')->post('/incomplete-bookings/{id}/send-email', [App\Http\Controllers\CrmController::class, 'sendIncompleteBookingEmail'])->whereNumber('id')->name('crm.incomplete_booking_email_send');
Route::domain('crm.fareshunt.com')->delete('/incomplete-bookings/{id}', [App\Http\Controllers\CrmController::class, 'incompleteBookingDelete'])->whereNumber('id')->name('crm.incomplete_booking_delete');
Route::domain('crm.fareshunt.com')->get('/travel-desk', [App\Http\Controllers\CrmTravelDeskController::class, 'index'])->name('crm.travel_desk');
Route::domain('crm.fareshunt.com')->get('/travel-desk/create', [App\Http\Controllers\CrmTravelDeskController::class, 'create'])->name('crm.travel_desk.create');
Route::domain('crm.fareshunt.com')->post('/travel-desk', [App\Http\Controllers\CrmTravelDeskController::class, 'store'])->name('crm.travel_desk.store');
Route::domain('crm.fareshunt.com')->get('/travel-desk/{id}', [App\Http\Controllers\CrmTravelDeskController::class, 'show'])->whereNumber('id')->name('crm.travel_desk.show');

Route::domain('crm.fareshunt.com')->get('/travel-plans', [App\Http\Controllers\CrmTravelPlanController::class, 'index'])->name('crm.travel_plans');
Route::domain('crm.fareshunt.com')->get('/travel-plans/{id}', [App\Http\Controllers\CrmTravelPlanController::class, 'show'])->whereNumber('id')->name('crm.travel_plans.show');

Route::domain('crm.fareshunt.com')->get('/travel-offers', [App\Http\Controllers\CrmTravelOfferController::class, 'index'])->name('crm.travel_offers');
Route::domain('crm.fareshunt.com')->get('/travel-offers/create', [App\Http\Controllers\CrmTravelOfferController::class, 'create'])->name('crm.travel_offers.create');
Route::domain('crm.fareshunt.com')->post('/travel-offers', [App\Http\Controllers\CrmTravelOfferController::class, 'store'])->name('crm.travel_offers.store');
Route::domain('crm.fareshunt.com')->get('/travel-offers/{id}/edit', [App\Http\Controllers\CrmTravelOfferController::class, 'edit'])->whereNumber('id')->name('crm.travel_offers.edit');
Route::domain('crm.fareshunt.com')->put('/travel-offers/{id}', [App\Http\Controllers\CrmTravelOfferController::class, 'update'])->whereNumber('id')->name('crm.travel_offers.update');
Route::domain('crm.fareshunt.com')->get('/travel-offers/{id}/preview', [App\Http\Controllers\CrmTravelOfferController::class, 'preview'])->whereNumber('id')->name('crm.travel_offers.preview');
Route::domain('crm.fareshunt.com')->get('/travel-offers/{id}/send', [App\Http\Controllers\CrmTravelOfferController::class, 'sendForm'])->whereNumber('id')->name('crm.travel_offers.send');
Route::domain('crm.fareshunt.com')->post('/travel-offers/{id}/send', [App\Http\Controllers\CrmTravelOfferController::class, 'send'])->whereNumber('id')->name('crm.travel_offers.send.submit');
Route::domain('crm.fareshunt.com')->get('/integrations', [App\Http\Controllers\CrmIntegrationController::class, 'index'])->name('crm.integrations');
Route::domain('crm.fareshunt.com')->post('/integrations', [App\Http\Controllers\CrmIntegrationController::class, 'store'])->name('crm.integrations.store');
Route::domain('crm.fareshunt.com')->get('/bookings/manual/create', [App\Http\Controllers\CrmBookingToolsController::class, 'manualCreate'])->name('crm.manual_booking_create');
Route::domain('crm.fareshunt.com')->post('/bookings/manual', [App\Http\Controllers\CrmBookingToolsController::class, 'manualStore'])->name('crm.manual_booking_store');
Route::domain('crm.fareshunt.com')->get('/bookings/{id}/invoice/create', [App\Http\Controllers\CrmBookingToolsController::class, 'invoicePrepare'])->whereNumber('id')->name('crm.invoice_create');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/invoice', [App\Http\Controllers\CrmBookingToolsController::class, 'invoiceStore'])->whereNumber('id')->name('crm.invoice_store');
Route::domain('crm.fareshunt.com')->get('/invoices/{invoice}', [App\Http\Controllers\CrmBookingToolsController::class, 'invoiceShow'])->whereNumber('invoice')->name('crm.invoice_show');
Route::domain('crm.fareshunt.com')->get('/bookings/{id}', [App\Http\Controllers\CrmController::class, 'bookingDetail'])->whereNumber('id')->name('crm.booking_detail');
Route::domain('crm.fareshunt.com')->get('/customers', [App\Http\Controllers\CrmController::class, 'customers'])->name('crm.customers');
Route::domain('crm.fareshunt.com')->get('/customer-detail', [App\Http\Controllers\CrmController::class, 'customerDetail'])->name('crm.customer_detail');
Route::domain('crm.fareshunt.com')->post('/customers/profile', [App\Http\Controllers\CrmController::class, 'customerProfileUpdate'])->name('crm.customer_profile_update');
Route::domain('crm.fareshunt.com')->post('/customers/activity', [App\Http\Controllers\CrmController::class, 'customerActivityStore'])->name('crm.customer_activity_store');
Route::domain('crm.fareshunt.com')->get('/leads', [App\Http\Controllers\CrmController::class, 'leads'])->name('crm.leads');
Route::domain('crm.fareshunt.com')->get('/leads/create', [App\Http\Controllers\CrmController::class, 'leadCreate'])->name('crm.lead_create');
Route::domain('crm.fareshunt.com')->post('/leads', [App\Http\Controllers\CrmController::class, 'leadStore'])->name('crm.lead_store');
Route::domain('crm.fareshunt.com')->get('/leads/import', [App\Http\Controllers\CrmController::class, 'leadImport'])->name('crm.lead_import');
Route::domain('crm.fareshunt.com')->post('/leads/import', [App\Http\Controllers\CrmController::class, 'leadImportStore'])->name('crm.lead_import_store');
Route::domain('crm.fareshunt.com')->get('/reports', [App\Http\Controllers\ReportsController::class, 'index'])->name('crm.reports');
Route::domain('crm.fareshunt.com')->get('/reports/export', [App\Http\Controllers\ReportsController::class, 'export'])->name('crm.reports_export');
Route::domain('crm.fareshunt.com')->get('/profit-report', [App\Http\Controllers\ProfitReportController::class, 'index'])->name('crm.profit_report');
Route::domain('crm.fareshunt.com')->get('/profit-report/export', [App\Http\Controllers\ProfitReportController::class, 'export'])->name('crm.profit_report_export');
Route::domain('crm.fareshunt.com')->post('/profit-report/{id}', [App\Http\Controllers\ProfitReportController::class, 'update'])->whereNumber('id')->name('crm.profit_report_update');
Route::domain('crm.fareshunt.com')->get('/payments', [App\Http\Controllers\CrmController::class, 'payments'])->name('crm.payments');
Route::domain('crm.fareshunt.com')->get('/payments/{id}', [App\Http\Controllers\CrmController::class, 'paymentDetail'])->whereNumber('id')->name('crm.payment_detail');
Route::domain('crm.fareshunt.com')->post('/payments/{id}/operations', [App\Http\Controllers\CrmController::class, 'paymentOperationsUpdate'])->whereNumber('id')->name('crm.payment_operations');
Route::domain('crm.fareshunt.com')->post('/payments/{id}/activity', [App\Http\Controllers\CrmController::class, 'paymentActivityStore'])->whereNumber('id')->name('crm.payment_activity');
Route::domain('crm.fareshunt.com')->get('/refunds', [App\Http\Controllers\CrmController::class, 'refunds'])->name('crm.refunds');
Route::domain('crm.fareshunt.com')->get('/refunds/create', [App\Http\Controllers\CrmController::class, 'refundCreate'])->name('crm.refund_create');
Route::domain('crm.fareshunt.com')->post('/refunds', [App\Http\Controllers\CrmController::class, 'refundStore'])->name('crm.refund_store');
Route::domain('crm.fareshunt.com')->get('/refunds/{id}', [App\Http\Controllers\CrmController::class, 'refundDetail'])->whereNumber('id')->name('crm.refund_detail');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/review', [App\Http\Controllers\CrmController::class, 'refundReview'])->whereNumber('id')->name('crm.refund_review');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/approve', [App\Http\Controllers\CrmController::class, 'refundApprove'])->whereNumber('id')->name('crm.refund_approve');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/process', [App\Http\Controllers\CrmController::class, 'refundProcess'])->whereNumber('id')->name('crm.refund_process');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/refund', [App\Http\Controllers\CrmController::class, 'refundComplete'])->whereNumber('id')->name('crm.refund_complete');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/close', [App\Http\Controllers\CrmController::class, 'refundClose'])->whereNumber('id')->name('crm.refund_close');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/reject', [App\Http\Controllers\CrmController::class, 'refundReject'])->whereNumber('id')->name('crm.refund_reject');
Route::domain('crm.fareshunt.com')->post('/refunds/{id}/cancel', [App\Http\Controllers\CrmController::class, 'refundCancel'])->whereNumber('id')->name('crm.refund_cancel');
Route::domain('crm.fareshunt.com')->get('/chargebacks', [App\Http\Controllers\CrmController::class, 'chargebacks'])->name('crm.chargebacks');
Route::domain('crm.fareshunt.com')->get('/chargebacks/create', [App\Http\Controllers\CrmController::class, 'chargebackCreate'])->name('crm.chargeback_create');
Route::domain('crm.fareshunt.com')->post('/chargebacks', [App\Http\Controllers\CrmController::class, 'chargebackStore'])->name('crm.chargeback_store');
Route::domain('crm.fareshunt.com')->get('/chargebacks/{id}', [App\Http\Controllers\CrmController::class, 'chargebackDetail'])->whereNumber('id')->name('crm.chargeback_detail');
Route::domain('crm.fareshunt.com')->group(function () {
    Route::post('/chargebacks/{id}/evidence-required', [App\Http\Controllers\CrmController::class, 'chargebackEvidenceRequired'])->whereNumber('id')->name('crm.chargeback_evidence_required');
    Route::post('/chargebacks/{id}/evidence-ready', [App\Http\Controllers\CrmController::class, 'chargebackEvidenceReady'])->whereNumber('id')->name('crm.chargeback_evidence_ready');
    Route::post('/chargebacks/{id}/submit', [App\Http\Controllers\CrmController::class, 'chargebackSubmit'])->whereNumber('id')->name('crm.chargeback_submit');
    Route::post('/chargebacks/{id}/review', [App\Http\Controllers\CrmController::class, 'chargebackReview'])->whereNumber('id')->name('crm.chargeback_review');
    Route::post('/chargebacks/{id}/resolve', [App\Http\Controllers\CrmController::class, 'chargebackResolve'])->whereNumber('id')->name('crm.chargeback_resolve');
    Route::post('/chargebacks/{id}/close', [App\Http\Controllers\CrmController::class, 'chargebackClose'])->whereNumber('id')->name('crm.chargeback_close');
});
Route::domain('crm.fareshunt.com')->get('/notifications', [App\Http\Controllers\CrmController::class, 'notifications'])->name('crm.notifications');
Route::domain('crm.fareshunt.com')->post('/notifications/read', [App\Http\Controllers\CrmController::class, 'notificationRead'])->name('crm.notification_read');
Route::domain('crm.fareshunt.com')->post('/notifications/read-all', [App\Http\Controllers\CrmController::class, 'notificationsReadAll'])->name('crm.notifications_read_all');
Route::domain('crm.fareshunt.com')->get('/leads/{id}', [App\Http\Controllers\CrmController::class, 'leadDetail'])
    ->whereNumber('id')
    ->name('crm.lead_detail');

Route::domain('crm.fareshunt.com')->post('/leads/{id}/operations', [App\Http\Controllers\CrmController::class, 'leadOperationsUpdate'])
    ->whereNumber('id')
    ->name('crm.lead_operations');

Route::domain('crm.fareshunt.com')->post('/leads/{id}/activity', [App\Http\Controllers\CrmController::class, 'leadActivityStore'])
    ->whereNumber('id')
    ->name('crm.lead_activity');

Route::domain('crm.fareshunt.com')->post('/leads/{id}/send-email', [App\Http\Controllers\CrmController::class, 'sendLeadEmail'])
    ->whereNumber('id')
    ->name('crm.lead_email_send');

Route::domain('crm.fareshunt.com')->post('/bookings/{id}/status', [App\Http\Controllers\CrmController::class, 'bookingStatus'])
    ->whereNumber('id')
    ->name('crm.booking_status');

Route::domain('crm.fareshunt.com')->get('/bookings/{id}/email-preview', [App\Http\Controllers\CrmController::class, 'bookingEmailPreview'])
    ->whereNumber('id')
    ->name('crm.booking_email_preview');

Route::domain('crm.fareshunt.com')->post('/bookings/{id}/email-resend', [App\Http\Controllers\CrmController::class, 'resendBookingConfirmation'])
    ->whereNumber('id')
    ->name('crm.booking_email_resend');
Route::domain('crm.fareshunt.com')->get('/bookings/{id}/template-email/preview', [App\Http\Controllers\CrmController::class, 'bookingTemplateEmailPreview'])->whereNumber('id')->name('crm.booking_template_email_preview');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/template-email/send', [App\Http\Controllers\CrmController::class, 'sendBookingTemplateEmail'])->whereNumber('id')->name('crm.booking_template_email_send');

Route::domain('crm.fareshunt.com')->post('/bookings/{id}/remark', [App\Http\Controllers\CrmController::class, 'bookingRemark'])
    ->whereNumber('id')
    ->name('crm.booking_remark');

Route::domain('crm.fareshunt.com')->post('/bookings/{id}/assign', [App\Http\Controllers\CrmController::class, 'bookingAssign'])
    ->whereNumber('id')
    ->name('crm.booking_assign');

Route::domain('crm.fareshunt.com')->post('/bookings/{id}/operations', [App\Http\Controllers\CrmController::class, 'bookingOperations'])
    ->whereNumber('id')
    ->name('crm.booking_operations');

Route::domain('crm.fareshunt.com')->get('/export/{type}', [App\Http\Controllers\CrmController::class, 'export'])
    ->where('type', 'bookings|customers|payments|leads')
    ->name('crm.export');


Route::domain('crm.fareshunt.com')->post('/universal/field', [App\Http\Controllers\CrmController::class, 'universalFieldUpdate'])->name('crm.universal_field_update');
Route::domain('crm.fareshunt.com')->post('/universal/archive', [App\Http\Controllers\CrmController::class, 'universalArchive'])->name('crm.universal_archive');
Route::domain('crm.fareshunt.com')->get('/trash', [App\Http\Controllers\CrmController::class, 'universalTrash'])->name('crm.universal_trash');
Route::domain('crm.fareshunt.com')->post('/trash/{archiveId}/restore', [App\Http\Controllers\CrmController::class, 'universalRestore'])
    ->whereNumber('archiveId')
    ->name('crm.universal_restore');
Route::domain('crm.fareshunt.com')->post('/trash/{archiveId}/permanent-delete', [App\Http\Controllers\CrmController::class, 'universalPermanentDelete'])
    ->whereNumber('archiveId')
    ->name('crm.universal_permanent_delete');

Route::domain('crm.fareshunt.com')->get('/travel-master-data', [App\Http\Controllers\CrmMasterDataController::class, 'index'])->name('crm.master_data');
Route::domain('crm.fareshunt.com')->get('/travel-master-data/create', [App\Http\Controllers\CrmMasterDataController::class, 'create'])->name('crm.master_data_create');
Route::domain('crm.fareshunt.com')->get('/travel-master-data/countries/{countryId}/cities', [App\Http\Controllers\CrmMasterDataController::class, 'citiesByCountry'])->whereNumber('countryId')->name('crm.master_data_cities_by_country');
Route::domain('crm.fareshunt.com')->post('/travel-master-data/countries', [App\Http\Controllers\CrmMasterDataController::class, 'storeCountry'])->name('crm.master_data_country_store');
Route::domain('crm.fareshunt.com')->post('/travel-master-data/cities', [App\Http\Controllers\CrmMasterDataController::class, 'storeCity'])->name('crm.master_data_city_store');
Route::domain('crm.fareshunt.com')->post('/travel-master-data/airports', [App\Http\Controllers\CrmMasterDataController::class, 'storeAirport'])->name('crm.master_data_airport_store');
Route::domain('crm.fareshunt.com')->post('/travel-master-data/airlines', [App\Http\Controllers\CrmMasterDataController::class, 'storeAirline'])->name('crm.master_data_airline_store');
Route::domain('crm.fareshunt.com')->post('/travel-master-data/booking-addons', [App\Http\Controllers\CrmMasterDataController::class, 'storeAddon'])->name('crm.master_data_addon_store');
Route::domain('crm.fareshunt.com')->get('/travel-master-data/{section}/{id}/edit', [App\Http\Controllers\CrmMasterDataController::class, 'edit'])->whereIn('section', ['countries','cities','airports','airlines','booking_addons'])->whereNumber('id')->name('crm.master_data_edit');
Route::domain('crm.fareshunt.com')->put('/travel-master-data/{section}/{id}', [App\Http\Controllers\CrmMasterDataController::class, 'update'])->whereIn('section', ['countries','cities','airports','airlines','booking_addons'])->whereNumber('id')->name('crm.master_data_update');
Route::domain('crm.fareshunt.com')->delete('/travel-master-data/{section}/{id}', [App\Http\Controllers\CrmMasterDataController::class, 'destroy'])->whereIn('section', ['countries','cities','airports','airlines','booking_addons'])->whereNumber('id')->name('crm.master_data_delete');

// LEGACY_FARE_RULE_REVIEW_ROUTES_OPT_IN
if (
    app()->environment('staging') ||
    (
        app()->environment('production') &&
        config('fare_rules.legacy_publish_enabled', false) === true
    )
) {
    Route::domain('crm.fareshunt.com')
        ->middleware('web')
        ->post('/fare-rule-changes/{requestId}/approve',
            [\App\Http\Controllers\LegacyFareRuleReviewController::class, 'approve'])
        ->whereNumber('requestId')
        ->name('crm.fare_rule_change_approve');

    Route::domain('crm.fareshunt.com')
        ->middleware('web')
        ->post('/fare-rule-changes/{requestId}/reject',
            [\App\Http\Controllers\LegacyFareRuleReviewController::class, 'reject'])
        ->whereNumber('requestId')
        ->name('crm.fare_rule_change_reject');
}

Route::domain('crm.fareshunt.com')->get('/fare-rules', [App\Http\Controllers\FareRuleController::class, 'index'])->name('crm.fare_rules');
Route::domain('crm.fareshunt.com')->get('/fare-rules/create', [App\Http\Controllers\FareRuleController::class, 'create'])->name('crm.fare_rule_create');
Route::domain('crm.fareshunt.com')->post('/fare-rules', [App\Http\Controllers\FareRuleController::class, 'store'])->name('crm.fare_rule_store');
Route::domain('crm.fareshunt.com')->get('/fare-rules/test', [App\Http\Controllers\FareRuleController::class, 'simulator'])->name('crm.fare_rule_test');
Route::domain('crm.fareshunt.com')->get('/fare-rules/{id}/edit', [App\Http\Controllers\FareRuleController::class, 'edit'])->whereNumber('id')->name('crm.fare_rule_edit');
Route::domain('crm.fareshunt.com')->put('/fare-rules/{id}', [App\Http\Controllers\FareRuleController::class, 'update'])->whereNumber('id')->name('crm.fare_rule_update');
Route::domain('crm.fareshunt.com')->post('/fare-rules/{id}/toggle', [App\Http\Controllers\FareRuleController::class, 'toggle'])->whereNumber('id')->name('crm.fare_rule_toggle');
Route::domain('crm.fareshunt.com')->post('/fare-rules/{id}/duplicate', [App\Http\Controllers\FareRuleController::class, 'duplicate'])->whereNumber('id')->name('crm.fare_rule_duplicate');
Route::domain('crm.fareshunt.com')->delete('/fare-rules/{id}', [App\Http\Controllers\FareRuleController::class, 'destroy'])->whereNumber('id')->name('crm.fare_rule_delete');
Route::domain('crm.fareshunt.com')->get('/fare-rules/{id}/history', [App\Http\Controllers\FareRuleController::class, 'history'])->whereNumber('id')->name('crm.fare_rule_history');
Route::domain('crm.fareshunt.com')->get('/tasks', [App\Http\Controllers\CrmController::class, 'followUps'])->name('crm.followups');
Route::domain('crm.fareshunt.com')->get('/api-control-legacy', [App\Http\Controllers\CrmController::class, 'apiControl'])->name('crm.api_control');
Route::domain('crm.fareshunt.com')->get('/tracking', [App\Http\Controllers\TrackingController::class, 'index'])->name('crm.tracking');
Route::domain('crm.fareshunt.com')->get('/cms', [App\Http\Controllers\CrmController::class, 'cms'])->name('crm.cms');
Route::domain('crm.fareshunt.com')->get('/pages', [App\Http\Controllers\CrmController::class, 'pagesManager'])->name('crm.pages_manager');
Route::domain('crm.fareshunt.com')->get('/pages/create', [App\Http\Controllers\CrmController::class, 'pageCreate'])->name('crm.page_create');
Route::domain('crm.fareshunt.com')->post('/pages', [App\Http\Controllers\CrmController::class, 'pageStore'])->name('crm.page_store');
Route::domain('crm.fareshunt.com')->get('/pages/{id}/edit', [App\Http\Controllers\CrmController::class, 'pageEdit'])->whereNumber('id')->name('crm.page_edit');
Route::domain('crm.fareshunt.com')->put('/pages/{id}', [App\Http\Controllers\CrmController::class, 'pageUpdate'])->whereNumber('id')->name('crm.page_update');
Route::domain('crm.fareshunt.com')->delete('/pages/{id}', [App\Http\Controllers\CrmController::class, 'pageDelete'])->whereNumber('id')->name('crm.page_delete');
Route::domain('crm.fareshunt.com')->get('/destinations/create', [App\Http\Controllers\CrmController::class, 'destinationCreate'])->name('crm.destination_create');
Route::domain('crm.fareshunt.com')->post('/destinations', [App\Http\Controllers\CrmController::class, 'destinationStore'])->name('crm.destination_store');
Route::domain('crm.fareshunt.com')->get('/destinations/{id}/edit', [App\Http\Controllers\CrmController::class, 'destinationEdit'])->whereNumber('id')->name('crm.destination_edit');
Route::domain('crm.fareshunt.com')->put('/destinations/{id}', [App\Http\Controllers\CrmController::class, 'destinationUpdate'])->whereNumber('id')->name('crm.destination_update');
Route::domain('crm.fareshunt.com')->delete('/destinations/{id}', [App\Http\Controllers\CrmController::class, 'destinationDelete'])->whereNumber('id')->name('crm.destination_delete');
Route::domain('crm.fareshunt.com')->get('/travel-guides', [App\Http\Controllers\CrmController::class, 'travelGuidesManager'])->name('crm.travel_guides_manager');
Route::domain('crm.fareshunt.com')->get('/travel-guides/create', [App\Http\Controllers\CrmController::class, 'travelGuideCreate'])->name('crm.travel_guide_create');
Route::domain('crm.fareshunt.com')->post('/travel-guides', [App\Http\Controllers\CrmController::class, 'travelGuideStore'])->name('crm.travel_guide_store');
Route::domain('crm.fareshunt.com')->get('/travel-guides/{id}/edit', [App\Http\Controllers\CrmController::class, 'travelGuideEdit'])->whereNumber('id')->name('crm.travel_guide_edit');
Route::domain('crm.fareshunt.com')->put('/travel-guides/{id}', [App\Http\Controllers\CrmController::class, 'travelGuideUpdate'])->whereNumber('id')->name('crm.travel_guide_update');
Route::domain('crm.fareshunt.com')->delete('/travel-guides/{id}', [App\Http\Controllers\CrmController::class, 'travelGuideDelete'])->whereNumber('id')->name('crm.travel_guide_delete');
Route::domain('crm.fareshunt.com')->get('/content-manager', [App\Http\Controllers\CrmController::class, 'contentManager'])->name('crm.content_manager');
Route::domain('crm.fareshunt.com')->post('/content-manager', [App\Http\Controllers\CrmController::class, 'contentManagerUpdate'])->name('crm.content_manager_update');
Route::domain('crm.fareshunt.com')->get('/footer-manager', [App\Http\Controllers\CrmController::class, 'footerManager'])->name('crm.footer_manager');
Route::domain('crm.fareshunt.com')->post('/footer-manager', [App\Http\Controllers\CrmController::class, 'footerManagerUpdate'])->name('crm.footer_manager_update');
Route::domain('crm.fareshunt.com')->post('/cms/settings', [App\Http\Controllers\CrmController::class, 'cmsSettingsUpdate'])->name('crm.cms_settings_update');
Route::domain('crm.fareshunt.com')->post('/cms/cards', [App\Http\Controllers\CrmController::class, 'cmsCardStore'])->name('crm.cms_card_store');
Route::domain('crm.fareshunt.com')->put('/cms/cards/{id}', [App\Http\Controllers\CrmController::class, 'cmsCardUpdate'])->whereNumber('id')->name('crm.cms_card_update');
Route::domain('crm.fareshunt.com')->delete('/cms/cards/{id}', [App\Http\Controllers\CrmController::class, 'cmsCardDelete'])->whereNumber('id')->name('crm.cms_card_delete');
Route::domain('crm.fareshunt.com')->get('/communications', [App\Http\Controllers\CrmController::class, 'communications'])->name('crm.communications');
Route::domain('crm.fareshunt.com')->get('/communications/{id}', [App\Http\Controllers\CrmController::class, 'communicationDetail'])->whereNumber('id')->name('crm.communication_detail');
Route::domain('crm.fareshunt.com')->post('/communications/{id}/quotation', [App\Http\Controllers\CrmController::class, 'communicationQuotationStore'])->whereNumber('id')->name('crm.communication_quotation');
Route::domain('crm.fareshunt.com')->get('/communications/{id}/quotation/{quoteId}/print', [App\Http\Controllers\CrmController::class, 'communicationQuotationPrint'])->whereNumber(['id','quoteId'])->name('crm.communication_quotation_print');
Route::domain('crm.fareshunt.com')->get('/settings', [App\Http\Controllers\CrmController::class, 'settings'])->name('crm.settings');
Route::domain('crm.fareshunt.com')->post('/settings/contact', [App\Http\Controllers\CrmController::class, 'settingsContactUpdate'])->name('crm.settings_contact_update');

// Email Templates V2 - template management only; SMTP/booking/fare logic unchanged.
Route::domain('crm.fareshunt.com')->get('/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'index'])->name('crm.email_templates');
Route::domain('crm.fareshunt.com')->get('/email-templates/create', [App\Http\Controllers\EmailTemplateController::class, 'create'])->name('crm.email_template_create');
Route::domain('crm.fareshunt.com')->post('/email-templates', [App\Http\Controllers\EmailTemplateController::class, 'store'])->name('crm.email_template_store');
Route::domain('crm.fareshunt.com')->get('/email-templates/{id}/edit', [App\Http\Controllers\EmailTemplateController::class, 'edit'])->whereNumber('id')->name('crm.email_template_edit');
Route::domain('crm.fareshunt.com')->put('/email-templates/{id}', [App\Http\Controllers\EmailTemplateController::class, 'update'])->whereNumber('id')->name('crm.email_template_update');
Route::domain('crm.fareshunt.com')->get('/email-templates/{id}/preview', [App\Http\Controllers\EmailTemplateController::class, 'preview'])->whereNumber('id')->name('crm.email_template_preview');
Route::domain('crm.fareshunt.com')->post('/email-templates/{id}/toggle', [App\Http\Controllers\EmailTemplateController::class, 'toggle'])->whereNumber('id')->name('crm.email_template_toggle');
Route::domain('crm.fareshunt.com')->post('/email-templates/{id}/duplicate', [App\Http\Controllers\EmailTemplateController::class, 'duplicate'])->whereNumber('id')->name('crm.email_template_duplicate');
Route::domain('crm.fareshunt.com')->delete('/email-templates/{id}', [App\Http\Controllers\EmailTemplateController::class, 'destroy'])->whereNumber('id')->name('crm.email_template_delete');



// CRM Booking Servicing V2
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/passengers', [App\Http\Controllers\BookingServicingController::class, 'passengerSave'])->whereNumber('id')->name('crm.booking_passenger_add');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/passengers/{index}', [App\Http\Controllers\BookingServicingController::class, 'passengerSave'])->whereNumber('id')->whereNumber('index')->name('crm.booking_passenger_update');
Route::domain('crm.fareshunt.com')->delete('/bookings/{id}/passengers/{index}', [App\Http\Controllers\BookingServicingController::class, 'passengerDelete'])->whereNumber('id')->whereNumber('index')->name('crm.booking_passenger_delete');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/segments/{trip}', [App\Http\Controllers\BookingServicingController::class, 'segmentSave'])->whereNumber('id')->name('crm.booking_segment_add');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/segments/{trip}/{index}', [App\Http\Controllers\BookingServicingController::class, 'segmentSave'])->whereNumber('id')->whereNumber('index')->name('crm.booking_segment_update');
Route::domain('crm.fareshunt.com')->delete('/bookings/{id}/segments/{trip}/{index}', [App\Http\Controllers\BookingServicingController::class, 'segmentDelete'])->whereNumber('id')->whereNumber('index')->name('crm.booking_segment_delete');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/contact-billing', [App\Http\Controllers\BookingServicingController::class, 'contactBilling'])->whereNumber('id')->name('crm.booking_contact_billing');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/reprice', [App\Http\Controllers\BookingServicingController::class, 'reprice'])->whereNumber('id')->name('crm.booking_reprice');
Route::domain('crm.fareshunt.com')->post('/bookings/{id}/card-metadata', [App\Http\Controllers\BookingServicingController::class, 'cardMetadata'])->whereNumber('id')->name('crm.booking_card_metadata');

// CRM Follow-ups V2
Route::domain('crm.fareshunt.com')->get('/follow-ups',[App\Http\Controllers\FollowUpController::class,'index'])->name('crm.followups_v2');
Route::domain('crm.fareshunt.com')->post('/follow-ups',[App\Http\Controllers\FollowUpController::class,'store'])->name('crm.followup_store');
Route::domain('crm.fareshunt.com')->post('/follow-ups/{id}/update',[App\Http\Controllers\FollowUpController::class,'update'])->whereNumber('id')->name('crm.followup_update');
Route::domain('crm.fareshunt.com')->post('/follow-ups/{id}/complete',[App\Http\Controllers\FollowUpController::class,'complete'])->whereNumber('id')->name('crm.followup_complete');
Route::domain('crm.fareshunt.com')->post('/follow-ups/{id}/duplicate',[App\Http\Controllers\FollowUpController::class,'duplicate'])->whereNumber('id')->name('crm.followup_duplicate');
Route::domain('crm.fareshunt.com')->post('/follow-ups/{id}/delete',[App\Http\Controllers\FollowUpController::class,'destroy'])->whereNumber('id')->name('crm.followup_delete');

// CRM API Control V2
Route::domain('crm.fareshunt.com')->get('/api-control-v2', [App\Http\Controllers\ApiControlController::class, 'index'])->name('crm.api_control_v2');
Route::domain('crm.fareshunt.com')->post('/api-control-v2/switch', [App\Http\Controllers\ApiControlController::class, 'switchMode'])->name('crm.api_control_v2_switch');
Route::domain('crm.fareshunt.com')->post('/api-control-v2/test', [App\Http\Controllers\ApiControlController::class, 'testConnection'])->name('crm.api_control_v2_test');

// CRM API Control V3
Route::domain('crm.fareshunt.com')->get('/api-control',[App\Http\Controllers\ApiControlController::class,'index'])->name('crm.api_control_v3');
Route::domain('crm.fareshunt.com')->post('/api-control/switch',[App\Http\Controllers\ApiControlController::class,'switchMode'])->name('crm.api_control_v3_switch');
Route::domain('crm.fareshunt.com')->post('/api-control/test',[App\Http\Controllers\ApiControlController::class,'testConnection'])->name('crm.api_control_v3_test');
Route::domain('crm.fareshunt.com')->post('/api-control/credentials',[App\Http\Controllers\ApiControlController::class,'saveCredentials'])->name('crm.api_control_v3_credentials');

// CRM API Control V3.1
Route::domain('crm.fareshunt.com')->get('/api-control',[App\Http\Controllers\ApiControlController::class,'index'])->name('crm.api_control_v3_1');

// CRM Booking Tracking — canonical URL is /crm/tracking.
// Keep the old V2 GET URL only as a compatibility redirect for old bookmarks.
Route::domain('crm.fareshunt.com')->get('/tracking-v2', function () { return redirect('/tracking', 301); })->name('crm.tracking_v2');
Route::domain('crm.fareshunt.com')->post('/tracking/manual',[App\Http\Controllers\TrackingController::class,'storeManual'])->name('crm.tracking_v2_manual');


// CRM Growth & Security V1
require __DIR__.'/crm_growth_security_v1.php';


// CRM User Management & RBAC V1
require __DIR__.'/crm_access_control.php';

// Live Pax Monitor - isolated CRM routes.
require __DIR__.'/live_pax_crm.php';
// Meta / Partner Performance Monitor - isolated CRM routes.
require __DIR__.'/meta_monitor_crm.php';
