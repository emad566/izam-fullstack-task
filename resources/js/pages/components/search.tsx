import { Input } from "@/components/ui/input"
import { useDebouncedCallback } from "@/hooks/use-debounced-callback"
import { cn } from "@/lib/utils"
import type { LucideIcon } from "lucide-react"
import { parseAsString, useQueryState } from "nuqs"
import React, { useState } from "react"

export interface InputProps
  extends React.InputHTMLAttributes<HTMLInputElement> {
  icon?: LucideIcon
  variant?: "white" | "default"
  wrapperClassName?: string
  hasError?: boolean
}

const Search = ({ className, ...props }: InputProps) => {
  const [q, setQ] = useQueryState("q", parseAsString.withDefault(""))
  const [value, setValue] = useState(q)

  const search = useDebouncedCallback(setQ, 500)

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setValue(e.target.value)
    search(e.target.value)
  }

  return (
    <Input
      placeholder={"Search by product name"}
      variant="white"
      {...props}
      value={value}
      onChange={handleChange}
      className={cn(className)}
    />
  )
}

export default Search
