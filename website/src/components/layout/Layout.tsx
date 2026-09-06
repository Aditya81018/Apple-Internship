import { useState, useEffect } from "react"
import { Outlet } from "react-router-dom"
import TopBar from "./TopBar"
import Navbar from "./Navbar"
import Footer from "./Footer"
import CartDrawer from "../cart/CartDrawer"
import ScrollToTop from "./ScrollToTop"

export default function Layout() {
  const [isVisible, setIsVisible] = useState(true)
  const [lastScrollY, setLastScrollY] = useState(0)

  useEffect(() => {
    const handleScroll = () => {
      const currentScrollY = window.scrollY

      if (currentScrollY <= 10) {
        setIsVisible(true)
      } else if (currentScrollY > lastScrollY && currentScrollY > 60) {
        // Hide header when scrolling DOWN
        setIsVisible(false)
      } else if (currentScrollY < lastScrollY) {
        // Show header when scrolling UP
        setIsVisible(true)
      }

      setLastScrollY(currentScrollY)
    }

    window.addEventListener("scroll", handleScroll, { passive: true })
    return () => window.removeEventListener("scroll", handleScroll)
  }, [lastScrollY])

  return (
    <div className="flex min-h-screen flex-col bg-background text-foreground scrollbar-gutter-stable">
      <ScrollToTop />

      {/* Auto-hiding Glassmorphic Fixed Header */}
      <div
        className={`fixed top-0 left-0 right-0 z-40 w-full backdrop-blur-md bg-card/90 border-b-2 border-border/60 shadow-xs transition-transform duration-300 ease-in-out ${
          isVisible ? "translate-y-0" : "-translate-y-full"
        }`}
      >
        <TopBar />
        <Navbar />
      </div>

      {/* Header Spacer to prevent layout overlap */}
      <div className="h-[105px] md:h-[74px]" />

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
