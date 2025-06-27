import { Button } from "@/components/ui/button"
import { Loader2 } from "lucide-react"

interface OrderSummaryProps {
  subtotal: number
  shipping: number
  tax: number
  total: number
  isProcessing: boolean
  onPlaceOrder: () => void
}

const OrderSummary = ({
  subtotal,
  shipping,
  tax,
  total,
  isProcessing,
  onPlaceOrder
}: OrderSummaryProps) => {
  return (
    <div className="lg:col-span-2">
      <div className="bg-gray-50 rounded-lg p-6 sticky top-8">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-lg font-semibold">Order Summary (#123)</h2>
          <span className="text-sm text-gray-500">
            {new Date().toLocaleDateString('en-US', {
              day: 'numeric',
              month: 'short',
              year: 'numeric'
            })}
          </span>
        </div>

        <div className="space-y-3 mb-6">
          <div className="flex justify-between">
            <span className="text-gray-600">Subtotal</span>
            <span className="font-medium">${subtotal.toFixed(0)}</span>
          </div>
          <div className="flex justify-between">
            <span className="text-gray-600">Shipping</span>
            <span className="font-medium">${shipping.toFixed(2)}</span>
          </div>
          <div className="flex justify-between">
            <span className="text-gray-600">Tax</span>
            <span className="font-medium">${tax.toFixed(2)}</span>
          </div>

          <div className="border-t pt-3">
            <div className="flex justify-between text-xl font-bold">
              <span>Total</span>
              <span>${total.toFixed(2)}</span>
            </div>
          </div>
        </div>

        <Button
          onClick={onPlaceOrder}
          disabled={isProcessing}
          className="w-full bg-black hover:bg-gray-800 text-white py-3 text-base font-medium"
          size="lg"
        >
          {isProcessing ? (
            <>
              <Loader2 className="w-4 h-4 mr-2 animate-spin" />
              Processing...
            </>
          ) : (
            'Place the order'
          )}
        </Button>
      </div>
    </div>
  )
}

export default OrderSummary
