<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { dashboard, login } from '@/routes'
import { ref, onMounted, onUnmounted } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import api from '@/utils/api'

gsap.registerPlugin(ScrollTrigger)

withDefaults(defineProps<{ canRegister?: boolean }>(), { canRegister: false })

interface Category {
    name: string
}

interface Product {
    id: number
    name: string
    description: string | null
    price: string | number
    image: string | null
    category: Category | null
    is_active: boolean
}

const products = ref<Product[]>([])
const loadingProducts = ref(true)
const featuredProduct = ref<Product | null>(null)

function getProductIcon(product: Product): string {
    const n = product.name.toLowerCase()
    const c = (product.category?.name ?? '').toLowerCase()
    if (n.includes('espresso') || n.includes('americano') || n.includes('ristretto')) return '☕'
    if (n.includes('latte') || n.includes('cappuccino') || n.includes('flat white') || n.includes('macchiato')) return '🥛'
    if (n.includes('mocha') || n.includes('coffee') || n.includes('brew') || n.includes('cold brew') || n.includes('pour')) return '☕'
    if (n.includes('matcha') || n.includes('green tea') || n.includes('chai')) return '🍵'
    if (n.includes('tea') || c.includes('tea')) return '🍵'
    if (n.includes('cake') || n.includes('muffin') || n.includes('cookie') || n.includes('brownie')) return '🎂'
    if (n.includes('pastry') || n.includes('croissant') || n.includes('danish') || n.includes('scone')) return '🥐'
    if (n.includes('sandwich') || n.includes('panini') || n.includes('wrap') || c.includes('food')) return '🥪'
    if (n.includes('frappe') || n.includes('frappuccino') || n.includes('blended') || n.includes('shake') || n.includes('smoothie')) return '🥤'
    if (n.includes('juice') || n.includes('refresher') || n.includes('lemonade')) return '🍹'
    return '☕'
}

function formatPrice(price: string | number): string {
    const num = typeof price === 'string' ? parseFloat(price) : price
    return `₱${num.toFixed(2)}`
}

function getImageUrl(image: string | null): string | null {
    if (!image) return null
    if (image.startsWith('http')) return image
    return `/storage/${image}`
}

async function loadProducts() {
    try {
        const response = await api.get('/api/v1/products')
        const raw = response.data
        const list: Product[] = Array.isArray(raw) ? raw : (raw?.data ?? [])
        products.value = list
        featuredProduct.value = list[0] ?? null
    } catch {
        products.value = []
    } finally {
        loadingProducts.value = false
    }
}

