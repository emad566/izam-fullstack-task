import { Loader2 } from "lucide-react"

const LoadingState = () => {
  return (
    <div className="min-h-screen flex items-center justify-center">
      <Loader2 className="size-8 animate-spin" />
    </div>
  )
}

export default LoadingState
