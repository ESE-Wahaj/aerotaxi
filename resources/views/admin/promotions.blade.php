@extends('admin.layouts.app')

@section('title', 'Promotions & Deals')

@section('content')
    {{-- Send Promotion Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800"><i class="fa-solid fa-bullhorn text-yellow-500 mr-2"></i>Send Promotion</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('admin.promotions.send') }}" x-data="{ sending: false }" @submit="sending = true">
                @csrf
                <div class="mb-4">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" name="subject" id="subject" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g. 20% Off All Airport Transfers!">
                </div>
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea name="message" id="message" rows="6" required
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Write your promotional message here..."></textarea>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">This will be sent to <strong>{{ $subscribers->where('active', '!=', 0)->count() }}</strong> active subscribers.</p>
                    <button type="submit" :disabled="sending"
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition disabled:opacity-50">
                        <span x-show="!sending"><i class="fa-solid fa-paper-plane mr-1"></i> Send to All Subscribers</span>
                        <span x-show="sending" x-cloak><i class="fa-solid fa-spinner fa-spin mr-1"></i> Sending...</span>
                    </button>
                </div>
            </form>
            @if(session('promo_success'))
                <div class="mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('promo_success') }}
                </div>
            @endif
            @if(session('promo_error'))
                <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {{ session('promo_error') }}
                </div>
            @endif
        </div>
    </div>

    {{-- Subscribers Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Subscribers</h2>
            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-medium">{{ $subscribers->count() }} total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed Date</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($subscribers as $index => $subscriber)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4">
                                <a href="mailto:{{ $subscriber->email }}" class="text-yellow-600 hover:text-yellow-700 font-medium">{{ $subscriber->email }}</a>
                            </td>
                            <td class="px-6 py-4 text-gray-900">{{ $subscriber->name ?? '--' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $subscriber->created_at?->format('d M Y \a\t H:i') }}</td>
                            <td class="px-6 py-4">
                                @if($subscriber->active !== 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-users text-3xl mb-3 block text-gray-300"></i>
                                No subscribers yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
