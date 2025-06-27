import ProductCard from "@/components/common/product-card"
import { Card, CardContent } from "@/components/ui/card"
import Api from "@/services"
import { useQuery } from "@tanstack/react-query"
import { Loader2 } from "lucide-react"
import { useSearchParams } from "react-router"
import type { ProductsResponse } from "../@types"

const Cart = () => {
  const [searchParams] = useSearchParams()
  const result = useQuery({
    queryKey: ["cart"],
    queryFn: async () => {
      const result = await Api.get<ProductsResponse>("/guest/cart", {
        params: searchParams,
      })

      return result.data.data.items
    },
  })

  return (
    <Card className="grow py-3 shadow-xs border-0 sticky top-32">
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
      </CardContent>
    </Card>
  )
}

export default Cart
