<x-layout>
    <section class="relative min-h-[600px] flex items-center font-heading">
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
    </section>

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
</x-layout>
