import { useState } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { useCartStore } from "@/store/useCartStore"
import { Button } from "@/components/ui/button"
import { Images, Sparkles, X, Image as ImageIcon } from "lucide-react"

// Custom Cake validation schema
const customCakeSchema = z.object({
  flavor: z.string().min(1, { message: "Please select a base flavor" }),
  instructions: z.string().min(10, { message: "Please describe your custom request in detail (min 10 characters)" }),
})

type CustomCakeFormValues = z.infer<typeof customCakeSchema>

const FLAVORS = [
  "Chocolate Truffle",
  "Vanilla Sponge",
  "Strawberry Cream",
  "White Forest",
  "Red Velvet",
  "Rosomalai Edition",
  "Mango Fruit Edition",
  "Other (Specify in Notes)",
]

const WEIGHTS = ["1 lb", "2 lb", "3 lb", "5 lb", "10 lb+"]

const INSPIRATION_IMAGES = [
  {
    src: "/image1.jpeg",
    alt: "Inspirational Berry Cake",
  },
  {
    src: "/image2.jpeg",
    alt: "Inspirational Cream Cake",
  },
  {
    src: "/image3.jpeg",
    alt: "Inspirational Strawberries",
  },
  {
    src: "/image4.jpeg",
    alt: "Inspirational Chocolate",
  },
  {
    src: "/image5.jpeg",
    alt: "Decorated Celebration Cake",
  },
  {
    src: "/image6.jpeg",
    alt: "Freshly Baked Cake",
  },
]

