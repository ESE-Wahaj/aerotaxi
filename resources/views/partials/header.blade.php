<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white shadow-sm overflow-hidden">
    <div class="max-w-7xl mx-auto px-3 sm:px-4">
        <div class="flex items-center justify-between h-16 md:h-20">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-0">
                <img src="/images/logo.png" alt="AeroTAXI" class="h-8 sm:h-10 md:h-14 w-auto">
                <img src="/images/logo2.png" alt="AeroTAXI" class="h-12 sm:h-14 md:h-20 w-auto ml-1 sm:ml-2">
            </a>

            {{-- Desktop Navigation --}}
            <div class="hidden md:flex items-center space-x-8">
                <a href="{{ route('home') }}"
                   class="{{ request()->routeIs('home') ? 'text-primary font-semibold' : 'text-gray-600 hover:text-primary' }} transition-colors">
                    Home
                </a>
                <a href="{{ route('coverage') }}"
                   class="{{ request()->routeIs('coverage') ? 'text-primary font-semibold' : 'text-gray-600 hover:text-primary' }} transition-colors">
                    Coverage
                </a>
                <a href="/help#contact-us"
                   class="text-gray-600 hover:text-primary transition-colors">
                    Contact us
                </a>
                <a href="{{ route('help') }}"
                   class="{{ request()->routeIs('help') ? 'text-primary font-semibold' : 'text-gray-600 hover:text-primary' }} transition-colors">
                    Help
                </a>
            </div>

            {{-- Mobile Hamburger Button --}}
            <button @click="open = !open" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-primary focus:outline-none" aria-label="Toggle menu">
                <svg x-show="!open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mobile Menu Dropdown --}}
        <div x-show="open" x-cloak x-transition class="md:hidden pb-4 space-y-2">
            <a href="{{ route('home') }}"
               class="block px-3 py-2 rounded-md {{ request()->routeIs('home') ? 'text-primary font-semibold bg-blue-50' : 'text-gray-600 hover:text-primary hover:bg-gray-50' }}">
                Home
            </a>
            <a href="{{ route('coverage') }}"
               class="block px-3 py-2 rounded-md {{ request()->routeIs('coverage') ? 'text-primary font-semibold bg-blue-50' : 'text-gray-600 hover:text-primary hover:bg-gray-50' }}">
                Coverage
            </a>
            <a href="/help#contact-us"
               class="block px-3 py-2 rounded-md text-gray-600 hover:text-primary hover:bg-gray-50">
                Contact us
            </a>
            <a href="{{ route('help') }}"
               class="block px-3 py-2 rounded-md {{ request()->routeIs('help') ? 'text-primary font-semibold bg-blue-50' : 'text-gray-600 hover:text-primary hover:bg-gray-50' }}">
                Help
            </a>
        </div>
    </div>
</nav>
