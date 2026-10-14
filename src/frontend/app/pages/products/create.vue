<script setup lang="ts">
interface Product {
  id: number
  name: string
  description: string | null
  price: string
  stock_quantity: number
}

type ProductField = 'name' | 'description' | 'price' | 'stock_quantity'

const form = reactive({
  name: '',
  description: '',
  price: '',
  stock_quantity: '',
})
const validationErrors = ref<Partial<Record<ProductField, string>>>({})
const submitError = ref('')
const isSubmitting = ref(false)

function validateForm() {
  const errors: Partial<Record<ProductField, string>> = {}
  const price = Number(form.price)
  const stock = Number(form.stock_quantity)

  if (!form.name.trim())
    errors.name = 'Enter a product name.'
  else if (form.name.trim().length > 255)
    errors.name = 'Product names must be 255 characters or fewer.'

  if (form.price.trim() === '')
    errors.price = 'Enter a price.'
  else if (!Number.isFinite(price) || price < 0)
    errors.price = 'Enter a valid price of 0 or more.'

  if (form.stock_quantity.trim() === '')
    errors.stock_quantity = 'Enter a stock quantity.'
  else if (!Number.isInteger(stock) || stock < 0)
    errors.stock_quantity = 'Enter a whole number of 0 or more.'

  validationErrors.value = errors
  return Object.keys(errors).length === 0
}

async function saveProduct() {
  submitError.value = ''
  if (!validateForm())
    return

  isSubmitting.value = true

  try {
    await $fetch<Product>('/api/products', {
      method: 'POST',
      body: {
        name: form.name.trim(),
        description: form.description.trim() || null,
        price: Number(form.price),
        stock_quantity: Number(form.stock_quantity),
      },
    })

    await navigateTo({ path: '/products', query: { created: '1' } })
  }
  catch (error) {
    const response = error as {
      data?: {
        errors?: Partial<Record<ProductField, string[]>>
        message?: string
      }
    }
    const serverErrors = response.data?.errors

    if (serverErrors) {
      validationErrors.value = Object.fromEntries(
        Object.entries(serverErrors).map(([field, messages]) => [field, messages?.[0] ?? 'Invalid value.']),
      ) as Partial<Record<ProductField, string>>
      submitError.value = 'Check the highlighted fields and try again.'
    }
    else {
      submitError.value = response.data?.message ?? 'The product could not be saved. Please try again.'
    }
  }
  finally {
    isSubmitting.value = false
  }
}

useHead({ title: 'Add product | Stockroom' })
</script>

<template>
  <main class="create-page">
    <div class="create-shell">
      <NuxtLink class="back-link" to="/products"><span aria-hidden="true">←</span> Back to products</NuxtLink>

      <header class="page-heading">
        <p class="eyebrow">STOCKROOM <span>/</span> CATALOG <span>/</span> NEW</p>
        <h1>Add a product</h1>
        <p class="heading-note">Enter the product details below. Required fields are marked.</p>
      </header>

      <form class="product-form" novalidate @submit.prevent="saveProduct">
        <div class="form-section-heading">
          <h2>Product details</h2>
          <p>Keep the catalog information accurate and easy to scan.</p>
        </div>

        <div class="field-grid">
          <div class="field field-full">
            <label for="product-name">Product name <span aria-hidden="true">*</span></label>
            <input
              id="product-name"
              v-model="form.name"
              type="text"
              maxlength="255"
              autocomplete="off"
              required
              :aria-invalid="Boolean(validationErrors.name)"
              :aria-describedby="validationErrors.name ? 'name-error' : undefined"
              placeholder="e.g. Ceramic desk lamp"
              @input="delete validationErrors.name"
            >
            <span v-if="validationErrors.name" id="name-error" class="field-error">{{ validationErrors.name }}</span>
          </div>

          <div class="field field-full">
            <label for="product-description">Description <span class="optional">Optional</span></label>
            <textarea
              id="product-description"
              v-model="form.description"
              rows="4"
              :aria-invalid="Boolean(validationErrors.description)"
              :aria-describedby="validationErrors.description ? 'description-error' : undefined"
              placeholder="Materials, size, or other useful details"
              @input="delete validationErrors.description"
            ></textarea>
            <span v-if="validationErrors.description" id="description-error" class="field-error">{{ validationErrors.description }}</span>
          </div>

          <div class="field">
            <label for="product-price">Price <span aria-hidden="true">*</span></label>
            <div class="number-input price-input">
              <span aria-hidden="true">$</span>
              <input
                id="product-price"
                v-model="form.price"
                type="number"
                min="0"
                step="0.01"
                inputmode="decimal"
                required
                :aria-invalid="Boolean(validationErrors.price)"
                :aria-describedby="validationErrors.price ? 'price-error' : undefined"
                placeholder="0.00"
                @input="delete validationErrors.price"
              >
            </div>
            <span v-if="validationErrors.price" id="price-error" class="field-error">{{ validationErrors.price }}</span>
          </div>

          <div class="field">
            <label for="product-stock">Stock quantity <span aria-hidden="true">*</span></label>
            <input
              id="product-stock"
              v-model="form.stock_quantity"
              type="number"
              min="0"
              step="1"
              inputmode="numeric"
              required
              :aria-invalid="Boolean(validationErrors.stock_quantity)"
              :aria-describedby="validationErrors.stock_quantity ? 'stock-error' : undefined"
              placeholder="0"
              @input="delete validationErrors.stock_quantity"
            >
            <span v-if="validationErrors.stock_quantity" id="stock-error" class="field-error">{{ validationErrors.stock_quantity }}</span>
          </div>
        </div>

        <p v-if="submitError" class="submit-error" role="alert">{{ submitError }}</p>

        <footer class="form-actions">
          <NuxtLink class="cancel-link" to="/products">Cancel</NuxtLink>
          <button class="submit-button" type="submit" :disabled="isSubmitting">
            {{ isSubmitting ? 'Saving...' : 'Save product' }}
          </button>
        </footer>
      </form>
    </div>
  </main>
