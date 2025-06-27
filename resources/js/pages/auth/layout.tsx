import { getSession } from "@/utils/get-session"
import { Navigate, Outlet } from "react-router"

const AuthLayout = () => {
  console.log(getSession())
  if (getSession()?.token) {
    return <Navigate to="/" replace />
  }
  return (
    <div className="flex  w-full items-center justify-center px-4 mt-24 md:pt-24">
      <div className="w-full max-w-md">
        <Outlet />
      </div>
    </div>
  )
}

export default AuthLayout
