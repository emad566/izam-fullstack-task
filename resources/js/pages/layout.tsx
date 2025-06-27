import { Header } from "@/components/common/header"
import { Outlet } from "react-router"

const AppLayout = () => {
  return (
    <>
      <Header />
      <main>
        <Outlet />
      </main>
    </>
  )
}

export default AppLayout
