import { useState, useEffect } from "react"
import { Link } from "react-router-dom"
import { Button } from "@/components/ui/button"
import CatalogGrid from "@/components/catalog/CatalogGrid"
import { Sparkles, Leaf, Clock, Award, Truck, Star, ChevronRight, Palette } from "lucide-react"

export default function Home() {
  const [heroImg, setHeroImg] = useState("https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?auto=format&fit=crop&w=800&q=80")
  const [customCakeImg, setCustomCakeImg] = useState("https://images.unsplash.com/photo-1535141192574-5d4897c13636?auto=format&fit=crop&w=600&q=80")

  useEffect(() => {
    const fetchSettings = async () => {
      const endpoints = [
        "http://localhost:8000/api/settings",
        "http://127.0.0.1:8000/api/settings",
        "/api/settings",
      ]

      for (const endpoint of endpoints) {
        try {
          const res = await fetch(endpoint)
          if (res.ok) {
            const data = await res.json()
            if (data.hero_image_url && data.hero_image_url.trim() !== "") {
              setHeroImg(data.hero_image_url)
            }
            if (data.custom_cake_teaser_image_url && data.custom_cake_teaser_image_url.trim() !== "") {
              setCustomCakeImg(data.custom_cake_teaser_image_url)
            }
            return
          }
        } catch {
          // Continue fallback
        }
      }
    }

    fetchSettings()
  }, [])

  const handleFeaturedScroll = () => {
    const featuredSection = document.getElementById("featured-cakes")
    if (featuredSection) {
      featuredSection.scrollIntoView({ behavior: "smooth" })
    }
  }

  return (
    <div className="w-full bg-[#FFFDF9]">
      {/* 1. Hero Section */}
      <section className="relative w-full bg-gradient-to-br from-[#FFFDF9] via-[#FFFDF9] to-[#FFF3F5] overflow-hidden">
        {/* Decorative background blurs */}
        <div className="absolute top-1/2 left-[-100px] h-[300px] w-[300px] -translate-y-1/2 rounded-full bg-secondary/10 blur-3xl -z-10" />
        <div className="absolute top-[-50px] right-[-50px] h-[250px] w-[250px] rounded-full bg-accent/30 blur-3xl -z-10" />

        <div className="mx-auto max-w-7xl px-4 py-16 md:py-24 md:px-8">
          <div className="grid grid-cols-1 gap-12 items-center md:grid-cols-12">

            {/* Left Content Column */}
            <div className="flex flex-col items-start text-left md:col-span-7">
              <div className="inline-flex items-center gap-2 rounded-full bg-accent/60 px-4 py-1.5 text-xs font-bold tracking-wider text-primary mb-6 shadow-2xs">
                <Sparkles className="h-3.5 w-3.5 text-primary shrink-0" />
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
                  onClick={handleFeaturedScroll}
                  size="lg"
                  className="bg-primary hover:bg-primary/95 text-white text-sm font-extrabold px-8 py-6 rounded-2xl transition-all duration-300 shadow-md hover:shadow-primary/20 hover:scale-105 active:scale-95 w-full sm:w-auto"
                >
                  Explore Featured Cakes
                </Button>
                <Link to="/catalog" className="w-full sm:w-auto">
                  <Button
                    variant="outline"
                    size="lg"
                    className="border-2 border-primary text-primary hover:bg-primary/5 bg-transparent text-sm font-extrabold px-8 py-6 rounded-2xl transition-all duration-300 hover:scale-105 active:scale-95 w-full sm:w-auto"
                  >
                    View Full Catalog
                  </Button>
                </Link>
              </div>
            </div>

            {/* Right Image Column with Offset frame */}
            <div className="relative w-full flex justify-center md:col-span-5">
              <div className="absolute -bottom-4 -right-4 h-full w-full max-w-[340px] md:max-w-[380px] rounded-[36px] bg-secondary/20 -z-10" />

              <div className="relative aspect-[4/5] w-full max-w-[340px] md:max-w-[380px] overflow-hidden rounded-[36px] border-4 border-white shadow-xl hover:rotate-1 transition-transform duration-500 bg-white">
                <img
                  src={heroImg}
                  alt="Signature Eggless Cake"
                  className="h-full w-full object-cover"
                />
              </div>
            </div>

          </div>
        </div>
      </section>

      {/* 2. Featured Cakes Section (Shows ONLY Featured Cakes) */}
      <section id="featured-cakes" className="mx-auto max-w-7xl px-4 py-20 md:px-8">
        <div className="mb-12 text-center">
          <div className="inline-flex items-center gap-1.5 text-xs font-black tracking-widest text-primary uppercase mb-2">
            <Star className="h-4 w-4 fill-primary text-primary" />
            OUR SPECIALTY CREATIONS
          </div>
          <h2 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary md:text-5xl">
            Featured Celebration Cakes
          </h2>
          <div className="mx-auto mt-4 h-1 w-16 bg-primary rounded-full"></div>
          <p className="mt-4 font-sans text-sm font-semibold text-text-secondary max-w-md mx-auto leading-relaxed">
            Handpicked signature cakes loved by hundreds of families across town.
          </p>
        </div>

        {/* Featured Cakes Only Grid */}
        <CatalogGrid featuredOnly={true} limit={6} hideHeader={true} />

        {/* Call-to-action button leading to the full Catalog Page */}
        <div className="mt-12 text-center">
          <Link to="/catalog">
            <Button
              size="lg"
              className="bg-primary hover:bg-primary/95 text-white text-sm font-extrabold px-10 py-6 rounded-2xl transition-all duration-300 shadow-md hover:shadow-primary/20 hover:scale-105 active:scale-95 inline-flex items-center gap-2"
            >
              Browse Complete Catalog & Add-ons
              <ChevronRight className="h-4 w-4" />
            </Button>
          </Link>
        </div>
      </section>

      {/* 3. Why Choose Us / Value Proposition Highlights */}
      <section className="w-full bg-accent/20 py-20 border-y border-border/50">
        <div className="mx-auto max-w-7xl px-4 md:px-8">
          <div className="text-center mb-14">
            <span className="text-xs font-black tracking-widest text-primary uppercase mb-2 block">
              THE RAJ CONFECTIONS DIFFERENCE
            </span>
            <h2 className="font-heading text-3xl font-extrabold text-text-primary md:text-4xl">
              Why Customers Love Our Bakes
            </h2>
            <div className="mx-auto mt-3 h-1 w-16 bg-primary rounded-full" />
          </div>

          <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
            {/* Feature 1 */}
            <div className="flex flex-col items-center text-center p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
              <div className="h-14 w-14 rounded-2xl bg-accent/80 text-primary flex items-center justify-center mb-5 shadow-xs">
                <Leaf className="h-7 w-7" />
              </div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-2">100% Eggless Guaranteed</h3>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
                Strictly pure vegetarian bakery with zero cross-contamination. Delicious taste without any eggs.
              </p>
            </div>

            {/* Feature 2 */}
            <div className="flex flex-col items-center text-center p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
              <div className="h-14 w-14 rounded-2xl bg-accent/80 text-primary flex items-center justify-center mb-5 shadow-xs">
                <Clock className="h-7 w-7" />
              </div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Baked Fresh Daily</h3>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
                We bake every cake fresh on the morning of your scheduled delivery for peak softness and flavor.
              </p>
            </div>

            {/* Feature 3 */}
            <div className="flex flex-col items-center text-center p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
              <div className="h-14 w-14 rounded-2xl bg-accent/80 text-primary flex items-center justify-center mb-5 shadow-xs">
                <Award className="h-7 w-7" />
              </div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Premium Ingredients</h3>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
                Crafted with organic cocoa, pure dairy cream, and real fruits with zero artificial preservatives.
              </p>
            </div>

            {/* Feature 4 */}
            <div className="flex flex-col items-center text-center p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs hover:shadow-md hover:-translate-y-1 transition-all duration-300">
              <div className="h-14 w-14 rounded-2xl bg-accent/80 text-primary flex items-center justify-center mb-5 shadow-xs">
                <Truck className="h-7 w-7" />
              </div>
              <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Express Doorstep Delivery</h3>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
                Temperature-controlled delivery right to your door so your celebration cake arrives in perfect condition.
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* 4. Custom Cake Teaser Section */}
      <section className="mx-auto max-w-7xl px-4 py-20 md:px-8">
        <div className="relative overflow-hidden rounded-[36px] bg-gradient-to-r from-primary via-primary/95 to-secondary p-8 md:p-14 text-white shadow-xl">
          <div className="grid grid-cols-1 gap-8 items-center lg:grid-cols-12">
            <div className="lg:col-span-8 flex flex-col items-start">
              <span className="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-4 py-1.5 text-xs font-extrabold text-white mb-4 backdrop-blur-xs">
                <Palette className="h-3.5 w-3.5" />
                BESPOKE BAKERY EXPERIENCE
              </span>
              <h2 className="font-heading text-3xl md:text-5xl font-black tracking-tight text-white mb-4 leading-tight">
                Have a Specific Cake Design in Mind?
              </h2>
              <p className="font-sans text-sm md:text-base font-semibold text-white/90 max-w-xl mb-8 leading-relaxed">
                Upload your reference photos, select your base flavor, and describe your custom theme. Our master bakers will bring your dream cake to life!
              </p>
              <div className="flex flex-wrap items-center gap-4">
                <Link to="/custom">
                  <Button
                    size="lg"
                    className="bg-white text-primary hover:bg-white/90 text-sm font-extrabold px-8 py-6 rounded-2xl transition-all shadow-lg hover:scale-105 active:scale-95"
                  >
                    Start Custom Order Builder
                  </Button>
                </Link>
                <Link to="/gallery">
                  <Button
                    size="lg"
                    variant="outline"
                    className="border-2 border-white/60 bg-white/10 text-white hover:bg-white hover:text-primary text-sm font-extrabold px-8 py-6 rounded-2xl transition-all backdrop-blur-xs hover:scale-105 active:scale-95"
                  >
                    Explore Creations Gallery
                  </Button>
                </Link>
              </div>
            </div>

            <div className="lg:col-span-4 flex justify-center">
              <div className="relative aspect-square w-full max-w-[280px] rounded-3xl overflow-hidden border-4 border-white/30 shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-300">
                <img
                  src={customCakeImg}
                  alt="Custom Cake Design Preview"
                  className="h-full w-full object-cover"
                />
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 5. How It Works / 3-Step Ordering Process */}
      <section className="mx-auto max-w-7xl px-4 py-16 md:px-8">
        <div className="text-center mb-14">
          <span className="text-xs font-black tracking-widest text-primary uppercase mb-2 block">
            EASY ORDERING
          </span>
          <h2 className="font-heading text-3xl font-extrabold text-text-primary md:text-4xl">
            How to Order Your Fresh Cake
          </h2>
          <div className="mx-auto mt-3 h-1 w-16 bg-primary rounded-full" />
        </div>

        <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
          <div className="flex flex-col items-center text-center p-6 rounded-3xl border border-border/60 bg-white">
            <div className="h-12 w-12 rounded-full bg-primary text-white font-black text-lg flex items-center justify-center mb-4">
              1
            </div>
            <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Choose or Design</h3>
            <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
              Select a favorite from our catalog or fill out our custom cake builder form with your specifications.
            </p>
          </div>

          <div className="flex flex-col items-center text-center p-6 rounded-3xl border border-border/60 bg-white">
            <div className="h-12 w-12 rounded-full bg-primary text-white font-black text-lg flex items-center justify-center mb-4">
              2
            </div>
            <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Freshly Baked</h3>
            <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
              Our bakers prepare your order fresh using 100% eggless ingredients and organic creams.
            </p>
          </div>

          <div className="flex flex-col items-center text-center p-6 rounded-3xl border border-border/60 bg-white">
            <div className="h-12 w-12 rounded-full bg-primary text-white font-black text-lg flex items-center justify-center mb-4">
              3
            </div>
            <h3 className="font-heading text-lg font-bold text-text-primary mb-2">Enjoy Doorstep Delivery</h3>
            <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed">
              Receive your cake safely at your doorstep or pick up at our local outlet for your celebration.
            </p>
          </div>
        </div>
      </section>

      {/* 6. Customer Reviews & Ratings */}
      <section className="w-full bg-gradient-to-b from-transparent to-accent/20 py-20 border-t border-border/40">
        <div className="mx-auto max-w-7xl px-4 md:px-8">
          <div className="text-center mb-14">
            <div className="inline-flex items-center gap-1 text-amber-500 mb-2">
              <Star className="h-4 w-4 fill-amber-400" />
              <Star className="h-4 w-4 fill-amber-400" />
              <Star className="h-4 w-4 fill-amber-400" />
              <Star className="h-4 w-4 fill-amber-400" />
              <Star className="h-4 w-4 fill-amber-400" />
            </div>
            <h2 className="font-heading text-3xl font-extrabold text-text-primary md:text-4xl">
              Loved by Happy Customers
            </h2>
            <p className="font-sans text-xs font-bold text-text-secondary mt-2">
              4.9/5 Rating based on 350+ celebration cake orders
            </p>
          </div>

          <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
            <div className="p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs">
              <div className="flex items-center gap-1 text-amber-400 mb-3">
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
              </div>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed mb-4">
                "The Black Forest cake was insanely soft and delicious! It was hard to believe it was completely eggless. Everyone at the party asked where we got it."
              </p>
              <div className="flex items-center gap-3">
                <div className="h-9 w-9 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center">
                  PS
                </div>
                <div>
                  <div className="font-sans text-xs font-extrabold text-text-primary">Priya Sharma</div>
                  <div className="font-sans text-[10px] text-text-secondary font-bold">Birthday Celebration</div>
                </div>
              </div>
            </div>

            <div className="p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs">
              <div className="flex items-center gap-1 text-amber-400 mb-3">
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
              </div>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed mb-4">
                "Used their custom builder for a two-tier Rosomalai cake. The output was identical to the picture I uploaded and tasted divine!"
              </p>
              <div className="flex items-center gap-3">
                <div className="h-9 w-9 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center">
                  AP
                </div>
                <div>
                  <div className="font-sans text-xs font-extrabold text-text-primary">Ananya Patel</div>
                  <div className="font-sans text-[10px] text-text-secondary font-bold">Anniversary Event</div>
                </div>
              </div>
            </div>

            <div className="p-6 bg-white rounded-3xl border-2 border-border/60 shadow-2xs">
              <div className="flex items-center gap-1 text-amber-400 mb-3">
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
                <Star className="h-4 w-4 fill-amber-400" />
              </div>
              <p className="font-sans text-xs font-semibold text-text-secondary leading-relaxed mb-4">
                "Super quick delivery and immaculate packaging. The Chocolate Overload cake is hands down the best eggless cake in town."
              </p>
              <div className="flex items-center gap-3">
                <div className="h-9 w-9 rounded-full bg-primary/10 text-primary font-bold text-xs flex items-center justify-center">
                  RM
                </div>
                <div>
                  <div className="font-sans text-xs font-extrabold text-text-primary">Rahul Mehta</div>
                  <div className="font-sans text-[10px] text-text-secondary font-bold">Family Gathering</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
