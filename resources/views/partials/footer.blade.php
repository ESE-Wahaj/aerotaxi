<footer class="bg-gray-900 text-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 py-8 sm:py-12">
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">

            {{-- Column 1: Logo & Tagline --}}
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-0 mb-4">
                    <img src="/images/logo.png" alt="AeroTAXI" class="h-8 sm:h-10 md:h-14 w-auto">
                    <img src="/images/logo2.png" alt="AeroTAXI" class="h-12 sm:h-14 md:h-20 w-auto ml-1 sm:ml-2">
                </a>
                <p class="text-gray-400 text-xs sm:text-sm leading-relaxed">
                    Connecting you to the best and affordable taxi services around the world.
                </p>
                <div class="mt-3 sm:mt-4">
                    <a href="mailto:supportaerotaxi@gmail.com" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors break-all">
                        <i class="fas fa-envelope mr-1 sm:mr-2"></i>supportaerotaxi@gmail.com
                    </a>
                </div>
            </div>

            {{-- Column 2: Quick Links --}}
            <div>
                <h3 class="text-sm sm:text-lg font-semibold mb-3 sm:mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('coverage') }}" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Coverage
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3: Support --}}
            <div>
                <h3 class="text-sm sm:text-lg font-semibold mb-3 sm:mb-4">Support</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('help') }}" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Help Center
                        </a>
                    </li>
                    <li>
                        <a href="/help#faqs" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            FAQs
                        </a>
                    </li>
                    <li>
                        <a href="/help#contact-us" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Contact Us
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 4: Legal --}}
            <div>
                <h3 class="text-sm sm:text-lg font-semibold mb-3 sm:mb-4">Legal</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('legal.terms') }}" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Terms of Service
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('legal.privacy-statement') }}" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Privacy Statement
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('legal.privacy-policy') }}" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('legal.cookie-policy') }}" class="text-gray-400 hover:text-yellow-400 text-xs sm:text-sm transition-colors">
                            Cookie Policy
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-gray-800 mt-6 sm:mt-10 pt-4 sm:pt-6 text-center">
            <p class="text-gray-500 text-xs sm:text-sm">&copy; 2026 AeroTAXI LLC. All rights reserved.</p>
        </div>
    </div>
</footer>
