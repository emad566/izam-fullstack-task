import { Minus, Plus } from "lucide-react"
import { Button } from "../ui/button"
import { useMutation, useQueryClient } from "@tanstack/react-query"
import { useEffect, useState } from "react"
import { useDebouncedCallback } from "@/hooks/use-debounced-callback"

type Props = { id: number | string }

const ProductCountButton = ({ id }: Props) => {
  const [count, setCount] = useState(0)
  const increment = () => {
    setCount((pre) => ++pre)
  }
  const decrement = () => {
    setCount((pre) => (pre === 0 ? 0 : --pre))
  }
  // query client
  const queryClient = useQueryClient()
  const mutation = useMutation({
    mutationFn: async ({ count }: { count: number }) => {
      // handle update count here
      return count
    },
    onSuccess(data, variables, context) {
      // handle refetching cart here

      queryClient.invalidateQueries({
        queryKey: ["cart"],
      })
    },
    onError(error, variables, context) {
      // handle error here

      queryClient.invalidateQueries({
        queryKey: ["cart"],
      })
    },
  })

  const update = useDebouncedCallback(mutation.mutate, 1000)

  useEffect(() => {
    update({ count })
  }, [count, update])
  return (
    <div className="rounded-md overflow-hidden border w-fit flex items-center flex-nowrap">
      <Button onClick={decrement} variant="secondary" size="icon">
        <Minus />
      </Button>
      <div className="min-w-18 flex justify-center items-center">{count}</div>
      <Button onClick={increment} variant="secondary" size="icon">
        <Plus />
      </Button>
    </div>
  )
}

export default ProductCountButton
