<x-layout>
    <header class="bg-burgundy-900 text-white relative overflow-hidden">
        <nav class="flex justify-between items-center container mx-auto px-6 py-4">
            <a href="{{ route('home') }}" class="flex items-center space-x-3">
                <x-logo class="w-10 h-10 fill-current" />
                <span class="text-2xl font-bold font-heading">VinoRecall</span>
            </a>
            <div class="space-x-4">
                <a href="{{ route('login') }}" class="hover:text-burgundy-200">Log In</a>
                <a href="{{ route('register') }}" class="bg-burgundy-500 text-white px-6 py-2 rounded-lg hover:bg-burgundy-600 transition">Sign Up</a>
            </div>
        </nav>
        <div class="relative min-h-[600px] flex items-center font-heading">
            <div class="absolute inset-0 z-0">
                <img src="{{ asset('img/hero.jpg') }}" alt="Vineyard with wine glass" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-burgundy-900/50"></div>
            </div>
            <div class="container mx-auto px-6 py-24 md:py-32 relative z-10">
                <div class="max-w-2xl">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight text-white text-shadow-lg/40">
                        Master Wine with the Power of Spaced Repetition
                    </h1>
                    <p class="text-xl mb-8 text-white text-shadow-lg/40">
                        Learn wine knowledge efficiently with spaced repetition. Perfect for WSET Level 1 & 2 students and wine enthusiasts.
                    </p>
                    <a href="{{ route('register') }}" class="bg-burgundy-500 drop-shadow-lg/60 text-white px-8 py-3 rounded-lg text-lg font-semibold hover:bg-burgundy-600 transition inline-block">Start Your Journey Free</a>
                </div>
            </div>
        </div>
    </header>

    <section class="py-20 bg-cream-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-16 text-center text-burgundy-900">How Spaced Repetition Works</h2>
            
            <div class="flex flex-col md:flex-row justify-center items-center gap-6 max-w-6xl mx-auto">
                <div class="flex flex-col items-center">
                    <div class="bg-burgundy-800 text-white rounded-2xl shadow-xl p-8 w-42 h-42 flex flex-col items-center justify-center relative">
                        <div class="absolute -top-8 w-20 h-20 -rotate-30">
                            <img src="{{ asset('img/homepage/grapes.png') }}" alt="Grape" class="w-full h-full object-contain">
                        </div>
                        <h3 class="text-xl font-bold text-center mt-4">SHORT-TERM<br>MEMORY</h3>
                    </div>
                </div>

                <div class="flex items-center">
                    <svg class="w-12 h-12 text-burgundy-600 transform rotate-90 md:rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </div>

                <div class="flex flex-col items-center gap-8">
                    <div class="text-center mb-4">
                        <p class="text-xl font-bold text-burgundy-900">SPACED REVIEW</p>
                        <p class="text-sm text-gray-600">(The Forgetting Curve)</p>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-6 items-end">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="bg-burgundy-700 text-white rounded-lg shadow-lg p-4 w-32 h-40 flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div class="border-t-2 border-white/30 w-16 my-1"></div>
                                <p class="text-lg font-bold">DAY 1</p>
                                <p class="text-xs">(Review)</p>
                            </div>
                        </div>

                        <svg class="w-8 h-8 text-burgundy-500 transform rotate-90 md:rotate-0 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>

                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="bg-burgundy-700 text-white rounded-lg shadow-lg p-4 w-32 h-40 flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div class="border-t-2 border-white/30 w-16 my-1"></div>
                                <p class="text-lg font-bold">DAY 3</p>
                                <p class="text-xs">(Review)</p>
                            </div>
                        </div>

                        <svg class="w-8 h-8 text-burgundy-500 transform rotate-90 md:rotate-0 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>

                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="bg-burgundy-700 text-white rounded-lg shadow-lg p-4 w-32 h-40 flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div class="border-t-2 border-white/30 w-16 my-1"></div>
                                <p class="text-lg font-bold">WEEK 1</p>
                                <p class="text-xs">(Review)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <svg class="w-12 h-12 text-burgundy-600 transform rotate-90 md:rotate-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </div>

                <div class="flex flex-col items-center">
                    <div class="bg-green-800 text-white rounded-2xl shadow-xl p-8 w-56 h-56 flex flex-col items-center justify-center relative">
                        <div class="absolute -top-22 w-40 h-40">
                            <img src="{{ asset('img/homepage/grape-bucket.png') }}" alt="Grape Basket" class="w-full h-full object-contain">
                        </div>
                        <h3 class="text-2xl font-bold text-center mt-4 mb-4">LONG-TERM<br>MEMORY</h3>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center max-w-3xl mx-auto">
                <p class="text-gray-700 text-lg">By reviewing at scientifically optimal intervals, wine knowledge moves from short-term memory into permanent long-term retention.</p>
            </div>
        </div>
    </section>

    <section class="py-20 bg-cream-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-center text-burgundy-900">What You'll Learn</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <img src="{{ asset('img/defaults/1.jpg') }}" alt="Key Grape Varieties" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Key Grape Varieties</h3>
                    <p>Learn the principal grape varieties, their characteristics, and the regions they are grown.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <img src="{{ asset('img/homepage/production.jpg') }}" alt="Wine Production Methods" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Wine Production Methods</h3>
                    <p>Understand fermentation, aging, and production techniques that create different wine styles.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <img src="{{ asset('img/homepage/tasting.jpg') }}" alt="Food & Wine Pairing" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Food & Wine Pairing</h3>
                    <p>Discover the principles of food and wine pairing to enhance your dining experience.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <img src="{{ asset('img/homepage/regions.jpg') }}" alt="Global Wine Regions" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Global Wine Regions</h3>
                    <p>Explore key wine-producing regions of the world and their wine styles.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <img src="{{ asset('img/homepage/service.jpg') }}" alt="Wine Service & Storage" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Wine Service & Storage</h3>
                    <p>Master proper serving temperatures, glassware selection, and optimal storage conditions.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md text-center">
                    <img src="{{ asset('img/homepage/labels.jpg') }}" alt="Label Terminology" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Label Terminology</h3>
                    <p>Learn to decipher wine labels and understand common terminology.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-burgundy-900">Ready to Master Wine?</h2>
            <p class="text-xl text-gray-700 mb-8">Start learning wine today with our proven spaced repetition method. Perfect preparation for WSET exams.</p>
            <a href="{{ route('register') }}" class="bg-burgundy-500 text-white px-10 py-4 rounded-lg text-xl font-semibold hover:bg-burgundy-600 transition inline-block">Get Started Today</a>
        </div>
    </section>

    <footer class="bg-burgundy-900 text-white py-12">
        <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <x-logo class="w-8 h-8 fill-current" />
                    <h3 class="text-xl font-bold">VinoRecall</h3>
                </div>
                <p>Your path to wine mastery through spaced repetition.</p>
            </div>
            <div>
                <h4 class="font-bold mb-4">Contact Us</h4>
                <ul>
                    <li><a href="#" class="hover:text-burgundy-200">Email</a></li>
                    <li><a href="#" class="hover:text-burgundy-200">Support</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Links</h4>
                <ul>
                    <li><a href="#" class="hover:text-burgundy-200">About Us</a></li>
                    <li><a href="#" class="hover:text-burgundy-200">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-burgundy-200">Privacy Policy</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold mb-4">Social</h4>
                <div class="flex space-x-4">
                    <a href="#" class="hover:text-burgundy-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.407.593 24 1.325 24H12.82v-9.294H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.593 1.323-1.325V1.325C24 .593 23.407 0 22.675 0z"/></svg>
                    </a>
                    <a href="#" class="hover:text-burgundy-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="hover:text-burgundy-200">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.012 3.584-.07 4.85c-.148 3.252-1.691 4.771-4.919 4.919-1.265.058-1.645.069-4.85.069s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.012-3.584.07-4.85c.148-3.252 1.691-4.771 4.919-4.919 1.265-.058 1.645-.069 4.85-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</x-layout>