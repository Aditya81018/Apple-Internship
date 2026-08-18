import { Button } from "@/components/ui/button"
import { Link } from "react-router-dom"

export default function CustomCake() {
  return (
    <div className="mx-auto max-w-3xl px-4 py-16 text-center md:px-8">
      <h1 className="font-heading text-4xl font-bold text-text-primary md:text-5xl">
        Custom Cake Builder
      </h1>
      <p className="mt-4 font-sans text-lg text-text-secondary">
        This is where you'll design your dream cake with customized flavors, decorations, and weight.
      </p>
      <div className="mt-8 flex justify-center gap-4">
        <Link to="/">
          <Button variant="outline">Back to Catalog</Button>
        </Link>
      </div>
    </div>
  )
}
