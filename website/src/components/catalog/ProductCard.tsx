import { useState } from "react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Sparkles, Star } from "lucide-react"

export interface Product {
  id: string
  name: string
  price: number
  image: string
  category: string
  is_featured?: boolean
  sizes?: string[]
  prices?: Record<string, number>
}

interface ProductCardProps {
  product: Product
  onAddToCart?: (product: Product, size: string, price: number) => void
}

export default function ProductCard({ product, onAddToCart }: ProductCardProps) {
  const hasSizes = product.sizes && product.sizes.length > 0
  const [selectedSize, setSelectedSize] = useState(hasSizes ? product.sizes![0] : "default")
  const [imgSrc, setImgSrc] = useState(product.image)

  const currentPrice = hasSizes && product.prices && product.prices[selectedSize]
    ? product.prices[selectedSize]
    : product.price

  const handleAddToCart = () => {
    if (onAddToCart) {
      onAddToCart(product, selectedSize, currentPrice)
    } else {
      console.log("Added to cart:", product.name, selectedSize, currentPrice)
    }
  }

  // Fallback image in case the Unsplash link fails or blocks loading
  const handleImageError = () => {
    setImgSrc("https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80")
  }

  return (
    <Card className="grid grid-rows-[subgrid] row-span-3 overflow-hidden rounded-[28px] border-2 border-border bg-card p-0 transition-all duration-300 ease-[cubic-bezier(0.175,0.885,0.32,1.275)] hover:-translate-y-2 hover:scale-[1.02] hover:rotate-[0.5deg] hover:border-primary/20 hover:shadow-lg shadow-sm">
      {/* 1. Image Area */}
      <div className="relative aspect-[4/3] w-full overflow-hidden bg-accent/10">
        <img
          src={imgSrc}
          alt={product.name}
          className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
          onError={handleImageError}
          loading="lazy"
        />
        {/* Playful Diet Tag with vector icon */}
        <div className="absolute top-4 left-4 inline-flex items-center gap-1 rounded-full bg-secondary/95 px-3.5 py-1.5 text-[9px] font-black tracking-widest text-white shadow-sm uppercase">
          <Sparkles className="h-3 w-3 shrink-0" />
          100% EGGLESS
        </div>

        {/* Featured Cake Badge */}
        {product.is_featured && (
          <div className="absolute top-4 right-4 inline-flex items-center gap-1 rounded-full bg-primary/95 px-3 py-1 text-[9px] font-black tracking-widest text-white shadow-sm uppercase">
            <Star className="h-3 w-3 shrink-0 fill-white" />
            FEATURED
          </div>
        )}
      </div>

      {/* 2. Content Area */}
      <div className="flex flex-col justify-between p-6">
        <div>
          <h3 className="font-heading text-xl font-bold tracking-tight text-text-primary">
            {product.name}
          </h3>
          <p className="mt-2 font-sans text-xs font-semibold text-text-secondary leading-relaxed">
            Freshly baked premium artisan cake, crafted with organic ingredients.
          </p>
        </div>

        {/* Size Selection (Pill Selectors / Variant Chips) */}
        {hasSizes && (
          <div className="mt-6 flex flex-col gap-2 border-t border-border/40 pt-4">
            <span className="font-sans text-xs text-text-secondary font-bold">Select Weight:</span>
            <div className="flex gap-2">
              {product.sizes?.map((size) => {
                const isActive = selectedSize === size
                return (
                  <button
                    key={size}
                    onClick={() => setSelectedSize(size)}
                    className={`rounded-xl px-4 py-1.5 font-sans text-xs font-extrabold transition-all duration-200 border cursor-pointer ${
                      isActive
                        ? "bg-primary text-white border-primary shadow-xs"
                        : "bg-white border-border text-text-primary hover:border-primary/45"
                    }`}
                  >
                    {size}
                  </button>
                )
              })}
            </div>
          </div>
        )}
      </div>

      {/* 3. Footer / Price & Add to Cart */}
      <div className="flex items-center justify-between border-t-2 border-border/60 bg-[#FAFAFA]/40 p-6">
        <span className="font-sans text-xl font-extrabold text-text-primary">
          ₹{currentPrice}
        </span>
        <Button
          onClick={handleAddToCart}
          size="sm"
          className="h-9.5 text-xs font-extrabold px-5 rounded-xl transition-all duration-200 hover:scale-105 active:scale-95 hover:shadow-md hover:shadow-primary/10"
        >
          Add to Cart
        </Button>
      </div>
    </Card>
  )
}
