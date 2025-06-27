import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import ProductCountButton from "@/components/common/product-count-button"
import Api from "@/services"
import { useQuery, useQueryClient } from "@tanstack/react-query"
import { Loader2, Trash2 } from "lucide-react"
import { useNavigate } from "react-router"
import type { ProductsResponse, Product } from "../@types"

interface CartItem {
  id: number | string
  quantity: number
}

const Cart = () => {
  const queryClient = useQueryClient()
  const navigate = useNavigate()

  // Get cart from localStorage
  const getCart = (): CartItem[] => {
    try {
      const cart = localStorage.getItem('cart')
      return cart ? JSON.parse(cart) : []
    } catch {
      return []
    }
  }

  // Save cart to localStorage and trigger sync events
  const saveCart = (cart: CartItem[]) => {
    localStorage.setItem('cart', JSON.stringify(cart))
    queryClient.invalidateQueries({
      queryKey: ["cart"],
    })
    // Dispatch custom event to sync all ProductCountButton instances
    window.dispatchEvent(new CustomEvent('cartUpdated', {
      detail: { cart }
    }))
  }

  // Remove item from cart
  const removeItem = (productId: number | string) => {
    const cart = getCart()
    const updatedCart = cart.filter(item => item.id.toString() !== productId.toString())
    saveCart(updatedCart)
  }

  const result = useQuery({
    queryKey: ["cart"],
    queryFn: async () => {
      const cart = getCart()

      if (cart.length === 0) {
        return { data: [], total: 0, subtotal: 0 }
      }

      // Get product IDs from cart
      const productIds = cart.map(item => item.id)

      // Fetch product details for items in cart
      const result = await Api.get<ProductsResponse>("/guest/products", {
        params: {
          per_page: 100,
        },
      })

      // Filter products that are in the cart and add quantity
      const cartProducts = result.data.data.items.data
        .filter((product: Product) => productIds.includes(product.id))
        .map((product: Product) => {
          const cartItem = cart.find(item => item.id.toString() === product.id.toString())
          return {
            ...product,
            cartQuantity: cartItem?.quantity || 0
          }
        })

      // Calculate subtotal
      const subtotal = cartProducts.reduce((sum, product) => {
        return sum + (parseFloat(product.price) * product.cartQuantity)
      }, 0)

      return {
        data: cartProducts,
        total: cart.reduce((sum, item) => sum + item.quantity, 0),
        subtotal
      }
    },
  })

  const cartItems = result.data?.data || []
  const subtotal = result.data?.subtotal || 0
  const shipping = 15.00 // Always $15
  const tax = subtotal * 0.15 // 15% of subtotal
  const total = subtotal + shipping + tax

  return (
    <Card className="w-full max-w-sm py-3 shadow-xs border-0 sticky top-32">
      <CardHeader className="pb-3">
        <CardTitle className="text-lg font-semibold">
          Order Summary
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {result.status === "pending" ? (
          <div className="flex items-center justify-center min-h-[20vh]">
            <Loader2 className="size-6 animate-spin" />
          </div>
        ) : null}

        {result.status === "error" ? (
          <p className="text-red-500 text-center py-4">Error loading cart</p>
        ) : null}

        {result.status === "success" ? (
          <>
            {cartItems.length === 0 ? (
              <div className="text-center py-8 text-gray-500">
                <p>Your cart is empty</p>
                <p className="text-sm mt-1">Add some products to get started</p>
              </div>
            ) : (
              <>
                {/* Cart Items */}
                <div className="space-y-4">
                  {cartItems.map((product: Product & { cartQuantity: number }) => (
                    <div key={product.id} className="flex gap-3 items-start">
                      {/* Product Image */}
                      <div className="w-12 h-12 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                        <img
                          src={product.image_urls?.thumb || product.image_urls?.medium}
                          alt={product.name}
                          className="w-full h-full object-cover"
                        />
                      </div>

                      {/* Product Info */}
                      <div className="flex-1 min-w-0">
                        {/* Title and Remove Button */}
                        <div className="flex items-center justify-between">
                          <h4 className="text-sm font-medium truncate flex-1">{product.name}</h4>
                          <button
                            onClick={() => removeItem(product.id)}
                            className="text-red-500 hover:text-red-600 ml-2 flex-shrink-0 p-1"
                            title="Remove item"
                            aria-label="Remove item from cart"
                          >
                            <Trash2 className="w-5 h-5" />
                          </button>
                        </div>

                        {/* Quantity and Price */}
                        <div className="flex items-center justify-between mt-2">
                          <div className="flex items-center">
                            <ProductCountButton id={product.id} stock={product.stock} />
                          </div>
                          <div className="text-sm font-semibold">
                            ${(parseFloat(product.price) * product.cartQuantity).toFixed(2)}
                          </div>
                        </div>
                      </div>
                    </div>
                  ))}
                </div>

                {/* Order Summary */}
                <div className="border-t pt-4 space-y-2">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Subtotal</span>
                    <span className="font-medium">${subtotal.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Shipping</span>
                    <span className="font-medium">${shipping.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Tax</span>
                    <span className="font-medium">${tax.toFixed(2)}</span>
                  </div>
                  <div className="flex justify-between text-base font-semibold pt-2 border-t">
                    <span>Total</span>
                    <span>${total.toFixed(2)}</span>
                  </div>
                </div>

                {/* Checkout Button */}
                <Button
                  onClick={() => navigate('/checkout')}
                  className="w-full bg-black hover:bg-gray-800 text-white py-3 text-base font-medium mt-4"
                  size="lg"
                >
                  Proceed to Checkout
                </Button>
              </>
            )}
          </>
        ) : null}
      </CardContent>
    </Card>
  )
}

export default Cart
