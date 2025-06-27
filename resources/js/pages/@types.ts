export interface ProductsResponse {
  status: boolean
  message: string
  data: Data
  errors: null
  response_code: number
  request_data: RequestData
}

export interface Data {
  helpers: null
  items: Items
}

export interface Items {
  data: Product[]
  links: Links
  meta: Meta
}

export interface Product {
  id: number
  name: string
  description: string
  image_urls: string
  price: string
  stock: number
  category: Category
  created_at: string
  updated_at: string
  deleted_at: null
}

export interface Category {
  id: number
  name: string
  created_at: string
  updated_at: string
  deleted_at: null
}

export interface Links {
  first: string
  last: string
  prev: null
  next: string
}

export interface Meta {
  current_page: number
  from: number
  last_page: number
  links: Link[]
  path: string
  per_page: number
  to: number
  total: number
}

export interface Link {
  url: null | string
  label: string
  active: boolean
}

export interface RequestData {
  per_page: number
  page: number
}
