import { getSession } from "@/utils/get-session"
import { logout } from "@/utils/logout"
import axios from "axios"

const baseURL = "https://izam-task.emadw3.com/api/ "

// Create an Axios instance
const InaashApi = axios.create({
  baseURL: baseURL,
})

// Add a request interceptor to include the authentication token
InaashApi.interceptors.request.use(
  async (config) => {
    const session = getSession()

    // turn URLSearchParams to object , and handle arrays
    if (config.params && config.params instanceof URLSearchParams) {
      const paramsObject: Record<string, unknown> = {}
      for (const [key, value] of config.params.entries()) {
        if (key.endsWith("[]")) {
          //  for arrays
          paramsObject[key.slice(0, -2)] = value.split(",").filter(Boolean)
        } else {
          // Otherwise, just assign the value
          paramsObject[key] = value
        }
      }

      config.params = paramsObject
    }

    if (session && session.token) {
      config.headers["Authorization"] = `Bearer ${session.token}`
    }

    return config
  },
  async (error) => {
    return Promise.reject(error)
  }
)
// Add a request interceptor to include the authentication token
InaashApi.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      logout()
    }
    return Promise.reject(error)
  }
)

export default InaashApi
