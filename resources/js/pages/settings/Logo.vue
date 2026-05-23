<script setup lang="ts">
import { ref } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Upload, Trash2, ImageIcon, Type, RotateCcw } from 'lucide-vue-next'

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Settings', href: '/settings/profile' },
            { title: 'Logo', href: '/settings/logo' },
        ],
    },
})

const props = defineProps<{
    currentLogoUrl:  string | null
    currentLogoText: string
}>()

const page = usePage()
const globalLogoText = (page.props as any).logoText as string | null

// ── Image upload ─────────────────────────────────────────────────────────────
const preview   = ref<string | null>(props.currentLogoUrl)
const file      = ref<File | null>(null)
const uploading = ref(false)
const dropping  = ref(false)

const onFile = (e: Event) => {
    const f = (e.target as HTMLInputElement).files?.[0]
    if (!f) return
    file.value = f
    preview.value = URL.createObjectURL(f)
}

const onDrop = (e: DragEvent) => {
    dropping.value = false
    const f = e.dataTransfer?.files[0]
    if (!f || !f.type.startsWith('image/')) return
    file.value = f
    preview.value = URL.createObjectURL(f)
}

const upload = () => {
    if (!file.value) return
    uploading.value = true
    const formData = new FormData()
    formData.append('logo', file.value)
    router.post('/settings/logo', formData, {
        preserveScroll: true,
        onSuccess: () => { file.value = null; toast.success('Logo updated') },
        onError: (errors) => toast.error(Object.values(errors)[0] as string ?? 'Upload failed'),
        onFinish: () => { uploading.value = false },
    })
}

const removeLogo = () => {
    if (!confirm('Reset to the default logo?')) return
    router.delete('/settings/logo', {
        preserveScroll: true,
        onSuccess: () => { preview.value = null; file.value = null; toast.success('Logo reset') },
        onError: () => toast.error('Failed to reset logo'),
    })
}

// ── Logo text ────────────────────────────────────────────────────────────────
const logoText       = ref(props.currentLogoText || globalLogoText || 'Load Cafe')
const savingText     = ref(false)

const saveLogoText = () => {
    if (!logoText.value.trim()) return
    savingText.value = true
    router.post('/settings/logo/text', { logo_text: logoText.value.trim() }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Logo text updated'),
        onError: (errors) => toast.error(Object.values(errors)[0] as string ?? 'Failed to save'),
        onFinish: () => { savingText.value = false },
    })
}

const resetLogoText = () => {
    if (!confirm('Reset logo text to the default?')) return
    router.delete('/settings/logo/text', {
        preserveScroll: true,
        onSuccess: () => { logoText.value = 'Load Cafe'; toast.success('Logo text reset') },
        onError: () => toast.error('Failed to reset logo text'),
    })
}
</script>

<template>
    <Head title="Logo Settings" />

    <div class="space-y-6">
        <div>
            <h2 class="text-base font-semibold">Logo</h2>
            <p class="text-sm text-muted-foreground mt-0.5">Customize the logo image and text shown in the sidebar and login page.</p>
        </div>

        <!-- ── Logo Text ──────────────────────────────────────────────────── -->
        <div class="rounded-xl border bg-card shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2">
                <Type class="h-4 w-4 text-muted-foreground" />
                <h3 class="font-semibold text-sm">Logo Text</h3>
            </div>
            <p class="text-xs text-muted-foreground">
                This text appears next to the logo icon in the sidebar and on the login page.
            </p>

            <div class="flex items-center gap-3">
                <input
                    v-model="logoText"
                    type="text"
                    maxlength="60"
                    placeholder="Load Cafe"
                    class="flex-1 rounded-lg border bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                />
                <button
                    @click="saveLogoText"
                    :disabled="savingText || !logoText.trim()"
                    class="flex items-center gap-1.5 rounded-lg bg-primary px-5 py-2 text-sm font-bold text-primary-foreground hover:bg-primary/90 disabled:opacity-40"
                >
                    {{ savingText ? 'Saving…' : 'Save' }}
                </button>
                <button
                    v-if="currentLogoText"
                    @click="resetLogoText"
                    class="flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                    title="Reset to default"
                >
                    <RotateCcw class="h-3.5 w-3.5" />
                </button>
            </div>
            <p class="text-xs text-muted-foreground">{{ logoText.length }}/60 characters</p>
        </div>

        <!-- ── Upload Logo Image ──────────────────────────────────────────── -->
        <div class="rounded-xl border bg-card shadow-sm p-5 space-y-4">
            <div class="flex items-center gap-2">
                <ImageIcon class="h-4 w-4 text-muted-foreground" />
                <h3 class="font-semibold text-sm">Logo Image</h3>
            </div>

            <label
                @dragover.prevent="dropping = true"
                @dragleave="dropping = false"
                @drop.prevent="onDrop"
                :class="[
                    'flex flex-col items-center justify-center gap-3 rounded-lg border-2 border-dashed p-8 cursor-pointer transition',
                    dropping ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50 hover:bg-muted/30',
                ]"
            >
                <input type="file" accept="image/png,image/jpeg,image/webp" class="hidden" @change="onFile" />

                <div v-if="preview" class="flex flex-col items-center gap-3">
                    <img :src="preview" class="h-20 w-20 object-contain rounded-lg border bg-white p-1" alt="Logo preview" />
                    <p class="text-xs text-muted-foreground">Click or drag to replace</p>
                </div>
                <div v-else class="flex flex-col items-center gap-2 text-muted-foreground">
                    <ImageIcon class="h-10 w-10 opacity-40" />
                    <p class="text-sm font-medium">Click to upload or drag & drop</p>
                    <p class="text-xs">PNG, JPG, or WebP — max 2MB</p>
                </div>
            </label>

            <p class="text-xs text-muted-foreground">
                Recommended: square image, at least 128&times;128 px. Transparent PNG works best.
            </p>

            <div class="flex items-center gap-3">
                <button
                    @click="upload"
                    :disabled="!file || uploading"
                    class="flex items-center gap-1.5 rounded-lg bg-primary px-5 py-2 text-sm font-bold text-primary-foreground hover:bg-primary/90 disabled:opacity-40"
                >
                    <Upload class="h-3.5 w-3.5" />
                    {{ uploading ? 'Uploading…' : 'Save Logo' }}
                </button>
                <button
                    v-if="currentLogoUrl"
                    @click="removeLogo"
                    class="flex items-center gap-1.5 rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                    Remove Logo
                </button>
            </div>
        </div>

        <!-- ── Preview ────────────────────────────────────────────────────── -->
        <div class="rounded-xl border bg-card shadow-sm p-5 space-y-3">
            <h3 class="font-semibold text-sm">Preview</h3>
            <div class="flex items-center gap-3 rounded-lg bg-sidebar p-3 w-fit">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sidebar-primary overflow-hidden">
                    <img v-if="preview" :src="preview" class="h-8 w-8 object-contain" alt="Preview" />
                    <svg v-else xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" class="h-5 w-5 fill-white dark:fill-black">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                    </svg>
                </div>
                <span class="text-sm font-semibold text-sidebar-foreground">{{ logoText || 'Load Cafe' }}</span>
            </div>
            <p class="text-xs text-muted-foreground">This is how the logo appears in the sidebar.</p>
        </div>
    </div>
</template>
