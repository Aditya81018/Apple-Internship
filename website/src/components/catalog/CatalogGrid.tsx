import { useState, useEffect } from "react"
import productsData from "@/data/products.json"
import ProductCard from "./ProductCard"
import type { Product } from "./ProductCard"
import { useCartStore } from "@/store/useCartStore"

function normalizeProduct(p: any): Product {
  let sizes: string[] = []
  if (Array.isArray(p.sizes)) {
    sizes = p.sizes
  } else if (typeof p.sizes === "string" && p.sizes.trim() !== "") {
    try {
      const parsed = JSON.parse(p.sizes)
      if (Array.isArray(parsed)) sizes = parsed
    } catch {
      sizes = []
    }
  }

  let prices: Record<string, number> = {}
  if (p.prices && typeof p.prices === "object" && !Array.isArray(p.prices)) {
    prices = p.prices
  } else if (typeof p.prices === "string" && p.prices.trim() !== "") {
    try {
      const parsed = JSON.parse(p.prices)
      if (parsed && typeof parsed === "object") prices = parsed
    } catch {
      prices = {}
    }
  }

  const basePrice = Number(p.price) || 0
  const category = (p.category || "cake").toLowerCase()

  // Default sizes and prices if not provided for cakes
  if (sizes.length === 0 && category.includes("cake")) {
    sizes = ["1 lb", "2 lb"]
    if (Object.keys(prices).length === 0) {
      prices = {
        "1 lb": basePrice,
        "2 lb": Math.round(basePrice * 1.75),
      }
    }
  }

  return {
    id: String(p.id),
    name: String(p.name),
    price: basePrice,
    image:
      p.image && p.image.trim() !== ""
        ? p.image
        : "https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80",
    category: category,
    is_featured: Boolean(p.is_featured ?? false),
    sizes: sizes,
    prices: prices,
  }
}

interface CatalogGridProps {
  featuredOnly?: boolean
  limit?: number
  hideHeader?: boolean
}

export default function CatalogGrid({ featuredOnly = false, limit, hideHeader = false }: CatalogGridProps) {
  const [products, setProducts] = useState<Product[]>(
    (productsData as unknown as any[]).map(normalizeProduct)
  )
  const [activeTab, setActiveTab] = useState<"all" | "cake" | "addon">("all")
  const [searchTerm, setSearchTerm] = useState("")
  const { addToCart } = useCartStore()

  useEffect(() => {
    const fetchProducts = async () => {
      const endpoints = [
        "http://localhost:8000/api/products",
        "http://127.0.0.1:8000/api/products",
        "/api/products",
      ]

      for (const endpoint of endpoints) {
        try {
          const res = await fetch(endpoint)
          if (res.ok) {
            const data = await res.json()
            if (Array.isArray(data) && data.length > 0) {
              const normalized = data.map(normalizeProduct)
              setProducts(normalized)
              return
            }
          }
        } catch {
          // Continue trying remaining endpoints
        }
      }
    }

    fetchProducts()
  }, [])

  // Filter products based on featuredOnly, category tab, and search term
  let displayProducts = products.filter((product) => {
    if (featuredOnly && !product.is_featured) return false

    if (searchTerm.trim() !== "") {
      const query = searchTerm.toLowerCase()
      const matchesName = product.name.toLowerCase().includes(query)
      const matchesCat = product.category.toLowerCase().includes(query)
      if (!matchesName && !matchesCat) return false
    }

    if (activeTab === "all") return true
    const cat = (product.category || "").toLowerCase()
    if (activeTab === "cake") return cat.includes("cake")
    if (activeTab === "addon") return cat.includes("addon")
    return true
  })

  if (limit && limit > 0) {
    displayProducts = displayProducts.slice(0, limit)
  }

  const cakeCount = products.filter((p) =>
    (p.category || "").toLowerCase().includes("cake")
  ).length
  const addonCount = products.filter((p) =>
    (p.category || "").toLowerCase().includes("addon")
  ).length

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
    <section id="catalog" className="mx-auto max-w-7xl px-4 py-16 md:px-8">
      {/* Section Header */}
      {!hideHeader && (
        <div className="mb-12 text-center">
          <div className="text-xs font-black tracking-widest text-primary uppercase mb-2">
            {featuredOnly ? "FEATURED CREATIONS" : "COMPLETE MENU CATALOG"}
          </div>
          <h2 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary md:text-5xl">
            {featuredOnly ? "Handcrafted Celebration Favorites" : "Explore Our Cakes & Add-ons"}
          </h2>
          <div className="mx-auto mt-4 h-1 w-16 bg-primary rounded-full"></div>
          <p className="mt-5 font-sans text-sm font-semibold text-text-secondary max-w-md mx-auto leading-relaxed">
            Handcrafted fresh daily with 100% vegetarian, egg-free ingredients. Browse custom cakes & party accessories.
          </p>

          {!featuredOnly && (
            <div className="mt-8 mx-auto max-w-md">
              <input
                type="text"
                placeholder="Search cakes, flavors or accessories..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full px-5 py-3 text-sm font-semibold rounded-2xl border-2 border-border bg-white text-text-primary outline-none focus:border-primary transition-all shadow-xs"
              />
            </div>
          )}

          {/* E-Commerce Category Tabs (All, Cakes, Addons) */}
          {!featuredOnly && (
            <div className="mt-8 flex flex-wrap justify-center gap-3">
              <button
                onClick={() => setActiveTab("all")}
                className={`rounded-full px-6 py-2.5 font-sans text-sm font-extrabold transition-all duration-200 cursor-pointer hover:scale-102 ${
                  activeTab === "all"
                    ? "bg-primary text-white shadow-md shadow-primary/20"
                    : "bg-white border-2 border-border text-text-primary hover:border-primary/30"
                }`}
              >
                All Items ({products.length})
              </button>

              <button
                onClick={() => setActiveTab("cake")}
                className={`rounded-full px-6 py-2.5 font-sans text-sm font-extrabold transition-all duration-200 cursor-pointer hover:scale-102 ${
                  activeTab === "cake"
                    ? "bg-primary text-white shadow-md shadow-primary/20"
                    : "bg-white border-2 border-border text-text-primary hover:border-primary/30"
                }`}
              >
                Cakes ({cakeCount})
              </button>

              <button
                onClick={() => setActiveTab("addon")}
                className={`rounded-full px-6 py-2.5 font-sans text-sm font-extrabold transition-all duration-200 cursor-pointer hover:scale-102 ${
                  activeTab === "addon"
                    ? "bg-primary text-white shadow-md shadow-primary/20"
                    : "bg-white border-2 border-border text-text-primary hover:border-primary/30"
                }`}
              >
                Add-ons & Accessories ({addonCount})
              </button>
            </div>
          )}
        </div>
      )}

      {displayProducts.length === 0 ? (
        <div className="text-center font-sans text-text-secondary py-16">
          No products found matching your selection.
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-x-8 gap-y-16 sm:grid-cols-2 lg:grid-cols-3">
          {displayProducts.map((product) => (
            <ProductCard
              key={product.id}
              product={product}
              onAddToCart={handleAddToCart}
            />
          ))}
        </div>
      )}
    </section>
  )
}
