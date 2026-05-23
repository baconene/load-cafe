<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { dashboard, login } from '@/routes'

withDefaults(defineProps<{ canRegister?: boolean }>(), { canRegister: false })
</script>

<template>
    <Head title="Load Cafe — Your Favorite Cafe Stop" />

    <div class="min-h-screen bg-[#0d0906] text-white font-sans overflow-x-hidden">

        <!-- ── NAV ─────────────────────────────────────────── -->
        <nav class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-6 py-4 bg-black/60 backdrop-blur-sm border-b border-amber-900/40">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full border-2 border-amber-500 flex items-center justify-center">
                    <span class="text-[9px] font-black text-amber-400 leading-tight text-center">LO<br>AD</span>
                </div>
                <span class="text-lg font-black tracking-widest text-white">LOAD <span class="text-amber-500">CAFE</span></span>
            </div>
            <div class="flex items-center gap-3">
                <Link v-if="$page.props.auth?.user" :href="dashboard()"
                    class="rounded-full border border-amber-500 px-5 py-1.5 text-sm font-semibold text-amber-400 hover:bg-amber-500 hover:text-black transition-colors">
                    Dashboard
                </Link>
                <Link v-else :href="login()"
                    class="rounded-full bg-amber-700 px-5 py-1.5 text-sm font-bold text-white hover:bg-amber-600 transition-colors">
                    Staff Login
                </Link>
            </div>
        </nav>

        <!-- ── HERO ───────────────────────────────────────── -->
        <section class="relative min-h-screen flex items-center justify-center pt-20 pb-16 px-6 overflow-hidden">
            <!-- coffee glow bg -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[900px] h-[500px] rounded-full opacity-25"
                    style="background: radial-gradient(ellipse, #92400e 0%, #451a03 30%, transparent 70%); filter: blur(60px);"></div>
                <div class="absolute top-1/3 right-1/4 w-80 h-80 rounded-full opacity-10"
                    style="background: radial-gradient(circle, #d97706 0%, transparent 70%); filter: blur(40px);"></div>
            </div>

            <div class="relative z-10 max-w-5xl mx-auto text-center">
                <!-- badge -->
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-800 bg-amber-950/60 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-amber-400 mb-8">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    Now Brewing · Your Daily Coffee Fix
                </div>

                <!-- headline -->
                <h1 class="text-6xl sm:text-7xl md:text-8xl font-black leading-none tracking-tight mb-6">
                    <span class="block text-white">THE</span>
                    <span class="block" style="background: linear-gradient(90deg, #d97706, #fbbf24, #d97706); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                        PERFECT BREW
                    </span>
                    <span class="block text-white text-4xl sm:text-5xl font-light tracking-widest mt-2">EXPERIENCE</span>
                </h1>

                <p class="max-w-xl mx-auto text-lg text-gray-300 leading-relaxed mb-10">
                    Rich espresso, smooth cold brews, creamy lattes — crafted with care every single day.<br>
                    <span class="text-amber-400 font-semibold">Your daily cup, done right.</span>
                </p>

                <!-- price badge -->
                <div class="inline-block bg-amber-800 text-white font-black text-4xl px-8 py-3 rounded-2xl shadow-lg shadow-amber-900/50 mb-10">
                    ₱79 <span class="text-lg font-bold">/ cup</span>
                </div>

                <!-- coffee cup visual -->
                <div class="flex justify-center items-end gap-3 mt-2 mb-12 select-none" aria-hidden="true">
                    <div class="relative flex flex-col items-center">
                        <!-- steam lines -->
                        <div class="flex gap-3 mb-3">
                            <div class="w-0.5 h-6 rounded-full bg-white/20 steam-line"></div>
                            <div class="w-0.5 h-9 rounded-full bg-white/30 steam-line" style="animation-delay: 0.4s"></div>
                            <div class="w-0.5 h-5 rounded-full bg-white/20 steam-line" style="animation-delay: 0.8s"></div>
                        </div>
                        <!-- cup body -->
                        <div class="w-28 h-28 rounded-b-3xl rounded-t-md bg-gradient-to-br from-amber-950 via-amber-900 to-stone-900 border-2 border-amber-700/60 flex items-center justify-center shadow-2xl shadow-amber-950/80">
                            <span class="text-6xl">☕</span>
                        </div>
                        <!-- saucer -->
                        <div class="w-36 h-2.5 rounded-full bg-amber-900/50 mt-1 blur-sm"></div>
                    </div>
                </div>

                <p class="text-xs uppercase tracking-widest text-gray-500">Scroll to discover our full menu</p>
            </div>
        </section>

        <!-- ── SIGNATURE DRINK ─────────────────────────────── -->
        <section class="py-24 px-6 border-t border-amber-900/30">
            <div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-16 items-center">
                <!-- visual -->
                <div class="relative flex items-center justify-center">
                    <div class="absolute w-72 h-72 rounded-full opacity-30"
                        style="background: radial-gradient(circle, #b45309, transparent 70%); filter: blur(50px);"></div>
                    <div class="relative z-10 w-56 h-56 rounded-full bg-gradient-to-br from-amber-950 via-amber-900 to-stone-950 border-4 border-amber-700 flex items-center justify-center shadow-2xl shadow-amber-950/60">
                        <span class="text-8xl select-none">☕</span>
                    </div>
                    <div class="absolute top-4 right-8 bg-amber-700 text-white text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider rotate-12 shadow-lg">
                        Best Seller
                    </div>
                </div>

                <!-- text -->
                <div>
                    <p class="text-amber-500 text-xs font-bold uppercase tracking-widest mb-3">Signature Drink</p>
                    <h2 class="text-4xl sm:text-5xl font-black mb-5 leading-tight">
                        Signature<br>
                        <span class="text-amber-400">Cold Brew</span>
                    </h2>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        House-brewed for 24 hours for a smooth, bold flavor with zero bitterness. Served over ice with a hint of cream — the perfect all-day pick-me-up.
                    </p>
                    <div class="flex flex-wrap gap-3 mb-8">
                        <span class="rounded-full bg-amber-950 border border-amber-800 px-4 py-1.5 text-sm text-amber-300">⏱ 24-hr Brew</span>
                        <span class="rounded-full bg-amber-950 border border-amber-800 px-4 py-1.5 text-sm text-amber-300">🧊 Served Iced</span>
                        <span class="rounded-full bg-amber-950 border border-amber-800 px-4 py-1.5 text-sm text-amber-300">Low Acidity</span>
                    </div>
                    <div class="text-5xl font-black text-amber-400">₱99</div>
                </div>
            </div>
        </section>

        <!-- ── MENU GRID ────────────────────────────────────── -->
        <section class="py-24 px-6 bg-gradient-to-b from-transparent to-black/40">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-14">
                    <p class="text-amber-500 text-xs font-bold uppercase tracking-widest mb-3">Fresh Brews Daily</p>
                    <h2 class="text-4xl font-black">Our Menu</h2>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div v-for="item in [
                        { emoji: '☕', name: 'Signature Cold Brew', price: '₱99', desc: 'House-brewed 24 hours for bold, smooth flavor with zero bitterness. The Load Cafe classic.', tag: 'Star' },
                        { emoji: '🥛', name: 'Spanish Latte', price: '₱109', desc: 'Rich double espresso meets sweet condensed milk — creamy, indulgent, and perfectly balanced.', tag: null },
                        { emoji: '🍵', name: 'Matcha Latte', price: '₱129', desc: 'Premium Japanese matcha whisked with steamed oat milk. Earthy, smooth, and energizing.', tag: null },
                        { emoji: '🫗', name: 'Iced Americano', price: '₱79', desc: 'Double shot of espresso over ice — bold, clean, and refreshing. The everyday essential.', tag: null },
                    ]" :key="item.name"
                        class="group relative flex gap-5 items-start rounded-2xl border border-white/5 p-5 hover:border-amber-700/60 hover:bg-amber-950/20 transition-all duration-300"
                        style="background: rgba(255,255,255,0.02)">
                        <div v-if="item.tag" class="absolute top-3 right-3 bg-amber-700 text-white text-[10px] font-black px-2 py-0.5 rounded-full uppercase">
                            {{ item.tag }}
                        </div>
                        <div class="shrink-0 w-16 h-16 rounded-xl bg-gradient-to-br from-amber-950 to-stone-900 border border-amber-800/40 flex items-center justify-center text-3xl shadow-inner">
                            {{ item.emoji }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-2 mb-1">
                                <h3 class="font-bold text-white text-sm leading-tight">{{ item.name }}</h3>
                                <span class="shrink-0 text-lg font-black text-amber-400">{{ item.price }}</span>
                            </div>
                            <p class="text-xs text-gray-400 leading-relaxed">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-500 mt-6">All drinks available hot or iced. Ask about our seasonal specials.</p>
            </div>
        </section>

        <!-- ── OUR STORY ───────────────────────────────────── -->
        <section class="py-24 px-6 border-t border-amber-900/30">
            <div class="max-w-5xl mx-auto text-center mb-16">
                <p class="text-amber-500 text-xs font-bold uppercase tracking-widest mb-3">Our Story</p>
                <h2 class="text-4xl sm:text-5xl font-black mb-6 leading-tight">
                    Where Every<br>
                    <span class="text-amber-400">Sip Matters</span>
                </h2>
                <p class="max-w-2xl mx-auto text-gray-300 leading-relaxed text-lg">
                    Load Cafe is your <strong class="text-white">neighborhood coffee shop</strong> — a place to slow down, recharge, and enjoy a cup made with care.
                    No rush. Just <strong class="text-amber-400">great coffee, great vibes</strong>, every single day.
                </p>
            </div>

            <div class="max-w-5xl mx-auto grid sm:grid-cols-3 gap-6">
                <div v-for="card in [
                    { icon: '📍', title: 'Convenient Locations', body: 'Every Load Cafe is set up where you need it — easy to find, easy to love, impossible to miss.' },
                    { icon: '☕', title: 'Freshly Brewed Daily', body: 'Every cup is made to order. No batch brewing. Just fresh, quality coffee crafted just for you.' },
                    { icon: '💸', title: 'Honest Prices', body: 'Premium coffee without the premium price tag. Great drinks starting at ₱79 — quality you can afford.' },
                ]" :key="card.title"
                    class="rounded-2xl border border-white/5 p-6 text-center hover:border-amber-700/50 transition-colors"
                    style="background: rgba(255,255,255,0.025)">
                    <div class="text-5xl mb-5">{{ card.icon }}</div>
                    <h3 class="font-bold text-white mb-3">{{ card.title }}</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">{{ card.body }}</p>
                </div>
            </div>
        </section>

        <!-- ── TAGLINE BANNER ──────────────────────────────── -->
        <section class="py-20 px-6 text-center relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none"
                style="background: linear-gradient(180deg, transparent, rgba(120,53,15,0.08), transparent);"></div>
            <div class="relative z-10 max-w-3xl mx-auto">
                <p class="text-3xl sm:text-4xl font-black leading-tight text-white mb-4">
                    "Life's too short for bad coffee —<span class="text-amber-400"> load up on the good stuff.</span>"
                </p>
                <p class="text-gray-400 text-sm uppercase tracking-widest">Load Cafe · Fresh Brews Daily</p>
            </div>
        </section>

        <!-- ── FOOTER ─────────────────────────────────────── -->
        <footer class="border-t border-amber-900/30 px-6 py-8 text-center">
            <div class="flex items-center justify-center gap-3 mb-3">
                <div class="h-8 w-8 rounded-full border-2 border-amber-700 flex items-center justify-center">
                    <span class="text-[8px] font-black text-amber-500 leading-tight text-center">LO<br>AD</span>
                </div>
                <span class="font-black tracking-widest text-white">LOAD <span class="text-amber-500">CAFE</span></span>
            </div>
            <p class="text-xs text-gray-600">Your favorite cafe, near you.</p>
        </footer>

    </div>
</template>

<style scoped>
.bg-white\/3 { background-color: rgba(255,255,255,0.03); }
.steam-line {
    animation: steam 2s ease-in-out infinite;
}
@keyframes steam {
    0%, 100% { opacity: 0.1; transform: translateY(0); }
    50% { opacity: 0.4; transform: translateY(-8px); }
}
</style>
