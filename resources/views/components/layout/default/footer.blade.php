<footer class="bg-burgundy-900 text-cream-100 py-8 mt-auto">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- About Section -->
            <div>
                <h3 class="text-lg font-bold mb-4">VinoRecall</h3>
                <p class="text-cream-200 text-sm">
                    Master wine knowledge through spaced repetition. Your path to WSET certification starts here.
                </p>
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('dashboard') }}" class="text-cream-200 hover:text-white transition">Dashboard</a></li>
                    <li><a href="{{ route('library') }}" class="text-cream-200 hover:text-white transition">Library</a></li>
                    <li><a href="{{ route('profile') }}" class="text-cream-200 hover:text-white transition">Profile</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-lg font-bold mb-4">Support</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="text-cream-200 hover:text-white transition">Help Center</a></li>
                    <li><a href="#" class="text-cream-200 hover:text-white transition">Contact Us</a></li>
                    <li><a href="#" class="text-cream-200 hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-cream-100/20 mt-8 pt-8 text-center text-sm text-cream-200">
            <p>&copy; {{ date('Y') }} VinoRecall. All rights reserved.</p>
        </div>
    </div>
</footer>






