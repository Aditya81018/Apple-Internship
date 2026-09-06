import CatalogGrid from "@/components/catalog/CatalogGrid"
import { Leaf, Award, ShieldCheck } from "lucide-react"

export default function Catalog() {
  return (
    <div className="w-full bg-[#FFFDF9]">
      {/* Catalog Hero Banner */}
      <section className="relative w-full bg-gradient-to-b from-accent/30 via-accent/10 to-transparent py-12 md:py-16">
        <div className="mx-auto max-w-7xl px-4 md:px-8 text-center">
          <div className="inline-flex items-center gap-2 rounded-full bg-white px-4 py-1.5 text-xs font-extrabold tracking-wider text-primary shadow-xs border border-border/60 mb-4">
            <Leaf className="h-3.5 w-3.5 text-primary shrink-0" />
            100% EGGLESS & PURE VEGETARIAN
          </div>
          <h1 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary sm:text-5xl">
            Our Complete Confectionery Menu
          </h1>
          <p className="mt-4 font-sans text-base font-semibold text-text-secondary max-w-2xl mx-auto leading-relaxed">
            From signature celebration cakes to party poppers and custom candles, browse our complete selection made fresh daily in our local kitchen.
          </p>

          <div className="mt-8 flex flex-wrap justify-center gap-6 text-xs font-bold text-text-secondary">
            <span className="flex items-center gap-1.5">
              <Award className="h-4 w-4 text-primary" /> Premium Organic Cream
            </span>
            <span className="flex items-center gap-1.5">
              <ShieldCheck className="h-4 w-4 text-primary" /> Zero Preservatives
            </span>
            <span className="flex items-center gap-1.5">
              <Leaf className="h-4 w-4 text-primary" /> Freshly Baked Daily
            </span>
          </div>
        </div>
      </section>

      {/* Main Catalog Grid */}
      <CatalogGrid hideHeader={false} />
    </div>
  )
}
