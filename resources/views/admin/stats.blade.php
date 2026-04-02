@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Bookings</p>
                    <p class="text-4xl font-bold text-gray-900">{{ $totalBookings }}</p>
                </div>
                <div class="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center">
                    <i class="fa-solid fa-ticket text-3xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
                    <p class="text-4xl font-bold text-green-600">&pound;{{ number_format($totalRevenue, 2) }}</p>
                </div>
                <div class="w-16 h-16 rounded-xl bg-green-50 flex items-center justify-center">
                    <i class="fa-solid fa-sterling-sign text-3xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Today's Bookings</p>
                    <p class="text-4xl font-bold text-blue-600">{{ $todayBookings }}</p>
                </div>
                <div class="w-16 h-16 rounded-xl bg-yellow-50 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-day text-3xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Pending Bookings</p>
                    <p class="text-4xl font-bold text-amber-600">{{ $pendingBookings }}</p>
                </div>
                <div class="w-16 h-16 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-3xl text-amber-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- By Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Bookings by Status</h3>
            <div class="space-y-4">
                @foreach($statusCounts as $status => $count)
                @php $c = ['confirmed'=>'green','pending'=>'amber','new'=>'blue','completed'=>'emerald','cancelled'=>'red','assigned'=>'sky','bidding'=>'yellow'][$status] ?? 'gray'; @endphp
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-{{ $c }}-500"></span>
                        <span class="text-sm text-gray-700 capitalize">{{ $status }}</span>
                    </div>
                    <span class="text-sm font-bold text-gray-900">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- By Vehicle --}}
        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Bookings by Vehicle</h3>
            <div class="space-y-4">
                @foreach($vehicleCounts as $vehicle => $count)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">{{ $vehicle }}</span>
                    <div class="flex items-center gap-3">
                        <div class="w-24 bg-gray-100 rounded-full h-2.5">
                            <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $totalBookings > 0 ? min(($count / $totalBookings * 100), 100) : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-900 w-8 text-right">{{ $count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Subscribers --}}
        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900">Email Subscribers</h3>
                <span class="bg-blue-50 text-blue-700 font-bold text-sm px-3 py-1 rounded-lg">{{ $subscribers->count() }}</span>
            </div>
            <div class="max-h-52 overflow-y-auto space-y-2">
                @forelse($subscribers as $sub)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div>
                        <p class="text-sm text-gray-800">{{ $sub->email }}</p>
                        @if($sub->name)<p class="text-xs text-gray-400">{{ $sub->name }}</p>@endif
                    </div>
                    <span class="text-xs text-gray-400">{{ $sub->created_at?->format('d M Y') }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-4">No subscribers yet</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Bookings --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900">Recent Bookings</h3>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:underline font-medium">View all jobs &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">Reference</th>
                        <th class="px-6 py-3 text-left">Passenger</th>
                        <th class="px-6 py-3 text-left">Route</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Price</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Payment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentBookings as $b)
                    @php $c = ['confirmed'=>'green','pending'=>'amber','new'=>'blue','completed'=>'emerald','cancelled'=>'red','assigned'=>'sky'][$b->status] ?? 'gray'; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-5 font-mono font-bold text-sm text-gray-900">{{ $b->reference }}</td>
                        <td class="px-6 py-5 text-sm text-gray-700">{{ $b->passenger_name }}</td>
                        <td class="px-6 py-5 text-sm text-gray-500 max-w-[250px] truncate">{{ $b->from_location }} &rarr; {{ $b->to_location }}</td>
                        <td class="px-6 py-5 text-sm text-gray-500 whitespace-nowrap">{{ $b->depart_date?->format('d M Y') }}</td>
                        <td class="px-6 py-5 text-sm font-semibold text-gray-900">&pound;{{ number_format($b->total_price, 2) }}</td>
                        <td class="px-6 py-5"><span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $c }}-100 text-{{ $c }}-700">{{ ucfirst($b->status) }}</span></td>
                        <td class="px-6 py-5">
                            @if($b->payment_status === 'paid')
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Paid</span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">{{ ucfirst($b->payment_status ?? 'Unpaid') }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
