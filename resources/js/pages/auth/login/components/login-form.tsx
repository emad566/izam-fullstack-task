import { Button } from "@/components/ui/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { cn } from "@/lib/utils"

import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import z from "zod"
import Api from "@/services"
import { handleFormError } from "@/utils/form-error"
import type { LoginResponse } from "@/@types/user"
import { LOCALSTORAGE_SESSION_KEY } from "@/config"
import { useNavigate } from "react-router"

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8).max(32),
})

export function LoginForm({
  className,
  ...props
}: React.ComponentProps<"div">) {
  const form = useForm({
    resolver: zodResolver(loginSchema),
    defaultValues: {
      email: "",
      password: "",
    },
  })

  const navigate = useNavigate()
  const onSubmit = form.handleSubmit(async (data) => {
    try {
      const response = await Api.post<LoginResponse>("/auth/user/login", data)
      window.localStorage.setItem(
        LOCALSTORAGE_SESSION_KEY,
        JSON.stringify({
          ...response.data.data.item,
          toke: response.data.data.token,
        })
      )

      navigate("/")
    } catch (error) {
      handleFormError(error, form)
    }
  })
  return (
    <div className={cn("flex flex-col gap-6 w-full", className)} {...props}>
      <Card className=" max-md:border-0 shadow-none md:shadow-md">
        <CardHeader className="text-center">
          <CardTitle className="text-lg md:text-2xl font-bold">
            Welcome backs{" "}
          </CardTitle>
          <CardDescription className="text-sm md:text-base">
            Please enter your details to sign in{" "}
          </CardDescription>
        </CardHeader>
        <CardContent className="max-md:px-0">
          <Form {...form}>
            <form onSubmit={onSubmit}>
              <div className="flex flex-col gap-6 w-full">
                <div className="grid gap-3">
                  <FormField
                    control={form.control}
                    name="email"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Email</FormLabel>
                        <FormControl>
                          <Input
                            placeholder="ex@example.com"
                            type="email"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
                <div className="grid gap-3">
                  <FormField
                    control={form.control}
                    name="password"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel>Password</FormLabel>
                        <FormControl>
                          <Input
                            placeholder="Enter your password"
                            type="password"
                            {...field}
                          />
                        </FormControl>
                        <FormMessage />
                      </FormItem>
                    )}
                  />
                </div>
                <Button
                  loading={form.formState.isSubmitting}
                  size={"lg"}
                  type="submit"
                  className="w-full"
                >
                  Login
                </Button>
                <p className="text-red-500 text-center text-sm">
                  {form.formState.errors.root?.message}
                </p>
              </div>
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  )
}
