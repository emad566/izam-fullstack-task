import { useState } from "react"
import Api from "@/services"
import { useQuery, useQueryClient } from "@tanstack/react-query"
import { useNavigate } from "react-router"
import type { ProductsResponse, Product } from "../@types"
import { toast } from "@/utils/toast"

// Import components
import CartItemsList from "./components/cart-items-list"
import OrderSummary from "./components/order-summary"
import EmptyCart from "./components/empty-cart"
import LoadingState from "./components/loading-state"
import SuccessModal from "./components/success-modal"

interface CartItem {
  id: number | string
  quantity: number
}

const CheckoutPage = () => {
  const queryClient = useQueryClient()
  const navigate = useNavigate()
  const [isProcessing, setIsProcessing] = useState(false)
  const [showSuccessModal, setShowSuccessModal] = useState(false)

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
      const cart = getCart()

      // Prepare order data
      const orderData = {
        products: cart.map(item => ({
          product_id: Number(item.id),
          quantity: item.quantity
        }))
      }

      // Call the API
      await Api.post("/user/orders", orderData)

      // Show success modal first, then clear cart
      setShowSuccessModal(true)
      // Clear cart after a small delay to prevent empty cart message from showing
      setTimeout(() => {
        saveCart([])
      }, 100)
        } catch (error: unknown) {
      // Type guard for axios error
      const isAxiosError = error && typeof error === 'object' && 'response' in error

      // Show error toast
      let errorMessage = "Failed to place order. Please try again."
      let shouldRedirect = false

      if (isAxiosError) {
        const axiosError = error as { response?: { status?: number; data?: { message?: string; response_code?: number } } }
        errorMessage = axiosError.response?.data?.message || errorMessage
        shouldRedirect = axiosError.response?.status === 400 || axiosError.response?.data?.response_code === 400
      }

      toast.error(errorMessage)

      // If response code is 400, redirect to login after 5 seconds
      if (shouldRedirect) {
        setTimeout(() => {
          navigate('/auth/login')
        }, 3000)
      }
    } finally {
      setIsProcessing(false)
    }
  }

  const handleSuccessModalClose = () => {
    setShowSuccessModal(false)
    navigate('/')
  }

  const handleContinueShopping = () => {
    navigate('/')
  }

  if (result.status === "pending") {
    return <LoadingState />
  }

  if (cartItems.length === 0 && !showSuccessModal) {
    return <EmptyCart onContinueShopping={handleContinueShopping} />
  }

    return (
    <>
      <div className="min-h-screen bg-white">
        <div className="max-w-6xl mx-auto px-4 py-8">
          {/* Mobile: Stack vertically, Desktop: Grid layout */}
          <div className="flex flex-col lg:grid lg:grid-cols-5 gap-8">
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

      <SuccessModal
        isOpen={showSuccessModal}
        onClose={handleSuccessModalClose}
      />
    </>
  )
}

export default CheckoutPage
