import { useState } from "react"
import { Link } from "react-router-dom"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import * as z from "zod"
import { useCartStore } from "@/store/useCartStore"
import { generateWhatsAppURL } from "@/utils/whatsapp"
import { Button } from "@/components/ui/button"
import {
  Sparkles,
  Info,
  MapPin,
  Store,
  Calendar,
  Clock,
  CheckCircle,
  XCircle,
  Loader2,
  ShoppingBag,
} from "lucide-react"

// Form validation schema
const checkoutSchema = z.object({
  name: z.string().min(1, { message: "Please enter your name" }),
  notes: z.string().optional(),
  fulfillment: z.enum(["pickup", "delivery"]),
  address: z.string().optional(),
  date: z.string().min(1, { message: "Please select a date" }),
  time: z.string().min(1, { message: "Please select a time" }),
}).refine((data) => {
  if (data.fulfillment === "delivery" && (!data.address || data.address.trim().length < 5)) {
    return false
  }
  return true
}, {
  message: "Please enter a valid delivery address (min 5 characters)",
  path: ["address"],
})

type CheckoutFormValues = z.infer<typeof checkoutSchema>

const TIME_SLOTS = [
  "10:00 AM",
  "11:00 AM",
  "12:00 PM",
  "01:00 PM",
  "02:00 PM",
  "03:00 PM",
  "04:00 PM",
  "05:00 PM",
  "06:00 PM",
  "07:00 PM",
  "08:00 PM",
]

