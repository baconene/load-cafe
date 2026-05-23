<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{ id: string }>();

const menuItems = [
    {
        emoji: '☕',
        name: 'Signature Cold Brew',
        price: '99.00',
        description: 'House-brewed for 24 hours for a smooth, bold flavor with zero bitterness. Served over ice with a hint of cream — the perfect all-day pick-me-up.',
        detail: 'Low acidity · Served iced'
    },
    {
        emoji: '🥛',
        name: 'Spanish Latte',
        price: '109.00',
        description: 'Rich double espresso meets sweet condensed milk — creamy, indulgent, and perfectly balanced. Available hot or iced.',
        detail: 'Hot or iced · Customer favorite'
    },
    {
        emoji: '🍵',
        name: 'Matcha Latte',
        price: '129.00',
        description: 'Premium Japanese matcha whisked smooth and served with steamed oat milk. Earthy, creamy, and naturally energizing.',
        detail: 'Oat milk · Hot or iced'
    },
    {
        emoji: '🫗',
        name: 'Iced Americano',
        price: '79.00',
        description: 'Double shot of rich espresso poured over ice — bold, clean, and refreshing. The everyday essential for coffee lovers.',
        detail: 'Double shot · Always iced'
    }
];
</script>

<template>
    <Head title="Load Cafe - Fresh Menu" />

    <div class="min-h-screen bg-[#0d0906] text-white">
        <!-- Coffee glow background -->
        <div class="absolute inset-0 opacity-20 pointer-events-none"
            style="background: radial-gradient(ellipse at center bottom, rgba(146,64,14,0.6) 0%, transparent 70%);"></div>

        <!-- Content -->
        <div class="relative z-10">
            <!-- Header -->
            <div class="border-b-4 border-amber-700 bg-gradient-to-r from-stone-950 via-amber-950/30 to-stone-950 px-4 py-12 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="flex items-center justify-between mb-8 gap-8">
                        <!-- Branding -->
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-amber-600 text-center">
                                <div class="text-xs font-bold text-amber-500 leading-tight">
                                    <div>LOAD</div>
                                    <div class="text-[10px]">CAFE</div>
                                </div>
                            </div>
                        </div>

                        <!-- Headings -->
                        <div class="flex-1 text-center">
                            <h1 class="text-5xl sm:text-6xl font-black tracking-wider text-white mb-4">
                                FRESH BREWS DAILY
                            </h1>
                            <p class="text-3xl text-amber-400 italic font-light tracking-wide">
                                Coffee & Beverage Selections
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="px-4 py-16 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="grid gap-12 lg:grid-cols-2">
                        <div v-for="(item, index) in menuItems.slice(0, 2)" :key="index" class="group">
                            <div class="flex gap-6 items-start">
                                <!-- Drink icon -->
                                <div class="flex-shrink-0 w-40 h-40 bg-gradient-to-br from-amber-950 via-amber-900 to-stone-900 rounded-full flex items-center justify-center shadow-lg border-4 border-amber-700/60">
                                    <div class="text-6xl">{{ item.emoji }}</div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1">
                                    <div class="flex items-baseline gap-3 mb-2">
                                        <h2 class="text-2xl font-bold text-white">{{ item.name }}</h2>
                                        <span class="text-2xl font-bold text-amber-400">₱{{ item.price }}</span>
                                    </div>
                                    <p class="text-gray-300 text-sm leading-relaxed italic mb-3">
                                        {{ item.description }}
                                    </p>
                                    <p class="text-xs text-amber-500 font-semibold">
                                        {{ item.detail }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Row -->
                    <div class="grid gap-12 lg:grid-cols-2 mt-16">
                        <div v-for="(item, index) in menuItems.slice(2)" :key="index + 2" class="group">
                            <div class="flex gap-6 items-start">
                                <!-- Drink icon -->
                                <div class="flex-shrink-0 w-40 h-40 bg-gradient-to-br from-amber-950 via-amber-900 to-stone-900 rounded-full flex items-center justify-center shadow-lg border-4 border-amber-700/60">
                                    <div class="text-6xl">{{ item.emoji }}</div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1">
                                    <div class="flex items-baseline gap-3 mb-2">
                                        <h2 class="text-2xl font-bold text-white">{{ item.name }}</h2>
                                        <span class="text-2xl font-bold text-amber-400">₱{{ item.price }}</span>
                                    </div>
                                    <p class="text-gray-300 text-sm leading-relaxed italic mb-3">
                                        {{ item.description }}
                                    </p>
                                    <p class="text-xs text-amber-500 font-semibold">
                                        {{ item.detail }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer CTA -->
            <div class="border-t-4 border-amber-700 bg-gradient-to-r from-stone-950 via-amber-950/30 to-stone-950 px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl text-center">
                    <p class="text-2xl font-bold text-amber-400 tracking-wide">
                        Life's too short for bad coffee — load up on the good stuff.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
