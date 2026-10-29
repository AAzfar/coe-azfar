<script setup lang="ts">
interface Product {
  id: number
  name: string
  sku: string
  category: string
  price: string
  stock: number
  unit: string | null
  image_url: string | null
}

definePageMeta({
  validate (route: { params: { id?: string | string[] } }) {
    // Check if the id is made up of digits
    return typeof route.params.id === 'string' && /^\d+$/.test(route.params.id)
  },
})

const route = useRoute()

const { data: product, status, error } = await useFetch<Product>(
  () => `/api/products/${route.params.id}`,
)

const imageFailed = ref(false)

function formatPrice(price: string) {
  return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(Number(price))
}

useHead({ title: 'Grocery item | FreshKeep Market' })
</script>

<template>
  <main class="min-h-screen bg-[#f5f6f1] px-4 pt-8 pb-16 text-forest-950 sm:px-6">
    <div class="mx-auto w-full max-w-4xl">
      <UButton to="/products" variant="link" icon="i-lucide-arrow-left" class="px-0 font-bold">
        Back to groceries
      </UButton>

      <UCard v-if="status === 'pending'" class="mt-6" role="status" aria-label="Loading grocery item">
        <div class="grid gap-9 md:grid-cols-2">
          <USkeleton class="aspect-square w-full" />
          <div class="flex flex-col justify-center gap-4">
            <USkeleton class="h-3 w-32" />
            <USkeleton class="h-10 w-3/4" />
            <USkeleton class="h-4 w-24" />
            <USkeleton class="h-7 w-28" />
          </div>
        </div>
      </UCard>

      <UEmpty
        v-else-if="error?.status === 404"
        class="mt-6 bg-default"
        variant="outline"
        icon="i-lucide-package-x"
        title="Grocery item not found."
        :actions="[{ label: 'Back to groceries', to: '/products' }]"
      />

      <UAlert
        v-else-if="error"
        class="mt-6"
        color="error"
        variant="subtle"
        icon="i-lucide-circle-alert"
        title="Grocery item could not be loaded."
        :description="error.message"
      />

      <UCard v-else-if="product" class="mt-6">
        <div class="grid items-center gap-6 md:grid-cols-2 md:gap-9">
          <div class="aspect-square overflow-hidden rounded-md bg-elevated">
            <img
              v-if="product.image_url && !imageFailed"
              :src="product.image_url"
              :alt="product.name"
              class="size-full object-cover"
              @error="imageFailed = true"
            >
            <div v-else class="grid size-full place-items-center font-serif text-7xl text-dimmed" aria-hidden="true">
              {{ product.name.charAt(0) }}
            </div>
          </div>

          <div>
            <UBadge :label="product.category" variant="subtle" class="mb-3" />
            <h1 class="font-serif text-[clamp(32px,7vw,48px)] font-medium leading-tight">{{ product.name }}</h1>
            <p class="mt-2 font-mono text-xs text-muted">SKU {{ product.sku }}</p>
            <p class="mt-6 mb-5 text-2xl font-bold tabular-nums">
              {{ formatPrice(product.price) }}
              <span v-if="product.unit" class="text-sm font-medium text-muted">/ {{ product.unit }}</span>
            </p>
            <UBadge
              :color="product.stock === 0 ? 'error' : 'success'"
              variant="soft"
              :icon="product.stock === 0 ? 'i-lucide-circle-x' : 'i-lucide-circle-check'"
              :label="product.stock === 0 ? 'Currently unavailable' : `${product.stock} ${product.unit || 'units'} available`"
            />
          </div>
        </div>
      </UCard>
    </div>
  </main>
</template>
