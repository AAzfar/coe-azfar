<script setup lang="ts">
interface Product {
  id: number
  name: string
  description: string | null
  price: string
  stock_quantity: number
  created_at: string
}

type SortField = 'name' | 'price' | 'stock_quantity' | 'created_at'

const route = useRoute()
const { data: products, status, error } = await useFetch<Product[]>('/api/products', {
  default: () => [],
})

const searchTerm = ref('')
const sortField = ref<SortField>('created_at')
const sortDirection = ref<'asc' | 'desc'>('desc')
const currentPage = ref(1)
const pageSize = ref(10)

const filteredProducts = computed(() => {
  const query = searchTerm.value.trim().toLocaleLowerCase()
  const matchingProducts = products.value.filter((product) => {
    return !query || `${product.name} ${product.description ?? ''}`.toLocaleLowerCase().includes(query)
  })

  return [...matchingProducts].sort((left, right) => {
    const comparison = sortField.value === 'name'
      ? left.name.localeCompare(right.name)
      : sortField.value === 'price'
        ? Number(left.price) - Number(right.price)
        : sortField.value === 'stock_quantity'
          ? left.stock_quantity - right.stock_quantity
          : new Date(left.created_at).getTime() - new Date(right.created_at).getTime()

    return sortDirection.value === 'asc' ? comparison : -comparison
  })
})

const pageCount = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / pageSize.value)))
const pageProducts = computed(() => {
  const offset = (currentPage.value - 1) * pageSize.value
  return filteredProducts.value.slice(offset, offset + pageSize.value)
})
const firstResult = computed(() => filteredProducts.value.length ? (currentPage.value - 1) * pageSize.value + 1 : 0)
const lastResult = computed(() => Math.min(currentPage.value * pageSize.value, filteredProducts.value.length))
const createdNotice = computed(() => route.query.created === '1')

watch(searchTerm, () => {
  currentPage.value = 1
})

watch(pageSize, () => {
  currentPage.value = 1
})

function setSort(field: SortField) {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  }
  else {
    sortField.value = field
    sortDirection.value = field === 'created_at' ? 'desc' : 'asc'
  }
  currentPage.value = 1
}

function sortLabel(field: SortField) {
  if (sortField.value !== field)
    return ''

  return sortDirection.value === 'asc' ? 'ascending' : 'descending'
}

function sortMark(field: SortField) {
  if (sortField.value !== field)
    return 'SORT'

  return sortDirection.value === 'asc' ? 'ASC' : 'DESC'
}

function formatPrice(price: string) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(price))
}

function formatDate(value: string) {
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' }).format(new Date(value))
}

useHead({ title: 'Products | Stockroom' })
</script>

