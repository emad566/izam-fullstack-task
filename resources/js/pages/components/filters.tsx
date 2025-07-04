import { SideModal } from "@/components/common/drawer"
import { Button } from "@/components/ui/button"
import { useModal } from "@/contexts/modal-context"
import { SlidersHorizontal } from "lucide-react"
import FiltersForm from "./filters-form"

const Filters = () => {
  const { openModal } = useModal()
  return (
    <>
      <Button
        onClick={() => {
          openModal("filters")
        }}
        className="size-16 rounded-md"
        variant="outline"
        size={"icon"}
      >
        <SlidersHorizontal strokeWidth={1.2} className="size-7" />
      </Button>
      <SideModal title="Filters" id="filters">
        <FiltersForm />
      </SideModal>
    </>
  )
}

export default Filters
