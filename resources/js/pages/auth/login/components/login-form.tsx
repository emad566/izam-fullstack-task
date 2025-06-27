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
import { Eye, EyeOff } from "lucide-react"
import { useState } from "react"

const loginSchema = z.object({
  email: z.string().email(),
  password: z.string().min(8).max(32),
})

export function LoginForm({
  className,
  ...props
}: React.ComponentProps<"div">) {
  const [showPassword, setShowPassword] = useState(false)

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
          token: response.data.data.token,
        })
      )

      // Dispatch custom event to notify header of auth change
      window.dispatchEvent(new CustomEvent('authChanged'))

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
            Welcome back{" "}
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
                          <div className="relative">
                            <Input
                              placeholder="Enter your password"
                              type={showPassword ? "text" : "password"}
                              {...field}
                              className="pr-10"
                            />
                            <button
                              type="button"
                              onClick={() => setShowPassword(!showPassword)}
                              className="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                            >
                              {showPassword ? (
                                <EyeOff className="h-4 w-4" />
                              ) : (
                                <Eye className="h-4 w-4" />
                              )}
                            </button>
                          </div>
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
