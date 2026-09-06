import { useState, useEffect } from "react"
import { Link } from "react-router-dom"
import { Sparkles, Camera, ArrowRight, X, Star, Filter } from "lucide-react"

export interface GalleryItem {
  id: number
  title: string
  category: string
  image: string
  is_featured: number | boolean
  created_at?: string
}

const CATEGORIES = [
  "All Creations",
  "Celebration Cakes",
  "Wedding & Tier",
  "Pastry & Cupcakes",
  "Custom Creations",
]

const FALLBACK_GALLERY: GalleryItem[] = [
  {
    id: 1,
    title: "Inspirational Mango Glaze",
    category: "Celebration Cakes",
    image: "https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=800&q=80",
    is_featured: 1,
  },
  {
    id: 2,
    title: "Berry Vanilla Cupcake Tower",
    category: "Pastry & Cupcakes",
    image: "https://images.unsplash.com/photo-1587314168485-3236d6710814?auto=format&fit=crop&w=800&q=80",
    is_featured: 1,
  },
  {
    id: 3,
    title: "Dark Fudgy Brownie Stack",
    category: "Pastry & Cupcakes",
    image: "https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=800&q=80",
    is_featured: 1,
  },
  {
    id: 4,
    title: "Strawberry Delight Custom Cake",
    category: "Celebration Cakes",
    image: "https://images.unsplash.com/photo-1535141192574-5d4897c13636?auto=format&fit=crop&w=800&q=80",
    is_featured: 1,
  },
  {
    id: 5,
    title: "Three-Tier Floral Wedding Grace",
    category: "Wedding & Tier",
    image: "https://images.unsplash.com/photo-1519869325930-281384150729?auto=format&fit=crop&w=800&q=80",
    is_featured: 0,
  },
  {
    id: 6,
    title: "Pistachio Saffron Gold Cake",
    category: "Celebration Cakes",
    image: "https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80",
    is_featured: 0,
  },
]

