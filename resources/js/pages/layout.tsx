import { Header } from "@/components/common/header"
import { Outlet } from "react-router"

const AppLayout = () => {
  return (
    <>
      <Header />
      <main className="pt-32">
        <Outlet />
      </main>
    </>
  )
}

export default AppLayout
