<?php

namespace App\Http\Controllers;

use App\Mail\AdminBookingNotification;
use App\Mail\BookingConfirmation;
use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\Subscriber;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function checkPrices(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $departDate = $request->query('depart_date');
        $departTime = $request->query('depart_time');
        $vehicles = Vehicle::orderBy('sort_order')->get();

        return view('booking.check-prices', compact('from', 'to', 'departDate', 'departTime', 'vehicles'));
    }

    public function transferDetails(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $departDate = $request->query('depart_date');
        $departTime = $request->query('depart_time');
        $vehicleId = $request->query('vehicle_id');
        $totalPrice = $request->query('total_price');
        $distance = $request->query('distance');
        $duration = $request->query('duration');
        $vehicle = Vehicle::findOrFail($vehicleId);

        return view('booking.transfer-details', compact('from', 'to', 'departDate', 'departTime', 'vehicle', 'totalPrice', 'distance', 'duration'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_location' => 'required|string',
            'to_location' => 'required|string',
            'depart_date' => 'required|date',
            'depart_time' => 'nullable|string',
            'vehicle_id' => 'required|exists:vehicles,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'total_price' => 'nullable|numeric|min:0',
            'flight_number' => 'nullable|string|max:20',
            'note_to_driver' => 'nullable|string|max:500',
            'country_code' => 'nullable|string|max:10',
            'agree_promotions' => 'nullable',
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $validated['passenger_name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        $validated['reference'] = 'ATH-' . strtoupper(Str::random(8));
        $validated['total_price'] = $validated['total_price'] ?? $vehicle->price;
        $validated['status'] = 'pending';
        $validated['payment_status'] = 'unpaid';

        // Save subscriber if opted in
        if (!empty($validated['agree_promotions'])) {
            $created = Subscriber::firstOrCreate(
                ['email' => $validated['email']],
                ['name' => $validated['passenger_name']]
            );
            if ($created->wasRecentlyCreated) {
                AdminNotification::create([
                    'type' => 'subscriber',
                    'message' => "New subscriber: {$validated['email']}",
                    'read' => false,
                ]);
            }
        }

        unset($validated['first_name'], $validated['last_name'], $validated['agree_promotions']);

        $booking = Booking::create($validated);

        return redirect()->route('booking.payment', ['reference' => $booking->reference]);
    }

    public function payment(Request $request)
    {
        $reference = $request->query('reference');
        $booking = Booking::where('reference', $reference)->with('vehicle')->firstOrFail();
        $stripeKey = config('services.stripe.key');

        return view('booking.payment', compact('booking', 'stripeKey'));
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate(['reference' => 'required|string']);

        $booking = Booking::where('reference', $request->reference)->firstOrFail();

        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $intent = $stripe->paymentIntents->create([
            'amount' => (int) round($booking->total_price * 100), // pence
            'currency' => 'gbp',
            'metadata' => [
                'booking_reference' => $booking->reference,
                'from' => $booking->from_location,
                'to' => $booking->to_location,
            ],
        ]);

        return response()->json(['clientSecret' => $intent->client_secret]);
    }

    public function confirmation(Request $request, $reference)
    {
        $booking = Booking::where('reference', $reference)->with('vehicle')->firstOrFail();

        // Mark as confirmed + paid and send email if still pending
        if ($booking->status === 'pending') {
            $booking->update(['status' => 'confirmed', 'payment_status' => 'paid']);

            AdminNotification::create([
                'type' => 'booking',
                'message' => "New booking {$booking->reference} from {$booking->passenger_name}",
                'read' => false,
            ]);

            try {
                // Send confirmation to customer
                Mail::to($booking->email)->send(new BookingConfirmation($booking));

                // Send notification to admin(s)
                $adminEmails = explode(',', env('ADMIN_EMAILS', 'supportaerotaxi@gmail.com'));
                $adminEmails = array_map('trim', $adminEmails);
                Mail::to($adminEmails)->send(new AdminBookingNotification($booking));
            } catch (\Exception $e) {
                Log::error('Failed to send booking emails: ' . $e->getMessage());
            }
        }

        return view('booking.confirmation', compact('booking'));
    }

    public function lookup(Request $request)
    {
        $reference = strtoupper(trim($request->query('reference', '')));
        $booking = null;

        if ($reference) {
            $booking = Booking::where('reference', $reference)->with('vehicle')->first();
        }

        return view('booking.lookup', compact('reference', 'booking'));
    }
}
