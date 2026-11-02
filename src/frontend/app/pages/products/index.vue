<script setup lang="ts">
import type { TableColumn } from '@nuxt/ui'
import type { ApiError, Category, Paginated, Product } from '~/types/api'

type SortField = 'name' | 'sku' | 'price' | 'stock'

const UButton = resolveComponent('UButton')
const UBadge = resolveComponent('UBadge')
const NuxtLink = resolveComponent('NuxtLink')

const ALL_DEPARTMENTS = 'all'

const route = useRoute()

const searchTerm = ref(typeof route.query.search === 'string' ? route.query.search : '')
const appliedSearch = ref(searchTerm.value.trim())
const sortField = ref<SortField>('name')
const sortDirection = ref<'asc' | 'desc'>('asc')
const currentPage = ref(1)
const pageSize = ref(10)
const pageSizes = [10, 20, 50]
const categoryFilter = computed(() => typeof route.query.category === 'string' ? route.query.category : ALL_DEPARTMENTS)

// Wait for a pause in typing before asking the API to search
let searchTimer: ReturnType<typeof setTimeout> | undefined
watch(searchTerm, (value) => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    appliedSearch.value = value.trim()
  }, 300)
})

watch([appliedSearch, categoryFilter, pageSize], () => {
  currentPage.value = 1
})

const { data: categories } = await useFetch<Paginated<Category>>('/api/categories', {
  query: { per_page: 100 },
})
const departmentOptions = computed(() => [
  { label: 'All departments', value: ALL_DEPARTMENTS },
  ...(categories.value?.data ?? []).map(category => ({ label: category.name, value: category.slug })),
])

const { data: products, status, error } = await useFetch<Paginated<Product>>('/api/products', {
  query: computed(() => ({
    search: appliedSearch.value || undefined,
    category: categoryFilter.value === ALL_DEPARTMENTS ? undefined : categoryFilter.value,
    sort: `${sortDirection.value === 'desc' ? '-' : ''}${sortField.value}`,
    page: currentPage.value,
    per_page: pageSize.value,
  })),
})

const rows = computed(() => products.value?.data ?? [])
const total = computed(() => products.value?.meta.total ?? 0)
const hasFilters = computed(() => Boolean(appliedSearch.value) || categoryFilter.value !== ALL_DEPARTMENTS)
const createdNotice = computed(() => route.query.created === '1')
const errorMessage = computed(() => (error.value?.data as ApiError | undefined)?.error?.message ?? error.value?.message)

function setCategory(slug: string) {
  navigateTo({ query: { ...route.query, category: slug === ALL_DEPARTMENTS ? undefined : slug } }, { replace: true })
}

function clearFilters() {
  searchTerm.value = ''
  appliedSearch.value = ''
  setCategory(ALL_DEPARTMENTS)
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
    id: 'category',
    header: 'Department',
    cell: ({ row }) => row.original.category?.name ?? '—',
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
        <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
          <UInput
            v-model="searchTerm"
            type="search"
            icon="i-lucide-search"
            placeholder="Search by name, SKU, or department"
            aria-label="Search groceries"
            size="lg"
            class="w-full sm:w-80"
            :ui="{ trailing: 'pe-1' }"
          >
            <template v-if="searchTerm" #trailing>
              <UButton color="neutral" variant="link" size="sm" icon="i-lucide-x" aria-label="Clear search" @click="searchTerm = ''" />
            </template>
          </UInput>

          <USelect
            :model-value="categoryFilter"
            :items="departmentOptions"
            aria-label="Filter by department"
            size="lg"
            class="w-full sm:w-56"
            @update:model-value="setCategory"
          />
        </div>

        <UFormField label="Items" orientation="horizontal" class="items-center gap-2">
          <USelect v-model="pageSize" :items="pageSizes" aria-label="Rows per page" class="w-20" />
        </UFormField>
      </section>

      <UAlert
        v-if="error"
        class="mt-6"
        color="error"
        variant="subtle"
        icon="i-lucide-circle-alert"
        title="Groceries could not be loaded."
        :description="errorMessage"
      />

      <UEmpty
        v-else-if="status !== 'pending' && total === 0 && !hasFilters"
        class="mt-6 bg-default"
        variant="outline"
        icon="i-lucide-shopping-basket"
        title="Your shelves are looking a little empty."
        description="Add your first grocery item to get started."
        :actions="[{ label: 'Add a grocery item', icon: 'i-lucide-plus', to: '/products/create' }]"
      />

      <UEmpty
        v-else-if="status !== 'pending' && total === 0"
        class="mt-6 bg-default"
        variant="outline"
        icon="i-lucide-search-x"
        title="No matching groceries."
        description="Try a different name, SKU, or department."
        :actions="[{ label: 'Clear filters', color: 'neutral', variant: 'outline', onClick: clearFilters }]"
      />

      <template v-else>
        <UCard class="mt-6" :ui="{ body: 'p-0 sm:p-0' }">
          <UTable
            :data="rows"
            :columns="columns"
            :loading="status === 'pending'"
            loading-color="primary"
            empty="Loading groceries..."
          />
        </UCard>

        <footer class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-muted">
          <p>
            Showing <strong class="text-highlighted">{{ products?.meta.from ?? 0 }}–{{ products?.meta.to ?? 0 }}</strong>
            of <strong class="text-highlighted">{{ total }}</strong> grocery items
          </p>
          <UPagination
            v-model:page="currentPage"
            :total="total"
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
