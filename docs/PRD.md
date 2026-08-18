Here is the Lean MVP Product Requirements Document (PRD), optimized for direct handoff to an AI coding agent.

### **App Name**

Raj Confections

### **One-Line App Idea**

A frontend-only ecommerce catalog and custom cake builder that captures customer requirements, add-ons, and delivery constraints, routing the finalized order directly to the shop owner's WhatsApp.

### **Target Users**

Local customers with specific dietary preferences seeking premium eggless, gluten-free, and lactose-free cakes for celebrations.

### **Core Problem Solved**

Eliminates the chaotic back-and-forth of taking custom orders over chat by forcing customers through a structured catalog and requirement-gathering flow *before* initiating the WhatsApp conversation.

---

### **Main Features**

1. **Specialty Branding Banner:** Persistent UI element highlighting the core USP: "100% Eggless | Gluten-Free | Lactose-Free Creams".
2. **Standard Catalog:** A static grid displaying pre-designed cakes with images, prices, sizes, and an "Add to Cart" button.
3. **Custom Cake Builder:** A dedicated page/modal for custom requests allowing users to upload a reference image (handled client-side via base64 or object URL) and write detailed descriptions.
4. **Cart & Upsell System:** A cart drawer/page that allows users to review items and add extras (candles, party poppers, birthday knives).
5. **Smart Checkout Form:**
* Fulfillment toggle: **Pickup** vs. **Delivery**.
* Date and Time picker for fulfillment.
* Text fields: "Name on Cake", "Special Messages", "Allergy Notes/Instructions".
* **Location Validation:** If "Delivery" is selected, an address input checks distance against the shop's origin coordinates (using a client-side Haversine formula script or free Geocoding API). If >10km, blocks delivery and forces "Pickup".


6. **Order Policy Disclaimers:** Hardcoded notices at checkout stating: *"Orders are manually confirmed via WhatsApp."* and *"A 30-40% advance payment is required for large orders."*
7. **WhatsApp Checkout Gateway:** A final "Place Order via WhatsApp" button that compiles the cart state, customer details, and checkout form into a URL-encoded string and redirects to the `wa.me/<owner_number>?text=<formatted_data>` API.

---

### **User Roles**

* **Guest Customer:** Browses, builds the cart, and submits the order (no account required).
* **Owner:** Receives structured messages on WhatsApp (external to the app).

---

### **User Stories**

* **US1:** As a customer, I want to view a catalog of standard cakes so I can easily pick a pre-made design.
* **US2:** As a customer, I want to submit a custom cake request with an image and text description so the baker understands my exact vision.
* **US3:** As a customer, I want to add birthday extras (candles, knives) to my cart so I don't have to buy them elsewhere.
* **US4:** As a customer, I want to select a specific date and time for pickup or delivery so my cake is fresh for the event.
* **US5:** As a customer, I want the system to tell me if my delivery address is outside the 10km radius so I can switch to pickup instead.
* **US6:** As the owner, I want the final WhatsApp message to contain the exact cart items, total price, delivery/pickup details, and customization notes so I can reply with a payment link immediately.

---

### **Success Metrics (V1)**

* **Completion Rate:** Percentage of users who click "Place Order via WhatsApp" after adding an item to the cart.
* **Time-to-Quote:** Reduction in the number of text messages required to finalize an order on WhatsApp.

---

### **MVP Technical Scope (For AI Agent)**

* **Architecture:** 100% Frontend (React / Next.js / Vanilla JS based on preference). No backend.
* **State Management:** Use LocalStorage or Context API to persist the Cart and Form data across page reloads.
* **Pages/Views:**
1. Home/Landing (Hero + USP)
2. Catalog Page
3. Custom Order Page
4. Cart/Checkout Drawer or Page


* **Data Layer:** Use a static `products.json` file for the standard cakes and add-ons (images stored locally in `/public`).
* **Location Logic:** Hardcode the shop's Lat/Lng. Use a free API (like OpenStreetMap Nominatim) to convert the user's entered address to Lat/Lng, then calculate the distance client-side.

---

### **Features Not Included in V1 (Do Not Build)**

* User Authentication (Login/Signup).
* Backend Database (No MongoDB, Firebase, or PostgreSQL).
* Payment Gateway Integration (No Stripe, Razorpay, or PayPal).
* Admin Dashboard or Inventory Management UI (owner updates `products.json` manually for now).
* Server-side image hosting (Custom images should just be sent as a description, or the user is prompted to share the image directly in the WhatsApp chat once the window opens).