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
      <div className="ms-[120px] py-4">
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
      <div className="flex gap-6  pb-10 relative">
        <Filters />
        <Results />
        <div className="min-w-sm ">
          <Cart />
        </div>
      </div>
    </div>
  )
}
