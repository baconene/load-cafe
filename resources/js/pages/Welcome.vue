<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { dashboard } from '@/routes'
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { Search, X, ChevronRight, ChevronLeft, Menu as MenuIcon, ArrowLeft, Coffee, MapPin, Sun, Heart, ArrowUpRight, Clock3 } from 'lucide-vue-next'


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
    { icon: Coffee, title: 'Coffee, made fresh', body: 'Hot or iced drinks prepared at the cart.' },
    { icon: Sun, title: 'Your kind of break', body: 'For quick coffee runs and moments that last longer.' },
    { icon: MapPin, title: 'At the town plaza', body: 'Find our pop-up coffee cart in Luisiana, Laguna.' },
    { icon: Heart, title: 'Good company', body: 'Bring a friend, take a breather, enjoy a cup.' },
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

// Keyboard escape and page scroll cleanup for the product detail sheet.
const onEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && sheetOpen.value) closeSheet()
}
onMounted(() => document.addEventListener('keydown', onEscape))
onUnmounted(() => {
    document.removeEventListener('keydown', onEscape)
    document.body.style.overflow = ''
})
</script>

<template>
    <Head :title="brandName + ' — Coffee at the Plaza'">
        <meta name="description" content="Load Cafe is a pop-up coffee cart at the Luisiana town plaza in Laguna. Explore our hot and iced drinks, then stop by for a cup." />
        <meta name="theme-color" content="#18110e" />
    </Head>

    <div class="load-site">
        <!-- The menu, pricing and product details continue to come from the live backend. -->
        <header class="site-header">
            <nav class="page-wrap site-nav" aria-label="Main navigation">
                <a href="#top" class="brand-lockup" aria-label="Load Cafe — return to top">
                    <span class="brand-disc">
                        <img v-if="logoUrl" :src="logoUrl" class="brand-image" alt="" />
                        <span v-else class="brand-monogram"><strong>LOAD</strong><small>Cafe</small></span>
                    </span>
                    <span class="brand-name"><strong>{{ brandName }}</strong><small>COFFEE CART · LUISIANA</small></span>
                </a>
                <div class="desktop-nav">
                    <a href="#menu">The menu</a>
                    <a href="#story">Our story</a>
                    <a href="#find-us">Find us</a>
                </div>
                <div class="nav-actions">
                    <Link v-if="$page.props.auth?.user" :href="dashboard()" class="staff-link">Dashboard</Link>
                    <a href="#menu" class="nav-cta">Explore menu <ArrowUpRight :size="16" aria-hidden="true" /></a>
                    <button class="mobile-menu-button" type="button" :aria-expanded="mobileNavOpen" aria-label="Toggle menu" @click="toggleMobileNav">
                        <X v-if="mobileNavOpen" :size="21" aria-hidden="true" />
                        <MenuIcon v-else :size="21" aria-hidden="true" />
                    </button>
                </div>
            </nav>
            <div v-if="mobileNavOpen" class="mobile-nav">
                <a href="#menu" @click="mobileNavOpen=false">The menu</a>
                <a href="#story" @click="mobileNavOpen=false">Our story</a>
                <a href="#find-us" @click="mobileNavOpen=false">Find us</a>
                <Link v-if="$page.props.auth?.user" :href="dashboard()" @click="mobileNavOpen=false">Dashboard</Link>
            </div>
        </header>

        <main id="top">
            <!-- HERO: photographs from the real Load Cafe coffee cart -->
            <section class="hero-section">
                <div class="hero-glow" aria-hidden="true"></div>
                <div class="page-wrap hero-grid">
                    <div class="hero-copy">
                        <p class="eyebrow"><span class="dot"></span> YOUR NEIGHBORHOOD COFFEE STOP <span class="eyebrow-line"></span> LUISIANA, LAGUNA</p>
                        <h1>Good day?<br /><em>Coffee.</em><br />Bad day?<br /><em>Coffee.</em></h1>
                        <p class="hero-description">Whatever kind of day you're having, there's always room for a good cup. Come find us at the plaza.</p>
                        <div class="hero-actions">
                            <a href="#menu" class="button-warm">Find your drink <ArrowUpRight :size="18" aria-hidden="true" /></a>
                            <a href="#find-us" class="button-outline">Come say hello <ChevronRight :size="17" aria-hidden="true" /></a>
                        </div>
                        <div class="hero-signoff"><Coffee :size="17" aria-hidden="true" /><span>Hot, iced, and made for your kind of day.</span></div>
                    </div>

                    <div class="hero-photos">
                        <div class="hero-main-photo photo-frame">
                            <img src="/images/load-cafe/espresso-station.webp" alt="The real espresso and grinder setup at Load Cafe" fetchpriority="high" />
                            <div class="photo-gradient"></div>
                            <span class="photo-label">AT THE CART <span>01 / 03</span></span>
                        </div>
                        <div class="hero-mini-photo photo-frame"><img src="/images/load-cafe/coffee-beans.webp" alt="Fresh coffee beans inside the Load Cafe grinder" loading="lazy" /></div>
                        <div class="hero-vertical-type" aria-hidden="true">THE GOOD COFFEE CORNER · LOAD CAFE</div>
                        <div class="hero-plate"><span class="plate-mark">LOAD</span><span>COFFEE AT THE PLAZA<br />LUISIANA, LAGUNA</span></div>
                    </div>
                </div>
                <div class="page-wrap hero-bottom"><span>GOOD COFFEE, GOOD COMPANY.</span><span class="hero-bottom-rule"></span><span>SCROLL TO EXPLORE ↓</span></div>
            </section>

            <div class="ticker-strip" aria-label="The Load Cafe experience">
                <div class="page-wrap ticker-content"><span>YOUR EVERYDAY COFFEE FIX</span><span class="asterisk">✳</span><span>HOT OR ICED</span><span class="asterisk">✳</span><span>SEE YOU AT THE PLAZA</span></div>
            </div>

            <!-- LIVE MENU -->
            <section id="menu" class="menu-section">
                <div class="page-wrap menu-intro">
                    <div><p class="eyebrow eyebrow-dark">FIND SOMETHING YOU LOVE</p><h2>The menu<span class="period">.</span><br /><em>Your moment.</em></h2></div>
                    <p>For coffee cravings, slow afternoons, and everything in between. Explore what's on the cart.</p>
                </div>
                <div class="menu-filter-wrap">
                    <div class="page-wrap">
                        <div class="filter-top">
                            <label class="search-field">
                                <Search :size="18" aria-hidden="true" />
                                <span class="sr-only">Search menu items</span>
                                <input v-model="searchQuery" type="search" placeholder="Looking for something?" />
                                <button v-if="searchQuery" type="button" aria-label="Clear search" @click="searchQuery=''"><X :size="17" /></button>
                            </label>
                            <span class="filter-count">{{ totalFiltered }} {{ totalFiltered === 1 ? 'ITEM' : 'ITEMS' }} TO EXPLORE</span>
                        </div>
                        <div v-if="categories.length" class="category-filter" role="group" aria-label="Filter by category">
                            <button type="button" :aria-pressed="activeCategory === null" :class="{ active: activeCategory === null }" @click="setCategory(null)">Everything</button>
                            <button v-for="cat in categories" :key="cat.name" type="button" :aria-pressed="activeCategory === cat.name" :class="{ active: activeCategory === cat.name }" @click="setCategory(cat.name)">{{ cat.name }}</button>
                        </div>
                    </div>
                </div>
                <div class="page-wrap menu-body">
                    <div v-if="!categories.length" class="empty-menu"><Coffee :size="36" :stroke-width="1.5" aria-hidden="true" /><h3>Good things are brewing.</h3><p>Our menu is getting ready. Check our Facebook page for the latest updates.</p><a href="https://www.facebook.com/share/1BNYr7c35C/" target="_blank" rel="noopener noreferrer">Find us on Facebook <ArrowUpRight :size="16" aria-hidden="true" /></a></div>
                    <div v-else-if="filteredCategories.length === 0" class="empty-menu"><Search :size="34" aria-hidden="true" /><h3>Nothing matching that yet.</h3><p>Try another search or explore everything we're serving.</p><button type="button" @click="searchQuery=''; activeCategory=null">Show the full menu</button></div>
                    <div v-for="(cat, index) in filteredCategories" :key="cat.name" :id="'cat-' + cat.name" class="menu-group">
                        <div class="menu-group-heading"><div class="group-title"><span>{{ String(index + 1).padStart(2, '0') }}</span><h3>{{ cat.name }}</h3></div><span class="group-count">{{ cat.products.length }} {{ cat.products.length === 1 ? 'drink / item' : 'drinks / items' }}</span></div>
                        <div class="product-grid" @touchstart.passive="onCatTouchStart($event, cat.name, cat.products)" @touchend.passive="onCatTouchEnd($event)">
                            <button v-for="product in pagedProducts(cat.name, cat.products)" :key="product.id" type="button" class="product-card" :aria-label="'View ' + product.name + ', ' + formatPrice(product.price)" @click="openProduct({ ...product, category: { name: cat.name } })">
                                <div class="product-thumbnail"><img v-if="product.image" :src="product.image" :alt="product.name" loading="lazy" /><div v-else class="product-placeholder"><Coffee :size="35" :stroke-width="1.2" aria-hidden="true" /><small>LOAD CAFE</small></div></div>
                                <div class="product-info"><span class="product-kicker">{{ cat.name }}</span><h4>{{ product.name }}</h4><p v-if="product.description">{{ product.description }}</p><div class="product-price"><strong>{{ formatPrice(product.price) }}</strong><span><ArrowUpRight :size="19" aria-hidden="true" /></span></div></div>
                            </button>
                        </div>
                        <div v-if="pageCount(cat.products) > 1" class="pagination"><button type="button" :aria-label="'Previous page of ' + cat.name" :disabled="getPage(cat.name) === 0" @click="prevPage(cat.name, cat.products)"><ChevronLeft :size="20" /></button><span>{{ getPage(cat.name) + 1 }} <i>/</i> {{ pageCount(cat.products) }}</span><button type="button" :aria-label="'Next page of ' + cat.name" :disabled="getPage(cat.name) >= pageCount(cat.products) - 1" @click="nextPage(cat.name, cat.products)"><ChevronRight :size="20" /></button></div>
                    </div>
                </div>
            </section>

            <!-- THE REAL CART -->
            <section id="story" class="story-section">
                <div class="page-wrap story-grid">
                    <div class="story-photo-set">
                        <div class="story-wide-photo"><img src="/images/load-cafe/brewing-bar.webp" loading="lazy" alt="Actual brewing equipment and tools on the Load Cafe coffee cart" /></div>
                        <div class="story-small-photo"><img src="/images/load-cafe/tip-box.webp" loading="lazy" alt="Real wooden Load Cafe tip box with the café's branding" /></div>
                        <div class="story-photo-credit">REAL PEOPLE. REAL COFFEE. NO FUSS.</div>
                    </div>
                    <div class="story-copy">
                        <p class="eyebrow">THIS IS LOAD CAFE <span class="eyebrow-line"></span> 01</p>
                        <h2>A little cart.<br /><em>A lot of heart.</em></h2>
                        <p>We're a pop-up coffee cart in the heart of Luisiana, Laguna. A simple spot to grab your usual, try something new, or catch up with your favorite people.</p>
                        <p>Just coffee, good conversations, and a place to take a pause. That's the kind of café we want to be.</p>
                        <a href="#find-us" class="text-link">Meet us at the plaza <ArrowUpRight :size="19" aria-hidden="true" /></a>
                    </div>
                </div>
            </section>

            <!-- REAL SIGNAGE / BRAND VOICE -->
            <section class="motto-section">
                <div class="page-wrap motto-grid">
                    <div class="motto-copy"><p class="eyebrow eyebrow-dark">OUR KIND OF PHILOSOPHY</p><h2>Any mood.<br /><em>There's coffee.</em></h2><p>Bad day? Coffee. Good day? Coffee. Stressed? Coffee. Happy? Coffee. Come as you are.</p><a href="#menu" class="motto-link">Find your pick <ArrowUpRight :size="19" aria-hidden="true" /></a></div>
                    <div class="motto-photo"><img src="/images/load-cafe/coffee-motto.webp" alt="Original Load Cafe cart sign: Bad day? Coffee. Good day? Coffee. Stressed? Coffee." loading="lazy" /><span>STRAIGHT FROM OUR CART</span></div>
                </div>
            </section>

            <!-- CUSTOMER EXPERIENCE -->
            <section class="features-section">
                <div class="page-wrap">
                    <div class="features-heading"><p class="eyebrow">THE LITTLE THINGS</p><h2>Made for<br /><em>your coffee moments.</em></h2></div>
                    <div class="features-grid"><div v-for="(feature, index) in features" :key="feature.title" class="feature-card"><div class="feature-top"><component :is="feature.icon" :size="27" :stroke-width="1.4" aria-hidden="true" /><span>{{ String(index + 1).padStart(2, '0') }}</span></div><h3>{{ feature.title }}</h3><p>{{ feature.body }}</p></div></div>
                </div>
            </section>

            <!-- FIND THE ACTUAL CART -->
            <section id="find-us" class="visit-section">
                <div class="page-wrap visit-grid">
                    <div class="visit-copy">
                        <p class="eyebrow">COME BY ANYTIME WE'RE SERVING</p>
                        <h2>Find us<br /><em>at the plaza.</em></h2>
                        <p>We're popping up at the Luisiana town plaza. For our latest schedule, what's available, and other coffee news, follow the Load Cafe Facebook page.</p>
                        <div class="visit-address"><MapPin :size="23" aria-hidden="true" /><div><strong>Luisiana Town Plaza</strong><span>Luisiana, Laguna 4032, Philippines</span></div></div>
                        <div class="visit-actions">
                            <a class="button-warm" href="https://www.google.com/maps/search/?api=1&amp;query=Luisiana%20Town%20Plaza%2C%20Laguna" target="_blank" rel="noopener noreferrer">Get directions <ArrowUpRight :size="18" aria-hidden="true" /></a>
                            <a class="visit-social" href="https://www.facebook.com/share/1BNYr7c35C/" target="_blank" rel="noopener noreferrer">Facebook updates <ArrowUpRight :size="18" aria-hidden="true" /></a>
                        </div>
                        <div class="visit-footnote"><Clock3 :size="15" aria-hidden="true" /> Pop-up schedule may vary. Check Facebook before visiting.</div>
                    </div>
                    <div class="visit-photos">
                        <div class="visit-menu-photo"><img src="/images/load-cafe/menu-board.webp" loading="lazy" alt="The real Load Cafe menu board displayed at the coffee cart" /><span>AT OUR CART · MENU SNAPSHOT</span></div>
                        <div class="visit-beans-photo"><img src="/images/load-cafe/coffee-beans.webp" loading="lazy" alt="Coffee beans in the Load Cafe grinder" /></div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="page-wrap footer-main">
                <a href="#top" class="brand-lockup"><span class="brand-disc"><img v-if="logoUrl" :src="logoUrl" class="brand-image" alt="" /><span v-else class="brand-monogram"><strong>LOAD</strong><small>Cafe</small></span></span><span class="brand-name"><strong>{{ brandName }}</strong><small>GOOD COFFEE. GOOD COMPANY.</small></span></a>
                <div class="footer-links"><a href="#menu">Menu</a><a href="#story">The cart</a><a href="#find-us">Find us</a><a href="https://www.facebook.com/share/1BNYr7c35C/" target="_blank" rel="noopener noreferrer">Facebook <ArrowUpRight :size="14" aria-hidden="true" /></a></div>
            </div>
            <div class="page-wrap footer-bottom"><span>© {{ new Date().getFullYear() }} {{ brandName }} · Luisiana, Laguna.</span><span>MADE FOR YOUR EVERYDAY COFFEE MOMENT ✳</span></div>
        </footer>

        <!-- Product details retain live product data, responsive dialog and swipe-friendly menu browsing. -->
        <Teleport to="body">
            <Transition name="fade"><div v-if="sheetOpen" class="sheet-backdrop" @click="closeSheet"></div></Transition>
            <Transition name="sheet"><section v-if="sheetOpen && selectedProduct" class="product-sheet" role="dialog" aria-modal="true" :aria-label="selectedProduct.name">
                <div class="sheet-drag-handle"></div>
                <div class="sheet-top"><button type="button" @click="closeSheet"><ArrowLeft :size="17" aria-hidden="true" /> Back to menu</button><span>{{ selectedProduct.category.name }}</span></div>
                <div class="sheet-image"><img v-if="selectedProduct.image" :src="selectedProduct.image" :alt="selectedProduct.name" /><div v-else class="sheet-placeholder"><Coffee :size="64" :stroke-width="1.2" aria-hidden="true" /> LOAD CAFE</div></div>
                <div class="sheet-details"><p class="eyebrow eyebrow-dark">SOMETHING GOOD FROM THE CART</p><div class="sheet-product-heading"><h2>{{ selectedProduct.name }}</h2><strong>{{ formatPrice(selectedProduct.price) }}</strong></div><p v-if="selectedProduct.description">{{ selectedProduct.description }}</p><p v-else>Made for your next coffee break.</p><div class="sheet-hint"><Coffee :size="18" aria-hidden="true" /> Ask for this at the Load Cafe cart.</div></div>
            </section></Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.load-site{--ink:#19120f;--ink-soft:#241a15;--wood:#35251b;--amber:#cf9a65;--amber-soft:#edc6a0;--cream:#f0e5d5;--warm-white:#fff9ed;--muted:#a99a8a;background:var(--ink);color:var(--warm-white);font-family:Inter,"Instrument Sans",system-ui,sans-serif;overflow-x:clip}
.load-site *{box-sizing:border-box}
.load-site a,.load-site button{transition:background-color .2s ease,color .2s ease,border-color .2s ease,transform .2s ease}
.load-site a:focus-visible,.load-site button:focus-visible,.load-site input:focus-visible{outline:2px solid #dcaa78;outline-offset:3px}
.page-wrap{width:min(100% - 48px,1240px);margin-inline:auto}
.site-header{position:sticky;top:0;z-index:55;background:rgba(23,16,13,.97);border-bottom:1px solid #ffffff1a;backdrop-filter:blur(18px)}
.site-nav{min-height:84px;display:flex;align-items:center;justify-content:space-between;gap:28px}
.brand-lockup{display:inline-flex;align-items:center;gap:12px;text-decoration:none;color:#f3e9dd;flex-shrink:0}
.brand-disc{width:54px;height:54px;border-radius:100%;background:#0e0e0e;border:1px solid #ffffff30;display:flex;align-items:center;justify-content:center;overflow:hidden}
.brand-image{width:100%;height:100%;object-fit:contain}
.brand-monogram{display:flex;align-items:center;justify-content:center;flex-direction:column;line-height:1.02;color:#f9f3e9;letter-spacing:-1.2px}
.brand-monogram strong{font-weight:700;font-size:15px}.brand-monogram small{font-size:8px;letter-spacing:.8px;font-weight:400;margin-top:3px}
.brand-name{display:flex;flex-direction:column;gap:4px}.brand-name strong{font-size:15px;letter-spacing:.035em;font-weight:750}.brand-name small{font-size:9px;letter-spacing:.16em;color:#c1ac98}
.desktop-nav{display:flex;gap:34px;align-items:center}.desktop-nav a,.staff-link{font-size:13px;color:#d9cfc3;text-decoration:none}.desktop-nav a:hover,.staff-link:hover{color:#eab77d}
.nav-actions{display:flex;align-items:center;gap:20px}.nav-cta{display:inline-flex;gap:9px;align-items:center;padding:12px 20px;border:1px solid #ad896b;border-radius:2px;font-size:12px;letter-spacing:.045em;text-decoration:none;color:#f8e8d7}.nav-cta:hover{background:#cd965e;color:#211811}
.mobile-menu-button{display:none;padding:8px;border:1px solid #6a5545;color:#f7eee5;background:transparent;border-radius:3px}.mobile-nav{padding:8px 24px 18px;border-top:1px solid #ffffff20;display:flex;flex-direction:column;gap:2px}.mobile-nav a{display:block;padding:12px;color:#ebdccb;text-decoration:none;border-bottom:1px solid #ffffff12}
.hero-section{position:relative;isolation:isolate;overflow:hidden;background:radial-gradient(ellipse at 78% 40%,#4a2c1c80,transparent 53%),repeating-linear-gradient(95deg,#ffffff03 0 1px,transparent 1px 38px),linear-gradient(135deg,#25180f,#100d0c 70%)}
.hero-glow{position:absolute;width:530px;height:530px;right:-170px;top:-140px;background:radial-gradient(circle,#b2763540,transparent 70%);filter:blur(30px);pointer-events:none}
.hero-grid{position:relative;display:grid;grid-template-columns:.9fr 1.1fr;gap:clamp(36px,6vw,100px);min-height:660px;align-items:center;padding-block:75px 65px}
.hero-copy{position:relative;z-index:1}.eyebrow{color:var(--amber-soft);font-size:10px;font-weight:700;letter-spacing:.18em;line-height:1.8;display:flex;align-items:center;flex-wrap:wrap;gap:9px;text-transform:uppercase}
.dot{height:7px;width:7px;background:var(--amber);display:inline-block;border-radius:50%}.eyebrow-line{width:24px;height:1px;background:#8c6549}
.hero-copy h1,.story-copy h2,.motto-copy h2,.features-heading h2,.visit-copy h2,.menu-intro h2{font-family:Georgia,"Times New Roman",serif;font-weight:400;letter-spacing:-.055em;line-height:.98}
.hero-copy h1{font-size:clamp(64px,7vw,106px);margin:25px 0 25px}.hero-copy h1 em,.story-copy h2 em,.motto-copy h2 em,.features-heading h2 em,.visit-copy h2 em,.menu-intro h2 em{font-weight:400;font-style:italic;color:var(--amber)}
.hero-description{font-size:16px;line-height:1.8;color:#bcb0a4;max-width:460px}.hero-actions{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin:32px 0 30px}
.button-warm,.button-outline,.text-link{min-height:49px;display:inline-flex;align-items:center;justify-content:center;gap:14px;padding:12px 20px;text-decoration:none;font-weight:700;font-size:13px;border-radius:2px}
.button-warm{background:var(--amber);color:#1c130e}.button-warm:hover{background:#e6b680;transform:translateY(-1px)}.button-outline{border:1px solid #74604e;color:#e8d8c7}.button-outline:hover{background:#ffffff12}
.hero-signoff{border-top:1px solid #ffffff20;padding-top:17px;max-width:440px;display:flex;align-items:center;gap:12px;color:#aa9581;font-size:12px}
.hero-photos{position:relative;min-width:0;height:545px}.photo-frame{position:absolute;overflow:hidden;background:#201a17;box-shadow:0 25px 60px #00000080}.photo-frame img,.story-photo-set img,.motto-photo img,.visit-photos img,.product-thumbnail img,.sheet-image img{width:100%;height:100%;object-fit:cover;display:block}
.hero-main-photo{inset:2% 6% 3% 18%;border:8px solid #38261b;transform:rotate(1.5deg)}.hero-main-photo img{object-position:center}
.photo-gradient{position:absolute;inset:55% 0 0;background:linear-gradient(transparent,#0d0909be)}.photo-label{position:absolute;left:23px;bottom:19px;right:23px;font-size:10px;letter-spacing:.18em;color:#f5e5d0;display:flex;justify-content:space-between}
.hero-mini-photo{width:34%;height:40%;left:-3%;bottom:6%;border:7px solid #453022;transform:rotate(-6deg)}.hero-mini-photo img{object-position:50% 50%}
.hero-vertical-type{position:absolute;right:-1%;top:13%;font-size:9px;letter-spacing:.2em;writing-mode:vertical-rl;color:#ac8766}
.hero-plate{position:absolute;left:0;top:3%;width:120px;height:120px;border:1px solid #b98b5e70;background:#1b1410dc;display:flex;flex-direction:column;justify-content:center;align-items:center;gap:10px;transform:rotate(-8deg);text-align:center;box-shadow:0 10px 34px #0007}
.plate-mark{font-weight:800;font-size:22px;letter-spacing:-.09em}.hero-plate span:last-child{font-size:7px;line-height:1.6;letter-spacing:.15em;color:#d2ae88}
.hero-bottom{height:51px;display:flex;align-items:center;gap:22px;color:#9d8977;letter-spacing:.2em;font-size:9px}.hero-bottom-rule{height:1px;flex:1;background:#ffffff1e}
.ticker-strip{background:#b78659;color:#211712;padding-block:17px}.ticker-content{display:flex;align-items:center;justify-content:space-between;gap:22px;font-size:11px;letter-spacing:.17em;font-weight:800}.asterisk{font-size:18px;color:#52301d}
.menu-section{background:var(--cream);color:#2c2019;padding-bottom:100px;scroll-margin-top:85px}.menu-intro{display:flex;align-items:flex-end;justify-content:space-between;gap:40px;padding-block:85px 53px}
.eyebrow-dark{color:#92623f}.menu-intro h2{font-size:clamp(48px,6vw,82px);margin:13px 0 0}.menu-intro h2 em{color:#a26e47}.menu-intro .period{color:#b47e56}.menu-intro>p{max-width:320px;color:#756253;line-height:1.8;font-size:15px;margin-bottom:10px}
.menu-filter-wrap{position:sticky;top:84px;background:#f0e5d5f0;backdrop-filter:blur(14px);z-index:30;border-block:1px solid #cdbca8}.filter-top{display:flex;align-items:center;justify-content:space-between;gap:20px;padding-block:14px}
.search-field{display:flex;align-items:center;gap:12px;background:#fff8f0;border:1px solid #cebca9;padding:11px 13px;width:min(100%,430px);border-radius:3px;color:#816751}.search-field input{width:100%;color:#2d1f17;font-family:inherit;font-size:14px;background:transparent;border:0;outline:none}.search-field input::placeholder{color:#a28b77}.search-field button{background:none;border:0;display:flex;color:#795c44;cursor:pointer}.filter-count{font-size:10px;font-weight:800;letter-spacing:.18em;color:#927862}
.category-filter{display:flex;gap:8px;padding:0 0 14px;overflow-x:auto;scrollbar-width:none}.category-filter::-webkit-scrollbar{display:none}.category-filter button{border:1px solid #c7b49f;padding:9px 17px;background:transparent;color:#654d3e;font-size:12px;font-weight:750;white-space:nowrap;cursor:pointer}.category-filter button.active{background:#2e2119;color:#f4e6d4;border-color:#2e2119}.category-filter button:hover:not(.active){background:#e0cdb8}
.menu-body{padding-top:48px}.menu-group{padding-top:12px;margin-bottom:52px;scroll-margin-top:220px}.menu-group-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;border-bottom:1px solid #cbbba9;padding-bottom:17px;margin-bottom:23px}
.group-title{display:flex;align-items:center;gap:18px}.group-title>span{font-size:12px;letter-spacing:.1em;color:#a57b59;font-weight:800}.group-title h3{font-family:Georgia,serif;font-weight:400;font-size:clamp(25px,3vw,37px);margin:0}.group-count{font-size:11px;color:#917e6b;white-space:nowrap}
.product-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px}.product-card{border:1px solid #d4c6b4;background:#f8efe3;display:flex;flex-direction:column;padding:0;text-align:left;color:#241b17;cursor:pointer;min-width:0;overflow:hidden;box-shadow:0 7px 24px #5b37120c}.product-card:hover{border-color:#aa7855;transform:translateY(-3px);box-shadow:0 12px 27px #5b371121}
.product-thumbnail{height:176px;overflow:hidden;background:#d9c7af}.product-thumbnail img{transition:transform .4s ease}.product-card:hover img{transform:scale(1.04)}
.product-placeholder{height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:11px;color:#9f7b5e;background:repeating-linear-gradient(35deg,#ead9c6 0 3px,#e8d5be 3px 8px)}.product-placeholder small{font-size:9px;letter-spacing:.2em;font-weight:700}
.product-info{padding:18px 20px 16px;display:flex;flex-direction:column;flex:1}.product-kicker{font-size:9px;color:#9b7759;letter-spacing:.19em;font-weight:800;text-transform:uppercase}.product-info h4{font-size:18px;line-height:1.35;font-weight:750;margin:9px 0 4px}.product-info p{font-size:12px;line-height:1.6;color:#8d7968;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin:0 0 18px}.product-price{border-top:1px solid #decfbe;display:flex;justify-content:space-between;align-items:center;padding-top:12px;margin-top:auto}.product-price strong{font-size:17px;color:#76502f}.product-price>span{width:31px;height:31px;background:#e7d5c2;display:grid;place-items:center;color:#63432e}
.pagination{display:flex;align-items:center;justify-content:center;gap:20px;margin-top:27px}.pagination button{height:39px;width:39px;border:1px solid #cbb9a5;color:#614632;background:transparent;display:grid;place-items:center;cursor:pointer}.pagination button:disabled{opacity:.3;cursor:not-allowed}.pagination>span{font-weight:800;font-size:12px;letter-spacing:.1em}.pagination i{font-style:normal;color:#ad987e;padding:0 6px}
.empty-menu{text-align:center;padding:70px 15px;color:#725440;display:flex;flex-direction:column;align-items:center}.empty-menu h3{font-family:Georgia,serif;font-size:31px;font-weight:400;margin:17px 0 7px}.empty-menu p{font-size:14px;color:#897360;line-height:1.6}.empty-menu a,.empty-menu button{margin-top:16px;color:#794820;background:transparent;border:0;border-bottom:1px solid #a47d5e;display:flex;align-items:center;gap:9px;cursor:pointer;font-size:13px;font-weight:750}
.story-section{padding:125px 0;background:radial-gradient(circle at 5% 80%,#51301d55,transparent 35%),#1b1511;scroll-margin-top:84px}.story-grid{display:grid;grid-template-columns:1.1fr .9fr;align-items:center;gap:clamp(44px,7vw,100px)}.story-photo-set{height:520px;position:relative}.story-wide-photo{position:absolute;top:0;left:0;width:85%;height:75%;border:8px solid #34261b;box-shadow:0 18px 50px #0007}.story-small-photo{position:absolute;bottom:0;right:0;width:49%;height:51%;border:8px solid #3e2a1b;transform:rotate(4deg);box-shadow:0 20px 40px #0009}.story-photo-credit{position:absolute;bottom:9%;left:0;letter-spacing:.18em;font-size:9px;color:#c7a17f;writing-mode:vertical-rl;transform:rotate(180deg)}
.story-copy h2{font-size:clamp(48px,5vw,79px);margin:24px 0}.story-copy p:not(.eyebrow){font-size:15px;line-height:1.88;color:#b3a495;margin:0 0 17px}.text-link{border:0;border-bottom:1px solid #ae805b;color:#eac5a5;padding-left:0;padding-right:0;margin-top:18px}.text-link:hover{color:#fff}
.motto-section{background:#c49972;padding-block:80px;color:#221812}.motto-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center}.motto-copy h2{font-size:clamp(46px,5vw,80px);margin:24px 0}.motto-copy h2 em{color:#6d422c}.motto-copy p:not(.eyebrow){font-size:16px;line-height:1.8;color:#533b2d;max-width:430px}.motto-link{color:#24170f;display:inline-flex;gap:12px;align-items:center;border-bottom:1px solid #5d402c;font-size:13px;font-weight:800;margin-top:22px;padding-bottom:9px;text-decoration:none}.motto-photo{height:470px;position:relative;background:#251d18;border:9px solid #3c2c21;box-shadow:20px 20px 0 #a57754}.motto-photo img{object-position:center 45%}.motto-photo>span{position:absolute;bottom:12px;right:14px;letter-spacing:.18em;font-size:9px;color:#ebd7c2;background:#1e1716c9;padding:7px 9px}
.features-section{padding:100px 0 115px;background:#221a15}.features-heading h2{font-size:clamp(45px,5vw,75px);margin:20px 0 46px}.features-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.feature-card{border:1px solid #684e383a;padding:28px 24px 32px;background:#ffffff05;min-height:235px}.feature-top{display:flex;align-items:start;justify-content:space-between;color:#c2956f;margin-bottom:38px}.feature-top>span{font-size:10px;letter-spacing:.12em;color:#806e5d}.feature-card h3{font-size:16px;font-weight:750;margin:0 0 10px}.feature-card p{color:#ae9f90;font-size:13px;line-height:1.7}
.visit-section{padding:110px 0;background:radial-gradient(circle at 78% 40%,#4a2f1c88,transparent 58%),#120f0d;scroll-margin-top:80px}.visit-grid{display:grid;grid-template-columns:.95fr 1.05fr;gap:clamp(40px,7vw,110px);align-items:center}.visit-copy h2{font-size:clamp(54px,6vw,90px);margin:23px 0}.visit-copy>p:not(.eyebrow){font-size:15px;color:#b4a392;line-height:1.85;max-width:480px}
.visit-address{display:flex;gap:15px;align-items:center;padding:25px 0;margin:24px 0;border-block:1px solid #ffffff21;color:#cfa176}.visit-address div{display:flex;flex-direction:column;gap:5px}.visit-address strong{font-size:16px;color:#f5e9dc}.visit-address span{font-size:13px;color:#a99989}.visit-actions{display:flex;flex-wrap:wrap;gap:15px;align-items:center}.visit-social{color:#e9c9a9;display:inline-flex;gap:8px;align-items:center;font-weight:750;font-size:13px;text-decoration:none}.visit-footnote{display:flex;align-items:center;gap:8px;margin-top:23px;color:#8e7d6f;font-size:11px;line-height:1.5}
.visit-photos{position:relative;height:560px}.visit-menu-photo{position:absolute;inset:0 17% 4% 11%;border:8px solid #4b3426;box-shadow:0 20px 70px #0009;overflow:hidden}.visit-menu-photo img{object-position:center}.visit-menu-photo span{position:absolute;bottom:14px;left:14px;background:#16110fdb;font-size:9px;letter-spacing:.16em;padding:9px 12px}.visit-beans-photo{position:absolute;bottom:0;right:0;width:40%;height:34%;border:7px solid #65442c;transform:rotate(6deg);box-shadow:0 18px 40px #0009;overflow:hidden}
.site-footer{background:#100d0c;border-top:1px solid #ffffff1d;padding:43px 0 20px}.footer-main{display:flex;justify-content:space-between;align-items:center;gap:25px;padding-bottom:34px}.footer-links{display:flex;gap:27px;flex-wrap:wrap}.footer-links a{color:#c7b29e;font-size:12px;text-decoration:none;display:inline-flex;align-items:center;gap:3px}.footer-links a:hover{color:#e7b48b}.footer-bottom{border-top:1px solid #ffffff15;padding-top:19px;display:flex;justify-content:space-between;gap:18px;flex-wrap:wrap;color:#78695e;font-size:10px;letter-spacing:.08em}
.sheet-backdrop{position:fixed;z-index:89;inset:0;background:#100c0bbd;backdrop-filter:blur(5px)}.product-sheet{position:fixed;z-index:90;top:50%;left:50%;transform:translate(-50%,-50%);width:min(92vw,495px);max-height:90dvh;overflow-y:auto;color:#281d16;background:#f3e6d5;box-shadow:0 25px 110px #000b;border:1px solid #b9a186}.sheet-drag-handle{display:none}.sheet-top{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 22px;border-bottom:1px solid #d6bfaa}.sheet-top button{display:inline-flex;align-items:center;gap:9px;background:none;border:0;font-size:13px;cursor:pointer;color:#533726}.sheet-top>span{font-size:10px;color:#886f5b;text-transform:uppercase;letter-spacing:.11em}.sheet-image{height:280px;background:#ddc8b2}.sheet-placeholder{height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:15px;color:#967a62;font-size:11px;letter-spacing:.2em}.sheet-details{padding:26px 28px 34px}.sheet-product-heading{display:flex;justify-content:space-between;gap:18px;align-items:start}.sheet-product-heading h2{font-size:27px;line-height:1.2;font-family:Georgia,serif;margin:12px 0}.sheet-product-heading strong{font-size:18px;white-space:nowrap;margin-top:17px;color:#76502e}.sheet-details>p:not(.eyebrow){font-size:14px;line-height:1.7;color:#826955}.sheet-hint{display:flex;align-items:center;gap:10px;border-top:1px solid #cdb7a4;padding-top:19px;margin-top:27px;color:#7c5c43;font-size:12px}
.fade-enter-active,.fade-leave-active{transition:opacity .25s}.fade-enter-from,.fade-leave-to{opacity:0}.sheet-enter-active,.sheet-leave-active{transition:opacity .2s,transform .3s}.sheet-enter-from,.sheet-leave-to{opacity:0;transform:translate(-50%,calc(-50% + 22px))}
@media(max-width:1100px){.desktop-nav{gap:17px}.hero-grid{gap:35px}.hero-copy h1{font-size:76px}.features-grid{grid-template-columns:repeat(2,1fr)}.motto-grid{gap:45px}}
@media(max-width:820px){.page-wrap{width:min(100% - 36px,1240px)}.site-nav{min-height:72px}.desktop-nav{display:none}.mobile-menu-button{display:grid;place-items:center}.nav-cta{display:none}.hero-grid{grid-template-columns:1fr;padding-block:60px 45px;gap:32px;min-height:0}.hero-copy h1{font-size:clamp(61px,11vw,95px)}.hero-description{font-size:15px}.hero-photos{height:500px;max-width:620px;width:100%;justify-self:center}.menu-filter-wrap{top:72px}.menu-intro{padding-block:70px 40px}.product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.story-grid,.motto-grid,.visit-grid{grid-template-columns:1fr}.story-grid{gap:65px}.story-copy{order:-1}.story-photo-set{height:420px}.motto-photo{max-width:560px;width:100%;justify-self:center}.visit-photos{height:480px;max-width:590px;width:100%;justify-self:center}}
@media(max-width:560px){.page-wrap{width:min(100% - 32px,1240px)}.site-nav{min-height:67px;gap:12px}.brand-disc{width:43px;height:43px}.brand-name strong{font-size:13px}.brand-name small{font-size:7px}.staff-link{font-size:11px}.nav-actions{gap:9px}.hero-grid{padding-block:43px 33px}.hero-copy h1{font-size:clamp(56px,15vw,83px);margin:18px 0 20px}.eyebrow{font-size:8px;letter-spacing:.12em;gap:7px}.hero-actions{gap:9px}.button-warm,.button-outline{font-size:12px;min-height:45px;padding:11px 15px}.hero-photos{height:360px}.hero-main-photo{inset:2% 9% 2% 13%;border-width:6px}.hero-mini-photo{border-width:5px}.hero-plate{width:83px;height:83px}.plate-mark{font-size:15px}.hero-plate span:last-child{font-size:5px}.hero-vertical-type{font-size:7px}.hero-bottom{font-size:7px;letter-spacing:.1em}.ticker-content{flex-wrap:wrap;justify-content:center;gap:9px 13px;text-align:center;font-size:9px}.ticker-content .asterisk:nth-of-type(4){display:none}.menu-intro{display:block;padding-block:59px 31px}.menu-intro h2{font-size:55px}.menu-intro>p{font-size:13px;line-height:1.7;margin-top:20px}.filter-count{font-size:9px;letter-spacing:.04em;white-space:nowrap}.search-field{max-width:unset;min-width:0;flex:1}.search-field input{font-size:12px}.category-filter button{font-size:11px;padding:9px 12px}.menu-body{padding-top:32px}.menu-group{scroll-margin-top:186px}.menu-group-heading{align-items:flex-end}.group-title{gap:10px}.group-title h3{font-size:25px}.group-count{font-size:10px}.product-grid{gap:9px}.product-thumbnail{height:135px}.product-info{padding:12px}.product-kicker{font-size:8px}.product-info h4{font-size:14px;margin:5px 0}.product-info p{font-size:11px;margin-bottom:10px}.product-price strong{font-size:14px}.product-price>span{width:25px;height:25px}.story-section,.visit-section{padding:75px 0}.story-copy h2,.motto-copy h2,.visit-copy h2,.features-heading h2{font-size:53px}.story-photo-set{height:335px}.motto-section{padding:65px 0}.motto-photo{height:370px}.features-section{padding:75px 0}.features-grid{gap:9px}.feature-card{padding:20px 15px;min-height:202px}.feature-top{margin-bottom:24px}.feature-card h3{font-size:14px}.feature-card p{font-size:11px}.visit-photos{height:400px}.footer-main{align-items:flex-start;flex-direction:column}.footer-links{gap:20px}.footer-bottom{font-size:9px}.product-sheet{width:100%;max-height:92dvh;top:auto;left:0;bottom:0;transform:none;border-radius:16px 16px 0 0}.sheet-drag-handle{display:block;width:38px;height:4px;border-radius:3px;background:#b69f88;margin:10px auto 0}.sheet-image{height:235px}.sheet-enter-from,.sheet-leave-to{transform:translateY(100%)}}
@media(prefers-reduced-motion:reduce){.load-site *{scroll-behavior:auto!important;animation:none!important;transition:none!important}}
</style>
