import { Link } from "react-router-dom"
import { Button } from "@/components/ui/button"
import CatalogGrid from "@/components/catalog/CatalogGrid"

export default function Home() {
  const handleOrderNowClick = () => {
    const catalogSection = document.getElementById("catalog")
    if (catalogSection) {
      catalogSection.scrollIntoView({ behavior: "smooth" })
    }
  }

  return (
    <div className="w-full">
      {/* Premium Split Hero Section */}
      <section className="relative w-full bg-gradient-to-br from-[#FFFDF9] via-[#FFFDF9] to-[#FFF3F5] overflow-hidden">
        {/* Subtle background decorative shapes */}
        <div className="absolute top-1/2 left-[-100px] h-[300px] w-[300px] -translate-y-1/2 rounded-full bg-secondary/10 blur-3xl -z-10" />
        <div className="absolute top-[-50px] right-[-50px] h-[250px] w-[250px] rounded-full bg-accent/30 blur-3xl -z-10" />

        <div className="mx-auto max-w-7xl px-4 py-16 md:py-24 md:px-8">
          <div className="grid grid-cols-1 gap-12 items-center md:grid-cols-12">

            {/* Left Content Column */}
            <div className="flex flex-col items-start text-left md:col-span-7">
              <div className="inline-flex items-center gap-2 rounded-full bg-accent/60 px-4 py-1.5 text-xs font-bold tracking-wider text-primary mb-6">
                <span className="h-2 w-2 rounded-full bg-primary animate-pulse" />
                PREMIUM EGGLESS CONFECTIONERY
              </div>

              <h1 className="font-heading text-4xl font-black tracking-tight text-text-primary sm:text-5xl md:text-6xl leading-[1.1] mb-6">
                Deliciously Eggless.<br />
                <span className="text-primary">Wonderfully Fresh.</span>
              </h1>

              <p className="font-sans text-base md:text-lg font-bold text-text-secondary leading-relaxed max-w-xl mb-10">
                Handcrafted artisan cakes, custom-designed celebration bakes, and fresh dairy-free creams made fresh daily. Zero preservatives, 100% happiness.
              </p>

              <div className="flex flex-wrap items-center gap-4 w-full sm:w-auto">
                <Button
                  onClick={handleOrderNowClick}
                  size="lg"
                  className="bg-primary hover:bg-primary/95 text-white text-sm font-extrabold px-8 py-6 rounded-2xl transition-all duration-300 shadow-md hover:shadow-primary/20 hover:scale-105 active:scale-95 hover:-rotate-1 w-full sm:w-auto"
                >
                  Explore Our Catalog
                </Button>
                <Link to="/custom" className="w-full sm:w-auto">
                  <Button
                    variant="outline"
                    size="lg"
                    className="border-2 border-primary text-primary hover:bg-primary/5 bg-transparent text-sm font-extrabold px-8 py-6 rounded-2xl transition-all duration-300 hover:scale-105 active:scale-95 w-full sm:w-auto"
                  >
                    Design Custom Cake
                  </Button>
                </Link>
              </div>
            </div>

            {/* Right Image Column with Offset frame */}
            <div className="relative w-full flex justify-center md:col-span-5">
              {/* Offset decorative box */}
              <div className="absolute -bottom-4 -right-4 h-full w-full max-w-[340px] md:max-w-[380px] rounded-[36px] bg-secondary/20 -z-10" />

              {/* Image frame */}
              <div className="relative aspect-[4/5] w-full max-w-[340px] md:max-w-[380px] overflow-hidden rounded-[36px] border-4 border-white shadow-xl hover:rotate-1 transition-transform duration-500 bg-white">
                <img
                  src="https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?auto=format&fit=crop&w=800&q=80"
                  alt="Premium Strawberries & Cream Cake"
                  className="h-full w-full object-cover"
                />
              </div>
            </div>

          </div>
        </div>
      </section>

      {/* Catalog Grid Section */}
      <CatalogGrid />
    </div>
  )
}