export default function Checkout() {
  const { cart, getCartTotal, clearCart } = useCartStore()
  const [fulfillmentType, setFulfillmentType] = useState<"pickup" | "delivery">("pickup")
  const [address, setAddress] = useState("")
  const [verificationState, setVerificationState] = useState<"idle" | "verifying" | "valid" | "invalid">("idle")
  const [isRedirecting, setIsRedirecting] = useState(false)

  // Enforce 3 days advance notice for orders
  const minDate = new Date()
  minDate.setDate(minDate.getDate() + 3)
  const minDateString = minDate.toISOString().split("T")[0]

  const {
    register,
    handleSubmit,
    setValue,
    formState: { errors },
  } = useForm<CheckoutFormValues>({
    resolver: zodResolver(checkoutSchema),
    defaultValues: {
      fulfillment: "pickup",
      date: "",
      time: "12:00 PM",
      name: "",
      notes: "",
      address: "",
    },
  })

  // Handle fulfillment toggle changes
  const handleFulfillmentChange = (type: "pickup" | "delivery") => {
    setFulfillmentType(type)
    setValue("fulfillment", type)
    if (type === "pickup") {
      setVerificationState("idle")
      setValue("address", "")
      setAddress("")
    }
  }

  // Location validation mock
  const handleVerifyLocation = () => {
    if (address.trim().length < 5) {
      setVerificationState("invalid")
      return
    }

    setVerificationState("verifying")
    
    // Simulate API delay
    setTimeout(() => {
      const lowerAddress = address.toLowerCase()
      if (lowerAddress.includes("far") || lowerAddress.includes("out of bounds")) {
        setVerificationState("invalid")
      } else {
        setVerificationState("valid")
      }
    }, 800)
  }

  // Helper to upload base64 images client-side
  const uploadBase64Image = async (base64String: string): Promise<string | null> => {
    try {
      const parts = base64String.split(";base64,")
      if (parts.length !== 2) return null
      const contentType = parts[0].split(":")[1]
      const raw = window.atob(parts[1])
      const rawLength = raw.length
      const uInt8Array = new Uint8Array(rawLength)
      for (let i = 0; i < rawLength; ++i) {
        uInt8Array[i] = raw.charCodeAt(i)
      }
      const blob = new Blob([uInt8Array], { type: contentType })
      const file = new File([blob], "custom_cake_ref.png", { type: contentType })

      const formData = new FormData()
      formData.append("file", file)

      const response = await fetch("https://tmpfiles.org/api/v1/upload", {
        method: "POST",
        body: formData,
      })

      if (!response.ok) {
        throw new Error(`Upload failed: ${response.statusText}`)
      }

      const result = await response.json()
      if (result && result.status === "success" && result.data?.url) {
        // Convert to direct download url for mobile previews
        return result.data.url.replace("tmpfiles.org/", "tmpfiles.org/dl/")
      }
      return null
    } catch (error) {
      console.error("CORS/Network error uploading reference image:", error)
      return null
    }
  }

  const onSubmit = async (data: CheckoutFormValues) => {
    // If delivery is selected but not verified as valid, force verification
    if (data.fulfillment === "delivery" && verificationState !== "valid") {
      handleVerifyLocation()
      return
    }

    setIsRedirecting(true)

    // Upload custom cake reference images in parallel
    const updatedCart = await Promise.all(
      cart.map(async (item) => {
        if (
          item.isCustom &&
          item.customDetails?.imagePreview &&
          !item.customDetails.uploadedImageUrl
        ) {
          const uploadedUrl = await uploadBase64Image(item.customDetails.imagePreview)
          if (uploadedUrl) {
            return {
              ...item,
              customDetails: {
                ...item.customDetails,
                uploadedImageUrl: uploadedUrl,
              },
            }
          }
        }
        return item
      })
    )

    // Form data with current address state
    const checkoutData = {
      ...data,
      address: data.fulfillment === "delivery" ? address : "",
    }

    // Generate payload and redirect with uploaded URLs
    const url = generateWhatsAppURL(updatedCart, checkoutData)
    
    setTimeout(() => {
      clearCart() // Empty cart locally on checkout redirect
      window.location.href = url
    }, 1500)
  }

  // If cart is empty, show empty state
  if (cart.length === 0 && !isRedirecting) {
    return (
      <div className="mx-auto max-w-xl px-4 py-20 text-center">
        <div className="rounded-full bg-accent/40 p-6 text-primary mb-6 mx-auto w-fit animate-pulse">
          <ShoppingBag className="h-10 w-10" />
        </div>
        <h1 className="font-heading text-3xl font-extrabold text-text-primary">
          Your Cart is Empty
        </h1>
        <p className="mt-4 font-sans text-sm text-text-secondary leading-relaxed">
          You cannot checkout because there are no items in your cart. Choose from our delicious cakes first!
        </p>
        <div className="mt-8">
          <Link to="/">
            <Button className="rounded-2xl font-extrabold text-xs px-8 py-5">
              Browse Cakes Menu
            </Button>
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-12 md:py-20 md:px-8">
      {/* Header */}
      <div className="mb-12 text-center md:mb-16">
        <span className="inline-flex items-center gap-1.5 rounded-full bg-accent/60 px-4 py-1.5 text-xs font-bold tracking-wider text-primary mb-3">
          <Sparkles className="h-3.5 w-3.5" />
          SECURE CHECKOUT
        </span>
        <h1 className="font-heading text-4xl font-extrabold tracking-tight text-text-primary md:text-5xl">
          Order Checkout
        </h1>
        <div className="mx-auto mt-4 h-1.5 w-16 bg-primary rounded-full"></div>
      </div>

      {isRedirecting ? (
        /* Redirect Overlay state */
        <div className="flex flex-col items-center justify-center text-center py-20 min-h-[300px]">
          <Loader2 className="h-12 w-12 text-primary animate-spin mb-6" />
          <h2 className="font-heading text-2xl font-bold text-text-primary">
            Redirecting to WhatsApp...
          </h2>
          <p className="mt-2.5 font-sans text-sm text-text-secondary max-w-xs leading-relaxed">
            We are compiling your order summary. Please send the pre-filled chat text when WhatsApp opens.
          </p>
        </div>
      ) : (
        /* Regular checkout flow layout */
        <div className="grid grid-cols-1 gap-12 lg:grid-cols-12 items-start">
          
          {/* Left Column: Form Details */}
          <div className="lg:col-span-7 bg-white border-2 border-border rounded-[32px] p-6 md:p-8 shadow-sm">
            <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-8">
              
              {/* Step 1: Customer Details */}
              <div className="flex flex-col gap-4">
                <h3 className="font-heading text-lg font-bold text-text-primary border-b border-border/40 pb-2">
                  1. Contact Details & Notes
                </h3>
                
                {/* Name */}
                <div className="flex flex-col gap-2">
                  <label htmlFor="name" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                    Full Name *
                  </label>
                  <input
                    id="name"
                    type="text"
                    placeholder="Enter your full name"
                    autoComplete="name"
                    {...register("name")}
                    className="w-full h-11 px-4 font-sans text-sm font-semibold rounded-2xl bg-background border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all"
                  />
                  {errors.name && (
                    <span className="font-sans text-xs text-destructive font-bold">{errors.name.message}</span>
                  )}
                </div>

                {/* Notes */}
                <div className="flex flex-col gap-2">
                  <label htmlFor="notes" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                    Name on Cake / Allergy Notes (Optional)
                  </label>
                  <textarea
                    id="notes"
                    placeholder="E.g., Write 'Happy Birthday Mia' on the cake. No nuts please."
                    rows={3}
                    {...register("notes")}
                    className="w-full p-4 font-sans text-sm font-medium rounded-2xl bg-background border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all resize-y min-h-[80px]"
                  />
                </div>
              </div>

              {/* Step 2: Fulfillment */}
              <div className="flex flex-col gap-4">
                <h3 className="font-heading text-lg font-bold text-text-primary border-b border-border/40 pb-2">
                  2. Fulfillment Method
                </h3>
                
                {/* Fulfillment Type Toggle */}
                <div className="grid grid-cols-2 gap-4">
                  <button
                    type="button"
                    onClick={() => handleFulfillmentChange("pickup")}
                    className={`flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer ${
                      fulfillmentType === "pickup"
                        ? "border-primary bg-accent/25 text-primary shadow-xs"
                        : "border-border bg-white text-text-primary hover:border-primary/35"
                    }`}
                  >
                    <Store className="h-6 w-6 mb-2" />
                    <span className="font-sans text-sm font-extrabold">Store Pickup</span>
                    <span className="font-sans text-[10px] text-text-secondary mt-0.5">Free</span>
                  </button>
                  <button
                    type="button"
                    onClick={() => handleFulfillmentChange("delivery")}
                    className={`flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all duration-200 cursor-pointer ${
                      fulfillmentType === "delivery"
                        ? "border-primary bg-accent/25 text-primary shadow-xs"
                        : "border-border bg-white text-text-primary hover:border-primary/35"
                    }`}
                  >
                    <MapPin className="h-6 w-6 mb-2" />
                    <span className="font-sans text-sm font-extrabold">Home Delivery</span>
                    <span className="font-sans text-[10px] text-text-secondary mt-0.5">Flat ₹50 Fee</span>
                  </button>
                </div>

                {/* Fulfillment Details Display */}
                {fulfillmentType === "pickup" ? (
                  /* Store Pickup Info */
                  <div className="rounded-2xl bg-accent/20 border border-border p-5 text-xs font-sans font-semibold text-text-secondary flex flex-col gap-2 leading-relaxed">
                    <span className="font-bold text-text-primary text-sm block mb-1">🏪 Shop Pickup Address</span>
                    <p>Raj Confections, Kolkata, West Bengal, India</p>
                    <p>Operational Hours: <strong className="text-text-primary">10:00 AM - 8:00 PM</strong> (Mon - Sun)</p>
                    <p>Contact No: <strong className="text-text-primary">+91 94774 89551</strong></p>
                  </div>
                ) : (
                  /* Home Delivery Info & geocode mock */
                  <div className="flex flex-col gap-3">
                    <div className="flex flex-col gap-2">
                      <label htmlFor="address" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider">
                        Delivery Address *
                      </label>
                      <div className="flex gap-2">
                        <textarea
                          id="address"
                          placeholder="Enter your street address (within 10km radius from shop)..."
                          rows={2}
                          value={address}
                          onChange={(e) => {
                            setAddress(e.target.value)
                            setValue("address", e.target.value)
                          }}
                          className="flex-1 p-3 font-sans text-sm font-medium rounded-2xl bg-background border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all resize-none h-20"
                        />
                        <button
                          type="button"
                          onClick={handleVerifyLocation}
                          disabled={address.trim().length < 5 || verificationState === "verifying"}
                          className="h-20 shrink-0 px-4 rounded-2xl bg-primary/10 hover:bg-primary/25 text-primary text-xs font-bold border border-primary/20 transition-all hover:scale-101 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                          Verify Location
                        </button>
                      </div>
                      {errors.address && (
                        <span className="font-sans text-xs text-destructive font-bold">{errors.address.message}</span>
                      )}
                    </div>

                    {/* Geofence verification feedback messages */}
                    {verificationState === "verifying" && (
                      <div className="flex items-center gap-2 text-xs font-bold text-text-secondary leading-none py-1">
                        <Loader2 className="h-4.5 w-4.5 text-primary animate-spin" />
                        Calculating distance from shop coordinates...
                      </div>
                    )}
                    {verificationState === "valid" && (
                      <div className="flex items-center gap-2 text-xs font-extrabold text-[#2A9D8F] leading-none py-1 bg-[#EDF7F6] border border-[#C5E8E4] rounded-xl p-3">
                        <CheckCircle className="h-4.5 w-4.5 text-[#2A9D8F]" />
                        Address within 10km radius. Eligible for delivery!
                      </div>
                    )}
                    {verificationState === "invalid" && (
                      <div className="flex items-center gap-2 text-xs font-extrabold text-[#FF4B5C] leading-normal py-1 bg-[#FFF5F6] border border-[#FFD3DC] rounded-xl p-3">
                        <XCircle className="h-5 w-5 text-[#FF4B5C] shrink-0" />
                        <div>
                          <strong className="block mb-0.5">Delivery Blocked:</strong>
                          Address exceeds 10km radius. Please select Store Pickup or enter a closer delivery location.
                        </div>
                      </div>
                    )}
                  </div>
                )}
              </div>

              {/* Step 3: Date & Time */}
              <div className="flex flex-col gap-4">
                <h3 className="font-heading text-lg font-bold text-text-primary border-b border-border/40 pb-2">
                  3. Fulfillment Schedule
                </h3>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  {/* Date Input */}
                  <div className="flex flex-col gap-2">
                    <label htmlFor="date" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider flex items-center gap-1.5">
                      <Calendar className="h-4 w-4 text-primary" />
                      Fulfillment Date *
                    </label>
                    <input
                      id="date"
                      type="date"
                      min={minDateString}
                      {...register("date")}
                      className="w-full h-11 px-4 font-sans text-sm font-semibold rounded-2xl bg-background border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all cursor-pointer"
                    />
                    {errors.date && (
                      <span className="font-sans text-xs text-destructive font-bold">{errors.date.message}</span>
                    )}
                  </div>

                  {/* Time Input */}
                  <div className="flex flex-col gap-2">
                    <label htmlFor="time" className="font-sans text-xs font-bold text-text-primary uppercase tracking-wider flex items-center gap-1.5">
                      <Clock className="h-4 w-4 text-primary" />
                      Fulfillment Time *
                    </label>
                    <select
                      id="time"
                      {...register("time")}
                      className="w-full h-11 px-4 font-sans text-sm font-semibold rounded-2xl bg-background border-2 border-border hover:border-primary/30 outline-none focus:border-primary transition-all cursor-pointer"
                    >
                      {TIME_SLOTS.map((slot) => (
                        <option key={slot} value={slot}>
                          {slot}
                        </option>
                      ))}
                    </select>
                    {errors.time && (
                      <span className="font-sans text-xs text-destructive font-bold">{errors.time.message}</span>
                    )}
                  </div>
                </div>

                <div className="text-[11px] font-sans font-semibold text-text-secondary leading-normal mt-1 bg-accent/20 rounded-xl p-3 border border-border">
                  ℹ Orders must be placed at least <strong className="text-text-primary">3 days prior</strong> to allow for hygienic baking and preparation.
                </div>
              </div>

              {/* Step 4: Policy Disclaimer */}
              <div className="flex gap-3 bg-[#FFF5F6] border border-[#FFD3DC] rounded-2xl p-4 text-xs font-sans font-semibold text-text-secondary leading-relaxed">
                <Info className="h-5 w-5 text-primary shrink-0 mt-0.5" />
                <p>
                  <strong className="text-primary block mb-0.5">Store Order Policy:</strong>
                  Your order is sent to the shop owner's WhatsApp. Confirmation is done manually via message thread. A <strong className="text-text-primary">30-40% advance payment</strong> is required to confirm large standard or custom cake orders.
                </p>
              </div>

              {/* Submit Checkout Button */}
              <Button
                type="submit"
                disabled={fulfillmentType === "delivery" && verificationState !== "valid"}
                className="w-full py-6 rounded-2xl font-extrabold text-sm shadow-md hover:shadow-primary/25 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
              >
                Place Order via WhatsApp
              </Button>

            </form>
          </div>

          {/* Right Column: Order Summary */}
          <div className="lg:col-span-5 bg-card border-2 border-border rounded-[32px] p-6 shadow-sm flex flex-col gap-6">
            <h3 className="font-heading text-lg font-bold text-text-primary border-b border-border/40 pb-2 flex items-center gap-2">
              <ShoppingBag className="h-5 w-5 text-primary" />
              Order Summary
            </h3>

            {/* List items */}
            <div className="flex flex-col gap-4 max-h-[320px] overflow-y-auto pr-1">
              {cart.map((item) => (
                <div key={item.id} className="flex gap-3 items-center justify-between border-b border-border/30 pb-3">
                  <div className="flex gap-3 items-center min-w-0">
                    <div className="h-12 w-12 overflow-hidden rounded-lg bg-accent/20 shrink-0 border border-border">
                      <img src={item.image} alt={item.name} className="h-full w-full object-cover" />
                    </div>
                    <div className="min-w-0">
                      <p className="font-sans text-sm font-bold text-text-primary truncate">{item.name}</p>
                      <p className="font-sans text-[11px] font-semibold text-text-secondary leading-tight">
                        {item.isCustom ? `Custom: ${item.customDetails?.flavor}` : `Weight: ${item.size}`}
                      </p>
                    </div>
                  </div>
                  <span className="font-sans text-sm font-bold text-text-primary shrink-0">
                    {item.isCustom ? "To be quoted" : `₹${item.price * item.quantity}`}
                  </span>
                </div>
              ))}
            </div>

            {/* Price Calculations */}
            <div className="flex flex-col gap-3 border-t border-border/40 pt-4 font-sans text-sm font-semibold">
              <div className="flex items-center justify-between text-text-secondary">
                <span>Items Subtotal</span>
                <span>₹{getCartTotal()}</span>
              </div>
              <div className="flex items-center justify-between text-text-secondary">
                <span>Delivery Fee</span>
                <span>{fulfillmentType === "delivery" ? "₹50" : "Free"}</span>
              </div>

              <div className="flex items-center justify-between text-text-primary font-black text-lg border-t border-border/40 pt-4 mt-2">
                <span>Grand Total</span>
                <span>₹{getCartTotal() + (fulfillmentType === "delivery" ? 50 : 0)}</span>
              </div>
            </div>

            {/* Quote details warning */}
            {cart.some((item) => item.isCustom) && (
              <div className="text-[11px] font-sans font-semibold text-text-secondary leading-normal bg-accent/20 rounded-xl p-3 border border-border/80">
                ⭐ *Note:* Your cart contains a custom cake request. The total price above excludes this cake, which will be manually priced and added to your total in the WhatsApp thread.
              </div>
            )}
          </div>

        </div>
      )}
    </div>
  )
}
