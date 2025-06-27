"use client"

import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
} from "@/components/ui/pagination"
import { cn } from "@/lib/utils"
import { parseAsInteger, useQueryState } from "nuqs"
import { type ReactNode } from "react"
import { Button } from "../ui/button"

export interface PaginationWithLinksProps {
  pageSizeSelectOptions?: {
    pageSizeSearchParam?: string
    pageSizeOptions: number[]
  }
  totalCount: number
  pageSize: number
  page: number
  pageSearchParam?: string
}

export function PaginationWithLinks({
  pageSizeSelectOptions,
  pageSize,
  totalCount,
}: PaginationWithLinksProps) {
  const [page, setPage] = useQueryState("page", parseAsInteger.withDefault(1))

  const totalPageCount = Math.ceil(totalCount / pageSize)

  const renderPageNumbers = () => {
    const items: ReactNode[] = []
    const maxVisiblePages = 5

    if (totalPageCount <= maxVisiblePages) {
      for (let i = 1; i <= totalPageCount; i++) {
        items.push(
          <PaginationItem key={i}>
            <Button
              size={"icon"}
              onClick={() => {
                setPage(i)
              }}
              variant={page === i ? "default" : "link"}
            >
              {i}
            </Button>
          </PaginationItem>
        )
      }
    } else {
      items.push(
        <PaginationItem key={1}>
          <Button
            onClick={() => {
              setPage(1)
            }}
            variant={page === 1 ? "default" : "link"}
          >
            1
          </Button>
        </PaginationItem>
      )

      if (page > 3) {
        items.push(
          <PaginationItem key="ellipsis-start">
            <PaginationEllipsis />
          </PaginationItem>
        )
      }

      const start = Math.max(2, page - 1)
      const end = Math.min(totalPageCount - 1, page + 1)

      for (let i = start; i <= end; i++) {
        items.push(
          <PaginationItem key={i}>
            <Button
              size={"icon"}
              onClick={() => {
                setPage(i)
              }}
              variant={page === i ? "default" : "link"}
            >
              {i}
            </Button>
          </PaginationItem>
        )
      }

      if (page < totalPageCount - 2) {
        items.push(
          <PaginationItem key="ellipsis-end">
            <PaginationEllipsis />
          </PaginationItem>
        )
      }

      items.push(
        <PaginationItem key={totalPageCount}>
          <Button
            onClick={() => {
              setPage(totalPageCount)
            }}
            variant={page === totalPageCount ? "default" : "link"}
          >
            {totalPageCount}
          </Button>
        </PaginationItem>
      )
    }

    return items
  }

  return (
    <div className="flex flex-col md:flex-row items-center gap-3 w-full">
      <Pagination className={cn({ "md:justify-end": pageSizeSelectOptions })}>
        <PaginationContent className="max-sm:gap-0">
          <PaginationItem>
            <Button
              onClick={() => {
                setPage(Math.max(page - 1, 1))
              }}
              tabIndex={page === 1 ? -1 : undefined}
              variant={"outline"}
              disabled={page === 1}
            >
              Previous
            </Button>
          </PaginationItem>
          {renderPageNumbers()}

          <PaginationItem>
            <Button
              onClick={() => {
                setPage(Math.min(page + 1, totalPageCount))
              }}
              tabIndex={page === totalPageCount ? -1 : undefined}
              variant={"outline"}
              disabled={page === totalPageCount}
            >
              Next
            </Button>
          </PaginationItem>
        </PaginationContent>
      </Pagination>
    </div>
  )
}
