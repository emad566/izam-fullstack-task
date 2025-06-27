import { PaginationWithLinks } from "@/components/common/pagination"
import ProductCard from "@/components/common/product-card"
import { Card, CardContent, CardHeader } from "@/components/ui/card"
import Api from "@/services"
import { useQuery } from "@tanstack/react-query"
import { Loader2 } from "lucide-react"
import { parseAsString, useQueryState } from "nuqs"
import type { ProductsResponse } from "../@types"
import Search from "./search"

const Results = () => {
  const [q] = useQueryState("q", parseAsString.withDefault(""))

  const searchParamsObj = { q }
  console.log("🚀 ~ Results ~ searchParamsObj:", searchParamsObj)

  const result = useQuery({
    queryKey: ["results", searchParamsObj],
    queryFn: async () => {
      const result = await Api.get<ProductsResponse>("/guest/products", {
        params: { ...searchParamsObj, page: 1, per_page: 9 },
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
        <div className="pt-10 pb-5">
          <PaginationWithLinks page={1} pageSize={20} totalCount={500} />
        </div>
      </CardContent>
    </Card>
  )
}

export default Results
