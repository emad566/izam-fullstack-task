import { Trash2 } from "lucide-react"
import ProductCountButton from "@/components/common/product-count-button"
import type { Product } from "../../@types"

interface CartItemProps {
  product: Product & { cartQuantity: number }
  onRemove: (productId: number | string) => void
}

const CartItem = ({ product, onRemove }: CartItemProps) => {
  return (
    <div className="flex gap-3 md:gap-4 items-start py-4">
      {/* Product Image */}
      <div className="w-20 h-20 md:w-32 md:h-32 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0 relative">
        <img
          src={product.image_urls?.medium || product.image_urls?.large}
          alt={product.name}
          className="w-full h-full object-cover"
        />
        {/* Quantity Badge */}
        <div className="absolute -top-2 -right-2 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-medium">
          {product.cartQuantity}
        </div>
      </div>

      {/* Product Details */}
      <div className="flex-1 min-w-0">
        <div className="flex justify-between items-start mb-2">
          <div className="flex-1 min-w-0 pr-2">
            <h3 className="font-medium text-sm md:text-lg truncate">{product.name}</h3>
            <p className="text-xs md:text-sm text-gray-500 truncate">{product.category.name}</p>
            <p className="text-xs md:text-sm text-gray-400">Stock: {product.stock}</p>
          </div>
          <button
            onClick={() => onRemove(product.id)}
            className="text-red-500 hover:text-red-600 p-1"
            title="Remove item"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        </div>

        <div className="flex items-center justify-between">
          <div className="text-sm md:text-lg font-semibold">
            ${product.price}
          </div>
          <div className="flex items-center gap-2 md:gap-4">
            <div className="scale-75 md:scale-100">
              <ProductCountButton id={product.id} stock={product.stock} />
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default CartItem
