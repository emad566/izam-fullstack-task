import { NuqsAdapter } from "nuqs/adapters/react-router/v7"

export default function MyNuqsAdapter({
  children,
}: {
  children: React.ReactNode
}) {
  return <NuqsAdapter>{children}</NuqsAdapter>
}
