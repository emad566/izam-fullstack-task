import {
  Drawer,
  DrawerContent,
  DrawerHeader,
  DrawerTitle,
  DrawerClose,
  DrawerDescription,
} from "@/components/ui/drawer"
import { useModal } from "@/contexts/modal-context"
import { cn } from "@/lib/utils"
import { X } from "lucide-react"
import { useEffect } from "react"

interface SideModalProps {
  id: string
  title: string
  description?: string
  children: React.ReactNode
  size?:
    | "sm"
    | "md"
    | "lg"
    | "xl"
    | "2xl"
    | "3xl"
    | "4xl"
    | "5xl"
    | "6xl"
    | "7xl"
    | "full"
  shouldCloseOnOverlayClick?: boolean
}

export function SideModal({
  id,
  title,
  children,
  description,
  size = "md",
  shouldCloseOnOverlayClick = false,
}: SideModalProps) {
  const { openModals, closeModal } = useModal()
  const isOpen = openModals.includes(id)

  // Close on escape key (only for top modal)
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape" && isOpen) {
        closeModal(id)
      }
    }

    document.addEventListener("keydown", handleKeyDown)
    return () => document.removeEventListener("keydown", handleKeyDown)
  }, [isOpen, id, closeModal])

  const sizeClasses = {
    sm: "max-w-sm",
    md: "max-w-md",
    lg: "max-w-lg",
    xl: "max-w-xl",
    "2xl": "max-w-2xl",
    "3xl": "max-w-3xl",
    "4xl": "max-w-4xl",
    "5xl": "max-w-5xl",
    "6xl": "max-w-6xl",
    "7xl": "max-w-7xl",
    full: "max-w-full",
  }

  return (
    <Drawer
      open={isOpen}
      onOpenChange={(open) => {
        if (!open) {
          closeModal(id)
        }
      }}
    >
      <DrawerContent
        className={` w-full ${sizeClasses[size]} rounded-none !border-0 flex flex-col bg-card `}
        onInteractOutside={(e) => {
          e.preventDefault()
        }}
      >
        <DrawerHeader className={cn("flex justify-between")}>
          <div>
            <DrawerTitle>{title}</DrawerTitle>
            <DrawerDescription
              className={`${!description ? "hidden" : "block"}`}
            >
              {description}
            </DrawerDescription>
          </div>
          <DrawerClose>
            <X className="size-5" />
          </DrawerClose>
        </DrawerHeader>
        <div className="flex-1 w-full p-[24px] pt-[20px] overflow-auto z-10">
          {children}
        </div>
      </DrawerContent>
    </Drawer>
  )
}
