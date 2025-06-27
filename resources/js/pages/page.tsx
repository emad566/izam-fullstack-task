import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from "@/components/ui/breadcrumb"
import Filters from "./components/filters"
import Results from "./components/results"
import Cart from "./components/cart"

export default function Home() {
  return (
    <div className="container mx-auto px-4">
      <div className=" py-4">
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink href="/">Home</BreadcrumbLink>
            </BreadcrumbItem>

            <BreadcrumbSeparator />
            <BreadcrumbItem>
              <BreadcrumbPage>Casual</BreadcrumbPage>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
      </div>
      {/* Fixed Filters on Left */}
      <div className="fixed left-4 top-53 transform -translate-y-1/2 z-50">
        <Filters />
      </div>

      <div className="flex gap-6 pb-10 relative">
        <Results />
        <div className="min-w-sm">
          <Cart />
        </div>
      </div>
    </div>
  )
}
