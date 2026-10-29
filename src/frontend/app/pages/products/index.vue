<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'

interface Product {
  id: number
  name: string
  sku: string
  category: string
  price: string
  stock: number
  unit: string | null
}

type SortField = 'name' | 'sku' | 'category' | 'price' | 'stock'

const UButton = resolveComponent('UButton')
const UBadge = resolveComponent('UBadge')
const NuxtLink = resolveComponent('NuxtLink')

const route = useRoute()
const { data: products, status, error } = await useFetch<Product[]>('/api/products', {
  default: () => [],
})

const searchTerm = ref(typeof route.query.search === 'string' ? route.query.search : '')
const sortField = ref<SortField>('name')
const sortDirection = ref<'asc' | 'desc'>('asc')
const currentPage = ref(1)
const pageSize = ref(10)
const pageSizes = [10, 20, 50]
const categoryFilter = computed(() => typeof route.query.category === 'string' ? route.query.category : '')

const filteredProducts = computed(() => {
  const query = searchTerm.value.trim().toLocaleLowerCase()
  const matchingProducts = products.value.filter((product) => {
    if (categoryFilter.value && product.category !== categoryFilter.value)
      return false

    return !query || `${product.name} ${product.sku} ${product.category}`.toLocaleLowerCase().includes(query)
  })

  return [...matchingProducts].sort((left, right) => {
    const comparison = sortField.value === 'name'
      ? left.name.localeCompare(right.name)
      : sortField.value === 'sku'
        ? left.sku.localeCompare(right.sku)
        : sortField.value === 'category'
          ? left.category.localeCompare(right.category)
          : sortField.value === 'price'
            ? Number(left.price) - Number(right.price)
            : left.stock - right.stock

    return sortDirection.value === 'asc' ? comparison : -comparison
  })
})

const pageProducts = computed(() => {
  const offset = (currentPage.value - 1) * pageSize.value
  return filteredProducts.value.slice(offset, offset + pageSize.value)
})
const firstResult = computed(() => filteredProducts.value.length ? (currentPage.value - 1) * pageSize.value + 1 : 0)
const lastResult = computed(() => Math.min(currentPage.value * pageSize.value, filteredProducts.value.length))
const createdNotice = computed(() => route.query.created === '1')

watch([searchTerm, categoryFilter, pageSize], () => {
  currentPage.value = 1
})

function clearCategory() {
  navigateTo({ query: { ...route.query, category: undefined } }, { replace: true })
}

function setSort(field: SortField) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  }
  else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
  currentPage.value = 1
}

function sortHeader(field: SortField, label: string) {
  return () => {
    const isSorted = sortField.value === field
    const icon = !isSorted
      ? 'i-lucide-arrow-up-down'
      : sortDirection.value === 'asc' ? 'i-lucide-arrow-up-narrow-wide' : 'i-lucide-arrow-down-wide-narrow'

    return h(UButton, {
      label,
      icon,
      trailing: true,
      color: isSorted ? 'primary' : 'neutral',
      variant: 'ghost',
      class: '-mx-2.5 font-semibold',
      'aria-label': `Sort by ${label}, ${isSorted ? (sortDirection.value === 'asc' ? 'ascending' : 'descending') : 'not sorted'}`,
      onClick: () => setSort(field),
    })
  }
}

function stockColor(stock: number) {
  if (stock === 0)
    return 'error'

  return stock <= 5 ? 'warning' : 'success'
}

function formatPrice(price: string) {
  return new Intl.NumberFormat('en-MY', { style: 'currency', currency: 'MYR' }).format(Number(price))
}

const columns: TableColumn<Product>[] = [
  {
    accessorKey: 'name',
    header: sortHeader('name', 'Grocery item'),
    cell: ({ row }) => h(NuxtLink, {
      to: `/products/${row.original.id}`,
      class: 'font-semibold text-highlighted hover:text-primary hover:underline underline-offset-4',
    }, () => row.original.name),
  },
  {
    accessorKey: 'sku',
    header: sortHeader('sku', 'SKU'),
    cell: ({ row }) => h('span', { class: 'font-mono text-xs text-muted' }, row.original.sku),
  },
  {
    accessorKey: 'category',
    header: sortHeader('category', 'Department'),
  },
  {
    accessorKey: 'price',
    header: sortHeader('price', 'Price'),
    cell: ({ row }) => h('span', { class: 'font-semibold tabular-nums' }, formatPrice(row.original.price)),
  },
  {
    accessorKey: 'stock',
    header: sortHeader('stock', 'Available'),
    cell: ({ row }) => h(UBadge, {
      color: stockColor(row.original.stock),
      variant: 'subtle',
      label: `${row.original.stock} ${row.original.unit || 'units'}`,
    }),
  },
]

