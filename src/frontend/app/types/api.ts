// Shapes returned by the Laravel API (see src/backend/README.md)

export interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  is_active: boolean
  products_count?: number
}

export interface Product {
  id: number
  name: string
  sku: string
  category_id: number
  category?: Category
  price: string
  stock: number
  unit: string | null
  image_url: string | null
}

export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
}

export interface Item<T> {
  data: T
}

export interface ApiError {
  error: {
    status: number
    code: string
    message: string
    details?: Record<string, string[]>
  }
}
