import { Minus, Plus } from "lucide-react"
import { Button } from "../ui/button"
import { useQueryClient } from "@tanstack/react-query"
import { useEffect, useState } from "react"

type Props = {
  id: number | string
  stock?: number // Optional stock limit
}

interface CartItem {
  id: number | string
  quantity: number
}

const ProductCountButton = ({ id, stock }: Props) => {
  const [count, setCount] = useState(0)

  // query client for invalidating cart queries
  const queryClient = useQueryClient()

  // Get cart from localStorage
  const getCart = (): CartItem[] => {
    try {
      const cart = localStorage.getItem('cart')
      return cart ? JSON.parse(cart) : []
    } catch {
      return []
    }
  }

  // Save cart to localStorage and trigger all sync events
  const saveCart = (cart: CartItem[]) => {
    localStorage.setItem('cart', JSON.stringify(cart))

    // Invalidate cart queries to trigger re-render
    queryClient.invalidateQueries({
      queryKey: ["cart"],
    })

    // Dispatch custom event to sync all ProductCountButton instances
    window.dispatchEvent(new CustomEvent('cartUpdated', {
      detail: { cart, updatedProductId: id }
    }))
  }

  // Load initial count from localStorage
  useEffect(() => {
    const cart = getCart()
    const item = cart.find(item => item.id.toString() === id.toString())
    setCount(item?.quantity || 0)
  }, [id])

    // Listen for cart changes from any source
  useEffect(() => {
    const handleCartUpdate = () => {
      const cart = getCart()
      const item = cart.find(item => item.id.toString() === id.toString())
      const newCount = item?.quantity || 0

      // Only update if count actually changed to prevent unnecessary re-renders
      if (newCount !== count) {
        setCount(newCount)
      }
    }

    // Listen for storage changes from other tabs/windows
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'cart') {
        handleCartUpdate()
      }
    }

    // Add event listeners
    window.addEventListener('storage', handleStorageChange)
    window.addEventListener('cartUpdated', handleCartUpdate as EventListener)

    return () => {
      window.removeEventListener('storage', handleStorageChange)
      window.removeEventListener('cartUpdated', handleCartUpdate as EventListener)
    }
  }, [id, count])

  const updateCart = (newCount: number) => {
    const cart = getCart()
    const existingItemIndex = cart.findIndex(item => item.id.toString() === id.toString())

    if (newCount === 0) {
      // Remove item from cart if count is 0
      if (existingItemIndex !== -1) {
        cart.splice(existingItemIndex, 1)
      }
    } else {
      // Add or update item in cart
      if (existingItemIndex !== -1) {
        cart[existingItemIndex].quantity = newCount
      } else {
        cart.push({ id, quantity: newCount })
      }
    }

    // Update local state immediately for responsiveness
    setCount(newCount)

    // Save to localStorage and trigger sync
    saveCart(cart)
  }

  const increment = () => {
    // Check if we can increment (either no stock limit or count is less than stock)
    if (stock === undefined || count < stock) {
      const newCount = count + 1
      updateCart(newCount)
    }
  }

  const decrement = () => {
    const newCount = count === 0 ? 0 : count - 1
    updateCart(newCount)
  }

  // Check if increment should be disabled
  const isIncrementDisabled = stock !== undefined && count >= stock

  return (
    <div className="rounded-md overflow-hidden border w-fit flex items-center flex-nowrap">
      <Button onClick={decrement} variant="secondary" size="icon" disabled={count === 0}>
        <Minus />
      </Button>
      <div className="min-w-18 flex justify-center items-center font-medium">{count}</div>
      <Button
        onClick={increment}
        variant="secondary"
        size="icon"
        disabled={isIncrementDisabled}
        title={isIncrementDisabled ? `Maximum stock: ${stock}` : undefined}
      >
        <Plus />
      </Button>
    </div>
  )
}

export default ProductCountButton
