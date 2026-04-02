<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - AeroTAXI Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('head')
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ mobileOpen: false }">

    <nav class="bg-[#1a2e4a] sticky top-0 z-50 shadow-lg">
        <div class="px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('admin.stats') }}" class="flex items-center gap-3 flex-shrink-0">
                    <img src="/images/logo.png" alt="AeroTAXI" class="h-9">
                    <span class="text-white font-bold text-lg">Admin</span>
                </a>

                <div class="hidden md:flex items-center gap-2">
                    @php
                        $nav = [
                            ['route' => 'admin.stats', 'label' => 'Dashboard', 'icon' => 'fa-chart-pie', 'match' => ['admin.stats','admin.subscribers']],
                            ['route' => 'admin.dashboard', 'label' => 'Jobs', 'icon' => 'fa-briefcase', 'match' => ['admin.dashboard','admin.booking-detail']],
                            ['route' => 'admin.bookings', 'label' => 'Unpaid Bookings', 'icon' => 'fa-clock', 'match' => ['admin.bookings']],
                            ['route' => 'admin.fleet', 'label' => 'Fleet', 'icon' => 'fa-car-side', 'match' => ['admin.fleet']],
                            ['route' => 'admin.zones-map', 'label' => 'Zones Map', 'icon' => 'fa-map-location-dot', 'match' => ['admin.zones-map']],
                            ['route' => 'admin.contact-messages', 'label' => 'Messages', 'icon' => 'fa-envelope', 'match' => ['admin.contact-messages']],
                            ['route' => 'admin.promotions', 'label' => 'Promotions', 'icon' => 'fa-bullhorn', 'match' => ['admin.promotions']],
                        ];
                    @endphp
                    @foreach($nav as $item)
                        <a href="{{ route($item['route']) }}"
                           class="px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2 {{ request()->routeIs(...$item['match']) ? 'bg-blue-600 text-white shadow' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                            <i class="fa-solid {{ $item['icon'] }}"></i> {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-4">
                    <span class="hidden sm:block text-sm text-blue-200"><i class="fa-solid fa-user-circle mr-1"></i> {{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>

                    @php $unreadCount = \App\Models\AdminNotification::where('read', false)->count(); @endphp
                    <div class="relative" x-data="{ notifOpen: false }">
                        <button @click="notifOpen = !notifOpen" class="relative text-blue-200 hover:text-white transition">
                            <i class="fa-solid fa-bell text-lg"></i>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </button>

                        <div x-show="notifOpen" x-cloak @click.outside="notifOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                                @if($unreadCount > 0)
                                    <form method="POST" action="{{ route('admin.notifications.mark-read') }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                                @php $recentNotifs = \App\Models\AdminNotification::orderBy('created_at', 'desc')->limit(15)->get(); @endphp
                                @forelse($recentNotifs as $notif)
                                    <div class="px-4 py-3 text-sm {{ !$notif->read ? 'bg-blue-50/60' : '' }} hover:bg-gray-50">
                                        <div class="flex items-start gap-2">
                                            @if($notif->type === 'booking')
                                                <i class="fa-solid fa-briefcase text-blue-500 mt-0.5"></i>
                                            @elseif($notif->type === 'contact')
                                                <i class="fa-solid fa-envelope text-green-500 mt-0.5"></i>
                                            @elseif($notif->type === 'subscriber')
                                                <i class="fa-solid fa-user-plus text-purple-500 mt-0.5"></i>
                                            @else
                                                <i class="fa-solid fa-bell text-gray-400 mt-0.5"></i>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <p class="text-gray-800 leading-snug">{{ $notif->message }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at?->diffForHumans() }}</p>
                                            </div>
                                            @if(!$notif->read)
                                                <span class="h-2 w-2 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center text-gray-400 text-sm">
                                        No notifications yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-blue-300 hover:text-red-400 transition text-sm"><i class="fas fa-sign-out-alt text-lg"></i></button>
                    </form>
                    <button @click="mobileOpen = !mobileOpen" class="md:hidden text-blue-200 hover:text-white text-xl">
                        <i class="fas" :class="mobileOpen ? 'fa-times' : 'fa-bars'"></i>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileOpen" x-cloak x-transition class="md:hidden border-t border-white/10 px-4 py-3 space-y-1">
            @foreach($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="block px-4 py-3 rounded-lg text-base font-medium {{ request()->routeIs(...$item['match']) ? 'bg-blue-600 text-white' : 'text-blue-200 hover:bg-white/10' }}">
                    <i class="fa-solid {{ $item['icon'] }} mr-2"></i> {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>

    <main class="p-4 sm:p-6">
        @yield('content')
    </main>

</body>
</html>