<template>
  <main class="catalog-page">
    <div class="catalog-shell">
      <header class="page-heading">
        <div>
          <p class="eyebrow">STOCKROOM <span>/</span> CATALOG</p>
          <h1>Products</h1>
          <p class="heading-note">Your inventory, in one place.</p>
        </div>
        <NuxtLink class="primary-link" to="/products/create">
          <span aria-hidden="true">+</span>
          Add product
        </NuxtLink>
      </header>

      <p v-if="createdNotice" class="notice" role="status">Product saved to your catalog.</p>

      <section class="catalog-tools" aria-label="Product list controls">
        <label class="search-box">
          <span class="search-symbol" aria-hidden="true"></span>
          <span class="visually-hidden">Search products</span>
          <input v-model="searchTerm" type="search" placeholder="Search by name or description">
          <button v-if="searchTerm" class="clear-search" type="button" @click="searchTerm = ''">Clear</button>
        </label>
        <label class="page-size-control">
          <span>Rows</span>
          <select v-model.number="pageSize" aria-label="Rows per page">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </label>
      </section>

      <div v-if="status === 'pending'" class="state-panel" role="status">Loading products...</div>
      <div v-else-if="error" class="state-panel state-error" role="alert">
        <strong>Products could not be loaded.</strong>
        <span>{{ error.message }}</span>
      </div>
      <div v-else-if="products.length === 0" class="state-panel">
        <strong>Your catalog is empty.</strong>
        <span>Add your first product to get started.</span>
        <NuxtLink to="/products/create">Create a product</NuxtLink>
      </div>
      <div v-else-if="filteredProducts.length === 0" class="state-panel">
        <strong>No matching products.</strong>
        <span>Try a different name or description.</span>
        <button class="text-button" type="button" @click="searchTerm = ''">Clear search</button>
      </div>

      <template v-else>
        <div class="table-frame">
          <table>
            <thead>
              <tr>
                <th scope="col" :aria-sort="sortLabel('name') || 'none'">
                  <button class="sort-button" type="button" :aria-label="`Sort by product name, ${sortLabel('name') || 'not sorted'}`" @click="setSort('name')">
                    Product <span class="sort-mark">{{ sortMark('name') }}</span>
                  </button>
                </th>
                <th scope="col" :aria-sort="sortLabel('price') || 'none'">
                  <button class="sort-button" type="button" :aria-label="`Sort by price, ${sortLabel('price') || 'not sorted'}`" @click="setSort('price')">
                    Price <span class="sort-mark">{{ sortMark('price') }}</span>
                  </button>
                </th>
                <th scope="col" :aria-sort="sortLabel('stock_quantity') || 'none'">
                  <button class="sort-button" type="button" :aria-label="`Sort by stock, ${sortLabel('stock_quantity') || 'not sorted'}`" @click="setSort('stock_quantity')">
                    In stock <span class="sort-mark">{{ sortMark('stock_quantity') }}</span>
                  </button>
                </th>
                <th scope="col" :aria-sort="sortLabel('created_at') || 'none'">
                  <button class="sort-button" type="button" :aria-label="`Sort by date added, ${sortLabel('created_at') || 'not sorted'}`" @click="setSort('created_at')">
                    Added <span class="sort-mark">{{ sortMark('created_at') }}</span>
                  </button>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="product in pageProducts" :key="product.id">
                <td class="product-cell">
                  <NuxtLink class="product-name" :to="`/products/${product.id}`">{{ product.name }}</NuxtLink>
                  <span class="product-description">{{ product.description || 'No description' }}</span>
                </td>
                <td class="price-cell">{{ formatPrice(product.price) }}</td>
                <td>
                  <span class="stock-value" :class="{ 'stock-low': product.stock_quantity <= 5, 'stock-out': product.stock_quantity === 0 }">
                    <span class="stock-dot" aria-hidden="true"></span>
                    {{ product.stock_quantity }}
                  </span>
                </td>
                <td class="date-cell">{{ formatDate(product.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <footer class="pagination-bar">
          <p>Showing <strong>{{ firstResult }}–{{ lastResult }}</strong> of <strong>{{ filteredProducts.length }}</strong> products</p>
          <nav class="pagination" aria-label="Product pages">
            <button type="button" :disabled="currentPage <= 1" aria-label="Previous page" @click="currentPage--">Previous</button>
            <span>Page <strong>{{ currentPage }}</strong> of {{ pageCount }}</span>
            <button type="button" :disabled="currentPage >= pageCount" aria-label="Next page" @click="currentPage++">Next</button>
          </nav>
        </footer>
      </template>
    </div>
  </main>
</template>

<style scoped>
.catalog-page {
  --ink: #172b24;
  --muted: #65736c;
  --line: #dce2dc;
  --paper: #f5f6f1;
  --green: #1f614b;
  --green-dark: #174a39;
  --clay: #a64f37;
  min-height: 100vh;
  padding: 42px 24px 72px;
  color: var(--ink);
  background-color: var(--paper);
  background-image: radial-gradient(#dce4dc 0.7px, transparent 0.7px);
  background-size: 18px 18px;
  font-family: "Trebuchet MS", "Segoe UI", sans-serif;
}

.catalog-shell {
  width: min(1120px, 100%);
  margin: 0 auto;
}

.page-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  padding-bottom: 28px;
  border-bottom: 1px solid var(--line);
}

.eyebrow {
  margin: 0 0 14px;
  color: var(--green);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.2px;
}

.eyebrow span {
  padding: 0 5px;
  color: #a4afa7;
}

h1 {
  margin: 0;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 42px;
  font-weight: 500;
  line-height: 1.08;
}

.heading-note {
  margin: 9px 0 0;
  color: var(--muted);
  font-size: 14px;
}

.primary-link {
  display: inline-flex;
  min-height: 44px;
  align-items: center;
  gap: 9px;
  padding: 0 17px;
  border: 1px solid var(--green-dark);
  border-radius: 5px;
  background: var(--green-dark);
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  transition: background 140ms ease, transform 140ms ease;
}

.primary-link span {
  font-size: 21px;
  font-weight: 400;
  line-height: 1;
}

.primary-link:hover {
  transform: translateY(-1px);
  background: var(--green);
}

.catalog-tools {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 20px 0 14px;
}

.search-box {
  display: flex;
  width: min(460px, 100%);
  min-height: 44px;
  align-items: center;
  gap: 11px;
  padding: 0 12px;
  border: 1px solid var(--line);
  border-radius: 5px;
  background: #fff;
}

.search-box:focus-within {
  border-color: var(--green);
  box-shadow: 0 0 0 3px #1f614b20;
}

.search-symbol {
  position: relative;
  width: 14px;
  height: 14px;
  flex: 0 0 14px;
  border: 1.5px solid #77857c;
  border-radius: 50%;
}

.search-symbol::after {
  position: absolute;
  right: -5px;
  bottom: -3px;
  width: 6px;
  height: 1.5px;
  transform: rotate(45deg);
  background: #77857c;
  content: "";
}

.search-box input {
  width: 100%;
  min-width: 0;
  border: 0;
  outline: 0;
  background: transparent;
  color: var(--ink);
  font: inherit;
  font-size: 14px;
}

.search-box input::placeholder {
  color: #89968e;
}

.clear-search,
.text-button {
  padding: 5px;
  border: 0;
  background: transparent;
  color: var(--green);
  cursor: pointer;
  font: inherit;
  font-size: 12px;
  font-weight: 700;
}

.page-size-control {
  display: flex;
  align-items: center;
  gap: 9px;
  color: var(--muted);
  font-size: 13px;
}

.page-size-control select {
  min-height: 38px;
  padding: 0 28px 0 10px;
  border: 1px solid var(--line);
  border-radius: 4px;
  background: #fff;
  color: var(--ink);
  font: inherit;
}

.table-frame {
  overflow-x: auto;
  border-top: 2px solid var(--green-dark);
  background: #fff;
}

table {
  width: 100%;
  min-width: 680px;
  border-collapse: collapse;
  text-align: left;
}

th {
  height: 48px;
  border-bottom: 1px solid var(--line);
  background: #eef1eb;
  color: #59675f;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.7px;
  text-transform: uppercase;
}

th:first-child,
td:first-child {
  padding-left: 20px;
}

th:last-child,
td:last-child {
  padding-right: 20px;
}

td {
  height: 74px;
  padding: 11px 14px;
  border-bottom: 1px solid #e9ede8;
  color: var(--ink);
  font-size: 14px;
}

tbody tr:last-child td {
  border-bottom: 0;
}

tbody tr:hover {
  background: #fbfcf9;
}

.sort-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 0;
  border: 0;
  background: transparent;
  color: inherit;
  cursor: pointer;
  font: inherit;
  letter-spacing: inherit;
  text-transform: inherit;
}

.sort-mark {
  color: #93a097;
  font-size: 9px;
  letter-spacing: 0;
}

.product-cell {
  max-width: 440px;
}

.product-name,
.product-description {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-name {
  width: fit-content;
  max-width: 100%;
  color: var(--green-dark);
  font-weight: 700;
  text-decoration: none;
}

.product-name:hover {
  text-decoration: underline;
  text-underline-offset: 3px;
}

.product-description {
  max-width: 400px;
  margin-top: 5px;
  color: var(--muted);
  font-size: 12px;
}

.price-cell {
  font-variant-numeric: tabular-nums;
  font-weight: 700;
}

.stock-value {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-variant-numeric: tabular-nums;
}

.stock-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #4d8b63;
}

.stock-low .stock-dot {
  background: #c68b36;
}

.stock-out {
  color: var(--clay);
}

.stock-out .stock-dot {
  background: var(--clay);
}

.date-cell {
  color: var(--muted);
  font-size: 13px;
  white-space: nowrap;
}

.pagination-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 16px 0;
  border-top: 1px solid var(--line);
  color: var(--muted);
  font-size: 13px;
}

.pagination-bar p {
  margin: 0;
}

.pagination-bar strong {
  color: var(--ink);
}

.pagination {
  display: flex;
  align-items: center;
  gap: 13px;
}

.pagination button {
  min-height: 34px;
  padding: 0 10px;
  border: 1px solid var(--line);
  border-radius: 4px;
  background: #fff;
  color: var(--ink);
  cursor: pointer;
  font: inherit;
}

.pagination button:disabled {
  color: #a3ada6;
  cursor: not-allowed;
}

.state-panel {
  display: flex;
  min-height: 210px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-top: 2px solid var(--green-dark);
  background: #fff;
  color: var(--muted);
  text-align: center;
  font-size: 14px;
}

.state-panel strong {
  color: var(--ink);
  font-family: Georgia, "Times New Roman", serif;
  font-size: 21px;
  font-weight: 500;
}

.state-panel a,
.state-panel .text-button {
  margin-top: 4px;
}

.state-error strong {
  color: var(--clay);
}

.notice {
  margin: 18px 0 0;
  padding: 12px 14px;
  border-left: 3px solid var(--green);
  background: #e7f0e8;
  color: var(--green-dark);
  font-size: 14px;
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  clip-path: inset(50%);
}

button:focus-visible,
a:focus-visible,
select:focus-visible {
  outline: 3px solid #a6c8ae;
  outline-offset: 2px;
}

@media (max-width: 640px) {
  .catalog-page {
    padding: 28px 16px 48px;
  }

  .page-heading {
    align-items: flex-start;
    flex-direction: column;
    gap: 18px;
  }

  h1 {
    font-size: 36px;
  }

  .catalog-tools {
    align-items: stretch;
    flex-direction: column;
  }

  .search-box {
    width: auto;
  }

  .page-size-control {
    justify-content: flex-end;
  }

  .pagination-bar {
    align-items: flex-start;
    flex-direction: column;
  }

  .pagination {
    width: 100%;
    justify-content: space-between;
  }
}
</style>