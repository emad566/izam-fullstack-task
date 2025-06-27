import { Button } from "@/components/ui/button"

interface EmptyCartProps {
  onContinueShopping: () => void
}

const EmptyCart = ({ onContinueShopping }: EmptyCartProps) => {
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-2xl font-bold mb-4">Your cart is empty</h1>
        <p className="text-gray-500 mb-4">Add some products to proceed with checkout</p>
        <Button onClick={onContinueShopping}>
          Continue Shopping
        </Button>
      </div>
    </div>
  )
}

export default EmptyCart
