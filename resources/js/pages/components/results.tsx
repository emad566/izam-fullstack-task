import { PaginationWithLinks } from "@/components/common/pagination"
import ProductCard from "@/components/common/product-card"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import Api from "@/services"
import { useQuery } from "@tanstack/react-query"
import { Loader2 } from "lucide-react"
import { useOptimisticSearchParams } from 'nuqs/adapters/react-router/v7'
import type { ProductsResponse } from "../@types"
import Search from "./search"

const Results = () => {
  const searchParams = useOptimisticSearchParams()
  console.log("🚀 ~ Results ~ searchParams:", searchParams)


  const result = useQuery({
    queryKey: ["results", searchParams.toString()],
    queryFn: async () => {
      const params = new URLSearchParams(searchParams)
      params.append("per_page", "9")
      const result = await Api.get<ProductsResponse>("/guest/products", {
        params: params,
      })

      return result.data.data.items
    },
  })

  return (
    <Card className="grow py-3 shadow-md bg-white rounded-lg border py-6">
      <CardHeader className="px-3">
        <Search />
      </CardHeader>
      <CardContent>
        {/* Title */}
        <div className="mb-6">
          <h2 className="text-2xl font-bold text-black font-semibold">Casual</h2>
        </div>

        {/* Products Count Display */}
        {result.status === "success" && result.data?.data.length > 0 && (
          <div className="mb-4 text-left block">
            <p className="text-sm text-gray-600 font-medium">
              Showing {result.data?.meta?.from || 1}-{result.data?.meta?.to || result.data?.data.length} of {result.data?.meta?.total || result.data?.data.length} Products
            </p>
          </div>
        )}

        {result.status === "pending" ? (
          <div className="flex items-center justify-center min-h-[30vh]">
            <Loader2 className="size-6  animate-spin" />
          </div>
        ) : null}
        {result.status === "error" ? (
          <p className="text-red-500 text-center py-4">server error</p>
        ) : null}
        {result.status === "success" ? (
          <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
            {result.data?.data.map((product) => {
              return <ProductCard {...product} key={product.id} />
            })}
          </div>
        ) : null}
        <div className="pt-10 pb-5">
          <PaginationWithLinks page={1} pageSize={20} totalCount={500} />
        </div>
      </CardContent>
    </Card>
  )
}

export default Results