onMounted(async () => {
    await loadProducts()

    // ── Hero entrance timeline ──────────────────────────────
    const heroTl = gsap.timeline({ delay: 0.15 })
    heroTl
        .fromTo('.hero-badge',
            { y: -20, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.7, ease: 'power3.out' })
        .fromTo('.hero-line',
            { y: 80, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.95, stagger: 0.14, ease: 'power4.out' }, '-=0.35')
        .fromTo('.hero-sub',
            { y: 28, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.75, ease: 'power3.out' }, '-=0.55')
        .fromTo('.hero-actions',
            { y: 20, opacity: 0 },
            { y: 0, opacity: 1, duration: 0.6, ease: 'power3.out' }, '-=0.45')
        .fromTo('.hero-visual',
            { scale: 0.88, opacity: 0 },
            { scale: 1, opacity: 1, duration: 1.1, ease: 'power3.out' }, '-=0.8')

    // ── Steam particles ──────────────────────────────────────
    gsap.set('.steam-a', { y: 0, opacity: 0.7, scaleX: 1 })
    gsap.set('.steam-b', { y: 0, opacity: 0.5, scaleX: 1 })
    gsap.set('.steam-c', { y: 0, opacity: 0.6, scaleX: 1 })
    gsap.to('.steam-a', { y: -70, opacity: 0, duration: 2.4, repeat: -1, ease: 'sine.inOut', delay: 0 })
    gsap.to('.steam-b', { y: -55, opacity: 0, duration: 2.0, repeat: -1, ease: 'sine.inOut', delay: 0.5 })
    gsap.to('.steam-c', { y: -65, opacity: 0, duration: 2.6, repeat: -1, ease: 'sine.inOut', delay: 1.0 })

    // ── Cup float ────────────────────────────────────────────
    gsap.to('.cup-float', {
        y: -14,
        duration: 3.2,
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut',
    })

    // ── Ambient glow pulse ───────────────────────────────────
    gsap.to('.hero-glow', {
        scale: 1.12,
        opacity: 0.12,
        duration: 4,
        repeat: -1,
        yoyo: true,
        ease: 'sine.inOut',
    })

    // ── Scroll-triggered reveals ──────────────────────────────
    gsap.utils.toArray<HTMLElement>('.reveal-up').forEach((el) => {
        gsap.fromTo(el,
            { y: 50, opacity: 0 },
            {
                y: 0, opacity: 1, duration: 0.8, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 87%', toggleActions: 'play none none none' },
            },
        )
    })

    gsap.utils.toArray<HTMLElement>('.reveal-card').forEach((el, i) => {
        gsap.fromTo(el,
            { y: 55, opacity: 0, scale: 0.96 },
            {
                y: 0, opacity: 1, scale: 1, duration: 0.65, ease: 'power3.out',
                delay: (i % 3) * 0.1,
                scrollTrigger: { trigger: el, start: 'top 90%', toggleActions: 'play none none none' },
            },
        )
    })

    gsap.utils.toArray<HTMLElement>('.reveal-left').forEach((el) => {
        gsap.fromTo(el,
            { x: -55, opacity: 0 },
            {
                x: 0, opacity: 1, duration: 0.9, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 83%', toggleActions: 'play none none none' },
            },
        )
    })

    gsap.utils.toArray<HTMLElement>('.reveal-right').forEach((el) => {
        gsap.fromTo(el,
            { x: 55, opacity: 0 },
            {
                x: 0, opacity: 1, duration: 0.9, ease: 'power3.out',
                scrollTrigger: { trigger: el, start: 'top 83%', toggleActions: 'play none none none' },
            },
        )
    })

    // ── Feature cards stagger ────────────────────────────────
    const feats = gsap.utils.toArray<HTMLElement>('.feat-card')
    if (feats.length) {
        gsap.fromTo(feats,
            { y: 40, opacity: 0 },
            {
                y: 0, opacity: 1, duration: 0.6, stagger: 0.12, ease: 'power3.out',
                scrollTrigger: { trigger: feats[0], start: 'top 85%', toggleActions: 'play none none none' },
            },
        )
    }

    // ── Quote banner text ────────────────────────────────────
    gsap.fromTo('.quote-text',
        { letterSpacing: '-0.08em', opacity: 0 },
        {
            letterSpacing: '-0.01em', opacity: 1, duration: 1.1, ease: 'power2.out',
            scrollTrigger: { trigger: '.quote-text', start: 'top 82%' },
        },
    )
})

onUnmounted(() => {
    ScrollTrigger.getAll().forEach(t => t.kill())
})
</script>