useHead({ title: 'Groceries | FreshKeep Market' })
</script>

<template>
  <main class="catalog-page min-h-screen px-4 pt-8 pb-16 text-forest-950 sm:px-6">
    <div class="mx-auto w-full max-w-5xl">
      <header class="flex flex-wrap items-end justify-between gap-4 border-b border-default pb-6">
        <div>
          <p class="mb-3 text-[11px] font-extrabold tracking-[1.2px] text-primary">
            FRESHKEEP MARKET <span class="px-1 text-muted">/</span> GROCERIES
          </p>
          <h1 class="font-serif text-4xl font-medium sm:text-[40px]">Groceries</h1>
          <p class="mt-2 text-sm text-muted">Fresh finds and everyday staples, all in one place.</p>
        </div>
        <UButton to="/products/create" icon="i-lucide-plus" size="lg">Add grocery item</UButton>
      </header>

      <UAlert
        v-if="createdNotice"
        class="mt-5"
        color="success"
        variant="subtle"
        icon="i-lucide-circle-check"
        title="Grocery item added to the market."
      />

      <section class="mt-5 flex flex-wrap items-center justify-between gap-3" aria-label="Product list controls">
        <UInput
          v-model="searchTerm"
          type="search"
          icon="i-lucide-search"
          placeholder="Search by name, SKU, or department"
          aria-label="Search groceries"
          size="lg"
          class="w-full sm:max-w-sm"
          :ui="{ trailing: 'pe-1' }"
        >
          <template v-if="searchTerm" #trailing>
            <UButton color="neutral" variant="link" size="sm" icon="i-lucide-x" aria-label="Clear search" @click="searchTerm = ''" />
          </template>
        </UInput>

        <UFormField label="Items" orientation="horizontal" class="items-center gap-2">
          <USelect v-model="pageSize" :items="pageSizes" aria-label="Rows per page" class="w-20" />
        </UFormField>
      </section>

      <div v-if="categoryFilter" class="mt-4 flex flex-wrap items-center gap-2 text-sm text-muted">
        Department:
        <UBadge color="primary" variant="subtle" size="lg" :label="categoryFilter" />
        <UButton variant="link" size="sm" @click="clearCategory">Show all departments</UButton>
      </div>

      <UAlert
        v-if="error"
        class="mt-6"
        color="error"
        variant="subtle"
        icon="i-lucide-circle-alert"
        title="Groceries could not be loaded."
        :description="error.message"
      />

      <UEmpty
        v-else-if="status !== 'pending' && products.length === 0"
        class="mt-6 bg-default"
        variant="outline"
        icon="i-lucide-shopping-basket"
        title="Your shelves are looking a little empty."
        description="Add your first grocery item to get started."
        :actions="[{ label: 'Add a grocery item', icon: 'i-lucide-plus', to: '/products/create' }]"
      />

      <UEmpty
        v-else-if="status !== 'pending' && filteredProducts.length === 0"
        class="mt-6 bg-default"
        variant="outline"
        icon="i-lucide-search-x"
        title="No matching groceries."
        description="Try a different name, SKU, or department."
        :actions="[{ label: 'Clear search', color: 'neutral', variant: 'outline', onClick: () => { searchTerm = '' } }]"
      />

      <template v-else>
        <UCard class="mt-6" :ui="{ body: 'p-0 sm:p-0' }">
          <UTable
            :data="pageProducts"
            :columns="columns"
            :loading="status === 'pending'"
            loading-color="primary"
            empty="Loading groceries..."
          />
        </UCard>

        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-muted">
          <p>
            Showing <strong class="text-highlighted">{{ firstResult }}–{{ lastResult }}</strong>
            of <strong class="text-highlighted">{{ filteredProducts.length }}</strong> grocery items
          </p>
          <UPagination
            v-model:page="currentPage"
            :total="filteredProducts.length"
            :items-per-page="pageSize"
            aria-label="Product pages"
          />
        </footer>
      </template>
    </div>
  </main>
</template>

<style scoped>
.catalog-page {
  background-color: #f5f6f1;
  background-image: radial-gradient(#dce4dc 0.7px, transparent 0.7px);
  background-size: 18px 18px;
}
</style>
