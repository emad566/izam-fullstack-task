import { PaginationWithLinks } from "@/components/common/pagination"
import ProductCard from "@/components/common/product-card"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import Api from "@/services"
import { useQuery } from "@tanstack/react-query"
import { Loader2 } from "lucide-react"
import { useSearchParams } from "react-router"
import type { ProductsResponse } from "../@types"
import Search from "./search"

const Results = () => {
  const [searchParams] = useSearchParams()
  const result = useQuery({
    queryKey: ["results", searchParams.toString()],
    queryFn: async () => {
      const result = await Api.get<ProductsResponse>("/guest/products", {
        params: searchParams,
      })

      return result.data.data.items
    },
  })

  return (
    <Card className="grow py-3 shadow-xs border-0 ">
      <CardHeader className="px-3">
        <Search />
      </CardHeader>
      <CardContent>
        {result.status === "pending" ? (
          <div className="flex items-center justify-center min-h-[30vh]">
            <Loader2 className="size-6  animate-spin" />
          </div>
        ) : null}
        {result.status === "error" ? (
          <p className="text-red-500 text-center py-4">server error</p>
        ) : null}
        {result.status === "success" ? (
          <div className="grid grid-cols-3 gap-4">
            {result.data?.data.map((product) => {
              return <ProductCard {...product} key={product.id} />
            })}
          </div>
        ) : null}
        <div className="pt-10">
          <PaginationWithLinks page={1} pageSize={20} totalCount={500} />
        </div>
      </CardContent>
    </Card>
  )
}

export default Results
