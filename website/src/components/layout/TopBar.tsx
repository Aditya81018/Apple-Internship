import { useLocation } from "react-router-dom"
import { ShoppingBag } from "lucide-react"
import { useCartStore } from "@/store/useCartStore"

export default function TopBar() {
  const location = useLocation()
  const isHome = location.pathname === "/"

  const { cart, toggleDrawer } = useCartStore()
  const cartItemCount = cart.reduce((total, item) => total + item.quantity, 0)

  const handleCartClick = () => {
    toggleDrawer(true)
  }

  return (
    <div
      className={`z-50 w-full py-2.5 px-4 flex items-center justify-center text-xs font-bold tracking-widest ${isHome
          ? "fixed top-0 left-0 bg-white/20 backdrop-blur-md text-white border-b border-white/10"
          : "sticky top-0 bg-accent text-primary shadow-xs"
        }`}
    >
      <span className="text-center w-full max-w-[70%] sm:max-w-none">
        100% EGGLESS | GLUTEN-FREE | LACTOSE-FREE CREAMS
      </span>

      {isHome && (
        <div className="absolute right-4 top-1/2 -translate-y-1/2">
          <button
            onClick={handleCartClick}
            className="relative flex items-center justify-center rounded-full p-2 bg-white/20 text-white backdrop-blur-md transition-all hover:scale-105 hover:bg-white/30 active:scale-95 shadow-sm"
            aria-label="Open cart"
          >
            <ShoppingBag className="h-5 w-5" />

            {cartItemCount > 0 && (
              <span className="absolute -top-1 -right-1 flex h-4 w-4 sm:h-5 sm:w-5 items-center justify-center rounded-full bg-white text-black text-[9px] sm:text-[10px] font-bold shadow-sm animate-bounce">
                {cartItemCount}
              </span>
            )}
          </button>
        </div>
      )}
    </div>
  )
}