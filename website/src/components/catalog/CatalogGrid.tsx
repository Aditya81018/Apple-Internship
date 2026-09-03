import { useState } from "react"
import productsData from "@/data/products.json"
import ProductCard from "./ProductCard"
import type { Product } from "./ProductCard"
import { useCartStore } from "@/store/useCartStore"

export default function CatalogGrid() {
  const [activeTab, setActiveTab] = useState<"all" | "classics" | "specials">("all")
  const { addToCart } = useCartStore()

  // Filter products to show only standard/specialty cakes, excluding addons
  const cakes = (productsData as unknown as Product[]).filter(
    (product) => product.category === "cake"
  )

  // Sub-filter based on active tab for a realistic, professional store experience
  const filteredCakes = cakes.filter((cake) => {
    if (activeTab === "all") return true

    if (activeTab === "classics") {
      // Classics: Vanilla, Butterscotch, Black Forest, Chocolate
      return [
        "vanilla-cake",
        "butter-scotch-cake",
        "black-forest-cake",
        "chocolate-overload",
      ].includes(cake.id)
    }

    if (activeTab === "specials") {
      // Specials: Rosomalai, Fresh Fruit
      return ["rosomalai-cake", "fresh-fruit-cake"].includes(cake.id)
    }

    return true
  })

  const handleAddToCart = (product: Product, size: string, price: number) => {
    addToCart({
      id: `${product.id}-${size}`,
      productId: product.id,
      name: product.name,
      price: price,
      size: size,
      image: product.image,
    })
  }

  return (
    <section id="catalog" className="mx-auto max-w-7xl px-4 py-24 md:px-8">
      {/* Section Header */}
      <div className="mb-16 text-center">
        <div className="text-xs font-black tracking-widest text-primary uppercase mb-2">
          MENU CATALOG
        </div>

        <h2 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary md:text-5xl">
          Explore Our Signature Cakes
        </h2>

        <div className="mx-auto mt-4 h-1 w-16 bg-primary rounded-full"></div>

        <p className="mt-5 font-sans text-sm font-semibold text-text-secondary max-w-md mx-auto leading-relaxed">
          Bakes of happiness handcrafted fresh daily with 100% vegetarian,
          egg-free ingredients.
        </p>

        {/* Category Tabs */}
        <div className="mt-10 flex flex-wrap justify-center gap-3">

          {/* ALL CAKES */}
          <button
            onClick={() => setActiveTab("all")}
            className={`rounded-full px-6 py-2.5 font-sans text-sm font-extrabold
              transition-all duration-200 hover:scale-105
              ${
                activeTab === "all"
                  ? "bg-[#F9C74F] text-black shadow-lg shadow-[#F9C74F]/30"
                  : "bg-[#F9C74F] text-black hover:brightness-110"
              }`}
          >
            All Cakes
          </button>

          {/* CLASSICS */}
          <button
            onClick={() => setActiveTab("classics")}
            className={`rounded-full px-6 py-2.5 font-sans text-sm font-extrabold
              transition-all duration-200 hover:scale-105
              ${
                activeTab === "classics"
                  ? "bg-[#F15BB5] text-black shadow-lg shadow-[#F15BB5]/30"
                  : "bg-[#F15BB5] text-black hover:brightness-110"
              }`}
          >
            Classics (Vanilla & Chocolate)
          </button>

          {/* ARTISAN FRUIT & SPECIALS */}
          <button
            onClick={() => setActiveTab("specials")}
            className={`rounded-full px-6 py-2.5 font-sans text-sm font-extrabold
              transition-all duration-200 hover:scale-105
              ${
                activeTab === "specials"
                  ? "bg-[#8BD646] text-black shadow-lg shadow-[#8BD646]/30"
                  : "bg-[#8BD646] text-black hover:brightness-110"
              }`}
          >
            Artisan Fruit & Specials
          </button>

        </div>
      </div>

      {filteredCakes.length === 0 ? (
        <div className="text-center font-sans text-text-secondary py-16">
          No cakes found in this category.
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-x-8 gap-y-16 sm:grid-cols-2 lg:grid-cols-3">
          {filteredCakes.map((cake) => (
            <ProductCard
              key={cake.id}
              product={cake}
              onAddToCart={handleAddToCart}
            />
          ))}
        </div>
      )}
    </section>
  )
}