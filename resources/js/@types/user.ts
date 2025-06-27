export interface LoginResponse {
  status: boolean
  message: string
  data: Data
  errors: null
  response_code: number
  request_data: RequestData
}

export interface Data {
  item: User
  token: string
}

export interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

export interface RequestData {
  email: string
  password: string
}
