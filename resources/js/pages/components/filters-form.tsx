import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from "@/components/ui/accordion"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form"
import { DualRangeSlider } from "@/components/ui/range-slider"
import {
  parseAsArrayOf,
  parseAsInteger,
  useQueryStates,
} from "nuqs"
import { useEffect } from "react"
import { useForm } from "react-hook-form"
import { useQuery } from "@tanstack/react-query"
import Api from "@/services"

interface Category {
  id: number
  name: string
  created_at: string
  updated_at: string
  deleted_at: string | null
}

interface CategoriesResponse {
  status: boolean
  message: string
  data: {
    helpers: null
    items: {
      data: Category[]
      links: {
        first: string
        last: string
        prev: string | null
        next: string | null
      }
      meta: {
        current_page: number
        from: number
        last_page: number
        per_page: number
        to: number
        total: number
      }
    }
  }
  errors: null
  response_code: number
}

const FiltersForm = () => {
  const [initValues, set] = useQueryStates({
    min_price: parseAsInteger.withDefault(0),
    max_price: parseAsInteger.withDefault(10000),
    "category_ids[]": parseAsArrayOf(parseAsInteger).withDefault([]),
  })

  // Fetch categories from API
  const categoriesQuery = useQuery({
    queryKey: ["categories"],
    queryFn: async () => {
      const result = await Api.get<CategoriesResponse>("/guest/categories", {
        params: { per_page: 100 } // Get all categories
      })
      return result.data.data.items.data
    },
  })

  const categories = categoriesQuery.data || []

  const form = useForm<{
    price: number[]
    all: boolean
    category_ids: number[]
  }>({
    defaultValues: {
      all: initValues["category_ids[]"]?.length === categories.length,
      price: [initValues.min_price, initValues.max_price],
      category_ids: initValues["category_ids[]"],
    },
  })

  const all = form.watch("all")
  const selected = form.watch("category_ids")

  // Update form when categories are loaded or initValues change
  useEffect(() => {
    if (categories.length > 0) {
      form.setValue("all", initValues["category_ids[]"]?.length === categories.length)
      form.setValue("category_ids", initValues["category_ids[]"])
    }
  }, [categories, initValues, form])

  // Keep 'All' in sync with individual checkboxes
  useEffect(() => {
    if (categories.length > 0) {
      const allSelected = selected.length === categories.length
      if (allSelected && !all) {
        form.setValue("all", true)
      } else if (!allSelected && all) {
        form.setValue("all", false)
      }
    }
  }, [selected, all, form, categories.length])

  const onSubmit = form.handleSubmit((values) => {
    set({
      min_price: values.price[0] !== 0 ? values.price[0] : null,
      max_price: values.price[1] !== 10000 ? values.price[1] : null,
      "category_ids[]": values.category_ids,
    })
  })

  const onClear = () => {
    set({ min_price: null, max_price: null, "category_ids[]": [] })
    form.reset({ price: [0, 10000], category_ids: [], all: false })
  }

  if (categoriesQuery.isLoading) {
    return <div className="p-4">Loading categories...</div>
  }

  if (categoriesQuery.isError) {
    return <div className="p-4 text-red-500">Error loading categories</div>
  }

  return (
    <Form {...form}>
      <form onSubmit={onSubmit} className="flex gap-7 flex-col h-full">
        <Accordion defaultValue={["item-1", "item-2"]} type="multiple">
          <AccordionItem value="item-1">
            <AccordionTrigger className="text-lg  font-semibold">
              Price
            </AccordionTrigger>
            <AccordionContent>
              <FormField
                control={form.control}
                name="price"
                render={({ field }) => (
                  <FormItem>
                    <FormControl>
                      <div
                        className="py-4 px-2"
                        onPointerDown={(e) => e.stopPropagation()}
                        onPointerMove={(e) => e.stopPropagation()}
                        onPointerUp={(e) => e.stopPropagation()}
                        onTouchStart={(e) => e.stopPropagation()}
                        onTouchMove={(e) => e.stopPropagation()}
                        onTouchEnd={(e) => e.stopPropagation()}
                      >
                        <DualRangeSlider
                          label={(value) => value}
                          value={field.value}
                          onValueChange={field.onChange}
                          min={0}
                          max={10000}
                          step={1}
                          labelPosition="bottom"
                        />
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />
            </AccordionContent>
          </AccordionItem>
          <AccordionItem value="item-2">
            <AccordionTrigger className="text-lg  font-semibold">
              Category
            </AccordionTrigger>
            <AccordionContent>
              <div className="flex flex-col gap-2">
                {/* All */}
                <FormField
                  control={form.control}
                  name="all"
                  render={({ field }) => (
                    <FormItem className="flex flex-row items-center space-x-3 space-y-0">
                      <FormControl>
                        <Checkbox
                          checked={field.value}
                          onCheckedChange={(checked) => {
                            field.onChange(checked)
                            form.setValue(
                              "category_ids",
                              checked ? categories.map(cat => cat.id) : []
                            )
                          }}
                        />
                      </FormControl>
                      <FormLabel className="text-sm font-normal">All</FormLabel>
                    </FormItem>
                  )}
                />

                {/* Individual categories */}
                {categories.map((category) => (
                  <FormField
                    key={category.id}
                    control={form.control}
                    name="category_ids"
                    render={({ field }) => {
                      const isChecked = field.value?.includes(category.id)
                      return (
                        <FormItem className="flex flex-row items-center space-x-3 space-y-0">
                          <FormControl>
                            <Checkbox
                              checked={isChecked}
                              onCheckedChange={(checked) => {
                                const newValues = checked
                                  ? [...field.value, category.id]
                                  : field.value.filter(
                                      (id) => id !== category.id
                                    )
                                field.onChange(newValues)
                              }}
                            />
                          </FormControl>
                          <FormLabel className="text-sm font-normal">
                            {category.name}
                          </FormLabel>
                        </FormItem>
                      )
                    }}
                  />
                ))}
              </div>
            </AccordionContent>
          </AccordionItem>
        </Accordion>
        <div className="flex mt-auto grow flex-col justify-end gap-2">
          <Button type="submit">Apply</Button>
          <Button
            onClick={onClear}
            type="button"
            className="text-[6B7280]"
            variant={"ghost"}
          >
            Clear all filters
          </Button>
        </div>
      </form>
    </Form>
  )
}

export default FiltersForm