</template>

<style scoped>
.create-page {
  --ink: #172b24;
  --muted: #65736c;
  --line: #dce2dc;
  --paper: #f5f6f1;
  --green: #1f614b;
  --green-dark: #174a39;
  --clay: #a64f37;
  min-height: 100vh;
  padding: 34px 24px 72px;
  color: var(--ink);
  background-color: var(--paper);
  background-image: radial-gradient(#dce4dc 0.7px, transparent 0.7px);
  background-size: 18px 18px;
  font-family: "Trebuchet MS", "Segoe UI", sans-serif;
}

.create-shell {
  width: min(850px, 100%);
  margin: 0 auto;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--green-dark);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
}

.back-link span {
  font-size: 18px;
}

.back-link:hover,
.cancel-link:hover {
  text-decoration: underline;
  text-underline-offset: 3px;
}

.page-heading {
  padding: 32px 0 25px;
  border-bottom: 1px solid var(--line);
}

.eyebrow {
  margin: 0 0 13px;
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
  font-size: 40px;
  font-weight: 500;
  line-height: 1.1;
}

.heading-note {
  margin: 9px 0 0;
  color: var(--muted);
  font-size: 14px;
}

.product-form {
  padding-top: 25px;
}

.form-section-heading {
  margin-bottom: 22px;
}

.form-section-heading h2 {
  margin: 0;
  font-family: Georgia, "Times New Roman", serif;
  font-size: 22px;
  font-weight: 500;
}

.form-section-heading p {
  margin: 6px 0 0;
  color: var(--muted);
  font-size: 13px;
}

.field-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px 22px;
}

.field {
  display: flex;
  min-width: 0;
  flex-direction: column;
  gap: 8px;
}

.field-full {
  grid-column: 1 / -1;
}

.field label {
  color: var(--ink);
  font-size: 13px;
  font-weight: 700;
}

.field label > span[aria-hidden="true"] {
  color: var(--clay);
}

.field .optional {
  margin-left: 6px;
  color: var(--muted);
  font-size: 11px;
  font-weight: 400;
}

.field input,
.field textarea {
  width: 100%;
  min-height: 44px;
  padding: 11px 12px;
  border: 1px solid #cbd5cd;
  border-radius: 4px;
  outline: none;
  background: #fff;
  color: var(--ink);
  font: inherit;
  font-size: 14px;
}

.field textarea {
  min-height: 112px;
  resize: vertical;
}

.field input::placeholder,
.field textarea::placeholder {
  color: #929d95;
}

.field input:focus,
.field textarea:focus {
  border-color: var(--green);
  box-shadow: 0 0 0 3px #1f614b20;
}

.field input[aria-invalid="true"],
.field textarea[aria-invalid="true"] {
  border-color: var(--clay);
}

.number-input {
  display: flex;
  min-height: 44px;
  align-items: center;
  border: 1px solid #cbd5cd;
  border-radius: 4px;
  background: #fff;
}

.number-input:focus-within {
  border-color: var(--green);
  box-shadow: 0 0 0 3px #1f614b20;
}

.number-input > span {
  padding-left: 12px;
  color: var(--muted);
  font-size: 14px;
}

.number-input input {
  border: 0;
  background: transparent;
  box-shadow: none;
}

.number-input input:focus {
  box-shadow: none;
}

.field-error {
  color: var(--clay);
  font-size: 12px;
}

.submit-error {
  margin: 20px 0 0;
  padding: 11px 13px;
  border-left: 3px solid var(--clay);
  background: #f7eae5;
  color: #813c2b;
  font-size: 13px;
}

.form-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 20px;
  margin-top: 28px;
  padding-top: 18px;
  border-top: 1px solid var(--line);
}

.cancel-link {
  color: var(--muted);
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
}

.submit-button {
  min-height: 44px;
  padding: 0 19px;
  border: 1px solid var(--green-dark);
  border-radius: 5px;
  background: var(--green-dark);
  color: #fff;
  cursor: pointer;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  transition: background 140ms ease;
}

.submit-button:hover:not(:disabled) {
  background: var(--green);
}

.submit-button:disabled {
  cursor: wait;
  opacity: 0.7;
}

a:focus-visible,
button:focus-visible {
  outline: 3px solid #a6c8ae;
  outline-offset: 2px;
}

@media (max-width: 600px) {
  .create-page {
    padding: 24px 16px 48px;
  }

  h1 {
    font-size: 35px;
  }

  .field-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 18px;
  }

  .field-full {
    grid-column: auto;
  }
}
</style>