import React, { useState, useEffect, useCallback } from "react"
import { Link } from "react-router-dom"

const images = [
  "/orange_cake.jpeg",
  "/custard_vanilla_cake.jpeg",
  "/yanderi_cake.jpeg",
  "/cake_2.jpeg",
  "/cake_3.jpeg",
  "/cake_1.jpeg",
]

export default function HeroCarousel() {
  const [currentIndex, setCurrentIndex] = useState(0)

  const goToNext = useCallback(() => {
    setCurrentIndex((prevIndex) => (prevIndex + 1) % images.length)
  }, [])

  useEffect(() => {
    const timer = setInterval(goToNext, 5000)
    return () => clearInterval(timer)
  }, [goToNext])

  const handleStandardCakesClick = (
    e: React.MouseEvent<HTMLAnchorElement>
  ) => {
    e.preventDefault()

    const catalogSection = document.getElementById("catalog")

    if (catalogSection) {
      catalogSection.scrollIntoView({ behavior: "smooth" })
    }
  }

  return (
    <div className="relative w-full h-[100vh] min-h-[600px] overflow-hidden bg-black">
      {/* Images Carousel */}
      {images.map((src, index) => {
        const isActive = index === currentIndex

        return (
          <div
            key={src}
            className={`absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out ${isActive ? "opacity-100 z-10" : "opacity-0 z-0"
              }`}
          >
            <img
              src={src}
              alt={`Premium Cake ${index + 1}`}
              className={`w-full h-full object-cover transition-transform duration-[10000ms] ease-linear origin-center ${isActive ? "scale-[1.02]" : "scale-100"
                }`}
            />
          </div>
        )
      })}

      {/* Subtle text-safe gradient */}
      <div
        className="absolute inset-y-0 left-0 z-15 w-[68%] pointer-events-none"
        style={{
          background:
            "linear-gradient(90deg, rgba(253,248,243,0.28) 0%, rgba(253,248,243,0.14) 42%, rgba(253,248,243,0) 100%)",
        }}
      />

      {/* Hero Content */}
      <div className="absolute inset-y-0 left-0 z-20 flex items-center px-6 sm:px-10 md:px-16 lg:px-24 pointer-events-none">
        <div className="flex flex-col items-start max-w-[650px]">

          {/* Brand */}
          <h1
            className="uppercase font-normal tracking-[0.15em] text-4xl sm:text-5xl md:text-6xl lg:text-7xl"
            style={{
              fontFamily: 'Georgia, "Times New Roman", serif',
              color: "#0d3161",
            }}
          >
            RAJ'S CONFECTIONS
          </h1>

          {/* Subtitle */}
          <p
            className="mt-1 text-lg sm:text-xl md:text-2xl lg:text-3xl font-normal tracking-wide"
            style={{
              fontFamily: 'Georgia, "Times New Roman", serif',
              color: "#0d3161",
            }}
          >
            Freshly Baked Homemade Cakes
          </p>

          {/* Navigation */}
          <div
            className="mt-4 flex items-center gap-5 sm:gap-6 text-sm sm:text-base md:text-lg font-normal tracking-wide pointer-events-auto"
            style={{
              fontFamily: 'Georgia, "Times New Roman", serif',
              color: "#0d3161",
            }}
          >
            <a
              href="#catalog"
              onClick={handleStandardCakesClick}
              className="underline underline-offset-4 decoration-[#0d3161]/70 hover:opacity-60 transition-opacity cursor-pointer"
            >
              Standard Cakes
            </a>

            <span className="opacity-50">|</span>

            <Link
              to="/custom"
              className="underline underline-offset-4 decoration-[#0d3161]/70 hover:opacity-60 transition-opacity cursor-pointer"
            >
              Customized Cakes
            </Link>
          </div>

        </div>
      </div>
    </div>
  )
}