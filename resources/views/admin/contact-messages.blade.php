@extends('admin.layouts.app')

@section('title', 'Contact Messages')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">Contact Messages</h2>
            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full font-medium">{{ $messages->count() }} total</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($messages as $msg)
                        <tr class="hover:bg-gray-50 {{ !$msg->read ? 'bg-blue-50/50' : '' }}" x-data="{ expanded: false }">
                            <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $msg->created_at?->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-gray-900 font-medium">{{ $msg->name }}</td>
                            <td class="px-6 py-4">
                                <a href="mailto:{{ $msg->email }}" class="text-yellow-600 hover:text-yellow-700">{{ $msg->email }}</a>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $msg->subject ?? '--' }}</td>
                            <td class="px-6 py-4 text-gray-600 max-w-xs">
                                <div x-show="!expanded" class="truncate max-w-xs">{{ Str::limit($msg->message, 60) }}</div>
                                <div x-show="expanded" x-cloak class="whitespace-pre-wrap">{{ $msg->message }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($msg->read)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Read</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Unread</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button @click="expanded = !expanded" class="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2">
                                    <span x-text="expanded ? 'Collapse' : 'View'"></span>
                                </button>
                                @if(!$msg->read)
                                    <form method="POST" action="{{ route('admin.contact-messages.mark-read', $msg->id) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Mark Read</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-envelope text-3xl mb-3 block text-gray-300"></i>
                                No contact messages yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
