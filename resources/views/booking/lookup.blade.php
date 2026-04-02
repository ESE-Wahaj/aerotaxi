@extends('layouts.app')

@section('title', 'My Booking - AeroTAXI')

@section('content')

    <section class="bg-cream py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Booking</h1>
            <p class="text-gray-500">Enter your booking reference to view your booking details</p>
        </div>
    </section>

    <section class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Search Form --}}
            <form action="{{ route('booking.lookup') }}" method="GET" class="mb-10">
                <div class="flex gap-3">
                    <input type="text" name="reference" value="{{ $reference }}" placeholder="Enter booking reference (e.g. ATH-XXXXXXXX)"
                           class="flex-1 border border-gray-200 rounded-xl px-5 py-3.5 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent uppercase tracking-wider"
                           required>
                    <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl px-8 py-3.5 transition-all hover:shadow-md active:scale-[0.98] whitespace-nowrap">
                        Find Booking
                    </button>
                </div>
            </form>

            @if($reference && !$booking)
                {{-- Not found --}}
                <div class="bg-red-50 border border-red-200 rounded-2xl p-8 text-center">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-magnifying-glass text-xl text-red-400"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Booking not found</h3>
                    <p class="text-sm text-gray-500">No booking was found with reference <strong class="text-gray-700">{{ $reference }}</strong>. Please check the reference and try again.</p>
                </div>
            @endif

            @if($booking)
                {{-- Booking Details --}}
                <div class="space-y-6">

                    {{-- Status Banner --}}
                    @if($booking->status === 'confirmed' && $booking->payment_status === 'paid')
                    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-circle-check text-xl text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-bold text-green-800">Booking Confirmed & Paid</p>
                            <p class="text-sm text-green-600">Your transfer is all set. Your driver will be waiting for you.</p>
                        </div>
                    </div>
                    @elseif($booking->status === 'pending')
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-clock text-xl text-amber-600"></i>
                        </div>
                        <div>
                            <p class="font-bold text-amber-800">Booking Pending</p>
                            <p class="text-sm text-amber-600">Payment has not been completed yet.</p>
                        </div>
                    </div>
                    @elseif($booking->status === 'cancelled')
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-5 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-ban text-xl text-red-500"></i>
                        </div>
                        <div>
                            <p class="font-bold text-red-800">Booking Cancelled</p>
                            <p class="text-sm text-red-600">This booking has been cancelled.</p>
                        </div>
                    </div>
                    @endif

                    {{-- Reference Card --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-gray-900">Booking Reference</h2>
                            <span class="bg-yellow-50 border border-yellow-300 text-gray-900 font-bold tracking-widest px-4 py-2 rounded-xl text-lg">{{ $booking->reference }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Booked on</p>
                                <p class="font-medium text-gray-800">{{ $booking->created_at->format('j M Y, H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs uppercase tracking-wider mb-1">Status</p>
                                @if($booking->status === 'confirmed')
                                <span class="inline-flex items-center gap-1 text-green-700 font-semibold"><i class="fa-solid fa-circle-check text-xs"></i> Confirmed</span>
                                @elseif($booking->status === 'pending')
                                <span class="inline-flex items-center gap-1 text-amber-600 font-semibold"><i class="fa-solid fa-clock text-xs"></i> Pending</span>
                                @else
                                <span class="inline-flex items-center gap-1 text-red-600 font-semibold"><i class="fa-solid fa-ban text-xs"></i> {{ ucfirst($booking->status) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Journey Details --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-5">Journey Details</h2>
                        <div class="space-y-4">
                            <div class="flex items-start gap-4 pb-4 border-b border-gray-50">
                                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-location-dot text-primary text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider">From</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $booking->from_location }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 pb-4 border-b border-gray-50">
                                <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-flag-checkered text-green-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider">To</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $booking->to_location }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 pb-4 border-b border-gray-50">
                                <div class="w-9 h-9 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-regular fa-calendar text-yellow-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider">Date & Time</p>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $booking->depart_date->format('l, j F Y') }}
                                        @if($booking->depart_time) at {{ $booking->depart_time }} @endif
                                    </p>
                                </div>
                            </div>
                            @if($booking->flight_number)
                            <div class="flex items-start gap-4 pb-4 border-b border-gray-50">
                                <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-plane text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider">Flight Number</p>
                                    <p class="text-sm font-semibold text-gray-800">{{ $booking->flight_number }}</p>
                                </div>
                            </div>
                            @endif
                            @if($booking->note_to_driver)
                            <div class="flex items-start gap-4">
                                <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i class="fa-solid fa-message text-gray-500 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider">Note to Driver</p>
                                    <p class="text-sm text-gray-700">{{ $booking->note_to_driver }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Vehicle & Passenger --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        {{-- Vehicle --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">Vehicle</h2>
                            @if($booking->vehicle)
                            <div class="flex items-center gap-4">
                                <img src="{{ $booking->vehicle->image }}" alt="{{ $booking->vehicle->name }}" class="h-16 object-contain">
                                <div>
                                    <p class="font-bold text-gray-900">{{ $booking->vehicle->name }}</p>
                                    @if($booking->vehicle->car_model)
                                    <p class="text-xs text-gray-400">{{ $booking->vehicle->car_model }}</p>
                                    @endif
                                    <div class="flex gap-3 text-xs text-gray-500 mt-1">
                                        <span><i class="fa-solid fa-users text-gray-400"></i> {{ $booking->vehicle->passengers }}</span>
                                        <span><i class="fa-solid fa-suitcase-rolling text-gray-400"></i> {{ $booking->vehicle->suitcases }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Passenger --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">Passenger</h2>
                            <div class="space-y-3 text-sm">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-user text-gray-400 w-4"></i>
                                    <span class="text-gray-800">{{ $booking->passenger_name }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-envelope text-gray-400 w-4"></i>
                                    <span class="text-gray-800">{{ $booking->email }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-phone text-gray-400 w-4"></i>
                                    <span class="text-gray-800">{{ $booking->country_code }} {{ $booking->phone }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">Payment</h2>
                        <div class="flex items-center justify-between">
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400 w-28">Payment Status</span>
                                    @if($booking->payment_status === 'paid')
                                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 font-semibold text-xs px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Paid
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 font-semibold text-xs px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-clock text-[10px]"></i> {{ ucfirst($booking->payment_status) }}
                                    </span>
                                    @endif
                                </div>
                                @if($booking->payment_id)
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400 w-28">Transaction ID</span>
                                    <span class="text-gray-700 font-mono text-xs">{{ $booking->payment_id }}</span>
                                </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400 mb-1">Total Amount</p>
                                <p class="text-3xl font-bold text-gray-900">&pound;{{ number_format($booking->total_price, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div class="bg-lightgreen rounded-2xl p-5">
                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-700 justify-center">
                            <span><i class="fa-solid fa-check text-green-600 mr-1"></i> Meet & Greet</span>
                            <span><i class="fa-solid fa-check text-green-600 mr-1"></i> Free flight tracking</span>
                            <span><i class="fa-solid fa-check text-green-600 mr-1"></i> Free cancellation (24h)</span>
                            <span><i class="fa-solid fa-check text-green-600 mr-1"></i> 24/7 support</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col sm:flex-row gap-3 justify-center pt-2">
                        <a href="mailto:supportaerotaxi@gmail.com?subject=Booking {{ $booking->reference }}"
                           class="inline-flex items-center justify-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-medium rounded-xl px-6 py-3 transition-all">
                            <i class="fa-solid fa-envelope text-sm"></i> Contact Support
                        </a>
                        <a href="{{ route('home') }}"
                           class="inline-flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-xl px-6 py-3 transition-all hover:shadow-md">
                            <i class="fa-solid fa-plus text-sm"></i> Book Another Ride
                        </a>
                    </div>

                </div>
            @endif

            @if(!$reference)
                {{-- Empty state --}}
                <div class="text-center py-10">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-ticket text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">Enter your booking reference above to view your booking details.</p>
                    <p class="text-sm text-gray-400 mt-2">Your reference was sent to you in your confirmation email.</p>
                </div>
            @endif

        </div>
    </section>

@endsection
