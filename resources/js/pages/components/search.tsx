import { Input } from "@/components/ui/input"
import { useDebouncedCallback } from "@/hooks/use-debounced-callback"
import { cn } from "@/lib/utils"
import type { LucideIcon } from "lucide-react"
import { parseAsString, useQueryState } from "nuqs"
import React, { useState } from "react"
import { Search as SearchIcon, SlidersHorizontal } from "lucide-react"
import { Button } from "@/components/ui/button"
import { useModal } from "@/contexts/modal-context"
import { SideModal } from "@/components/common/drawer"
import FiltersForm from "./filters-form"

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
  const { openModal } = useModal()

  const search = useDebouncedCallback(setQ, 500)

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setValue(e.target.value)
    search(e.target.value)
  }

    return (
    <>
      <div className="flex gap-2 items-center">
        <div className="relative flex-1">
          <div className="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <SearchIcon className="w-4 h-4 text-gray-400" />
          </div>
          <Input
            placeholder={"Search by product name"}
            variant="white"
            {...props}
            value={value}
            onChange={handleChange}
            className={cn("pl-10", className)}
          />
        </div>

        {/* Mobile Filter Button - Only visible on small screens */}
        <Button
          onClick={() => openModal("filters")}
          className="md:hidden w-10 h-10 rounded-full"
          variant="outline"
          size="icon"
        >
          <SlidersHorizontal className="w-4 h-4" />
        </Button>
      </div>

      {/* Mobile Filters Modal */}
      <SideModal title="Filters" id="filters" shouldCloseOnOverlayClick={true}>
        <FiltersForm />
      </SideModal>
    </>
  )
}

export default Search
