<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import { Search, X, ChevronRight, ChevronLeft, Menu as MenuIcon, ArrowLeft, Leaf, Sparkles, Thermometer, Heart } from 'lucide-vue-next'

gsap.registerPlugin(ScrollTrigger)

withDefaults(defineProps<{ canRegister?: boolean }>(), { canRegister: false })

const page       = usePage()
const logoUrl    = computed(() => (page.props as any).logoUrl   as string | null)
const brandName  = computed(() => ((page.props as any).brandName as string | null) ?? 'Load Cafe')
const categories = computed<Category[]>(() => (page.props as any).categories ?? [])

interface Product {
    id: number; name: string; description: string | null
    price: number; image: string | null; category?: { name: string }
}
interface Category { name: string; products: Product[] }

const brandInitials = computed(() =>
    (brandName.value || '')
        .split(' ')
        .filter(Boolean)
        .map((w: string) => w[0].toUpperCase())
        .slice(0, 2)
        .join('')
)

const features = [
    { icon: Leaf,        title: 'Ethically Sourced', body: 'Premium beans from certified sustainable farms.' },
    { icon: Sparkles,    title: 'Handcrafted',        body: 'Every drink prepared with precision by our baristas.' },
    { icon: Thermometer, title: 'Perfect Temp',       body: 'Precise brewing temperatures for optimal flavor.' },
    { icon: Heart,       title: 'Made with Love',     body: 'Passion poured into every cup, every day.' },
]

// ── Search & filter ───────────────────────────────────────────────────────────
const searchQuery    = ref('')
const activeCategory = ref<string | null>(null)

const filteredCategories = computed(() => {
    const q = searchQuery.value.trim().toLowerCase()
    return categories.value
        .filter(c => !activeCategory.value || c.name === activeCategory.value)
        .map(c => ({
            ...c,
            products: q
                ? c.products.filter(p =>
                    p.name.toLowerCase().includes(q) ||
                    (p.description ?? '').toLowerCase().includes(q))
                : c.products,
        }))
        .filter(c => c.products.length > 0)
})

const totalFiltered = computed(() =>
    filteredCategories.value.reduce((n, c) => n + c.products.length, 0)
)

