import type { CartItem } from "@/store/useCartStore"

interface CheckoutFormData {
  name: string
  notes?: string
  fulfillment: "pickup" | "delivery"
  address: string
  date: string
  time: string
}

export function generateWhatsAppURL(cart: CartItem[], formData: CheckoutFormData): string {
  const ownerNumber = import.meta.env.VITE_WHATSAPP_NUMBER || "919875652246"

  // 1. Compile Items List
  const itemsText = cart
    .map((item) => {
      if (item.isCustom) {
        const imageLink = item.customDetails?.uploadedImageUrl
          ? `\n  • Reference Image: ${item.customDetails.uploadedImageUrl}`
          : ""
        return `- 1x Custom Cake Request (${item.customDetails?.flavor}, Size: ${item.size}) - *To be quoted*${imageLink}`
      }
      return `- ${item.quantity}x ${item.name} (${item.size === "default" ? "Standard" : item.size}) - *₹${item.price * item.quantity}*`
    })
    .join("\n")

  // 2. Calculate Subtotal (Ignoring custom cakes)
  const subtotal = cart.reduce((total, item) => {
    if (item.isCustom) return total
    return total + item.price * item.quantity
  }, 0)

  // 3. Format Date nicely
  let formattedDate = formData.date
  try {
    const d = new Date(formData.date)
    formattedDate = d.toLocaleDateString("en-IN", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    })
  } catch (e) {
    console.error("Date formatting error:", e)
  }

  // 4. Construct the WhatsApp Message
  const message = [
    "*NEW ORDER REQUEST - RAJ CONFECTIONS*",
    "",
    "*Items:*",
    itemsText,
    "",
    `*Total (Excluding Custom):* ₹${subtotal}`,
    "",
    "*Fulfillment details:*",
    `- Method: ${formData.fulfillment === "delivery" ? "Delivery" : "Store Pickup"}`,
    `- Date: ${formattedDate}`,
    `- Time: ${formData.time}`,
    formData.fulfillment === "delivery" ? `- Address: ${formData.address}` : "",
    "",
    "*Customer Details:*",
    `- Name: ${formData.name}`,
    formData.notes ? `- Notes / Allergy Instructions: ${formData.notes}` : "",
    "",
    "---------------------------------",
    "*Please Note:*",
    "• Orders are manually confirmed via chat.",
    "• A 30-40% advance payment is required for large/custom orders.",
  ]
    .filter((line) => line !== "") // filter out empty strings like address if pickup
    .join("\n")

  // 5. Return the URL-encoded string
  return `https://wa.me/${ownerNumber}?text=${encodeURIComponent(message)}`
}
