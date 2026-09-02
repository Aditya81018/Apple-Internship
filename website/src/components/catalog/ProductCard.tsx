import { useState } from "react"
import { Card } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Sparkles } from "lucide-react"

export interface Product {
  id: string
  name: string
  price: number
  image: string
  category: string
  sizes?: string[]
  prices?: Record<string, number>
}

interface ProductCardProps {
  product: Product
  onAddToCart?: (product: Product, size: string, price: number) => void
}

export default function ProductCard({
  product,
  onAddToCart,
}: ProductCardProps) {
  const hasSizes = product.sizes && product.sizes.length > 0

  // No weight is selected when the card first loads
  const [selectedSize, setSelectedSize] = useState<string | null>(null)

  const [imgSrc, setImgSrc] = useState(product.image)

  const currentPrice =
    selectedSize && product.prices && product.prices[selectedSize]
      ? product.prices[selectedSize]
      : product.price

  const handleAddToCart = () => {
    if (onAddToCart) {
      onAddToCart(
        product,
        selectedSize || product.sizes?.[0] || "default",
        currentPrice
      )
    } else {
      console.log(
        "Added to cart:",
        product.name,
        selectedSize || product.sizes?.[0] || "default",
        currentPrice
      )
    }
  }

  // Fallback image
  const handleImageError = () => {
    setImgSrc(
      "https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80"
    )
  }

  /*
   * Each cake gets its own weight-button color.
   *
   * Vanilla       → Yellow
   * Butterscotch  → Orange
   * Black Forest  → Red
   * Chocolate     → Brown
   * Rosomalai     → Pink
   * Fresh Fruit   → Green
   */

  const getButtonColors = () => {
    switch (product.id) {
      case "vanilla-cake":
        return {
          border: "border-[#F4C542]",
          active: "bg-[#F4C542] border-[#F4C542] text-black",
          hover: "hover:bg-[#F4C542] hover:text-black",
        }

      case "butter-scotch-cake":
        return {
          border: "border-[#E89B3D]",
          active: "bg-[#E89B3D] border-[#E89B3D] text-black",
          hover: "hover:bg-[#E89B3D] hover:text-black",
        }

      case "black-forest-cake":
        return {
          border: "border-[#E85D5D]",
          active: "bg-[#E85D5D] border-[#E85D5D] text-white",
          hover: "hover:bg-[#E85D5D] hover:text-white",
        }

      case "chocolate-overload":
        return {
          border: "border-[#A66A4C]",
          active: "bg-[#A66A4C] border-[#A66A4C] text-white",
          hover: "hover:bg-[#A66A4C] hover:text-white",
        }

      case "rosomalai-cake":
        return {
          border: "border-[#E58BAA]",
          active: "bg-[#E58BAA] border-[#E58BAA] text-white",
          hover: "hover:bg-[#E58BAA] hover:text-white",
        }

      case "fresh-fruit-cake":
        return {
          border: "border-[#70B77E]",
          active: "bg-[#70B77E] border-[#70B77E] text-white",
          hover: "hover:bg-[#70B77E] hover:text-white",
        }

      default:
        return {
          border: "border-[#F4C542]",
          active: "bg-[#F4C542] border-[#F4C542] text-black",
          hover: "hover:bg-[#F4C542] hover:text-black",
        }
    }
  }

  const buttonColors = getButtonColors()

  return (
    <Card className="grid grid-rows-[subgrid] row-span-3 overflow-hidden rounded-[28px] border-2 border-border bg-card p-0 transition-all duration-300 ease-[cubic-bezier(0.175,0.885,0.32,1.275)] hover:-translate-y-2 hover:scale-[1.02] hover:rotate-[0.5deg] hover:border-primary/20 hover:shadow-lg shadow-sm">

      {/* =========================
          1. IMAGE AREA
          ========================= */}
      <div className="relative aspect-[4/3] w-full overflow-hidden bg-accent/10">
        <img
          src={imgSrc}
          alt={product.name}
          className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
          onError={handleImageError}
          loading="lazy"
        />

        {/* Eggless Tag */}
        <div className="absolute top-4 left-4 inline-flex items-center gap-1 rounded-full bg-secondary/95 px-3.5 py-1.5 text-[9px] font-black tracking-widest text-white shadow-sm uppercase">
          <Sparkles className="h-3 w-3 shrink-0" />
          100% EGGLESS
        </div>
      </div>

      {/* =========================
          2. CONTENT AREA
          ========================= */}
      <div className="flex flex-col justify-between p-6">
        <div>
          <h3 className="font-heading text-xl font-bold tracking-tight text-text-primary">
            {product.name}
          </h3>

          <p className="mt-2 font-sans text-xs font-semibold text-text-secondary leading-relaxed">
            Freshly baked premium artisan cake, crafted with organic
            ingredients.
          </p>
        </div>

        {/* =========================
            WEIGHT SELECTION
            ========================= */}
        {hasSizes && (
          <div className="mt-6 flex flex-col gap-2 border-t border-border/40 pt-4">
            <span className="font-sans text-xs text-text-secondary font-bold">
              Select Weight:
            </span>

            <div className="flex gap-2">
              {product.sizes?.map((size) => {
                const isActive = selectedSize === size

                return (
                  <button
                    key={size}
                    onClick={() => setSelectedSize(size)}
                    className={`
                      rounded-full
                      px-5
                      py-2
                      font-sans
                      text-xs
                      font-extrabold
                      border-2
                      cursor-pointer
                      transition-all
                      duration-200
                      hover:scale-105
                      active:scale-95

                      ${
                        isActive
                          ? buttonColors.active
                          : `bg-transparent ${buttonColors.border} text-white ${buttonColors.hover}`
                      }
                    `}
                  >
                    {size}
                  </button>
                )
              })}
            </div>
          </div>
        )}
      </div>

      {/* =========================
          3. FOOTER
          ========================= */}
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