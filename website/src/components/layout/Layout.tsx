import { Outlet, useLocation } from "react-router-dom"
import TopBar from "./TopBar"
import Navbar from "./Navbar"
import Footer from "./Footer"
import CartDrawer from "../cart/CartDrawer"

export default function Layout() {
  const location = useLocation()
  const isHome = location.pathname === "/"

  return (
    <div className="flex min-h-screen flex-col bg-background text-foreground scrollbar-gutter-stable">
      {/* Header Area */}
      {!isHome ? (
        <div className="sticky top-0 z-40 w-full backdrop-blur-md bg-card/90 border-b-2 border-border/60 shadow-xs">
          <TopBar />
          <Navbar />
        </div>
      ) : (
        <TopBar />
      )}

      {/* Main Content Area */}
      <main className="flex-1">
        <Outlet />
      </main>

      {/* Footer Details */}
      <Footer />

      {/* Global Sliding Cart Drawer */}
      <CartDrawer />
    </div>
  )
}
