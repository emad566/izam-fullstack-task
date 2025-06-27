import { LOCALSTORAGE_SESSION_KEY } from "@/config"

export const logout = async () => {
  localStorage.removeItem(LOCALSTORAGE_SESSION_KEY)

  // Dispatch custom event to notify header of auth change
  window.dispatchEvent(new CustomEvent('authChanged'))

  window.location.href = "/auth/login"
}
