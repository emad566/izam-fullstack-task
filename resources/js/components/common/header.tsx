"use client"

import { logo } from "@/assets"
import { Button } from "@/components/ui/button"
import { getSession } from "@/utils/get-session"
import { logout } from "@/utils/logout"
import { Menu, MoveRight, ShoppingCart, X } from "lucide-react"
import { useState, useEffect } from "react"
import { Link } from "react-router"

export const Header = () => {
  const navigationItems = [
    {
      title: "Home",
      href: "/",
      description: "",
    },
  ]

  const [isOpen, setOpen] = useState(false)
  const [banner, setBanner] = useState(true)
  const [isAuthenticated, setIsAuthenticated] = useState(!!getSession()?.token)

  // Listen for authentication changes
  useEffect(() => {
    const handleAuthChange = () => {
      setIsAuthenticated(!!getSession()?.token)
    }

    // Listen for storage changes (when localStorage is updated)
    window.addEventListener('storage', handleAuthChange)

    // Listen for custom auth events
    window.addEventListener('authChanged', handleAuthChange)

    // Cleanup
    return () => {
      window.removeEventListener('storage', handleAuthChange)
      window.removeEventListener('authChanged', handleAuthChange)
    }
  }, [])

  return (
    <header className="w-full z-10 fixed top-0 left-0 bg-background border-b">
      {banner && (
        <div className="bg-black flex items-center py-2 text-white text-center px-2">
          <div></div>
          <div className="flex  justify-center w-full text-xs md:text-sm grow">
            <p>Sign up and get 20% off to your first order. </p>
            <span className="font-bold">Sign Up Now</span>
          </div>
          <Button
            className="h-7 w-7"
            variant="ghost"
            onClick={() => setBanner(!banner)}
          >
            <X className="w-5 h-5" />
          </Button>
        </div>
      )}
      <div className="container relative mx-auto py-4 flex gap-2 flex-row lg:grid lg:grid-cols-2 items-center">
        <div className="flex w-12 shrink lg:hidden items-end justify-end">
          <Button variant="ghost" onClick={() => setOpen(!isOpen)}>
            {isOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </Button>
          {isOpen && (
            <div className="absolute top-16 px-4 border-t flex flex-col w-full right-0 bg-background shadow-lg py-4 container gap-8">
              {navigationItems.map((item) => (
                <div key={item.title}>
                  <div className="flex flex-col gap-2">
                    {item.href ? (
                      <Link
                        to={item.href}
                        className="flex justify-between items-center"
                      >
                        <span className="text-lg">{item.title}</span>
                        <MoveRight className="w-4 h-4 stroke-1 text-muted-foreground" />
                      </Link>
                    ) : (
                      <p className="text-lg">{item.title}</p>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
        <div className="flex gap-4">
          <img alt="logo" src={logo} className="shrink-0 h-10" />
          <Link to={"/"}>
            <Button className="rounded-sm max-md:hidden text-black text-md font-semibold" variant="ghost">
              Products
            </Button>
          </Link>

          <Button className="rounded-sm max-md:hidden  px-4 mt-1 h-9 text-sm">
            Sell Your Product
          </Button>
        </div>
        <div className="flex justify-end w-full gap-3">
          {/* <Link to={"/"}>
            <Button size={"icon"} variant="ghost" className=" rounded-sm">
              <Search className="size-5" />
            </Button>
          </Link> */}
                      <Link to={"/checkout"}>
              <Button size={"icon"} variant="ghost" className=" rounded-sm">
                <ShoppingCart className="size-6 mt-2 fill-black" />
              </Button>
            </Link>
          {isAuthenticated ? (
            <Button
              variant={"outline"}
              className="rounded-sm px-4 mt-1 h-9 text-sm"
              onClick={() => logout()}>
              logout
            </Button>
          ) : (
            <Link to={"/auth/login"}>
              <Button className="rounded-sm px-4 mt-1 h-9 text-sm">login</Button>
            </Link>
          )}
        </div>
      </div>
    </header>
  )
}
