import { Navigate, Route, Routes } from "react-router"
import AuthLayout from "./pages/auth/layout"
import LoginPage from "./pages/auth/login/page"
import AppLayout from "./pages/layout"
import Home from "./pages/page"

const MyRouter = () => {
  return (
    <Routes>
      <Route path="/" element={<AppLayout />}>
        <Route path="auth" element={<AuthLayout />}>
          <Route index element={<Navigate to={"/auth/login"} replace />} />
          <Route path="login" element={<LoginPage />} />
        </Route>
        <Route index element={<Home />} />
      </Route>
    </Routes>
  )
}

export default MyRouter
