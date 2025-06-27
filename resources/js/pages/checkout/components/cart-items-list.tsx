import CartItem from "./cart-item"
import type { Product } from "../../@types"

interface CartItemsListProps {
  cartItems: (Product & { cartQuantity: number })[]
  onRemoveItem: (productId: number | string) => void
}

const CartItemsList = ({ cartItems, onRemoveItem }: CartItemsListProps) => {
  return (
    <div className="lg:col-span-3">
      <h1 className="text-2xl font-bold mb-8">Your cart</h1>

      <div className="space-y-6">
        {cartItems.map((product) => (
          <CartItem
            key={product.id}
            product={product}
            onRemove={onRemoveItem}
          />
        ))}
      </div>
    </div>
  )
}

export default CartItemsList
