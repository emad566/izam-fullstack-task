import type { ErrorResponse } from "@/@types"
import axios from "axios"
import type { FieldValues, Path, UseFormReturn } from "react-hook-form"

// a function to handle form errors
export const handleFormError = <T extends FieldValues>(
  error: unknown,
  form: UseFormReturn<T>
) => {
  if (axios.isAxiosError(error) && error.response?.status) {
    const responseError = error.response.data as ErrorResponse<{ "": "" }>

    form.setError("root", { message: responseError.message })

    if (responseError.errors) {
      for (const key in responseError.errors) {
        form.setError(key as Path<T>, {
          message:
            responseError.errors![key as keyof typeof responseError.errors]![0],
          type: "custom",
        })
      }
    }

    return
  }
  form.setError("root", { message: "server error!" })
}
