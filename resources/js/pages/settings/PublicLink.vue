<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import QRCode from 'qrcode'
import { Copy, Download, RefreshCw, Globe } from 'lucide-vue-next'

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Settings', href: '/settings/profile' },
            { title: 'Public Link', href: '/settings/public-link' },
        ],
    },
})

const props = defineProps<{
    siteUrl: string
    brandName: string
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
const qrDataUrl = ref<string | null>(null)
const generating = ref(false)

async function generateQR() {
    generating.value = true
    try {
        qrDataUrl.value = await QRCode.toDataURL(props.siteUrl, {
            width: 400,
            margin: 2,
            color: { dark: '#000000', light: '#ffffff' },
            errorCorrectionLevel: 'M',
        })
    } catch {
        toast.error('Failed to generate QR code.')
    } finally {
        generating.value = false
    }
}

function copyUrl() {
    navigator.clipboard.writeText(props.siteUrl)
        .then(() => toast.success('URL copied to clipboard.'))
        .catch(() => toast.error('Failed to copy URL.'))
}

function downloadQR() {
    if (!qrDataUrl.value) return
    const a = document.createElement('a')
    a.href = qrDataUrl.value
    a.download = `${props.brandName.replace(/\s+/g, '-').toLowerCase()}-qr.png`
    a.click()
}

onMounted(() => generateQR())
</script>

<template>
    <Head title="Public Link" />

    <div class="space-y-6">
        <div>
            <h2 class="text-base font-semibold">Public Link</h2>
            <p class="text-sm text-muted-foreground mt-0.5">
                Share your website link or QR code with customers.
            </p>
        </div>

        <!-- URL card -->
        <div class="rounded-xl border bg-card shadow-sm p-5 space-y-3">
            <h3 class="font-semibold text-sm flex items-center gap-2">
                <Globe class="h-4 w-4" /> Website URL
            </h3>
            <div class="flex items-center gap-2">
                <input
                    :value="siteUrl"
                    readonly
                    class="flex-1 rounded-lg border bg-muted px-3 py-2 text-sm font-mono text-muted-foreground select-all"
                    @focus="($event.target as HTMLInputElement).select()"
                />
                <button
                    @click="copyUrl"
                    class="flex items-center gap-1.5 rounded-lg border px-4 py-2 text-sm font-medium hover:bg-muted transition-colors"
                >
                    <Copy class="h-3.5 w-3.5" />
                    Copy
                </button>
            </div>
        </div>

        <!-- QR card -->
        <div class="rounded-xl border bg-card shadow-sm p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-sm">QR Code</h3>
                <button
                    @click="generateQR"
                    :disabled="generating"
                    class="flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted transition-colors disabled:opacity-40"
                >
                    <RefreshCw class="h-3 w-3" :class="{ 'animate-spin': generating }" />
                    Regenerate
                </button>
            </div>

            <div class="flex flex-col items-center gap-4">
                <div class="rounded-xl border bg-white p-4 shadow-sm">
                    <img
                        v-if="qrDataUrl"
                        :src="qrDataUrl"
                        alt="QR code"
                        class="h-48 w-48 block"
                    />
                    <div v-else class="h-48 w-48 flex items-center justify-center text-muted-foreground text-xs">
                        Generating…
                    </div>
                </div>

                <p class="text-xs text-muted-foreground text-center max-w-xs">
                    Customers can scan this code to visit <span class="font-medium">{{ brandName }}</span>'s website.
                </p>

                <button
                    @click="downloadQR"
                    :disabled="!qrDataUrl"
                    class="flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-bold text-primary-foreground hover:bg-primary/90 disabled:opacity-40 transition-colors"
                >
                    <Download class="h-4 w-4" />
                    Download PNG
                </button>
            </div>
        </div>
    </div>
</template>
