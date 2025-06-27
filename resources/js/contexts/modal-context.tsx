// stores/modal-store.ts
import type { ReactNode } from "react"
import { createContext, useContext, useReducer } from "react"

// Types
interface ModalState {
  openModals: string[]
}

type ModalAction =
  | { type: "OPEN_MODAL"; id: string }
  | { type: "CLOSE_MODAL"; id: string }

interface ModalContextType {
  openModals: string[]
  openModal: (id: string) => void
  closeModal: (id: string) => void
}

const ModalContext = createContext<ModalContextType | undefined>(undefined)

// Reducer
function modalReducer(state: ModalState, action: ModalAction): ModalState {
  switch (action.type) {
    case "OPEN_MODAL": {
      if (state.openModals.includes(action.id)) {
        return state
      }
      return { openModals: [...state.openModals, action.id] }
    }

    case "CLOSE_MODAL": {
      return {
        openModals: state.openModals.filter(id => id !== action.id),
      }
    }

    default:
      return state
  }
}

// Provider
export const ModalProvider = ({ children }: { children: ReactNode }) => {
  const [state, dispatch] = useReducer(modalReducer, { openModals: [] })
  // const { pathname } = useRouter(); // Uncomment and fix this import if you have a custom router hook

  const value: ModalContextType = {
    openModals: state.openModals,
    openModal: (id: string) => dispatch({ type: "OPEN_MODAL", id }),

    closeModal: (id: string) => dispatch({ type: "CLOSE_MODAL", id }),
  }

  return <ModalContext.Provider value={value}>{children}</ModalContext.Provider>
}

// Hook
export const useModal = () => {
  const context = useContext(ModalContext)
  if (!context) {
    throw new Error("useModal must be used within a ModalProvider")
  }
  return context
}
