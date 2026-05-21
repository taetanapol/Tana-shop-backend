@php
    $record = $schemaComponent->getRecord();
    $images = [];
    if ($record) {
        $images = $record->images ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }
    }
@endphp

@if(!empty($images))
    <div class="space-y-3" 
         x-data="{
             activeSlide: 0,
             images: {{ json_encode(array_map(fn($img) => \Illuminate\Support\Facades\Storage::disk('public')->url($img), $images)) }},
             lightbox: false,
             next() {
                 this.activeSlide = (this.activeSlide + 1) % this.images.length;
             },
             prev() {
                 this.activeSlide = (this.activeSlide - 1 + this.images.length) % this.images.length;
             }
         }"
    >
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">พรีวิวรูปภาพสินค้า</span>
        
        <!-- Main Carousel Frame -->
        <div class="relative w-full aspect-[4/3] bg-gray-950 rounded-xl overflow-hidden group shadow-md border border-gray-200 dark:border-gray-800">
            <template x-for="(image, index) in images" :key="index">
                <div x-show="activeSlide === index"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute inset-0 flex items-center justify-center bg-gray-900/40 dark:bg-black/60"
                >
                    <img :src="image" alt="Product Image" class="w-full h-full object-contain cursor-zoom-in" @click="lightbox = true">
                </div>
            </template>
            
            <!-- Slide Controls -->
            <template x-if="images.length > 1">
                <div>
                    <!-- Prev -->
                    <button @click.prevent="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-md opacity-0 group-hover:opacity-100 focus:opacity-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <!-- Next -->
                    <button @click.prevent="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-md opacity-0 group-hover:opacity-100 focus:opacity-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </template>

            <!-- Overlay Indicators -->
            <template x-if="images.length > 1">
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 bg-black/20 px-3 py-1.5 rounded-full backdrop-blur-sm">
                    <template x-for="(image, index) in images" :key="index">
                        <button @click.prevent="activeSlide = index"
                                :class="activeSlide === index ? 'bg-white w-4 scale-110' : 'bg-white/50 w-2 hover:bg-white/80'"
                                class="h-2 rounded-full transition-all duration-300">
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <!-- Thumbnails Strip -->
        <template x-if="images.length > 1">
            <div class="flex gap-2 overflow-x-auto py-1 scrollbar-none">
                <template x-for="(image, index) in images" :key="index">
                    <button @click.prevent="activeSlide = index" 
                            :class="activeSlide === index ? 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-900 opacity-100 scale-105' : 'opacity-70 hover:opacity-100'"
                            class="relative w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 transition duration-200 shadow-sm">
                        <img :src="image" alt="Thumbnail" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </template>

        <!-- Fullscreen Lightbox Modal -->
        <template x-if="lightbox">
            <div class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/95 backdrop-blur-sm"
                 x-transition
                 @keydown.escape.window="lightbox = false"
            >
                <!-- Close Button -->
                <button @click="lightbox = false" class="absolute top-6 right-6 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Fullscreen Image -->
                <div class="relative max-w-5xl max-h-[85vh] w-full px-4 flex items-center justify-center" @click.away="lightbox = false">
                    <img :src="images[activeSlide]" alt="Zoomed Product Image" class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
                    
                    <!-- Lightbox Navigation -->
                    <template x-if="images.length > 1">
                        <div>
                            <button @click.prevent="prev()" class="absolute left-6 w-14 h-14 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition backdrop-blur-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click.prevent="next()" class="absolute right-6 w-14 h-14 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition backdrop-blur-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- Position indicator -->
                <div class="absolute bottom-6 text-white/70 text-sm font-medium bg-black/40 px-3 py-1 rounded-full backdrop-blur-sm">
                    <span x-text="activeSlide + 1"></span> / <span x-text="images.length"></span>
                </div>
            </div>
        </template>
    </div>
@endif