function setCategory(name: string | null) {
    activeCategory.value = name
    searchQuery.value    = ''
    if (name) {
        nextTick(() => {
            document.getElementById(`cat-${name}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
        })
    }
}

// ── Per-category pagination ───────────────────────────────────────────────────
const PAGE_SIZE    = 5
const catPages     = ref<Record<string, number>>({})

watch([searchQuery, activeCategory], () => { catPages.value = {} })

function getPage(name: string)                     { return catPages.value[name] ?? 0 }
function pageCount(products: Product[])            { return Math.ceil(products.length / PAGE_SIZE) }
function pagedProducts(name: string, products: Product[]) {
    const p = getPage(name)
    return products.slice(p * PAGE_SIZE, (p + 1) * PAGE_SIZE)
}
function goPage(name: string, page: number)        { catPages.value = { ...catPages.value, [name]: page } }
function prevPage(name: string, products: Product[]) {
    const p = getPage(name)
    if (p > 0) goPage(name, p - 1)
}
function nextPage(name: string, products: Product[]) {
    const p = getPage(name)
    if (p < pageCount(products) - 1) goPage(name, p + 1)
}

// swipe gesture per category
let swipeCatName: string       = ''
let swipeCatProducts: Product[] = []
let swipeTouchStartX = 0
let swipeTouchStartY = 0

function onCatTouchStart(e: TouchEvent, name: string, products: Product[]) {
    swipeCatName     = name
    swipeCatProducts = products
    swipeTouchStartX = e.touches[0].clientX
    swipeTouchStartY = e.touches[0].clientY
}

function onCatTouchEnd(e: TouchEvent) {
    const dx = e.changedTouches[0].clientX - swipeTouchStartX
    const dy = e.changedTouches[0].clientY - swipeTouchStartY
    if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) {
        if (dx < 0) nextPage(swipeCatName, swipeCatProducts)
        else prevPage(swipeCatName, swipeCatProducts)
    }
}

// ── Product detail sheet ──────────────────────────────────────────────────────
const selectedProduct = ref<Product & { category: { name: string } } | null>(null)
const sheetOpen       = ref(false)

function openProduct(p: Product & { category: { name: string } }) {
    selectedProduct.value        = p
    sheetOpen.value              = true
    document.body.style.overflow = 'hidden'
}

function closeSheet() {
    sheetOpen.value              = false
    document.body.style.overflow = ''
}

// ── Mobile nav ────────────────────────────────────────────────────────────────
const mobileNavOpen = ref(false)
function toggleMobileNav() { mobileNavOpen.value = !mobileNavOpen.value }

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatPrice(price: number) { return '₱' + price.toFixed(2) }

// ── GSAP ──────────────────────────────────────────────────────────────────────
onMounted(() => {
    const heroTl = gsap.timeline({ delay: 0.1 })
    heroTl
        .fromTo('.hero-badge', { y: -16, opacity: 0 }, { y: 0, opacity: 1, duration: 0.6,  ease: 'power3.out' })
        .fromTo('.hero-line',  { y: 60,  opacity: 0 }, { y: 0, opacity: 1, duration: 0.85, stagger: 0.12, ease: 'power4.out' }, '-=0.3')
        .fromTo('.hero-sub',   { y: 24,  opacity: 0 }, { y: 0, opacity: 1, duration: 0.65, ease: 'power3.out' }, '-=0.5')
        .fromTo('.hero-cta',   { y: 16,  opacity: 0 }, { y: 0, opacity: 1, duration: 0.5,  ease: 'power3.out' }, '-=0.35')

    gsap.set(['.steam-a', '.steam-b', '.steam-c'], { y: 0, opacity: 0.6 })
    gsap.to('.steam-a', { y: -70, opacity: 0, duration: 2.4, repeat: -1, ease: 'sine.inOut' })
    gsap.to('.steam-b', { y: -55, opacity: 0, duration: 2.0, repeat: -1, ease: 'sine.inOut', delay: 0.5 })
    gsap.to('.steam-c', { y: -65, opacity: 0, duration: 2.6, repeat: -1, ease: 'sine.inOut', delay: 1.0 })
    gsap.to('.cup-float', { y: -12, duration: 3, repeat: -1, yoyo: true, ease: 'sine.inOut' })

    gsap.utils.toArray<HTMLElement>('.reveal-up').forEach(el => {
        gsap.fromTo(el, { y: 40, opacity: 0 }, {
            y: 0, opacity: 1, duration: 0.75, ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 88%', toggleActions: 'play none none none' },
        })
    })
})

onUnmounted(() => {
    ScrollTrigger.getAll().forEach(t => t.kill())
    document.body.style.overflow = ''
})
</script>

<template>
    <Head :title="`${brandName} — Menu`" />

    <div class="min-h-screen bg-black text-white font-sans overflow-x-hidden">

        <!-- ── NAV ───────────────────────────────────────────────────────── -->
        <nav class="fixed top-0 inset-x-0 z-50 border-b border-white/8"
            style="background:rgba(0,0,0,0.9);backdrop-filter:blur(16px)">
            <div class="max-w-7xl mx-auto flex items-center justify-between px-4 sm:px-6 lg:px-8 h-14 sm:h-16">

                <div class="flex items-center gap-2.5 shrink-0">
                    <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-full overflow-hidden border border-white/20 flex items-center justify-center bg-white/5 shrink-0">
                        <img v-if="logoUrl" :src="logoUrl" class="h-full w-full object-contain" alt="Logo" />
                        <span v-else class="text-white font-black text-[10px] tracking-tight">{{ brandInitials }}</span>
                    </div>
                    <span class="font-black tracking-widest text-white text-sm sm:text-base">{{ brandName }}</span>
                </div>

                <div class="hidden md:flex items-center gap-6 text-sm text-white/50">
                    <a href="#menu" class="hover:text-white transition-colors">Menu</a>
                    <a href="#experience" class="hover:text-white transition-colors">Experience</a>
                </div>

                <div class="flex items-center gap-2">
                    <Link v-if="$page.props.auth?.user" :href="dashboard()"
                        class="hidden sm:flex rounded-full border border-white/30 px-4 py-1.5 text-xs font-semibold text-white hover:bg-white hover:text-black transition-all duration-200">
                        Dashboard
                    </Link>

                    <button @click="toggleMobileNav"
                        class="sm:hidden flex items-center justify-center h-9 w-9 rounded-lg border border-white/15 hover:bg-white/5 transition-colors">
                        <X v-if="mobileNavOpen" class="h-4 w-4" />
                        <MenuIcon v-else class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <div v-if="mobileNavOpen"
                class="sm:hidden border-t border-white/8 px-4 py-4 space-y-1"
                style="background:rgba(0,0,0,0.95)">
                <a href="#menu" @click="mobileNavOpen=false"
                    class="block py-2.5 text-sm text-white/70 hover:text-white border-b border-white/5">Menu</a>
                <a href="#experience" @click="mobileNavOpen=false"
                    class="block py-2.5 text-sm text-white/70 hover:text-white border-b border-white/5">Experience</a>
                <div v-if="$page.props.auth?.user" class="pt-2">
                    <Link :href="dashboard()"
                        class="block text-center rounded-full border border-white/30 px-4 py-2.5 text-sm font-semibold text-white">
                        Dashboard
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ── HERO ──────────────────────────────────────────────────────── -->
        <section class="relative flex items-center pt-14 sm:pt-16 min-h-[55vh] sm:min-h-[75vh] pb-10 px-4 sm:px-6 lg:px-8 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-[0.06]"
                    style="background:radial-gradient(circle,#fff 0%,transparent 65%);filter:blur(80px)"></div>
                <div class="absolute inset-0 opacity-[0.025]"
                    style="background-image:linear-gradient(rgba(255,255,255,0.8) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.8) 1px,transparent 1px);background-size:64px 64px"></div>
            </div>

            <div class="relative z-10 max-w-7xl mx-auto w-full grid lg:grid-cols-2 gap-8 lg:gap-16 items-center">
                <div class="text-center lg:text-left">
                    <div class="hero-badge inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-3 py-1 text-[10px] font-semibold uppercase tracking-widest text-white/60 mb-5 sm:mb-7">
                        <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span>
                        Now Brewing · Premium Coffee
                    </div>

                    <h1 class="mb-4 sm:mb-6 overflow-hidden">
                        <span class="hero-line block text-[4rem] sm:text-[6rem] lg:text-[7rem] font-black leading-none tracking-tight">SAVOR</span>
                        <span class="hero-line block text-[4rem] sm:text-[6rem] lg:text-[7rem] font-black leading-none tracking-tight text-white/25">EVERY</span>
                        <span class="hero-line block text-xl sm:text-3xl font-light tracking-[0.5em] text-white/40 mt-1">SIP</span>
                    </h1>

                    <p class="hero-sub text-white/45 text-sm sm:text-base leading-relaxed mb-7 max-w-sm mx-auto lg:mx-0">
                        Premium coffee crafted with care — from ethically sourced beans to every carefully pulled shot.
                    </p>

                    <div class="hero-cta flex flex-wrap items-center justify-center lg:justify-start gap-3">
                        <a href="#menu"
                            class="rounded-full bg-white px-6 sm:px-8 py-3 text-xs sm:text-sm font-bold text-black hover:bg-white/90 transition-all duration-200 shadow-lg shadow-white/10">
                            Browse Menu
                        </a>
                        <a href="#experience"
                            class="rounded-full border border-white/20 px-6 sm:px-8 py-3 text-xs sm:text-sm font-semibold text-white/60 hover:text-white hover:border-white/40 transition-all duration-200">
                            Our Story
                        </a>
                    </div>
                </div>

                <div class="hidden sm:flex justify-center lg:justify-end">
                    <div class="cup-float relative w-56 h-56 lg:w-72 lg:h-72 flex items-center justify-center">
                        <div class="absolute inset-3 rounded-full border border-white/8 animate-spin [animation-duration:22s]"></div>
                        <div class="absolute inset-6 rounded-full border border-white/5 animate-spin [animation-duration:32s] [animation-direction:reverse]"></div>
                        <div class="absolute -top-12 left-1/2 flex gap-4 -translate-x-1/2">
                            <div class="steam-a w-[3px] h-12 rounded-full bg-gradient-to-t from-white/60 to-transparent"></div>
                            <div class="steam-b w-[2px] h-9 rounded-full bg-gradient-to-t from-white/40 to-transparent mt-3"></div>
                            <div class="steam-c w-[3px] h-12 rounded-full bg-gradient-to-t from-white/60 to-transparent"></div>
                        </div>
                        <div class="relative w-44 h-44 lg:w-60 lg:h-60 rounded-full flex items-center justify-center"
                            style="background:radial-gradient(135deg at 30% 30%,#1a1a1a 0%,#080808 60%);border:1px solid rgba(255,255,255,0.12)">
                            <img v-if="logoUrl" :src="logoUrl"
                                class="w-24 lg:w-32 h-24 lg:h-32 object-contain rounded-full" alt="" />
                            <span v-else class="font-black text-white/20 tracking-widest select-none text-5xl lg:text-6xl">
                                {{ brandInitials }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
                <p class="text-[9px] uppercase tracking-[0.3em] text-white/20">Scroll</p>
                <div class="w-px h-8 bg-gradient-to-b from-white/25 to-transparent"></div>
            </div>
        </section>

        <!-- ── MENU SECTION ──────────────────────────────────────────────── -->
        <section id="menu" class="border-t border-white/8">

            <!-- Sticky filter bar -->
            <div class="sticky top-14 sm:top-16 z-40 border-b border-white/8"
                style="background:rgba(0,0,0,0.92);backdrop-filter:blur(16px)">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                    <div class="py-3 flex items-center gap-3">
                        <div class="relative flex-1 max-w-xs sm:max-w-sm">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-white/35 pointer-events-none" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search menu..."
                                class="w-full rounded-full border border-white/15 bg-white/5 pl-9 pr-8 py-2 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white/30 transition-all"
                            />
                            <button v-if="searchQuery" @click="searchQuery=''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60">
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <span v-if="searchQuery" class="text-xs text-white/35 shrink-0">
                            {{ totalFiltered }} result{{ totalFiltered !== 1 ? 's' : '' }}
                        </span>
                    </div>

                    <div v-if="categories.length" class="flex gap-2 overflow-x-auto pb-3 scrollbar-hide -mx-1 px-1">
                        <button
                            @click="setCategory(null)"
                            :class="[
                                'shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all duration-200',
                                activeCategory === null
                                    ? 'bg-white text-black'
                                    : 'border border-white/15 text-white/50 hover:text-white hover:border-white/30',
                            ]">
                            All
                        </button>
                        <button
                            v-for="cat in categories"
                            :key="cat.name"
                            @click="setCategory(cat.name)"
                            :class="[
                                'shrink-0 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all duration-200',
                                activeCategory === cat.name
                                    ? 'bg-white text-black'
                                    : 'border border-white/15 text-white/50 hover:text-white hover:border-white/30',
                            ]">
                            {{ cat.name }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">

                <div v-if="filteredCategories.length === 0" class="text-center py-20">
                    <p class="text-white/40 text-sm">No items match "{{ searchQuery }}"</p>
                    <button @click="searchQuery=''"
                        class="mt-4 text-xs text-white/50 underline hover:text-white/80 transition-colors">
                        Clear search
                    </button>
                </div>

                <template v-else>
                    <div v-for="cat in filteredCategories" :key="cat.name" :id="`cat-${cat.name}`">

                        <!-- Category heading -->
                        <div class="flex items-center gap-3 mb-4 reveal-up">
                            <h2 class="text-lg font-black text-white">{{ cat.name }}</h2>
                            <span class="text-xs text-white/30 font-semibold">{{ cat.products.length }}</span>
                            <div class="flex-1 h-px bg-white/8"></div>
                        </div>

                        <!-- Product list (5 per page) with swipe gesture -->
                        <div
                            @touchstart.passive="onCatTouchStart($event, cat.name, cat.products)"
                            @touchend.passive="onCatTouchEnd($event)"
                        >
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <button
                                    v-for="product in pagedProducts(cat.name, cat.products)"
                                    :key="product.id"
                                    @click="openProduct({ ...product, category: { name: cat.name } })"
                                    class="group text-left flex items-center gap-3 rounded-xl border border-white/8 px-4 py-3.5 hover:border-white/25 hover:bg-white/4 active:scale-[0.98] transition-all duration-200 cursor-pointer"
                                >
                                    <div class="shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden border border-white/8 group-hover:border-white/15 transition-colors"
                                        style="background:rgba(255,255,255,0.04)">
                                        <img v-if="product.image" :src="product.image" :alt="product.name"
                                            class="w-full h-full object-cover" />
                                        <div v-else class="w-full h-full flex items-center justify-center">
                                            <span class="text-lg font-black text-white/20">{{ product.name[0] }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-sm text-white leading-snug truncate">{{ product.name }}</p>
                                        <p v-if="product.description"
                                            class="text-xs text-white/38 leading-relaxed line-clamp-1 mt-0.5">
                                            {{ product.description }}
                                        </p>
                                        <p class="text-sm font-black text-white mt-1.5">{{ formatPrice(product.price) }}</p>
                                    </div>
                                    <ChevronRight class="h-4 w-4 text-white/20 shrink-0 group-hover:text-white/50 transition-colors" />
                                </button>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="pageCount(cat.products) > 1"
                            class="flex items-center justify-center gap-3 mt-5">
                            <button
                                @click="prevPage(cat.name, cat.products)"
                                :disabled="getPage(cat.name) === 0"
                                class="flex items-center justify-center h-7 w-7 rounded-full border border-white/15 text-white/40 hover:text-white hover:border-white/30 disabled:opacity-20 disabled:cursor-not-allowed transition-all">
                                <ChevronLeft class="h-3.5 w-3.5" />
                            </button>

                            <!-- Dots (scrollable if many pages) -->
                            <div class="flex gap-1.5 overflow-x-auto scrollbar-hide" style="max-width:140px">
                                <button
                                    v-for="(_, i) in pageCount(cat.products)"
                                    :key="i"
                                    @click="goPage(cat.name, i)"
                                    :aria-label="`Page ${i + 1}`"
                                    :class="getPage(cat.name) === i ? 'w-4 bg-white' : 'w-1.5 bg-white/25 hover:bg-white/50'"
                                    class="h-1.5 shrink-0 rounded-full transition-all duration-300"
                                />
                            </div>

                            <button
                                @click="nextPage(cat.name, cat.products)"
                                :disabled="getPage(cat.name) >= pageCount(cat.products) - 1"
                                class="flex items-center justify-center h-7 w-7 rounded-full border border-white/15 text-white/40 hover:text-white hover:border-white/30 disabled:opacity-20 disabled:cursor-not-allowed transition-all">
                                <ChevronRight class="h-3.5 w-3.5" />
                            </button>
                        </div>

                    </div>
                </template>
            </div>
        </section>

        <!-- ── EXPERIENCE ─────────────────────────────────────────────────── -->
        <section id="experience" class="py-20 sm:py-28 px-4 sm:px-6 lg:px-8 border-t border-white/8">
            <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="reveal-up">
                    <p class="text-white/40 text-xs font-bold uppercase tracking-widest mb-3">The {{ brandName }} Way</p>
                    <h2 class="text-3xl sm:text-5xl font-black leading-tight mb-5 text-white">
                        More Than<br><span class="text-white/40">Just Coffee</span>
                    </h2>
                    <p class="text-white/45 leading-relaxed text-base sm:text-lg mb-4">
                        At {{ brandName }}, every cup is a ritual. We source premium beans from sustainable farms, brew
                        with precision, and serve with genuine warmth.
                    </p>
                    <p class="text-white/30 leading-relaxed text-sm sm:text-base">
                        Whether you're kickstarting your morning or winding down your afternoon, every sip is crafted to
                        be the highlight of your day.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 reveal-up">
                    <div
                        v-for="feat in features"
                        :key="feat.title"
                        class="rounded-xl border border-white/8 p-4 sm:p-5 hover:border-white/15 transition-colors"
                        style="background:rgba(255,255,255,0.03)">
                        <component :is="feat.icon" class="h-5 w-5 text-white/35 mb-2 sm:mb-3" />
                        <h3 class="text-xs sm:text-sm font-bold text-white mb-1">{{ feat.title }}</h3>
                        <p class="text-[11px] sm:text-xs text-white/38 leading-relaxed">{{ feat.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── QUOTE ──────────────────────────────────────────────────────── -->
        <section class="py-16 sm:py-24 px-4 relative overflow-hidden">
            <div class="relative z-10 max-w-3xl mx-auto text-center reveal-up">
                <p class="text-3xl sm:text-5xl font-black leading-tight text-white mb-5">
                    "Life is too short<br>for <span class="text-white/35">bad coffee.</span>"
                </p>
                <div class="w-12 h-px bg-white/20 mx-auto mb-4"></div>
                <p class="text-white/20 text-xs uppercase tracking-[0.4em]">{{ brandName }} · Brewed Fresh Daily</p>
            </div>
        </section>

        <!-- ── FOOTER ─────────────────────────────────────────────────────── -->
        <footer class="border-t border-white/8 px-4 sm:px-6 lg:px-8 py-8">
            <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-5">
                <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-full overflow-hidden border border-white/15 flex items-center justify-center bg-white/5 shrink-0">
                        <img v-if="logoUrl" :src="logoUrl" class="h-full w-full object-contain" alt="Logo" />
                        <span v-else class="text-white font-black text-[10px] tracking-tight">{{ brandInitials }}</span>
                    </div>
                    <span class="font-black tracking-widest text-white text-sm">{{ brandName }}</span>
                </div>

                <div class="flex items-center gap-6 text-xs text-white/30">
                    <a href="#menu" class="hover:text-white/60 transition-colors">Menu</a>
                    <a href="#experience" class="hover:text-white/60 transition-colors">Experience</a>
                </div>

                <div class="flex items-center gap-2 text-xs text-white/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-white/50 animate-pulse"></span>
                    Open Daily · Premium Coffee
                </div>
            </div>
        </footer>

        <!-- ── PRODUCT DETAIL SHEET ───────────────────────────────────────── -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="sheetOpen" @click="closeSheet"
                    class="fixed inset-0 z-[60] bg-black/70 backdrop-blur-sm" />
            </Transition>

            <Transition name="sheet">
                <div v-if="sheetOpen && selectedProduct"
                    class="fixed z-[61] bg-zinc-950 border border-white/12 shadow-2xl
                           inset-x-0 bottom-0 rounded-t-2xl
                           md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2
                           md:w-full md:max-w-md md:rounded-2xl"
                    style="max-height:90dvh;overflow-y:auto">

                    <div class="flex justify-center pt-3 pb-1 md:hidden">
                        <div class="w-10 h-1 rounded-full bg-white/20"></div>
                    </div>

                    <div class="sticky top-0 z-10 flex items-center justify-between px-5 py-3 border-b border-white/8"
                        style="background:rgba(9,9,11,0.95);backdrop-filter:blur(8px)">
                        <button @click="closeSheet"
                            class="flex items-center gap-1.5 text-xs text-white/50 hover:text-white transition-colors">
                            <ArrowLeft class="h-3.5 w-3.5" />
                            <span class="md:hidden">Back</span>
                            <span class="hidden md:inline">Close</span>
                        </button>
                        <span v-if="selectedProduct.category"
                            class="text-[10px] font-bold uppercase tracking-widest text-white/35 border border-white/10 rounded-full px-2.5 py-0.5 bg-white/5">
                            {{ selectedProduct.category.name }}
                        </span>
                    </div>

                    <div class="relative mx-5 mt-5 rounded-xl overflow-hidden border border-white/8"
                        style="background:rgba(255,255,255,0.03)">
                        <div class="aspect-square flex items-center justify-center">
                            <img v-if="selectedProduct.image"
                                :src="selectedProduct.image"
                                :alt="selectedProduct.name"
                                class="w-full h-full object-cover" />
                            <span v-else
                                class="font-black text-white/10 select-none"
                                style="font-size:clamp(5rem,20vw,8rem)">
                                {{ selectedProduct.name[0] }}
                            </span>
                        </div>
                    </div>

                    <div class="px-5 pt-5 pb-8 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <h3 class="text-xl font-black text-white leading-tight">{{ selectedProduct.name }}</h3>
                            <span class="text-xl font-black text-white shrink-0">{{ formatPrice(selectedProduct.price) }}</span>
                        </div>
                        <p v-if="selectedProduct.description" class="text-sm text-white/50 leading-relaxed">
                            {{ selectedProduct.description }}
                        </p>
                        <p v-else class="text-sm text-white/25 italic">Premium quality item.</p>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </div>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.sheet-enter-active, .sheet-leave-active { transition: transform 0.3s cubic-bezier(0.32,0.72,0,1); }
.sheet-enter-from, .sheet-leave-to { transform: translateY(100%); }

@media (min-width: 768px) {
    .sheet-enter-active, .sheet-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
    .sheet-enter-from, .sheet-leave-to { opacity: 0; transform: translate(-50%, calc(-50% + 16px)); }
    .sheet-enter-to, .sheet-leave-from { opacity: 1; transform: translate(-50%, -50%); }
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
