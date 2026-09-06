import CatalogGrid from "@/components/catalog/CatalogGrid"

export default function Catalog() {
  return (
    <div className="w-full bg-[#FFFDF9]">
      {/* Main Catalog Grid */}
      <CatalogGrid hideHeader={false} />
    </div>
  )
}
