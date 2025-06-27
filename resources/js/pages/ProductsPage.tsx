import React from 'react'

const ProductsPage: React.FC = () => {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold tracking-tight text-foreground">Products</h1>
        <p className="mt-2 text-muted-foreground">
          Browse our collection of amazing products.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {[1, 2, 3, 4, 5, 6].map((item) => (
          <div
            key={item}
            className="rounded-lg border border-border bg-card p-6 shadow-sm"
          >
            <div className="aspect-square bg-muted rounded-md mb-4"></div>
            <h3 className="text-lg font-semibold text-card-foreground">Product {item}</h3>
            <p className="text-muted-foreground">Sample product description</p>
            <div className="mt-4 flex items-center justify-between">
              <span className="text-lg font-bold text-foreground">$99.99</span>
              <button className="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-3">
                Add to Cart
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}

export default ProductsPage
