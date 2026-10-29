<script setup lang="ts">
import type { FormError, FormSubmitEvent } from '@nuxt/ui'

interface Product {
  id: number
  name: string
  sku: string
  category: string
  price: string
  stock: number
  unit: string | null
}

interface ProductForm {
  name: string
  sku: string
  category: string
  price: number | undefined
  stock: number | undefined
  unit: string
}

const form = useTemplateRef('form')
const state = reactive<ProductForm>({
  name: '',
  sku: '',
  category: 'Fresh Foods',
  price: undefined,
  stock: undefined,
  unit: 'kg',
})
const categories = [
  'Fresh Foods',
  'Pantry & Dry Goods',
  'Snacks',
  'Beverages',
  'Frozen Foods',
  'Bakery',
  'Dairy & Eggs',
  'Meat & Seafood',
  'Health & Wellness',
  'Household Essentials',
  'Baby & Kids',
  'Pet Supplies',
  'International Foods',
  'Organic & Natural',
  'Specialty Diets',
]
const units = ['kg', 'pack', 'bottle', 'piece', 'box']
const submitError = ref('')
const isSubmitting = ref(false)

function validate(values: Partial<ProductForm>): FormError[] {
  const errors: FormError[] = []
  const name = values.name?.trim() ?? ''
  const sku = values.sku?.trim() ?? ''

  if (!name)
    errors.push({ name: 'name', message: 'Enter a product name.' })
  else if (name.length > 255)
    errors.push({ name: 'name', message: 'Product names must be 255 characters or fewer.' })

  if (!sku)
    errors.push({ name: 'sku', message: 'Enter a SKU.' })
  else if (sku.length > 255)
    errors.push({ name: 'sku', message: 'SKUs must be 255 characters or fewer.' })

  if (!values.category)
    errors.push({ name: 'category', message: 'Choose a department.' })

  if (values.price == null)
    errors.push({ name: 'price', message: 'Enter a price.' })
  else if (!Number.isFinite(values.price) || values.price < 0)
    errors.push({ name: 'price', message: 'Enter a valid price of 0 or more.' })

  if (values.stock == null)
    errors.push({ name: 'stock', message: 'Enter a quantity.' })
  else if (!Number.isInteger(values.stock) || values.stock < 0)
    errors.push({ name: 'stock', message: 'Enter a whole number of 0 or more.' })

  if (!values.unit)
    errors.push({ name: 'unit', message: 'Choose a selling unit.' })

  return errors
}

async function saveProduct(event: FormSubmitEvent<ProductForm>) {
  submitError.value = ''
  isSubmitting.value = true

  try {
    await $fetch<Product>('/api/products', {
      method: 'POST',
      body: {
        name: event.data.name.trim(),
        sku: event.data.sku.trim(),
        category: event.data.category,
        price: event.data.price,
        stock: event.data.stock,
        unit: event.data.unit,
      },
    })

    await navigateTo({ path: '/products', query: { created: '1' } })
  }
  catch (error) {
    const response = error as {
      data?: {
        errors?: Record<string, string[]>
        message?: string
      }
    }
    const serverErrors = response.data?.errors

    if (serverErrors) {
      form.value?.setErrors(
        Object.entries(serverErrors).map(([name, messages]) => ({ name, message: messages[0] ?? 'Invalid value.' })),
      )
      submitError.value = 'Check the highlighted fields and try again.'
    }
    else {
      submitError.value = response.data?.message ?? 'The grocery item could not be saved. Please try again.'
    }
  }
  finally {
    isSubmitting.value = false
  }
}

useHead({ title: 'Add grocery item | FreshKeep Market' })
</script>

<template>
  <main class="create-page min-h-screen px-4 pt-8 pb-16 text-forest-950 sm:px-6">
    <div class="mx-auto w-full max-w-3xl">
      <UButton to="/products" variant="link" icon="i-lucide-arrow-left" class="px-0 font-bold">
        Back to groceries
      </UButton>

      <header class="border-b border-default pt-6 pb-6">
        <p class="mb-3 text-[11px] font-extrabold tracking-[1.2px] text-primary">
          FRESHKEEP MARKET <span class="px-1 text-muted">/</span> GROCERIES <span class="px-1 text-muted">/</span> NEW
        </p>
        <h1 class="font-serif text-4xl font-medium sm:text-[40px]">Add a grocery item</h1>
        <p class="mt-2 text-sm text-muted">Add a fresh find or everyday staple to the market.</p>
      </header>

      <UCard class="mt-6">
        <template #header>
          <h2 class="font-serif text-[22px] font-medium">Grocery details</h2>
          <p class="mt-1 text-sm text-muted">Keep item names, prices, and availability up to date.</p>
        </template>

        <UForm ref="form" :state="state" :validate="validate" class="grid grid-cols-1 gap-5 sm:grid-cols-2" @submit="saveProduct">
          <UFormField label="Grocery item" name="name" required class="sm:col-span-2">
            <UInput v-model="state.name" :maxlength="255" autocomplete="off" placeholder="e.g. Heirloom tomatoes" size="lg" class="w-full" />
          </UFormField>

          <UFormField label="SKU" name="sku" required>
            <UInput v-model="state.sku" :maxlength="255" autocomplete="off" placeholder="e.g. FK-FRT-004" size="lg" class="w-full" />
          </UFormField>

          <UFormField label="Department" name="category" required>
            <USelect v-model="state.category" :items="categories" size="lg" class="w-full" />
          </UFormField>

          <UFormField label="Price" name="price" required hint="MYR">
            <UInputNumber
              v-model="state.price"
              :min="0"
              :step="0.01"
              :format-options="{ style: 'currency', currency: 'MYR' }"
              placeholder="RM 0.00"
              size="lg"
              class="w-full"
            />
          </UFormField>

          <UFormField label="Quantity available" name="stock" required>
            <UInputNumber v-model="state.stock" :min="0" :step="1" placeholder="0" size="lg" class="w-full" />
          </UFormField>

          <UFormField label="Selling unit" name="unit" required>
            <USelect v-model="state.unit" :items="units" size="lg" class="w-full" />
          </UFormField>

          <UAlert
            v-if="submitError"
            class="sm:col-span-2"
            color="error"
            variant="subtle"
            icon="i-lucide-circle-alert"
            :title="submitError"
          />

          <div class="flex items-center justify-end gap-3 border-t border-default pt-5 sm:col-span-2">
            <UButton to="/products" color="neutral" variant="ghost">Cancel</UButton>
            <UButton type="submit" size="lg" icon="i-lucide-check" :loading="isSubmitting">
              Save grocery item
            </UButton>
          </div>
        </UForm>
      </UCard>
    </div>
  </main>
</template>

<style scoped>
.create-page {
  background-color: #f5f6f1;
  background-image: radial-gradient(#dce4dc 0.7px, transparent 0.7px);
  background-size: 18px 18px;
}
</style>
