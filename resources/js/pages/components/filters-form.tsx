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
  parseAsString,
  useQueryStates,
} from "nuqs"
import { useEffect } from "react"
import { useForm } from "react-hook-form"
const categoryOptions = ["T-shirts", "Polo", "Jeans", "Shirts"]

const FiltersForm = () => {
  const [initValues, set] = useQueryStates({
    price: parseAsArrayOf(parseAsInteger).withDefault([0, 100]),
    categories: parseAsArrayOf(parseAsString).withDefault([]),
  })
  const form = useForm<{
    price: number[]
    all: boolean
    categories: string[]
  }>({
    defaultValues: { ...initValues, all: initValues.categories?.length === 4 },
  })
  const all = form.watch("all")
  const selected = form.watch("categories")

  // Keep 'All' in sync with individual checkboxes
  useEffect(() => {
    const allSelected = selected.length === categoryOptions.length
    if (allSelected && !all) {
      form.setValue("all", true)
    } else if (!allSelected && all) {
      form.setValue("all", false)
    }
  }, [selected, all, form])

  const onSubmit = form.handleSubmit((values) => {
    set({ price: values.price, categories: values.categories })
  })
  const onClear = () => {
    set({ price: [0, 100], categories: [] })
    form.reset({ price: [0, 100], categories: [], all: false })
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
                          max={100}
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
                              "categories",
                              checked ? categoryOptions : []
                            )
                          }}
                        />
                      </FormControl>
                      <FormLabel className="text-sm font-normal">All</FormLabel>
                    </FormItem>
                  )}
                />

                {/* Individual categories */}
                {categoryOptions.map((category) => (
                  <FormField
                    key={category}
                    control={form.control}
                    name="categories"
                    render={({ field }) => {
                      const isChecked = field.value?.includes(category)
                      return (
                        <FormItem className="flex flex-row items-center space-x-3 space-y-0">
                          <FormControl>
                            <Checkbox
                              checked={isChecked}
                              onCheckedChange={(checked) => {
                                const newValues = checked
                                  ? [...field.value, category]
                                  : field.value.filter(
                                      (item) => item !== category
                                    )
                                field.onChange(newValues)
                              }}
                            />
                          </FormControl>
                          <FormLabel className="text-sm font-normal">
                            {category}
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
