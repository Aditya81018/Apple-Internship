import { Link, useLocation } from "react-router-dom"
import { ShoppingBag } from "lucide-react"
import { useCartStore } from "@/store/useCartStore"

export default function Navbar() {
  const location = useLocation()
  const { cart, toggleDrawer } = useCartStore()

  const cartItemCount = cart.reduce((total, item) => total + item.quantity, 0)

  const handleCartClick = () => {
    toggleDrawer(true)
  }

  return (
    <header className="w-full bg-transparent">
      <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 md:px-8">
        {/* Logo and Brand Sub-title */}
        <Link to="/" className="flex flex-col items-start transition-transform hover:scale-[1.02]">
          <span className="font-heading text-2xl font-black tracking-tight text-primary leading-tight">
            Raj Confections
          </span>
          <span className="font-sans text-[9px] font-extrabold tracking-widest text-text-secondary uppercase -mt-0.5">
            Happiness Homemade
          </span>
        </Link>

        {/* Center Navigation Links */}
        <nav className="hidden items-center gap-8 md:flex">
          <Link
            to="/"
            className={`relative py-1.5 font-sans text-sm font-bold transition-all hover:text-primary ${
              location.pathname === "/" ? "text-primary" : "text-text-primary"
            }`}
          >
            Home
            {location.pathname === "/" && (
              <span className="absolute bottom-0 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-primary" />
            )}
          </Link>

          <Link
            to="/catalog"
            className={`relative py-1.5 font-sans text-sm font-bold transition-all hover:text-primary ${
              location.pathname === "/catalog" ? "text-primary" : "text-text-primary"
            }`}
          >
            Menu Catalog
            {location.pathname === "/catalog" && (
              <span className="absolute bottom-0 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-primary" />
            )}
          </Link>

          <Link
            to="/custom"
            className={`relative py-1.5 font-sans text-sm font-bold transition-all hover:text-primary ${
              location.pathname === "/custom" ? "text-primary" : "text-text-primary"
            }`}
          >
            Custom Order
            {location.pathname === "/custom" && (
              <span className="absolute bottom-0 left-1/2 h-1.5 w-1.5 -translate-x-1/2 rounded-full bg-primary" />
            )}
          </Link>
        </nav>

        {/* Cart Trigger */}
        <button
          onClick={handleCartClick}
          className="relative rounded-full bg-accent/60 p-2.5 text-primary transition-all hover:scale-105 hover:bg-accent active:scale-95 shadow-2xs cursor-pointer"
          aria-label="Open cart"
        >
          <ShoppingBag className="h-5.5 w-5.5" />
          {cartItemCount > 0 && (
            <span className="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white shadow-sm animate-bounce">
              {cartItemCount}
            </span>
          )}
        </button>
      </div>
    </header>
  )
}
