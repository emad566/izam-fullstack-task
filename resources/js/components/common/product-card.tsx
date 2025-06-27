import type { Product } from "@/pages/@types"
import { Card, CardContent } from "../ui/card"
import { Badge } from "../ui/badge"
import ProductCountButton from "./product-count-button"

const ProductCard = (props: Product) => {
  return (
    <Card className="border-0 pt-0">
      <div className="w-full aspect-[9/10]">
        <img
          src={props.image_urls}
          alt={props.name}
          className="w-full h-full object-cover"
        />
      </div>
      <CardContent className="space-y-3">
        <div className="flex gap-4 flex-nowrap">
          <p className="font-bold w-full truncate">{props.name}</p>
          <Badge variant="secondary">{props.category.name}</Badge>
        </div>
        <div className="flex gap-4 justify-between">
          <p className="font-bold">${props.price}</p>
          <span className="text-[#6B7280] text-sm">Stock : {props.stock}</span>
        </div>
        <div>
          <ProductCountButton id={props.id} />
        </div>
      </CardContent>
    </Card>
  )
}

export default ProductCard
