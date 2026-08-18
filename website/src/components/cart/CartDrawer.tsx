import { Link } from "react-router-dom"
import { useCartStore } from "@/store/useCartStore"
import productsData from "@/data/products.json"
import type { Product } from "@/components/catalog/ProductCard"
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
} from "@/components/ui/sheet"
import { Button } from "@/components/ui/button"
import { Plus, Minus, Trash2, Sparkles, ShoppingBag } from "lucide-react"

export default function CartDrawer() {
  const {
    cart,
    isDrawerOpen,
    toggleDrawer,
    updateQuantity,
    removeFromCart,
    addToCart,
    getCartTotal,
  } = useCartStore()

  // Extract addons from products.json for the upsell section
  const addons: Product[] = (productsData as unknown as Product[]).filter(
    (product) => product.category === "addon"
  )

  const handleAddonClick = (addon: Product) => {
    addToCart({
      id: addon.id,
      productId: addon.id,
      name: addon.name,
      price: addon.price,
      size: "default",
      image: addon.image,
    })
  }

  return (
    <Sheet open={isDrawerOpen} onOpenChange={toggleDrawer}>
      <SheetContent className="w-full flex flex-col h-full sm:max-w-md p-0 bg-card border-l-2 border-border overflow-hidden">
        {/* Header */}
        <SheetHeader className="p-6 border-b border-border/80 flex flex-row items-center justify-between">
          <SheetTitle className="font-heading text-2xl font-black text-text-primary flex items-center gap-2">
            Shopping Cart
          </SheetTitle>
        </SheetHeader>

        {/* Scrollable Content */}
        <div className="flex-1 overflow-y-auto p-6 flex flex-col gap-6 scrollbar-gutter-stable">
          {cart.length === 0 ? (
            /* Empty State */
            <div className="flex flex-col items-center justify-center text-center my-auto py-12">
              <div className="rounded-full bg-accent/40 p-6 text-primary mb-6 animate-pulse">
                <ShoppingBag className="h-10 w-10" />
              </div>
              <h3 className="font-heading text-xl font-bold text-text-primary">
                Your cart is empty
              </h3>
              <p className="mt-2.5 font-sans text-xs text-text-secondary max-w-xs leading-relaxed">
                Add some of our delicious signature cakes or design a custom cake to start your order!
              </p>
              <Button
                onClick={() => toggleDrawer(false)}
                className="mt-8 rounded-xl font-extrabold text-xs"
              >
                Browse Signature Cakes
              </Button>
            </div>
          ) : (
            /* Cart Items List */
            <div className="flex flex-col gap-4">
              {cart.map((item) => (
                <div
                  key={item.id}
                  className="flex gap-4 border-2 border-border/60 rounded-2xl p-4 bg-white shadow-2xs hover:border-primary/20 transition-all"
                >
                  {/* Item Image */}
                  <div className="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-accent/15 border border-border">
                    <img
                      src={item.image}
                      alt={item.name}
                      className="h-full w-full object-cover"
                    />
                  </div>

                  {/* Item Details */}
                  <div className="flex-1 flex flex-col justify-between min-w-0">
                    <div>
                      <h4 className="font-heading text-base font-bold text-text-primary truncate">
                        {item.name}
                      </h4>
                      {item.isCustom ? (
                        <div className="mt-1 font-sans text-[10px] text-text-secondary leading-tight flex flex-col gap-0.5">
                          <span className="font-extrabold text-primary uppercase">Custom Order</span>
                          <span>Flavor: {item.customDetails?.flavor}</span>
                          <span>Weight: {item.size}</span>
                        </div>
                      ) : (
                        <span className="inline-block mt-0.5 font-sans text-xs font-bold text-text-secondary">
                          Size: {item.size}
                        </span>
                      )}
                    </div>

                    <div className="flex items-center justify-between mt-2">
                      {/* Quantity Controls */}
                      <div className="flex items-center border border-border rounded-lg bg-background p-0.5">
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity - 1)}
                          className="p-1 text-text-secondary hover:text-primary transition-colors cursor-pointer"
                        >
                          <Minus className="h-3.5 w-3.5" />
                        </button>
                        <span className="px-2.5 font-sans text-xs font-extrabold text-text-primary">
                          {item.quantity}
                        </span>
                        <button
                          onClick={() => updateQuantity(item.id, item.quantity + 1)}
                          className="p-1 text-text-secondary hover:text-primary transition-colors cursor-pointer"
                        >
                          <Plus className="h-3.5 w-3.5" />
                        </button>
                      </div>

                      {/* Price / Quote Info */}
                      <span className="font-sans text-sm font-black text-text-primary">
                        {item.isCustom ? "To be quoted" : `₹${item.price * item.quantity}`}
                      </span>
                    </div>
                  </div>

                  {/* Remove Button */}
                  <button
                    onClick={() => removeFromCart(item.id)}
                    className="text-text-secondary hover:text-destructive transition-colors self-start p-1 cursor-pointer"
                    aria-label="Remove item"
                  >
                    <Trash2 className="h-4.5 w-4.5" />
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Footer (If cart has items) */}
        {cart.length > 0 && (
          <div className="border-t-2 border-border/80 bg-accent/15 p-6 flex flex-col gap-6">
            {/* Upsell Scroller */}
            <div className="flex flex-col gap-2">
              <span className="font-sans text-xs font-black tracking-wider text-text-primary flex items-center gap-1.5 uppercase">
                <Sparkles className="h-3.5 w-3.5 text-primary" />
                Add Party Essentials
              </span>
              <div className="flex gap-3 overflow-x-auto pb-2 scrollbar-none snap-x">
                {addons.map((addon) => (
                  <div
                    key={addon.id}
                    className="flex shrink-0 w-44 gap-3 items-center border border-border/80 bg-card rounded-xl p-2.5 snap-start hover:border-primary/20 transition-all shadow-3xs"
                  >
                    <div className="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-accent/20">
                      <img
                        src={addon.image}
                        alt={addon.name}
                        className="h-full w-full object-cover"
                      />
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-sans text-[11px] font-bold text-text-primary truncate leading-tight">
                        {addon.name}
                      </p>
                      <p className="font-sans text-[11px] font-black text-primary leading-tight mt-0.5">
                        ₹{addon.price}
                      </p>
                    </div>
                    <button
                      onClick={() => handleAddonClick(addon)}
                      className="rounded-lg bg-primary hover:bg-primary/95 text-white p-1 cursor-pointer hover:scale-105 active:scale-95 transition-transform"
                      aria-label={`Add ${addon.name} to cart`}
                    >
                      <Plus className="h-3.5 w-3.5" />
                    </button>
                  </div>
                ))}
              </div>
            </div>

            {/* Price Calculations & checkout CTA */}
            <div className="border-t border-border/40 pt-4 flex flex-col gap-4">
              <div className="flex items-center justify-between">
                <span className="font-sans text-base font-bold text-text-primary">Subtotal</span>
                <span className="font-sans text-xl font-black text-text-primary">
                  ₹{getCartTotal()}
                </span>
              </div>
              <p className="font-sans text-[10px] text-text-secondary leading-relaxed -mt-2">
                * Shipping fees calculated at checkout. Custom cake orders will be manually priced by the baker on WhatsApp.
              </p>
              
              <Link to="/checkout" className="w-full">
                <Button
                  onClick={() => toggleDrawer(false)}
                  className="w-full py-6 rounded-2xl font-extrabold text-sm shadow-md hover:shadow-primary/25 cursor-pointer"
                >
                  Proceed to Checkout
                </Button>
              </Link>
            </div>
          </div>
        )}
      </SheetContent>
    </Sheet>
  )
}
