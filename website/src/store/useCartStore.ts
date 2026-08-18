import { create } from "zustand"
import { persist } from "zustand/middleware"

export interface CartItem {
  id: string // product id + size (for variants) or unique custom id
  productId: string
  name: string
  price: number
  size: string
  quantity: number
  image: string
  isCustom?: boolean
  customDetails?: {
    flavor: string
    instructions?: string
    imagePreview?: string
    uploadedImageUrl?: string
  }
}

interface CartStore {
  cart: CartItem[]
  isDrawerOpen: boolean
  addToCart: (item: Omit<CartItem, "quantity">) => void
  removeFromCart: (id: string) => void
  updateQuantity: (id: string, quantity: number) => void
  toggleDrawer: (open?: boolean) => void
  getCartTotal: () => number
  clearCart: () => void
}

export const useCartStore = create<CartStore>()(
  persist(
    (set, get) => ({
      cart: [],
      isDrawerOpen: false,
      addToCart: (item) => {
        set((state) => {
          // Standard items: if same product ID and same size, increment quantity
          const existingItemIndex = state.cart.findIndex(
            (i) =>
              i.productId === item.productId &&
              i.size === item.size &&
              !i.isCustom &&
              !item.isCustom
          )

          if (existingItemIndex > -1) {
            const newCart = [...state.cart]
            newCart[existingItemIndex].quantity += 1
            return { cart: newCart, isDrawerOpen: true }
          }

          // New items or custom items: add to list with qty 1
          const newItem: CartItem = {
            ...item,
            quantity: 1,
          }
          return { cart: [...state.cart, newItem], isDrawerOpen: true }
        })
      },
      removeFromCart: (id) => {
        set((state) => ({
          cart: state.cart.filter((item) => item.id !== id),
        }))
      },
      updateQuantity: (id, quantity) => {
        set((state) => ({
          cart: state.cart.map((item) =>
            item.id === id ? { ...item, quantity: Math.max(1, quantity) } : item
          ),
        }))
      },
      toggleDrawer: (open) => {
        set((state) => ({
          isDrawerOpen: open !== undefined ? open : !state.isDrawerOpen,
        }))
      },
      getCartTotal: () => {
        return get().cart.reduce((total, item) => {
          if (item.isCustom) return total // Custom items: To be quoted (0 price)
          return total + item.price * item.quantity
        }, 0)
      },
      clearCart: () => set({ cart: [] }),
    }),
    {
      name: "raj-confections-cart-storage", // name of local storage item
    }
  )
)
