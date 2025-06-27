import type { Product } from "@/pages/@types"
import { Card, CardContent } from "../ui/card"
import { Badge } from "../ui/badge"
import { Button } from "../ui/button"
import ProductCountButton from "./product-count-button"
import { SideModal } from "./drawer"
import { useModal } from "@/contexts/modal-context"
import { useQueryClient } from "@tanstack/react-query"
import { useState, useEffect } from "react"

interface CartItem {
  id: number | string
  quantity: number
}

const ProductCard = (props: Product) => {
  const { openModal, closeModal } = useModal()
  const queryClient = useQueryClient()
  const [currentQuantity, setCurrentQuantity] = useState(0)

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
    // Invalidate cart queries to trigger re-render
    queryClient.invalidateQueries({
      queryKey: ["cart"],
    })
    // Dispatch custom event to sync all ProductCountButton instances
    window.dispatchEvent(new CustomEvent('cartUpdated', {
      detail: { cart }
    }))
  }

  // Load current quantity from localStorage
  useEffect(() => {
    const cart = getCart()
    const item = cart.find(item => item.id.toString() === props.id.toString())
    setCurrentQuantity(item?.quantity || 0)
  }, [props.id])

  // Listen for cart changes to update quantity
  useEffect(() => {
    const handleStorageChange = () => {
      const cart = getCart()
      const item = cart.find(item => item.id.toString() === props.id.toString())
      setCurrentQuantity(item?.quantity || 0)
    }

    // Listen for storage changes from other tabs/windows
    window.addEventListener('storage', handleStorageChange)

    // Listen for custom cart update events
    window.addEventListener('cartUpdated', handleStorageChange)

    return () => {
      window.removeEventListener('storage', handleStorageChange)
      window.removeEventListener('cartUpdated', handleStorageChange)
    }
  }, [props.id])

    const handleAddToCart = () => {
    // Check if stock is available before adding
    if (props.stock && props.stock <= 0) {
      console.log("Product is out of stock")
      return
    }

    if (currentQuantity > 0) {
      // Item is already in cart, show success message
      console.log(`Updated ${currentQuantity} of ${props.name} in cart`)
    } else {
      // Add one item to cart if quantity is 0
      const cart = getCart()
      const existingItemIndex = cart.findIndex(item => item.id.toString() === props.id.toString())

      if (existingItemIndex !== -1) {
        cart[existingItemIndex].quantity = 1
      } else {
        cart.push({ id: props.id, quantity: 1 })
      }

      saveCart(cart)
      setCurrentQuantity(1)

      // Dispatch custom event to update other components
      window.dispatchEvent(new CustomEvent('cartUpdated', {
        detail: { cart }
      }))
    }

    // Always close modal after any cart action
    closeModal(`product-${props.id}`)
  }

  return (
    <>
      <Card className="border-0 pt-0">
        <div
          className="w-full aspect-[9/10] cursor-pointer"
          onClick={() => {
            openModal(`product-${props.id}`)
          }}
        >
          <img
            src={props.image_urls?.large}
            alt={props.name}
            className="w-full h-full object-cover hover:opacity-90 transition-opacity"
          />
        </div>
        <CardContent className="space-y-3">
          {/* Mobile: Vertical Layout, Desktop: Horizontal */}
          <div className="md:flex md:gap-4 md:flex-nowrap space-y-2 md:space-y-0">
            <p
              onClick={() => {
                openModal(`product-${props.id}`)
              }}
              className="font-bold w-full truncate cursor-pointer hover:text-gray-600"
            >
              {props.name}
            </p>
            <Badge variant="secondary" className="w-fit truncate max-w-[100px]">{props.category.name}</Badge>
          </div>

          {/* Mobile: Vertical Stack, Desktop: Horizontal */}
          <div className="space-y-2 md:space-y-0 md:flex md:gap-4 md:justify-between">
            <p className="font-bold">${props.price}</p>
            <span className="text-[#6B7280] text-sm">
              Stock : {props.stock}
            </span>
          </div>

          {/* Product Count Button - Centered on mobile */}
          <div className="flex justify-center">
            <div className="scale-75 md:scale-100">
              <ProductCountButton id={props.id} stock={props.stock} />
            </div>
          </div>
        </CardContent>
      </Card>

      <SideModal
        id={`product-${props.id}`}
        title="Product Details"
        shouldCloseOnOverlayClick={true}
        size="md"
      >
        <div className="space-y-4">
          {/* Product Image - Increased Size */}
          <div className="w-full max-w-[300px] mx-auto aspect-[3/4] bg-gray-100 rounded-lg overflow-hidden">
            <img
              src={props.image_urls?.large}
              alt={props.name}
              className="w-full h-full object-cover"
            />
          </div>

          {/* Product Info */}
          <div className="space-y-4">
            {/* Title and Category */}
            <div className="flex items-start justify-between gap-3">
              <h2 className="text-xl font-semibold leading-tight">
                {props.name}
              </h2>
              <Badge variant="secondary" className="shrink-0">
                {props.category.name}
              </Badge>
            </div>

            {/* Price */}
            <div className="text-2xl font-bold">
              ${props.price}
            </div>

            {/* Product Details Section */}
            <div className="space-y-3">
              <h3 className="font-semibold text-lg">Product Details</h3>

              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span className="text-gray-600">Category:</span>
                  <span className="font-medium">{props.category.name}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Stock:</span>
                  <span className="font-medium">{props.stock} items</span>
                </div>
              </div>
            </div>

            {/* Quantity Selector - Horizontal Layout */}
            <div className="flex items-center justify-between">
              <h4 className="font-medium">Quantity</h4>
              <ProductCountButton id={props.id} stock={props.stock} />
            </div>

            {/* Add to Cart Button */}
            <Button
              className="w-full bg-black hover:bg-gray-800 text-white py-3 text-base font-medium disabled:bg-gray-400"
              size="lg"
              onClick={handleAddToCart}
              disabled={props.stock === 0}
            >
              {props.stock === 0
                ? 'Out of Stock'
                : currentQuantity > 0
                  ? `Update Cart (${currentQuantity})`
                  : 'Add to Cart'
              }
            </Button>
          </div>
        </div>
      </SideModal>
    </>
  )
}

export default ProductCard
