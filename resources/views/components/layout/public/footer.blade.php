<footer class="bg-burgundy-900 text-cream-100 py-8 mt-auto">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- About Section -->
            <div>
                <h3 class="flex items-center gap-2 mb-4">
                    <x-logo class="w-8 h-8 fill-current" />
                    <span class="text-xl font-bold font-heading">VinoRecall</span>
                </h3>
                <p class="text-cream-200 text-sm">
                    Master wine knowledge through spaced repetition.
                </p>
            </div>

            <!-- Get Started -->
            <div>
                <h3 class="text-lg font-bold mb-4">Get Started</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('login') }}" class="text-cream-200 hover:text-white transition">Log In</a></li>
                    <li><a href="{{ route('register') }}" class="text-cream-200 hover:text-white transition">Sign Up</a></li>
                </ul>
            </div>

            <!-- Support -->
            <div>
                <h3 class="text-lg font-bold mb-4">Support</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="mailto:hello@vinorecall.com" class="text-cream-200 hover:text-white transition">Contact Us</a></li>
                    <li><a href="{{ route('about') }}" class="text-cream-200 hover:text-white transition">About Us</a></li>
                    <li><a href="{{ route('terms') }}" class="text-cream-200 hover:text-white transition">Terms of Service</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-cream-200 hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Social -->
            <div>
                <h3 class="text-lg font-bold mb-4">Follow Us</h3>
                <div class="flex space-x-4">
                    <a href="http://instagram.com/strifeandwine" target="_blank" rel="noopener noreferrer" class="text-cream-200 hover:text-white transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.012 3.584-.07 4.85c-.148 3.252-1.691 4.771-4.919 4.919-1.265.058-1.645.069-4.85.069s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.012-3.584.07-4.85c.148-3.252 1.691-4.771 4.919-4.919 1.265-.058 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-cream-100/20 mt-8 pt-8 text-center text-sm text-cream-200">
            <p>&copy; {{ date('Y') }} VinoRecall. All rights reserved.</p>
        </div>
    </div>
</footer>
