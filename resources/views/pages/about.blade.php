<x-layout>
    <section class="py-16 bg-cream-50 min-h-[60vh]">
        <div class="container mx-auto px-6 max-w-4xl">
            <h1 class="text-4xl font-bold text-burgundy-900 mb-8 font-heading">About VinoRecall</h1>

            <div class="flex flex-col md:flex-row gap-10 items-start">
                <div class="md:w-1/3 flex-shrink-0">
                    <img src="{{ asset('img/me.png') }}" alt="The person behind VinoRecall" class="rounded-2xl shadow-lg w-full max-w-xs mx-auto">
                </div>

                <div class="md:w-2/3 space-y-6 text-gray-700 text-lg leading-relaxed">
                    <p>
                        Hello!
                    </p>
                    <p>
                        I'm Dan, the person behind VinoRecall - part wine nerd in training, part web dev, who thought, "Hey, I should probably actually learn what I'm drinking."
                    </p>

                    <p>
                        I started this site to help me get through my WSET journey (starting at Level 1 and slowly climbing). I've been building websites for years and drinking wine for... also years... so it just made sense to mash those two things together into something vaguely productive.
                    </p>

                    <p>
                        VinoRecall is very much a work in progress - I'm adding study decks and content as I go, learning the world of wine one slightly-confusing concept at a time. If things look a bit unfinished... that's because they are. I'm learning as I build.
                    </p>

                    <p>
                        If you'd like to help out - maybe by adding some study decks, sharing course-style content, or teaming up as a fellow wine enthusiast/nerd - I'd genuinely love that.
                    </p>

                    <p class="pt-4">
                        <span class="font-semibold text-burgundy-800">Say hi:</span>
                        <a href="mailto:hello@vinorecall.com" class="text-burgundy-600 hover:text-burgundy-800 underline">hello@vinorecall.com</a>
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-layout>
