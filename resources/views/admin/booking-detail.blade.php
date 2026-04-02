@extends('admin.layouts.app')

@section('title', 'Booking #' . $booking->reference)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-arrow-left mr-1"></i> Back
        </a>
        <span class="text-sm text-gray-400">Booking ID: {{ $booking->id }}</span>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-3 mb-6">
        <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $booking->reference }}</h1>
        @php
            $sc = ['confirmed'=>'green','pending'=>'yellow','new'=>'blue','completed'=>'green','cancelled'=>'red','assigned'=>'blue','bidding'=>'yellow'];
            $col = $sc[$booking->status] ?? 'gray';
        @endphp
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $col }}-100 text-{{ $col }}-700">{{ ucfirst($booking->status ?? 'new') }}</span>
        @if($booking->payment_status === 'paid')
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Paid</span>
        @else
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ ucfirst($booking->payment_status ?? 'Unpaid') }}</span>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.booking-update', $booking->id) }}">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Journey Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-route text-blue-500"></i> Journey Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pickup Location</label>
                        <input type="text" name="from_location" value="{{ old('from_location', $booking->from_location) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Drop-off Location</label>
                        <input type="text" name="to_location" value="{{ old('to_location', $booking->to_location) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Date</label>
                            <input type="date" name="depart_date" value="{{ old('depart_date', $booking->depart_date?->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Time</label>
                            <input type="time" name="depart_time" value="{{ old('depart_time', $booking->depart_time) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Flight Number</label>
                        <input type="text" name="flight_number" value="{{ old('flight_number', $booking->flight_number) }}" placeholder="e.g. BA1234"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Note to Driver</label>
                        <textarea name="note_to_driver" rows="2" placeholder="Any special instructions..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none">{{ old('note_to_driver', $booking->note_to_driver) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Passenger Information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-blue-500"></i> Passenger Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Full Name</label>
                        <input type="text" name="passenger_name" value="{{ old('passenger_name', $booking->passenger_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $booking->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Country Code</label>
                            <input type="text" name="country_code" value="{{ old('country_code', $booking->country_code) }}" placeholder="+44"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $booking->phone) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vehicle & Pricing --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-car text-blue-500"></i> Vehicle & Pricing
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Vehicle Type</label>
                        <select name="vehicle_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(\App\Models\Vehicle::orderBy('sort_order')->get() as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $booking->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->name }} ({{ $vehicle->car_model }}) - From £{{ number_format($vehicle->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Price (£)</label>
                        <input type="number" name="total_price" step="0.01" min="0" value="{{ old('total_price', number_format($booking->total_price, 2, '.', '')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Status & Payment --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-cog text-blue-500"></i> Status & Payment
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Booking Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['new','confirmed','pending','completed','cancelled','assigned','bidding'] as $s)
                                <option value="{{ $s }}" {{ ($booking->status ?? 'new') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Payment Status</label>
                        <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @foreach(['unpaid','paid','refunded'] as $ps)
                                <option value="{{ $ps }}" {{ ($booking->payment_status ?? 'unpaid') === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Payment ID</label>
                        <input type="text" name="payment_id" value="{{ old('payment_id', $booking->payment_id) }}" placeholder="Stripe/manual reference"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100 text-xs text-gray-400">
                        <div>Created: {{ $booking->created_at?->format('d M Y H:i') }}</div>
                        <div>Updated: {{ $booking->updated_at?->format('d M Y H:i') }}</div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Save Button --}}
        <div class="mt-6 flex items-center gap-4">
            <button type="submit" class="inline-flex items-center px-8 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-save mr-2"></i> Save All Changes
            </button>
            <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>

@endsection
