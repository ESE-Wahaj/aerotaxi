<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CoverageController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/coverage', [CoverageController::class, 'index'])->name('coverage');
Route::get('/help', [HelpController::class, 'index'])->name('help');
Route::get('/your-ride', [BookingController::class, 'checkPrices'])->name('booking.check-prices');
Route::get('/transfer-details', [BookingController::class, 'transferDetails'])->name('booking.transfer-details');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::post('/payment/create-intent', [BookingController::class, 'createPaymentIntent'])->name('booking.create-intent');
Route::get('/booking/confirmation/{reference}', [BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/lookup', [BookingController::class, 'lookup'])->name('booking.lookup');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/terms-of-service', [LegalController::class, 'termsOfService'])->name('legal.terms');
Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('legal.privacy-policy');
Route::get('/privacy-statement', [LegalController::class, 'privacyStatement'])->name('legal.privacy-statement');
Route::get('/cookie-policy', [LegalController::class, 'cookiePolicy'])->name('legal.cookie-policy');

Route::get('/api/flight/validate', function (\Illuminate\Http\Request $request) {
    $flightNumber = strtoupper(trim($request->query('flight_number', '')));
    $date = $request->query('date', date('Y-m-d'));

    if (!$flightNumber) {
        return response()->json(['found' => false]);
    }

    // Extract airline code and flight number (e.g. BA1234 -> BA + 1234)
    // Match airline code (2 chars, may include digit like W6, U2) + flight number
    preg_match('/^([A-Z\d]{2})(\d{1,5})$/', $flightNumber, $matches);
    if (!$matches) {
        preg_match('/^([A-Z]{3})(\d{1,5})$/', $flightNumber, $matches);
    }
    if (!$matches) {
        return response()->json(['found' => false]);
    }

    $airlineCode = $matches[1];
    $flightNum = $matches[2];

    // Try AviationStack free API
    $apiKey = config('services.aviationstack.key');
    if ($apiKey) {
        try {
            $url = "http://api.aviationstack.com/v1/flights?access_key={$apiKey}&flight_iata={$flightNumber}&date={$date}";
            $response = @file_get_contents($url);
            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data['data'])) {
                    $flight = $data['data'][0];
                    $dep = $flight['departure']['airport'] ?? 'Unknown';
                    $arr = $flight['arrival']['airport'] ?? 'Unknown';
                    $airline = $flight['airline']['name'] ?? $airlineCode;
                    $status = $flight['flight_status'] ?? '';
                    $depTime = $flight['departure']['scheduled'] ?? '';
                    $timeStr = $depTime ? date('H:i', strtotime($depTime)) : '';
                    $info = "{$airline} {$flightNumber} · {$dep} → {$arr}";
                    if ($timeStr) $info .= " · Departs {$timeStr}";
                    if ($status) $info .= " · Status: " . ucfirst($status);
                    return response()->json(['found' => true, 'info' => $info]);
                }
                return response()->json(['found' => false]);
            }
        } catch (\Exception $e) {
            // Fall through to fallback
        }
    }

    // Fallback: validate flight format using known UK airline IATA codes
    $knownAirlines = [
        'BA' => 'British Airways', 'EZY' => 'easyJet', 'U2' => 'easyJet',
        'FR' => 'Ryanair', 'VS' => 'Virgin Atlantic', 'LS' => 'Jet2',
        'MT' => 'Thomas Cook', 'TOM' => 'TUI', 'BY' => 'TUI Airways',
        'BE' => 'Flybe', 'LM' => 'Loganair', 'EI' => 'Aer Lingus',
        'LH' => 'Lufthansa', 'AF' => 'Air France', 'KL' => 'KLM',
        'AA' => 'American Airlines', 'UA' => 'United Airlines', 'DL' => 'Delta',
        'EK' => 'Emirates', 'QR' => 'Qatar Airways', 'TK' => 'Turkish Airlines',
        'SQ' => 'Singapore Airlines', 'CX' => 'Cathay Pacific', 'NH' => 'ANA',
        'IB' => 'Iberia', 'AZ' => 'ITA Airways', 'SK' => 'SAS',
        'AY' => 'Finnair', 'TP' => 'TAP Portugal', 'LX' => 'Swiss',
        'OS' => 'Austrian', 'SN' => 'Brussels Airlines', 'W6' => 'Wizz Air',
        'W9' => 'Wizz Air UK', 'ZT' => 'Titan Airways', 'RK' => 'Ryanair UK',
    ];

    if (isset($knownAirlines[$airlineCode])) {
        $airline = $knownAirlines[$airlineCode];
        return response()->json([
            'found' => true,
            'info' => "{$airline} flight {$flightNumber} · Date: {$date}"
        ]);
    }

    return response()->json(['found' => false]);
})->name('api.flight.validate');

Route::get('/api/airports/search', function (\Illuminate\Http\Request $request) {
    $q = $request->query('q', '');
    $airports = \App\Models\Airport::query()
        ->when($q, function ($query, $q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%")
                  ->orWhere('city', 'like', "%{$q}%");
        })
        ->orderBy('sort_order')
        ->limit(10)
        ->get(['id', 'code', 'name', 'city', 'image']);
    return response()->json($airports);
})->name('api.airports.search');

// Test email route (remove in production)
Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('Test email from AeroTAXI at ' . now(), function ($m) {
            $m->to('supportaerotaxi@gmail.com')->subject('AeroTAXI Email Test');
        });
        return 'Email sent successfully! Check inbox.';
    } catch (\Exception $e) {
        return 'Email FAILED: ' . $e->getMessage();
    }
});

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'stats'])->name('stats');
        Route::get('/jobs', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/bookings', [AdminDashboardController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{id}', [AdminDashboardController::class, 'bookingDetail'])->name('booking-detail');
        Route::post('/bookings/{id}/update', [AdminDashboardController::class, 'updateBooking'])->name('booking-update');
        Route::get('/fleet', [AdminDashboardController::class, 'fleet'])->name('fleet');
        Route::get('/zones-map', [AdminDashboardController::class, 'zonesMap'])->name('zones-map');
        Route::get('/subscribers', [AdminDashboardController::class, 'subscribers'])->name('subscribers');
        Route::get('/contact-messages', [AdminDashboardController::class, 'contactMessages'])->name('contact-messages');
        Route::post('/contact-messages/{id}/mark-read', [AdminDashboardController::class, 'markContactMessageRead'])->name('contact-messages.mark-read');
        Route::get('/promotions', [AdminDashboardController::class, 'promotions'])->name('promotions');
        Route::post('/promotions/send', [AdminDashboardController::class, 'sendPromotion'])->name('promotions.send');
        Route::get('/notifications', [AdminDashboardController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/mark-read', [AdminDashboardController::class, 'markNotificationsRead'])->name('notifications.mark-read');
    });
});
