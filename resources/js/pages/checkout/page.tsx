import { useState } from "react"
import Api from "@/services"
import { useQuery, useQueryClient } from "@tanstack/react-query"
import { useNavigate } from "react-router"
import type { ProductsResponse, Product } from "../@types"

// Import components
import CartItemsList from "./components/cart-items-list"
import OrderSummary from "./components/order-summary"
import EmptyCart from "./components/empty-cart"
import LoadingState from "./components/loading-state"

interface CartItem {
  id: number | string
  quantity: number
}

const CheckoutPage = () => {
  const queryClient = useQueryClient()
  const navigate = useNavigate()
  const [isProcessing, setIsProcessing] = useState(false)

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

      const productIds = cart.map(item => item.id)
      const result = await Api.get<ProductsResponse>("/guest/products", {
        params: { per_page: 100 },
      })

      const cartProducts = result.data.data.items.data
        .filter((product: Product) => productIds.includes(product.id))
        .map((product: Product) => {
          const cartItem = cart.find(item => item.id.toString() === product.id.toString())
          return {
            ...product,
            cartQuantity: cartItem?.quantity || 0
          }
        })

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
  const shipping = 15.00
  const tax = subtotal * 0.15
  const total = subtotal + shipping + tax

  const handlePlaceOrder = async () => {
    setIsProcessing(true)
    try {
      await new Promise(resolve => setTimeout(resolve, 2000))
      saveCart([])
      alert("Order placed successfully!")
      navigate('/')
    } catch {
      alert("Failed to place order. Please try again.")
    } finally {
      setIsProcessing(false)
    }
  }

  const handleContinueShopping = () => {
    navigate('/')
  }

  if (result.status === "pending") {
    return <LoadingState />
  }

  if (cartItems.length === 0) {
    return <EmptyCart onContinueShopping={handleContinueShopping} />
  }

  return (
    <div className="min-h-screen bg-white">
      <div className="max-w-6xl mx-auto px-4 py-8">
        <div className="grid lg:grid-cols-5 gap-8">
          <CartItemsList
            cartItems={cartItems}
            onRemoveItem={removeItem}
          />

          <OrderSummary
            subtotal={subtotal}
            shipping={shipping}
            tax={tax}
            total={total}
            isProcessing={isProcessing}
            onPlaceOrder={handlePlaceOrder}
          />
        </div>
      </div>
    </div>
  )
}

export default CheckoutPage