<template>
    <Head title="Load Café — Premium Coffee Experience" />

    <div class="min-h-screen bg-[#0c1512] text-[#F2F0EB] font-sans overflow-x-hidden">

        <!-- ── NAV ───────────────────────────────────────────── -->
        <nav
            class="fixed top-0 inset-x-0 z-50 flex items-center justify-between px-6 lg:px-12 py-4 border-b border-[#1a3028]/60"
            style="background: rgba(12,21,18,0.88); backdrop-filter: blur(16px);">
            <div class="flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-full bg-[#00704A] flex items-center justify-center shadow-lg shadow-[#00704A]/40">
                    <span class="text-white font-black text-xs tracking-tight select-none">LC</span>
                </div>
                <span class="text-lg font-black tracking-widest text-white">LOAD <span
                        class="text-[#00704A]">CAFÉ</span></span>
            </div>

            <div class="hidden md:flex items-center gap-8 text-sm text-[#B8AFA4]">
                <a href="#menu" class="hover:text-white transition-colors duration-200">Menu</a>
                <a href="#experience" class="hover:text-white transition-colors duration-200">Experience</a>
                <a href="#story" class="hover:text-white transition-colors duration-200">Our Story</a>
            </div>

            <div class="flex items-center gap-3">
                <Link v-if="$page.props.auth?.user" :href="dashboard()"
                    class="rounded-full border border-[#00704A] px-5 py-1.5 text-sm font-semibold text-[#00704A] hover:bg-[#00704A] hover:text-white transition-all duration-200">
                Dashboard
                </Link>
                <Link v-else :href="login()"
                    class="rounded-full bg-[#00704A] px-5 py-1.5 text-sm font-bold text-white hover:bg-[#008f5e] transition-all duration-200 shadow-md shadow-[#00704A]/30">
                Staff Login
                </Link>
            </div>
        </nav>

        <!-- ── HERO ──────────────────────────────────────────── -->
        <section class="relative min-h-screen flex items-center pt-20 pb-16 px-6 lg:px-12 overflow-hidden">

            <!-- Ambient background -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="hero-glow absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full opacity-10"
                    style="background: radial-gradient(circle, #00704A 0%, transparent 65%); filter: blur(90px);"></div>
                <div class="absolute bottom-16 right-1/3 w-80 h-80 rounded-full opacity-8"
                    style="background: radial-gradient(circle, #CBA258 0%, transparent 70%); filter: blur(70px);"></div>
                <!-- Grid lines -->
                <div class="absolute inset-0 opacity-[0.025]"
                    style="background-image: linear-gradient(rgba(0,112,74,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(0,112,74,0.6) 1px, transparent 1px); background-size: 60px 60px;">
                </div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto w-full grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                <!-- Left: Text -->
                <div>
                    <div
                        class="hero-badge inline-flex items-center gap-2 rounded-full border border-[#00704A]/40 bg-[#00704A]/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-[#00704A] mb-8">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#00704A] animate-pulse"></span>
                        Now Brewing · Premium Coffee
                    </div>

                    <h1 class="overflow-hidden mb-6">
                        <span
                            class="hero-line block text-[6rem] sm:text-[7rem] lg:text-[8rem] font-black leading-none tracking-tight text-[#F2F0EB]">SAVOR</span>
                        <span
                            class="hero-line block text-[6rem] sm:text-[7rem] lg:text-[8rem] font-black leading-none tracking-tight text-[#CBA258]">EVERY</span>
                        <span
                            class="hero-line block text-3xl sm:text-4xl font-light tracking-[0.5em] text-[#B8AFA4] mt-2">SIP</span>
                    </h1>

                    <p class="hero-sub max-w-md text-[#B8AFA4] text-lg leading-relaxed mb-10">
                        Premium coffee crafted with care. From ethically sourced beans to every carefully pulled shot,
                        Load Café is where your day begins.
                    </p>

                    <div class="hero-actions flex flex-wrap items-center gap-4">
                        <a href="#menu"
                            class="rounded-full bg-[#00704A] px-8 py-3.5 text-sm font-bold text-white hover:bg-[#008f5e] transition-all duration-300 shadow-lg shadow-[#00704A]/30 hover:shadow-[#00704A]/50 hover:-translate-y-0.5">
                            Explore Menu
                        </a>
                        <a href="#story"
                            class="rounded-full border border-[#CBA258]/40 px-8 py-3.5 text-sm font-semibold text-[#CBA258] hover:bg-[#CBA258]/10 transition-all duration-300 hover:-translate-y-0.5">
                            Our Story
                        </a>
                    </div>
                </div>

                <!-- Right: Coffee visual -->
                <div class="hero-visual flex justify-center lg:justify-end">
                    <div class="cup-float relative w-72 h-72 lg:w-96 lg:h-96 flex items-center justify-center">

                        <!-- Outer glow ring -->
                        <div
                            class="absolute inset-4 rounded-full border border-[#00704A]/15 animate-spin [animation-duration:20s]">
                        </div>
                        <div
                            class="absolute inset-8 rounded-full border border-[#CBA258]/10 animate-spin [animation-duration:30s] [animation-direction:reverse]">
                        </div>

                        <!-- Steam trails -->
                        <div class="absolute -top-14 left-1/2 flex gap-5 -translate-x-1/2">
                            <div
                                class="steam-a w-[3px] h-14 rounded-full bg-gradient-to-t from-[#00704A]/80 to-transparent">
                            </div>
                            <div
                                class="steam-b w-[2px] h-10 rounded-full bg-gradient-to-t from-[#00704A]/60 to-transparent mt-4">
                            </div>
                            <div
                                class="steam-c w-[3px] h-14 rounded-full bg-gradient-to-t from-[#00704A]/80 to-transparent">
                            </div>
                        </div>

                        <!-- Glow backdrop -->
                        <div class="absolute inset-0 rounded-full opacity-25"
                            style="background: radial-gradient(circle, #00704A 0%, transparent 65%); filter: blur(50px);">
                        </div>

                        <!-- Cup circle -->
                        <div
                            class="relative w-56 h-56 lg:w-72 lg:h-72 rounded-full flex items-center justify-center shadow-2xl shadow-[#00704A]/20"
                            style="background: radial-gradient(135deg at 30% 30%, #1a3d2e 0%, #0c1512 60%, #0a1210 100%); border: 1px solid rgba(0,112,74,0.3);">
                            <span class="text-[7rem] lg:text-[9rem] select-none">☕</span>
                        </div>

                        <!-- Decorative dots -->
                        <div class="absolute top-6 right-6 w-2 h-2 rounded-full bg-[#CBA258]/60"></div>
                        <div class="absolute bottom-10 left-8 w-1.5 h-1.5 rounded-full bg-[#00704A]/80"></div>
                        <div class="absolute top-1/2 right-2 w-1 h-1 rounded-full bg-[#CBA258]/40"></div>
                    </div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#B8AFA4]/40">Scroll to explore</p>
                <div class="w-px h-10 bg-gradient-to-b from-[#00704A]/50 to-transparent"></div>
            </div>
        </section>

        <!-- ── FEATURED PRODUCT ──────────────────────────────── -->
        <section v-if="featuredProduct && !loadingProducts"
            class="py-28 px-6 lg:px-12 border-t border-[#1a3028]/40">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center">

                <!-- Visual -->
                <div class="reveal-left flex justify-center">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full opacity-30"
                            style="background: radial-gradient(circle, #CBA258 0%, transparent 70%); filter: blur(60px);">
                        </div>
                        <div
                            class="relative w-64 h-64 lg:w-80 lg:h-80 rounded-full flex items-center justify-center shadow-2xl shadow-[#CBA258]/10"
                            style="background: linear-gradient(135deg, #1a3d2e 0%, #0c1512 100%); border: 2px solid rgba(203,162,88,0.3);">
                            <img v-if="getImageUrl(featuredProduct.image)"
                                :src="getImageUrl(featuredProduct.image)!"
                                :alt="featuredProduct.name"
                                class="w-full h-full object-cover rounded-full" />
                            <span v-else class="text-[6rem] select-none">{{ getProductIcon(featuredProduct) }}</span>
                        </div>
                        <div
                            class="absolute top-4 right-0 bg-[#CBA258] text-[#0c1512] text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-lg rotate-12">
                            Featured
                        </div>
                    </div>
                </div>

                <!-- Text -->
                <div class="reveal-right">
                    <p class="text-[#00704A] text-xs font-bold uppercase tracking-widest mb-4">Signature Item</p>
                    <h2 class="text-4xl sm:text-5xl font-black leading-tight mb-5 text-[#F2F0EB]">
                        {{ featuredProduct.name }}
                    </h2>
                    <p class="text-[#B8AFA4] leading-relaxed mb-6 text-lg">
                        {{ featuredProduct.description ?? 'A signature offering crafted with the finest ingredients, perfected for your enjoyment.' }}
                    </p>
                    <div v-if="featuredProduct.category"
                        class="inline-flex items-center gap-2 mb-6 rounded-full border border-[#00704A]/30 bg-[#00704A]/10 px-4 py-1.5 text-xs font-semibold text-[#00704A]">
                        {{ featuredProduct.category.name }}
                    </div>
                    <div class="text-5xl font-black text-[#CBA258]">{{ formatPrice(featuredProduct.price) }}</div>
                </div>
            </div>
        </section>

        <!-- ── MENU SECTION ──────────────────────────────────── -->
        <section id="menu" class="py-28 px-6 lg:px-12 border-t border-[#1a3028]/40">
            <div class="max-w-7xl mx-auto">

                <div class="text-center mb-16 reveal-up">
                    <p class="text-[#00704A] text-xs font-bold uppercase tracking-widest mb-3">Crafted For You</p>
                    <h2 class="text-5xl font-black text-[#F2F0EB]">Our Menu</h2>
                    <p class="text-[#B8AFA4] mt-4 max-w-lg mx-auto leading-relaxed">
                        Every item on our menu is thoughtfully prepared — from our artisan espresso drinks to fresh-baked
                        treats.
                    </p>
                </div>

                <!-- Loading skeleton -->
                <div v-if="loadingProducts" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="n in 6" :key="n"
                        class="rounded-2xl border border-[#1a3028]/60 p-5 animate-pulse"
                        style="background: rgba(26,48,40,0.25)">
                        <div class="flex gap-4 items-start">
                            <div class="w-16 h-16 rounded-xl bg-[#1a3028]/80"></div>
                            <div class="flex-1 space-y-2.5 pt-1">
                                <div class="h-4 bg-[#1a3028] rounded-lg w-3/4"></div>
                                <div class="h-3 bg-[#1a3028]/70 rounded-lg w-full"></div>
                                <div class="h-3 bg-[#1a3028]/50 rounded-lg w-2/3"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products grid -->
                <div v-else-if="products.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div v-for="(product) in products" :key="product.id"
                        class="reveal-card group relative rounded-2xl border border-[#1a3028]/60 p-5 hover:border-[#00704A]/40 hover:-translate-y-1 transition-all duration-300 cursor-default"
                        style="background: rgba(26,48,40,0.18);">

                        <!-- Category chip -->
                        <div v-if="product.category"
                            class="absolute top-3.5 right-3.5 text-[9px] font-bold uppercase tracking-wider text-[#00704A]/80 bg-[#00704A]/10 border border-[#00704A]/15 px-2 py-0.5 rounded-full">
                            {{ product.category.name }}
                        </div>

                        <div class="flex gap-4 items-start">
                            <!-- Product image or icon -->
                            <div
                                class="shrink-0 w-16 h-16 rounded-xl overflow-hidden border border-[#1a3028] group-hover:border-[#00704A]/30 transition-colors duration-300"
                                style="background: linear-gradient(135deg, #1a3d2e 0%, #0c1512 100%);">
                                <img v-if="getImageUrl(product.image)"
                                    :src="getImageUrl(product.image)!"
                                    :alt="product.name"
                                    class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-2xl">
                                    {{ getProductIcon(product) }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0 pr-6">
                                <div class="flex items-baseline justify-between gap-2 mb-1.5">
                                    <h3 class="font-bold text-[#F2F0EB] text-sm leading-snug">{{ product.name }}</h3>
                                </div>
                                <p v-if="product.description"
                                    class="text-xs text-[#B8AFA4] leading-relaxed line-clamp-2">
                                    {{ product.description }}
                                </p>
                                <p v-else class="text-xs text-[#B8AFA4]/40 italic">Premium quality item.</p>
                                <p class="mt-2 text-base font-black text-[#CBA258]">{{ formatPrice(product.price) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-else class="text-center py-24">
                    <span class="text-6xl block mb-4">☕</span>
                    <p class="text-[#B8AFA4]">Menu is being updated. Come back soon.</p>
                </div>
            </div>
        </section>

        <!-- ── EXPERIENCE SECTION ────────────────────────────── -->
        <section id="experience" class="py-28 px-6 lg:px-12 border-t border-[#1a3028]/40">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">

                <!-- Left text -->
                <div class="reveal-left">
                    <p class="text-[#00704A] text-xs font-bold uppercase tracking-widest mb-4">The Load Café Way</p>
                    <h2 class="text-4xl sm:text-5xl font-black leading-tight mb-6 text-[#F2F0EB]">
                        More Than<br><span class="text-[#CBA258]">Just Coffee</span>
                    </h2>
                    <p class="text-[#B8AFA4] leading-relaxed text-lg mb-5">
                        At Load Café, every cup is a ritual. We source premium beans from sustainable farms, brew with
                        precision, and serve with genuine warmth — because great coffee deserves great company.
                    </p>
                    <p class="text-[#B8AFA4] leading-relaxed">
                        Whether you're kickstarting your morning or winding down your afternoon, every sip is crafted to
                        be the highlight of your day.
                    </p>
                </div>

                <!-- Right feature grid -->
                <div class="grid grid-cols-2 gap-4 reveal-right">
                    <div v-for="feat in [
                        { icon: '🌱', title: 'Ethically Sourced', body: 'Premium beans from certified sustainable farms around the globe.' },
                        { icon: '✋', title: 'Handcrafted', body: 'Every drink prepared with precision by our skilled baristas.' },
                        { icon: '🌡️', title: 'Perfect Temperature', body: 'Precise brewing temperatures for optimal flavor extraction.' },
                        { icon: '💚', title: 'Made with Love', body: 'Passion poured into every cup, every single day.' },
                    ]" :key="feat.title"
                        class="feat-card rounded-2xl border border-[#1a3028]/60 p-5 hover:border-[#00704A]/30 transition-colors duration-300"
                        style="background: rgba(26,48,40,0.2)">
                        <div class="text-3xl mb-3">{{ feat.icon }}</div>
                        <h3 class="text-sm font-bold text-[#F2F0EB] mb-1.5">{{ feat.title }}</h3>
                        <p class="text-xs text-[#B8AFA4] leading-relaxed">{{ feat.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── OUR STORY ─────────────────────────────────────── -->
        <section id="story" class="py-28 px-6 lg:px-12 border-t border-[#1a3028]/40">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-16 reveal-up">
                    <p class="text-[#00704A] text-xs font-bold uppercase tracking-widest mb-3">Our Roots</p>
                    <h2 class="text-5xl font-black text-[#F2F0EB]">The Story</h2>
                </div>

                <div class="grid sm:grid-cols-3 gap-6">
                    <div v-for="card in [
                        { icon: '☕', step: '01', title: 'The Bean', body: 'It starts at origin. We partner with farmers who care as deeply about quality as we do — selecting only the finest single-origin and blended beans.' },
                        { icon: '🔥', step: '02', title: 'The Roast', body: 'Our roasters bring out the nuanced flavors locked inside each bean through meticulous roasting profiles developed over years of refinement.' },
                        { icon: '🥛', step: '03', title: 'The Cup', body: 'From espresso to cold brew, every beverage is crafted with technical precision and an eye for the little details that make your cup extraordinary.' },
                    ]" :key="card.step"
                        class="reveal-card rounded-2xl border border-[#1a3028]/60 p-7 text-center hover:border-[#00704A]/30 transition-colors duration-300"
                        style="background: rgba(26,48,40,0.18)">
                        <div
                            class="text-4xl mb-4 w-16 h-16 mx-auto rounded-full flex items-center justify-center border border-[#1a3028]"
                            style="background: rgba(26,48,40,0.6)">
                            {{ card.icon }}
                        </div>
                        <p class="text-[#00704A]/60 text-xs font-black tracking-widest mb-2">{{ card.step }}</p>
                        <h3 class="font-bold text-[#F2F0EB] text-lg mb-3">{{ card.title }}</h3>
                        <p class="text-sm text-[#B8AFA4] leading-relaxed">{{ card.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── QUOTE BANNER ──────────────────────────────────── -->
        <section class="py-28 px-6 relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none"
                style="background: linear-gradient(180deg, transparent, rgba(0,112,74,0.05), transparent);"></div>
            <div class="relative z-10 max-w-4xl mx-auto text-center">
                <p class="quote-text text-4xl sm:text-5xl lg:text-6xl font-black leading-tight text-[#F2F0EB] mb-6">
                    "Life is too short<br>for <span class="text-[#CBA258]">bad coffee.</span>"
                </p>
                <div class="w-16 h-px bg-[#00704A]/40 mx-auto mb-6"></div>
                <p class="text-[#B8AFA4]/60 text-xs uppercase tracking-[0.4em]">Load Café · Brewed Fresh Daily</p>
            </div>
        </section>

        <!-- ── FOOTER ────────────────────────────────────────── -->
        <footer class="border-t border-[#1a3028]/40 px-6 lg:px-12 py-10">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-3">
                    <div
                        class="h-9 w-9 rounded-full bg-[#00704A] flex items-center justify-center shadow-md shadow-[#00704A]/30">
                        <span class="text-white font-black text-[10px] select-none">LC</span>
                    </div>
                    <span class="font-black tracking-widest text-[#F2F0EB]">LOAD <span
                            class="text-[#00704A]">CAFÉ</span></span>
                </div>

                <div class="flex items-center gap-8 text-xs text-[#B8AFA4]/60">
                    <a href="#menu" class="hover:text-[#B8AFA4] transition-colors">Menu</a>
                    <a href="#experience" class="hover:text-[#B8AFA4] transition-colors">Experience</a>
                    <a href="#story" class="hover:text-[#B8AFA4] transition-colors">Our Story</a>
                </div>

                <div class="flex items-center gap-2 text-xs text-[#B8AFA4]/50">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#00704A] animate-pulse"></span>
                    Open Daily · Premium Coffee
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
