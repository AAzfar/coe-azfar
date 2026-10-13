<script setup lang="ts">
interface Product {
  id: number
  name: string
  description: string | null
  price: string
  stock_quantity: number
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
</script>

<template>
  <div>
    <p v-if="status === 'pending'">Loading...</p>
    <p v-else-if="error?.status === 404">Product not found.</p>
    <p v-else-if="error">Something went wrong: {{ error.message }}</p>
    <div v-else-if="product">
      <h1>{{ product.name }}</h1>
      <p>{{ product.description }}</p>
      <p>Price: {{ product.price }}</p>
      <p>In stock: {{ product.stock_quantity }}</p>
    </div>
  </div>
</template>