export default function CustomCake() {
  const { addToCart } = useCartStore()
  const [selectedWeight, setSelectedWeight] = useState("2.0 lb")
  const [imagePreview, setImagePreview] = useState<string | null>(null)
  const [isGalleryOpen, setIsGalleryOpen] = useState(false)
  
  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<CustomCakeFormValues>({
    resolver: zodResolver(customCakeSchema),
    defaultValues: {
      flavor: "Chocolate Truffle",
      instructions: "",
    },
  })

  // Handle local image file selection and canvas-based compression
  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      const reader = new FileReader()
      reader.onload = (event) => {
        const img = new Image()
        img.onload = () => {
          const canvas = document.createElement("canvas")
          const max_width = 800
          const max_height = 800
          let width = img.width
          let height = img.height

          if (width > height) {
            if (width > max_width) {
              height = Math.round((height * max_width) / width)
              width = max_width
            }
          } else {
            if (height > max_height) {
              width = Math.round((width * max_height) / height)
              height = max_height
            }
          }

          canvas.width = width
          canvas.height = height
          const ctx = canvas.getContext("2d")
          if (ctx) {
            ctx.drawImage(img, 0, 0, width, height)
            // Compress to JPEG with 0.7 quality to keep size under ~100KB
            const compressedBase64 = canvas.toDataURL("image/jpeg", 0.7)
            setImagePreview(compressedBase64)
          } else {
            setImagePreview(event.target?.result as string)
          }
        }
        img.src = event.target?.result as string
      }
      reader.readAsDataURL(file)
    }
  }

  const onSubmit = (data: CustomCakeFormValues) => {
    // Generate custom cart item
    addToCart({
      id: `custom-${Date.now()}`,
      productId: "custom-cake",
      name: "Custom Cake Request",
      price: 0,
      size: selectedWeight,
      image: imagePreview || "https://images.unsplash.com/photo-1588195538326-c5b1e9f80a1b?auto=format&fit=crop&w=800&q=80",
      isCustom: true,
      customDetails: {
        flavor: data.flavor,
        instructions: data.instructions,
        imagePreview: imagePreview || undefined,
      },
    })
    
    // Reset form and local states
    reset()
    setImagePreview(null)
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-12 md:py-20 md:px-8">
      {/* Page Header */}
      <div className="mb-12 text-center md:mb-16">
        <span className="inline-flex items-center gap-1.5 rounded-full bg-[#FFDF33] px-4 py-1.5 text-xs font-bold tracking-wider text-primary mb-3">
          <Sparkles className="h-3.5 w-3.5" />
          ARTISAN BAKERY SERVICE
        </span>
        <h1 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary md:text-5xl">
          Custom Cake Builder
        </h1>
        <div className="mx-auto mt-4 h-1.5 w-16 bg-primary rounded-full"></div>
        <p className="mt-5 font-sans text-sm font-semibold text-text-secondary max-w-md mx-auto leading-relaxed">
          Design your dream eggless creation. Let us know your favorite flavors, colors, or theme, and we'll bake it to perfection.
        </p>
      </div>

      <div className="grid grid-cols-1 gap-12 lg:grid-cols-12 items-start">
        {/* Left Column: Inspiration Gallery */}
        <div className="lg:col-span-5 flex flex-col gap-6">
          <h3 className="font-heading text-xl font-bold text-text-primary">
            Get Inspired by Our Creations
          </h3>
          <div className="grid grid-cols-2 gap-4">
            {INSPIRATION_IMAGES.slice(0, 4).map((image) => (
              <div key={image.src} className="aspect-square overflow-hidden rounded-[24px] border-2 border-border/60 hover:scale-[1.03] transition-all duration-300 shadow-2xs">
                <img src={image.src} alt={image.alt} className="h-full w-full object-cover" />
              </div>
            ))}
          </div>
          <Button
            type="button"
            variant="outline"
            onClick={() => setIsGalleryOpen(true)}
            className="w-full rounded-2xl border-2 border-primary py-5 font-extrabold text-primary hover:bg-primary hover:text-primary-foreground"
          >
            <Images />
            View More
          </Button>
          <div className="bg-accent/20 border-2 border-border rounded-3xl p-5 text-xs text-text-secondary leading-relaxed font-sans font-semibold">
            * Every customized cake is made to order with organic, fresh ingredients. Prices are calculated based on size, custom elements, and materials used. You will receive a final payment request on WhatsApp.
          </div>

          {isGalleryOpen && (
            <div
              className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
              role="dialog"
              aria-modal="true"
              aria-labelledby="inspiration-gallery-title"
              onClick={() => setIsGalleryOpen(false)}
            >
              <div
                className="relative max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-3xl border-2 border-border bg-black p-5 md:p-8"
                onClick={(event) => event.stopPropagation()}
              >
                <div className="mb-5 flex items-center justify-between gap-4">
                  <h2 id="inspiration-gallery-title" className="font-heading text-2xl font-bold text-text-primary">
                    Cake Inspiration Gallery
                  </h2>
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    aria-label="Close gallery"
                    onClick={() => setIsGalleryOpen(false)}
                  >
                    <X />
                  </Button>
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                  {INSPIRATION_IMAGES.map((image) => (
                    <img
                      key={image.src}
                      src={image.src}
                      alt={image.alt}
                      className="aspect-square w-full rounded-2xl object-cover"
                    />
                  ))}
                </div>
              </div>
            </div>
          )}
        </div>

        {/* Right Column: Custom Form */}
        <div className="lg:col-span-7 bg-[#123456] border-2 border-border rounded-[32px] p-6 md:p-8 shadow-sm">
          <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-6">
            
            {/* Base Flavor */}
            <div className="flex flex-col gap-2">
              <label htmlFor="flavor" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                1. Base Flavor Choice
              </label>
              <select
                id="flavor"
                {...register("flavor")}
                className="w-full h-11 px-4 font-sans text-sm font-semibold rounded-2xl bg-background border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all cursor-pointer"
              >
                {FLAVORS.map((flavor) => (
                  <option key={flavor} value={flavor}>
                    {flavor}
                  </option>
                ))}
              </select>
              {errors.flavor && (
                <span className="font-sans text-xs text-destructive font-bold">{errors.flavor.message}</span>
              )}
            </div>

            {/* Weight Option Chips */}
            <div className="flex flex-col gap-2.5">
              <span className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                2. Weight / Size Selection
              </span>
              <div className="flex flex-wrap gap-2.5">
                {WEIGHTS.map((weight) => {
                  const isActive = selectedWeight === weight
                  return (
                    <button
                      key={weight}
                      type="button"
                      onClick={() => setSelectedWeight(weight)}
                      className={`rounded-2xl px-5 py-2 font-sans text-xs font-extrabold border transition-all duration-200 cursor-pointer ${
                        isActive
                          ? "bg-primary text-black border-primary shadow-xs"
                          : "bg-[#E6D0FF] border-border text-black hover:border-primary/45"
                      }`}
                    >
                      {weight}
                    </button>
                  )
                })}
              </div>
            </div>

            {/* Detailed Instructions */}
            <div className="flex flex-col gap-2">
              <label htmlFor="instructions" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                3. Design & Customization Details
              </label>
              <textarea
                id="instructions"
                placeholder="Describe your cake in detail. Specify colors, messages, decorations, or other custom requests..."
                rows={5}
                {...register("instructions")}
                className="w-full p-4 font-sans text-sm font-medium rounded-2xl bg-[#FAF6E8] border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all resize-y min-h-[120px] placeholder:text-black"
              />
              {errors.instructions && (
                <span className="font-sans text-xs text-destructive font-bold">{errors.instructions.message}</span>
              )}
            </div>

            {/* Reference Image Upload */}
            <div className="flex flex-col gap-3">
              <span className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                4. Upload Reference Image (Optional)
              </span>
              <div className="flex items-center gap-4">
                <label
                  htmlFor="ref-image"
                  className="inline-flex items-center gap-2 rounded-2xl border-2 border-dashed border-border/90 hover:border-primary/50 bg-background px-5 py-4 cursor-pointer text-xs font-bold text-text-secondary transition-all hover:scale-101 hover:bg-accent/10"
                >
                  <ImageIcon className="h-5 w-5 text-primary" />
                  Select Image File
                </label>
                <input
                  id="ref-image"
                  type="file"
                  accept="image/*"
                  onChange={handleImageChange}
                  className="hidden"
                />

                {imagePreview && (
                  <div className="relative h-14 w-14 overflow-hidden rounded-xl border border-border">
                    <img src={imagePreview} alt="Reference Preview" className="h-full w-full object-cover" />
                  </div>
                )}
              </div>


            </div>

            {/* Submit Button */}
            <Button
              type="submit"
              className="mt-4 py-6 rounded-2xl font-extrabold text-sm shadow-md hover:shadow-primary/25 cursor-pointer"
            >
              Add Custom Request to Cart
            </Button>

          </form>
        </div>
      </div>
    </div>
  )
}
