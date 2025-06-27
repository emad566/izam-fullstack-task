import { getSession } from "@/utils/get-session"
import { Navigate, Outlet } from "react-router"

const AuthLayout = () => {
  console.log(getSession())
  if (getSession()?.token) {
    return <Navigate to="/" replace />
  }
  return (
    <div className="flex min-h-svh w-full items-center justify-center p-6 md:p-10">
      <Outlet />
    </div>
  )
}

export default AuthLayout
