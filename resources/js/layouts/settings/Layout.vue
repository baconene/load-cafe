<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { edit as editSecurity } from '@/routes/security';
import type { NavItem } from '@/types';
import { ChevronDown, Search, X } from 'lucide-vue-next';

const page = usePage();
const isAdmin = computed(() => (page.props.auth?.roles as string[] ?? []).includes('admin'));

const baseNavItems: NavItem[] = [
    { title: 'Profile',    href: editProfile() },
    { title: 'Security',   href: editSecurity() },
    { title: 'Appearance', href: editAppearance() },
    { title: 'Printing',   href: '/settings/printing' },
];

const sidebarNavItems = computed<NavItem[]>(() => {
    const items = [...baseNavItems];
    if (isAdmin.value) {
        items.push({ title: 'Page Content',    href: '/settings/page-content' });
        items.push({ title: 'Media',           href: '/settings/media' });
        items.push({ title: 'Prices',          href: '/settings/prices' });
        items.push({ title: 'Advertisements',  href: '/settings/advertisements' });
        items.push({ title: 'Payment Tenders', href: '/settings/payment-tenders' });
        items.push({ title: 'Print Service',   href: '/settings/print-service' });
        items.push({ title: 'Users',           href: '/settings/users' });
        items.push({ title: 'Logo',            href: '/settings/logo' });
        items.push({ title: 'Date & Time',     href: '/settings/clock' });
        items.push({ title: 'Kitchen',         href: '/settings/kitchen' });
        items.push({ title: 'HRIS',            href: '/settings/hris' });
        items.push({ title: 'Public Link',     href: '/settings/public-link' });
        items.push({ title: 'System',          href: '/settings/system' });
    }
    return items;
});

const { isCurrentOrParentUrl } = useCurrentUrl();

// ── Search ───────────────────────────────────────────────────────────────────
const search = ref('');

const filteredItems = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return sidebarNavItems.value;
    return sidebarNavItems.value.filter(i => i.title.toLowerCase().includes(q));
});

const activeItem = computed(() =>
    sidebarNavItems.value.find(i => isCurrentOrParentUrl(i.href)) ?? sidebarNavItems.value[0]
);

// ── Mobile dropdown ──────────────────────────────────────────────────────────
const dropdownOpen = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const mobileSearchRef = ref<HTMLInputElement | null>(null);

function toggleDropdown() {
    dropdownOpen.value = !dropdownOpen.value;
    if (dropdownOpen.value) {
        search.value = '';
        setTimeout(() => mobileSearchRef.value?.focus(), 50);
    }
}

function navigateTo(href: string) {
    dropdownOpen.value = false;
    search.value = '';
    router.visit(href);
}

function onClickOutside(e: MouseEvent) {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
        dropdownOpen.value = false;
    }
}

onMounted(() => document.addEventListener('mousedown', onClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', onClickOutside));
</script>

<template>
    <div class="px-4 py-6">
        <Heading title="Settings" description="Manage your profile and account settings" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">

            <!-- ── Mobile dropdown ─────────────────────────────────────── -->
            <div class="lg:hidden relative" ref="dropdownRef">
                <button
                    @click="toggleDropdown"
                    class="w-full flex items-center justify-between gap-2 rounded-lg border bg-card px-4 py-2.5 text-sm font-medium shadow-sm hover:bg-muted/50 transition-colors"
                    :aria-expanded="dropdownOpen"
                >
                    <span>{{ activeItem?.title ?? 'Settings' }}</span>
                    <ChevronDown
                        class="h-4 w-4 text-muted-foreground transition-transform duration-200"
                        :class="{ 'rotate-180': dropdownOpen }"
                    />
                </button>

                <!-- Dropdown panel -->
                <div
                    v-if="dropdownOpen"
                    class="absolute z-50 mt-1 w-full rounded-xl border bg-popover shadow-lg overflow-hidden"
                >
                    <!-- Search input -->
                    <div class="p-2 border-b">
                        <div class="relative">
                            <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                            <input
                                ref="mobileSearchRef"
                                v-model="search"
                                type="text"
                                placeholder="Search settings…"
                                class="w-full rounded-md border-0 bg-muted pl-8 pr-8 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                            />
                            <button
                                v-if="search"
                                @click="search = ''"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="max-h-64 overflow-y-auto py-1">
                        <button
                            v-for="item in filteredItems"
                            :key="toUrl(item.href)"
                            @click="navigateTo(item.href)"
                            class="w-full text-left px-4 py-2 text-sm hover:bg-muted transition-colors"
                            :class="{ 'bg-muted font-semibold': isCurrentOrParentUrl(item.href) }"
                        >
                            {{ item.title }}
                        </button>
                        <p v-if="filteredItems.length === 0" class="px-4 py-3 text-sm text-muted-foreground text-center">
                            No settings found.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Desktop sidebar ─────────────────────────────────────── -->
            <aside class="hidden lg:block w-48 shrink-0">
                <!-- Search -->
                <div class="relative mb-2">
                    <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-muted-foreground pointer-events-none" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search…"
                        class="w-full rounded-lg border bg-background pl-8 pr-7 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                    />
                    <button
                        v-if="search"
                        @click="search = ''"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                </div>

                <nav class="flex flex-col space-y-0.5" aria-label="Settings">
                    <Link
                        v-for="item in filteredItems"
                        :key="toUrl(item.href)"
                        :href="item.href"
                        class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm transition-colors hover:bg-muted"
                        :class="isCurrentOrParentUrl(item.href)
                            ? 'bg-muted font-semibold text-foreground'
                            : 'text-muted-foreground'"
                    >
                        <component :is="item.icon" v-if="item.icon" class="h-4 w-4 shrink-0" />
                        {{ item.title }}
                    </Link>
                    <p v-if="filteredItems.length === 0" class="px-3 py-2 text-xs text-muted-foreground">
                        No results.
                    </p>
                </nav>
            </aside>

            <Separator class="my-4 lg:hidden" />

            <!-- ── Content ─────────────────────────────────────────────── -->
            <div class="flex-1 min-w-0">
                <section class="space-y-6">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
