import { Button } from "@/components/ui/button"
import { CheckCircle } from "lucide-react"
import { useEffect, useState } from "react"

interface SuccessModalProps {
  isOpen: boolean
  onClose: () => void
}

const SuccessModal = ({ isOpen, onClose }: SuccessModalProps) => {
  const [showAnimation, setShowAnimation] = useState(false)

  useEffect(() => {
    if (isOpen) {
      setShowAnimation(true)
    }
  }, [isOpen])

  if (!isOpen) return null

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-8 max-w-md w-full mx-4 text-center">
        <div className={`transition-all duration-500 ${showAnimation ? 'scale-100 opacity-100' : 'scale-0 opacity-0'}`}>
          <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-4" />
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Order Placed Successfully!</h2>
          <p className="text-gray-600 mb-6">
            Thank you for your purchase. Your order has been confirmed and is being processed.
          </p>
          <Button
            onClick={onClose}
            className="w-full bg-green-600 hover:bg-green-700 text-white"
          >
            Continue Shopping
          </Button>
        </div>
      </div>
    </div>
  )
}

export default SuccessModal
