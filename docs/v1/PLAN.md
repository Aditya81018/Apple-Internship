Here is the lean, rapid-execution implementation plan for the Raj Confections MVP.

Since getting a **presentable UI immediately** is the top priority, we will bypass complex logic initially. We will build the visual shell and product catalog first, then wire up state, and finally connect the WhatsApp business logic.

---

### **Phase 1: Setup Steps (Dependencies & Mock Data)**

*Skip base project init, as React/Tailwind/Shadcn is already running.*

1. **Install Core Libraries:**
Run: `pnpm add react-router-dom zustand lucide-react react-hook-form @hookform/resolvers zod date-fns`
2. **Add Shadcn Components:**
Run: `pnpm dlx shadcn-ui@latest add button card input textarea sheet toast badge radio-group select`
3. **Database Steps (Static JSON Replacement):**
* Create `src/data/products.json`.
* Populate with an array of 6 standard cakes and 3 add-on items.
* *Structure:* `{ id, name, price, image (use placeholder URLs like Unsplash/food), category (cake/addon), sizes: [1kg, 2kg] }`.


4. **Authentication Steps:**
* *Action:* **SKIP ENTIRELY**. As per the MVP PRD, users are guests. No auth is needed.



---

### **Phase 2: UI Steps (Immediate Visuals - "What to build first")**

*Goal: Get the app looking like a real store within the first hour.*

1. **Global Shell (`src/App.jsx`):**
* Set up `BrowserRouter` from `react-router-dom`.
* Create a `<Layout>` wrapper component.


2. **Top Announcement Bar (`src/components/layout/TopBar.jsx`):**
* UI: Light pink background (`#FDF2F4`), dark text, sticky top. Text: "100% Eggless | Gluten-Free | Lactose-Free Creams".


3. **Navbar (`src/components/layout/Navbar.jsx`):**
* UI: Flexbox container. Logo (Left), Links (Center), Cart Icon (Right).
* Implementation: Use `lucide-react` for the ShoppingBag icon.


4. **Home & Hero Section (`src/pages/Home.jsx`):**
* UI: Large hero banner (placeholder image of a cake), Title ("Artisan Eggless Cakes"), Subtitle, and "Order Now" button.


5. **Product Card Component (`src/components/catalog/ProductCard.jsx`):**
* UI: Shadcn `<Card>`. Image top, title, price, size dropdown (`<Select>`), and Primary Button ("Add to Cart").


6. **Catalog Grid (`src/components/catalog/CatalogGrid.jsx`):**
* Action: Import `products.json`. Map through the array and render `<ProductCard>` in a responsive Tailwind grid (`grid-cols-1 md:grid-cols-3 gap-6`).



*At this point, the app is 100% presentable to stakeholders. Now we make it work.*

---

### **Phase 3: Feature Steps ("What to build second")**

*Goal: Interactivity, Cart State, and WhatsApp Routing.*

1. **Global Cart State (`src/store/useCartStore.js`):**
* Setup `Zustand`.
* State: `cart` (array), `isDrawerOpen` (boolean).
* Actions: `addToCart(item)`, `removeFromCart(id)`, `toggleDrawer()`, `getCartTotal()`.


2. **Cart Drawer UI (`src/components/cart/CartDrawer.jsx`):**
* Implementation: Use Shadcn `<Sheet>` component triggered by the Navbar cart icon.
* UI: List items from Zustand state. Add an "Upsell" horizontal scroll section at the bottom mapping over 'addons' from `products.json`.
* Action: "Proceed to Checkout" button routes to `/checkout` and closes sheet.


3. **Custom Cake Builder Page (`src/pages/CustomCake.jsx`):**
* UI: Left column (Inspirational images grid), Right column (Form).
* Form: Use `react-hook-form`. Textarea for description, file input for reference image (Client-side object URL preview only).
* Action: On submit, create a mock cart object `id: 'custom-' + Date.now(), price: 0`, and fire `addToCart`. Open Cart Drawer.


4. **Checkout Page (`src/pages/Checkout.jsx`):**
* UI: Two-column layout. Left: Forms. Right: Order Summary.
* **Form Step 1 (Details):** Name, Notes (Shadcn `<Input>`, `<Textarea>`).
* **Form Step 2 (Fulfillment Toggle):** Shadcn `<RadioGroup>` for Pickup vs. Delivery.
* *Logic:* If Delivery, show Address Input.
* *Location Mock Logic:* Since we want speed, add a "Verify Location" button. Write a dummy JS function that simply checks if the address string contains the word "far" (simulating a >10km rejection) for UI testing, otherwise accept.


* **Form Step 3 (Time):** Date picker and Time dropdown.


5. **WhatsApp Payload Generator (`src/utils/whatsapp.js`):**
* Create a helper function `generateWhatsAppURL(cartData, formData)`.
* Format using URL encoding (`%0A` for new lines, `*` for bold).
* Include the 30-40% advance payment disclaimer in the message footer.


6. **Finalize Order Action:**
* On Checkout form submit, call `generateWhatsAppURL`, then execute `window.location.href = url`.



---

### **Phase 4: Testing Steps (QA Checklist)**

1. **Routing Test:** Click all Nav links. Ensure `/`, `/custom`, and `/checkout` load without full page reloads.
2. **Cart State Test:** Add standard cake -> Add custom cake -> Add upsell item from drawer. Check if the total price calculates correctly (custom cake should be ignored in sum or marked "To be quoted").
3. **Validation Test:** Attempt to submit the checkout form empty. Verify Shadcn/Zod form validation highlights required fields (Date, Time, Address if delivery).
4. **Fulfillment Toggle Test:** Switch between Pickup and Delivery. Ensure address field appears/disappears and delivery fee (if any) updates.
5. **WhatsApp Redirection Test:** Submit a valid order. Check the resulting `wa.me` URL in the browser address bar. Decode it mentally to ensure line breaks and cart items look correct.

---

### **Phase 5: Deployment Steps**

*Since there is no backend, deployment is instantaneous via static hosting.*

1. **Build the App:** Run `pnpm run build`. Ensure there are no Vite/Webpack compilation errors.
2. **Deploy via Vercel CLI (Fastest):**
* Run `pnpm dlx vercel` in the terminal.
* Accept default settings. It will automatically detect Vite/React.


3. **Alternatively, Netlify CLI:**
* Run `pnpm dlx netlify deploy --prod`.
* Set build command: `pnpm run build`. Set publish directory: `dist`.


4. **Post-Deploy:** Visit the live URL on a mobile device to ensure the responsive grid and cart drawer behave correctly on touch screens.