export default function Gallery() {
  const [items, setItems] = useState<GalleryItem[]>(FALLBACK_GALLERY)
  const [selectedCategory, setSelectedCategory] = useState("All Creations")
  const [activeModalItem, setActiveModalItem] = useState<GalleryItem | null>(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetch("/api/gallery")
      .then((res) => res.json())
      .then((data) => {
        if (Array.isArray(data) && data.length > 0) {
          setItems(data)
        }
      })
      .catch(() => {
        // Fallback to static dataset if network fails
      })
      .finally(() => {
        setIsLoading(false)
      })
  }, [])

  const filteredItems =
    selectedCategory === "All Creations"
      ? items
      : items.filter(
          (item) => item.category.toLowerCase() === selectedCategory.toLowerCase()
        )

  return (
    <div className="mx-auto max-w-7xl px-4 py-12 md:py-20 md:px-8">
      {/* Hero Banner Header */}
      <div className="mb-12 text-center md:mb-16">
        <span className="inline-flex items-center gap-1.5 rounded-full bg-accent/60 px-4 py-1.5 text-xs font-bold tracking-wider text-primary mb-3">
          <Camera className="h-3.5 w-3.5" />
          ARTISAN CREATIONS GALLERY
        </span>
        <h1 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary md:text-5xl">
          Past Bakes & Inspired Cakes
        </h1>
        <div className="mx-auto mt-4 h-1.5 w-16 bg-primary rounded-full"></div>
        <p className="mt-5 font-sans text-sm font-semibold text-text-secondary max-w-lg mx-auto leading-relaxed">
          Explore a curated collection of customized cakes, tiered wedding masterpieces, and artisanal eggless creations crafted at Raj Confections.
        </p>
      </div>

      {/* Category Filter Tabs */}
      <div className="mb-10 flex flex-wrap items-center justify-center gap-2.5">
        <span className="hidden sm:inline-flex items-center gap-1 text-xs font-extrabold text-text-secondary mr-2 uppercase tracking-wider">
          <Filter className="h-3.5 w-3.5 text-primary" /> Filter:
        </span>
        {CATEGORIES.map((cat) => {
          const isActive = selectedCategory === cat
          return (
            <button
              key={cat}
              onClick={() => setSelectedCategory(cat)}
              className={`rounded-2xl px-5 py-2.5 font-sans text-xs font-extrabold transition-all duration-200 cursor-pointer ${
                isActive
                  ? "bg-primary text-white border-2 border-primary shadow-sm scale-[1.02]"
                  : "bg-white border-2 border-border text-text-primary hover:border-primary/40 hover:bg-accent/10"
              }`}
            >
              {cat}
            </button>
          )
        })}
      </div>

      {/* Gallery Grid */}
      {isLoading ? (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {[1, 2, 3, 4, 5, 6].map((idx) => (
            <div
              key={idx}
              className="h-72 w-full animate-pulse rounded-[28px] bg-border/40"
            />
          ))}
        </div>
      ) : filteredItems.length === 0 ? (
        <div className="rounded-3xl border-2 border-dashed border-border p-12 text-center font-sans">
          <Camera className="mx-auto h-12 w-12 text-text-secondary/40 mb-3" />
          <h3 className="text-lg font-bold text-text-primary">No creations found</h3>
          <p className="text-xs font-semibold text-text-secondary mt-1">
            Try switching to another category tab above.
          </p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {filteredItems.map((item) => (
            <div
              key={item.id}
              onClick={() => setActiveModalItem(item)}
              className="group relative cursor-pointer overflow-hidden rounded-[28px] border-2 border-border/80 bg-white shadow-xs transition-all duration-300 hover:-translate-y-1.5 hover:shadow-lg hover:border-primary/40"
            >
              {/* Image Container */}
              <div className="relative aspect-4/3 w-full overflow-hidden bg-accent/20">
                <img
                  src={item.image}
                  alt={item.title}
                  className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-108"
                />
                
                {/* Featured Badge */}
                {Boolean(item.is_featured) && (
                  <div className="absolute top-3.5 right-3.5 flex items-center gap-1 rounded-full bg-white/90 backdrop-blur-md px-3 py-1 text-[11px] font-extrabold text-amber-600 shadow-sm border border-amber-200">
                    <Star className="h-3 w-3 fill-amber-500 text-amber-500" />
                    Featured
                  </div>
                )}

                {/* Category Badge */}
                <div className="absolute bottom-3.5 left-3.5 rounded-xl bg-text-primary/80 backdrop-blur-md px-3 py-1 text-[11px] font-bold text-white shadow-xs">
                  {item.category}
                </div>
              </div>

              {/* Card Bottom Content */}
              <div className="flex items-center justify-between p-5">
                <h3 className="font-heading text-base font-bold text-text-primary group-hover:text-primary transition-colors">
                  {item.title}
                </h3>
                <span className="rounded-full bg-accent/50 p-2 text-primary group-hover:bg-primary group-hover:text-white transition-all">
                  <ArrowRight className="h-4 w-4" />
                </span>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Lightbox Modal */}
      {activeModalItem && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 animate-fadeIn">
          <div className="relative max-w-3xl w-full overflow-hidden rounded-[32px] bg-white border-2 border-border shadow-2xl">
            {/* Close Button */}
            <button
              onClick={() => setActiveModalItem(null)}
              className="absolute top-4 right-4 z-10 rounded-full bg-white/80 backdrop-blur-md p-2.5 text-text-primary hover:bg-white transition-all shadow-md cursor-pointer"
            >
              <X className="h-5 w-5" />
            </button>

            {/* Modal Image */}
            <div className="relative aspect-16/10 w-full bg-black/5">
              <img
                src={activeModalItem.image}
                alt={activeModalItem.title}
                className="h-full w-full object-cover"
              />
            </div>

            {/* Modal Body */}
            <div className="p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
              <div>
                <span className="inline-block rounded-lg bg-accent/60 px-3 py-1 text-xs font-bold text-primary mb-1.5">
                  {activeModalItem.category}
                </span>
                <h3 className="font-heading text-2xl font-extrabold text-text-primary">
                  {activeModalItem.title}
                </h3>
              </div>

              <Link
                to="/custom"
                onClick={() => setActiveModalItem(null)}
                className="inline-flex items-center gap-2 rounded-2xl bg-primary px-6 py-3.5 font-sans text-xs font-extrabold text-white shadow-md transition-all hover:bg-primary-hover hover:scale-[1.02]"
              >
                <span>Order Custom Cake Like This</span>
                <ArrowRight className="h-4 w-4" />
              </Link>
            </div>
          </div>
        </div>
      )}

      {/* Bottom CTA Card */}
      <div className="mt-16 rounded-[36px] bg-gradient-to-r from-accent/70 via-accent/40 to-background border-2 border-border/80 p-8 md:p-12 text-center shadow-sm">
        <Sparkles className="mx-auto h-8 w-8 text-primary mb-3" />
        <h2 className="font-heading text-2xl font-black text-text-primary md:text-3xl">
          Want a Custom Creation Made for Your Special Event?
        </h2>
        <p className="mt-3 font-sans text-xs font-semibold text-text-secondary max-w-md mx-auto">
          Send us your theme, flavor preference, and reference photos. We customize every detail for you.
        </p>
        <div className="mt-6">
          <Link
            to="/custom"
            className="inline-flex items-center gap-2.5 rounded-2xl bg-primary px-8 py-4 font-sans text-sm font-extrabold text-white shadow-md hover:bg-primary-hover transition-all hover:scale-[1.02]"
          >
            <span>Start Building Custom Cake</span>
            <ArrowRight className="h-4.5 w-4.5" />
          </Link>
        </div>
      </div>
    </div>
  )
}
