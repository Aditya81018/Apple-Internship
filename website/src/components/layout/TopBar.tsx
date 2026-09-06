import { useState, useEffect } from "react"

export default function TopBar() {
  const [topbarText, setTopbarText] = useState("100% EGGLESS | GLUTEN-FREE | LACTOSE-FREE CREAMS")
  const [isAcceptingOrders, setIsAcceptingOrders] = useState(true)

  useEffect(() => {
    const fetchSettings = async () => {
      const endpoints = [
        "http://localhost:8000/api/settings",
        "http://127.0.0.1:8000/api/settings",
        "/api/settings",
      ]

      for (const endpoint of endpoints) {
        try {
          const res = await fetch(endpoint)
          if (res.ok) {
            const data = await res.json()
            if (data.topbar_text) {
              setTopbarText(data.topbar_text)
            }
            if (data.accepting_orders !== undefined) {
              setIsAcceptingOrders(String(data.accepting_orders) === "1")
            }
            return
          }
        } catch {
          // Continue fallback
        }
      }
    }

    fetchSettings()
  }, [])

  return (
    <div className="sticky top-0 z-50 w-full bg-accent px-4 py-2 text-center text-xs font-bold tracking-wider text-primary shadow-xs flex flex-wrap items-center justify-center gap-3">
      {/* Live Order Acceptance Status Pill */}
      <div className="inline-flex items-center gap-1.5 rounded-full bg-white/80 px-2.5 py-0.5 text-[10px] font-extrabold uppercase shadow-2xs">
        <span
          className={`h-2 w-2 rounded-full ${
            isAcceptingOrders ? "bg-emerald-500 animate-pulse" : "bg-amber-500"
          }`}
        />
        <span className={isAcceptingOrders ? "text-emerald-700" : "text-amber-700"}>
          {isAcceptingOrders ? "ACCEPTING ORDERS" : "ORDERS PAUSED"}
        </span>
      </div>

      <span className="truncate">{topbarText}</span>
    </div>
  )
}
