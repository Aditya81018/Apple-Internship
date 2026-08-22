import CatalogGrid from "@/components/catalog/CatalogGrid"
import HeroCarousel from "@/components/home/HeroCarousel"

export default function Home() {
  return (
    <div className="w-full">
      <HeroCarousel />
      <CatalogGrid />
    </div>
  )
}
