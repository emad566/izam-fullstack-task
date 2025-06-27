import { ModalProvider } from "./contexts/modal-context"
import MyNuqsAdapter from "./lib/nuqs"
import MyReactQueryProvider from "./lib/react-query"
import MyRouter from "./router"

function App() {
  return (
    <MyReactQueryProvider>
      <ModalProvider>
        <MyNuqsAdapter>
          <MyRouter />
        </MyNuqsAdapter>
      </ModalProvider>
    </MyReactQueryProvider>
  )
}

export default App
