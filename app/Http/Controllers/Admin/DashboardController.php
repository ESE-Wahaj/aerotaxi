<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Subscriber;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function prepareBookingsJson($bookings)
    {
        return $bookings->map(function ($b) {
            return [
                'id' => $b->id,
                'reference' => $b->reference,
                'passenger_name' => $b->passenger_name,
                'from_location' => $b->from_location,
                'to_location' => $b->to_location,
                'depart_date' => $b->depart_date ? $b->depart_date->format('Y-m-d') : null,
                'depart_time' => $b->depart_time,
                'status' => $b->status ?? 'new',
                'payment_status' => $b->payment_status ?? 'unpaid',
                'vehicle_name' => $b->vehicle?->name ?? 'N/A',
                'total_price' => $b->total_price,
                'created_at' => $b->created_at?->toISOString(),
            ];
        })->values();
    }

    public function index()
    {
        $bookings = Booking::with('vehicle')->orderBy('created_at', 'desc')->get();
        $bookingsJson = $this->prepareBookingsJson($bookings);

        return view('admin.dashboard', compact('bookings', 'bookingsJson'));
    }

    public function bookings(Request $request)
    {
        $bookings = Booking::with('vehicle')
            ->where(function ($query) {
                $query->where('payment_status', '!=', 'paid')
                      ->orWhereNull('payment_status');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $bookingsJson = $this->prepareBookingsJson($bookings);

        return view('admin.bookings', compact('bookings', 'bookingsJson'));
    }

    public function bookingDetail($id)
    {
        $booking = Booking::with('vehicle')->findOrFail($id);

        return view('admin.booking-detail', compact('booking'));
    }

    public function updateBooking(Request $request, $id)
    {
        $validated = $request->validate([
            'from_location' => 'required|string|max:500',
            'to_location' => 'required|string|max:500',
            'depart_date' => 'required|date',
            'depart_time' => 'nullable|string|max:10',
            'flight_number' => 'nullable|string|max:20',
            'note_to_driver' => 'nullable|string|max:500',
            'passenger_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:50',
            'vehicle_id' => 'required|exists:vehicles,id',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|in:new,confirmed,pending,completed,cancelled,assigned,bidding',
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'payment_id' => 'nullable|string|max:255',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->update($validated);

        return redirect()->route('admin.booking-detail', $id)->with('success', 'Booking updated successfully.');
    }

    public function fleet()
    {
        $vehicles = Vehicle::orderBy('sort_order')->get();

        return view('admin.fleet', compact('vehicles'));
    }

    public function zonesMap()
    {
        return view('admin.zones-map');
    }

    public function stats()
    {
        $totalBookings = Booking::count();
        $totalRevenue = Booking::where('payment_status', 'paid')->sum('total_price');
        $todayBookings = Booking::whereDate('created_at', today())->count();
        $pendingBookings = Booking::where('status', 'pending')->count();
        $recentBookings = Booking::with('vehicle')->orderBy('created_at', 'desc')->limit(10)->get();
        $statusCounts = Booking::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $vehicleCounts = Booking::with('vehicle')->get()->groupBy(fn($b) => $b->vehicle?->name ?? 'N/A')->map->count()->sortDesc();
        $subscribers = Subscriber::orderBy('created_at', 'desc')->get();

        return view('admin.stats', compact('totalBookings', 'totalRevenue', 'todayBookings', 'pendingBookings', 'recentBookings', 'statusCounts', 'vehicleCounts', 'subscribers'));
    }

    public function subscribers()
    {
        $subscribers = Subscriber::orderBy('created_at', 'desc')->get();

        return view('admin.subscribers', compact('subscribers'));
    }

    public function contactMessages()
    {
        $messages = ContactMessage::orderBy('created_at', 'desc')->get();

        return view('admin.contact-messages', compact('messages'));
    }

    public function markContactMessageRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['read' => true]);

        return redirect()->route('admin.contact-messages')->with('success', 'Message marked as read.');
    }

    public function promotions()
    {
        $subscribers = Subscriber::orderBy('created_at', 'desc')->get();

        return view('admin.promotions', compact('subscribers'));
    }

    public function sendPromotion(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $subscribers = Subscriber::where('active', '!=', 0)->get();

        if ($subscribers->isEmpty()) {
            return redirect()->route('admin.promotions')->with('promo_error', 'No active subscribers to send to.');
        }

        // In a production app, this would queue emails. For now, just confirm.
        return redirect()->route('admin.promotions')->with('promo_success', "Promotion \"{$validated['subject']}\" queued for {$subscribers->count()} subscribers.");
    }

    public function notifications()
    {
        $notifications = AdminNotification::orderBy('created_at', 'desc')->limit(50)->get();

        return response()->json($notifications);
    }

    public function markNotificationsRead()
    {
        AdminNotification::where('read', false)->update(['read' => true]);

        return response()->json(['success' => true]);
    }
}
