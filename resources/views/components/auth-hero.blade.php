{{-- Left Section (70%) - Hero Section --}}
<div class="relative w-[70%] hidden lg:flex flex-col justify-between overflow-hidden bg-gray-50 dark:bg-gray-900 p-6" x-data="heroSlideshow()">

    {{-- Hero Section with Gradient Background --}}
    <div class="relative w-full h-full rounded-2xl overflow-hidden">
        
        {{-- Gradient Mesh Background - Light Mode --}}
        <div class="light:block dark:hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-100 via-blue-100 to-indigo-100"></div>
            <div class="absolute top-0 left-0 w-full h-full opacity-40">
                <div class="absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-blue-300/30 to-purple-300/30 rounded-full blur-3xl animate-float-1"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-l from-indigo-300/30 to-pink-300/30 rounded-full blur-3xl animate-float-2"></div>
                <div class="absolute top-1/2 left-1/3 w-80 h-80 bg-gradient-to-br from-cyan-300/25 to-blue-300/25 rounded-full blur-3xl animate-float-3"></div>
            </div>
        </div>
        
        {{-- Gradient Mesh Background - Dark Mode --}}
        <div class="hidden dark:block">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-800 via-slate-800/30 to-indigo-800/20"></div>
            <div class="absolute top-0 left-0 w-full h-full opacity-40">
                <div class="absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-blue-500/30 to-purple-500/30 rounded-full blur-3xl animate-float-1"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-l from-indigo-500/30 to-pink-500/30 rounded-full blur-3xl animate-float-2"></div>
                <div class="absolute top-1/2 left-1/3 w-80 h-80 bg-gradient-to-br from-cyan-500/25 to-blue-500/25 rounded-full blur-3xl animate-float-3"></div>
            </div>
        </div>
        
        {{-- Top Section: Content Container and Navigation Controls --}}
        <div class="relative z-10 flex w-full h-[22%] justify-end">
            
            {{-- Left Content Container (65% width) --}}
            <div class="flex flex-col justify-start p-12 w-[65%]">
                <div class="flex flex-col justify-start space-y-6 max-w-2xl">
                    
                    {{-- Version Text --}}
                    <div class="text-xs text-gray-600/20 dark:text-gray-500 font-mono">
                        {{ $gitVersion ?? 'v0.3_local' }}
                    </div>
                    
                    {{-- Title --}}
                    <div>
                        <h1 id="heroTitle" class="text-2xl font-bold dark:text-white text-gray-600 -mb-4 transition-all duration-500 leading-tight hero-title">
                            Loading...
                        </h1>
                    </div>

                    {{-- Description --}}
                    <div class="min-h-14 xl:min-h-6">
                        <p id="heroDescription" class="text-xs xl:text-base dark:text-white text-gray-600 transition-all duration-500 leading-relaxed hero-description">
                            Loading...
                        </p>
                    </div>

                    {{-- Slider Navigation --}}
                    <nav class="flex items-center space-x-3 slider-navigation" id="sliderNav" aria-label="Slide navigation">
                        
                        {{-- Slide 1 --}}
                        <button type="button" data-slide="0" aria-label="Go to slide 1" class="relative w-12 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                            <div id="progressBar0" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-gray-200 rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                        </button>

                        {{-- Slide 2 --}}
                        <button type="button" data-slide="1" aria-label="Go to slide 2" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                            <div id="progressBar1" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-gray-200 rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                        </button>

                        {{-- Slide 3 --}}
                        <button type="button" data-slide="2" aria-label="Go to slide 3" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                            <div id="progressBar2" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-gray-200 rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                        </button>

                        {{-- Slide 4 --}}
                        <button type="button" data-slide="3" aria-label="Go to slide 4" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                            <div id="progressBar3" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-white rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                        </button>

                        {{-- Slide 5 --}}
                        <button type="button" data-slide="4" aria-label="Go to slide 5" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                            <div id="progressBar4" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-white rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                        </button>

                        {{-- Slide 6 --}}
                        <button type="button" data-slide="5" aria-label="Go to slide 6" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                            <div id="progressBar5" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-white rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                        </button>

                    </nav>

                </div>
            </div>

            {{-- Right Content Container (35% width) --}}
            <div class="relative flex flex-col justify-start w-[35%]">
                
                {{-- Whats New Button (Top) --}}
                <nav class="flex justify-end" aria-label="Whats new action button">
                    <x-tooltip position="bottom" text="{{ __('login.tooltips.whatsNew') }}">
                        <button type="button"
                                onclick="if (window.showGlobalModal) { window.showGlobalModal('changelog'); }"
                                class="inline-flex items-start cursor-pointer hover:scale-105 transition-transform duration-300"
                                aria-label="What's New">
                            <img src="{{ asset('images/actions/whats-news.png') }}" 
                                 alt="What's New" 
                                 class="h-28 w-auto opacity-80 hover:opacity-100 transition-all duration-300 bounce-bounce whats-new-button" 
                                 loading="eager" 
                                 draggable="false">
                        </button>
                    </x-tooltip>
                </nav>
                
                {{-- Navigation Controls (Bottom) --}}
                <nav class="flex justify-end mt-4 px-12" aria-label="Hero slider navigation">
                    <div class="flex items-center space-x-3">
                        
                        {{-- Previous Button --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.previousSlide')">
                            <button id="prevSlide" 
                                    type="button"
                                    aria-label="Previous slide"
                                    class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                                @svg('heroicon-m-arrow-left', 'w-5 h-5 text-primary-900 transition-colors')
                            </button>
                        </x-tooltip>

                        {{-- Pause/Play Button --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.pausePlaySlide')">
                            <button id="pausePlaySlide" 
                                    type="button"
                                    aria-label="Pause auto-slide"
                                    class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">

                                {{-- Play Icon (shown when paused) --}}
                                <div id="playIcon">
                                    @svg('heroicon-o-play', 'w-4 h-4 text-primary-900 transition-colors')
                                </div>

                                {{-- Pause Icon (shown when playing) --}}
                                <div id="pauseIcon" class="hidden">
                                    @svg('heroicon-o-pause', 'w-4 h-4 text-primary-900 transition-colors')
                                </div>

                            </button>
                        </x-tooltip>
                        
                        {{-- Next Button --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.nextSlide')">
                            <button id="nextSlide" 
                                    type="button"
                                    aria-label="Next slide"
                                    class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                                @svg('heroicon-m-arrow-right', 'w-5 h-5 text-primary-900 transition-colors')
                            </button>
                        </x-tooltip>

                    </div>
                </nav>

            </div>

        </div>

        {{-- Bottom Section: Hero Images --}}
        <div class="absolute bottom-0 left-0 w-full h-[78%] pl-12 hero-image-container">

            {{-- Hero Image Wrapper for Animations --}}
            <div id="heroImageWrapper" class="relative w-full h-full">
                <img id="heroImage"
                     src="{{ asset('images/hero-images/light/01.png') }}"
                     alt="CheQQme Data Center platform showcase"
                     class="w-full h-full object-cover object-center rounded-tl-3xl border-l-8 border-t-8 border-white/50 dark:border-white/10 transition-all duration-500"
                     loading="eager"
                     draggable="false">

                {{-- Play Button Overlay --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <x-tooltip position="top" :text="__('login.tooltips.playVideo')">
                        <div class="relative group">

                            {{-- Animated Rotating Circle --}}
                            <div class="absolute -inset-2 rounded-full border-2 border-dashed group-hover:border-solid border-primary-600/5 border-t-primary-600/80 animate-spin-slow transition-all duration-600"></div>
                            <div class="absolute -inset-2 rounded-full border-2 border-dashed group-hover:border-solid border-primary-600/5 border-b-primary-600/80 animate-spin-slow transition-all duration-600"></div>

                            <button type="button"
                                    @click="openVideoModal()"
                                    class="relative pointer-events-auto w-16 h-16 bg-primary-400/80 hover:bg-primary-400 rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all duration-300 group backdrop-blur-sm"
                                    aria-label="Play video">
                                <x-heroicon-m-play class="w-8 h-8 text-primary-900 ml-1 group-hover:scale-110 transition-transform duration-200" />
                            </button>

                        </div>
                    </x-tooltip>
                </div>

            </div>
            
        </div>

    </div>

    </div>

    {{-- Video Modal Script --}}
    <script>
        function heroSlideshow() {
            return {
                currentSlide: 0,
                videoUrls: [
                    '{{ asset("videos/vid_slide_01.mp4") }}', // Slide 1
                    '{{ asset("videos/vid_slide_02.mp4") }}', // Slide 2
                    '{{ asset("videos/vid_slide_03.mp4") }}', // Slide 3
                    '{{ asset("videos/resources_tutorial_video01.mp4") }}', // Slide 4
                    '{{ asset("videos/resources_tutorial_video01.mp4") }}', // Slide 5
                    '{{ asset("videos/resources_tutorial_video01.mp4") }}', // Slide 6
                ],

                init() {
                    // Listen for slide changes from HeroSlider
                    document.addEventListener('heroSlideChanged', (event) => {
                        this.currentSlide = event.detail.slideIndex;
                    });
                },

                getCurrentTitle() {
                    if (window.heroSliderLang) {
                        const slideNumber = this.currentSlide + 1;
                        return window.heroSliderLang[`title${slideNumber}`] || 'Product Demo';
                    }
                    return 'Product Demo';
                },

                getCurrentDescription() {
                    if (window.heroSliderLang) {
                        const slideNumber = this.currentSlide + 1;
                        return window.heroSliderLang[`description${slideNumber}`] || 'Watch how our platform works';
                    }
                    return 'Watch how our platform works';
                },

                openVideoModal() {
                    if (window.showGlobalModal) {
                        // Pass the current slide's video URL, title, and description
                        window.showGlobalModal('heroVideo', {
                            videoUrl: this.videoUrls[this.currentSlide],
                            title: this.getCurrentTitle(),
                            description: this.getCurrentDescription()
                        });
                    }
                }
            }
        }
    </script>

{{-- Sticky Version Text for Responsive (1024px and below) --}}
<div class="version-text-sticky hidden">
    <div class="text-[11px] text-gray-600/20 dark:text-gray-500 pt-1 font-mono">
        {{ $gitVersion ?? 'v0.3_local' }}
    </div>
</div>

{{-- Sticky What's New Button for Responsive (1024px and below) --}}
<div class="whats-new-sticky hidden">
    <x-tooltip position="bottom" text="{{ __('login.tooltips.whatsNew') }}">
        <button type="button"
                onclick="if (window.showGlobalModal) { window.showGlobalModal('changelog'); }"
                class="inline-flex items-start cursor-pointer"
                aria-label="What's New">
            <img src="{{ asset('images/actions/whats-news.png') }}" 
                 alt="What's New" 
                 class="transition-all duration-300 bounce-bounce" 
                 loading="eager">
        </button>
    </x-tooltip>
</div>
