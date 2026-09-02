Here is the complete App Flow document for the Raj Confections MVP, designed strictly for rapid frontend execution by an AI coding agent.

### **Global UI Elements (Persistent Across App)**

* **Top Announcement Bar:** Sticky banner reading: *"100% Eggless | Gluten-Free | Lactose-Free Creams"* (Non-clickable).
* **Header (Navbar):**
* **Logo:** Left-aligned. Action: Routes to Home `/`.
* **Links:** "Standard Cakes" (Scrolls/routes to Catalog), "Custom Order" (Routes to `/custom`).
* **Cart Icon:** Right-aligned with dynamic item count badge. Action: Toggles Cart Drawer (`isCartOpen = true`).


* **Footer:** Standard links, shop physical address, contact info, and operational hours.

---

### **1. Screen: Home / Standard Catalog (`/`)**

**Purpose:** Primary landing page showcasing pre-designed cakes.

* **Hero Section:** High-quality image, headline, and "Order Now" CTA (Anchors down to Catalog).
* **Catalog Grid:** Maps over `products.json`.
* **Card UI:** Product Image, Title, Price, Size options (dropdown or pills).
* **Button Action:** "Add to Cart" click triggers:
1. Adds item object to `cartState`.
2. Shows brief toast notification: "Added to Cart".
3. Updates Cart Icon badge count.





### **2. Screen: Custom Cake Builder (`/custom`)**

**Purpose:** Form to capture complex, tailored cake requests.

* **UI Components:**
* **Description Textarea:** "Describe your dream cake (flavor, colors, theme)..."
* **Weight/Size Dropdown:** 1kg, 2kg, 3kg, etc.
* **Reference Image Uploader:**
* *Action:* Accepts `.jpg`, `.png`. Displays local thumbnail preview.
* *Helper Text:* "Upload a reference image here. *Note: You will need to attach this image directly in the WhatsApp chat when placing the order.*"


* **Button Action:** "Add Custom Request to Cart" click triggers:
1. Generates a placeholder product object (Title: "Custom Cake Request", Price: "To be quoted", Details: Form inputs).
2. Adds to `cartState`.
3. Opens Cart Drawer automatically.





### **3. UI Element: Cart Drawer (Slide-out from right)**

**Purpose:** Review items, upsell, and transition to checkout.

* **Cart Items List:** Shows image, title, size, price, and a "Remove" (trash icon) button.
* **Upsell Section ("Don't forget the party!"):**
* Horizontal scroll of small items: Magic Candles, Party Poppers, Birthday Knife.
* *Action:* Small "+" button adds directly to cart total.


* **Cart Footer:** Subtotal calculation (ignores "To be quoted" custom cakes).
* **Button Action:** "Proceed to Checkout" closes drawer and routes to `/checkout`.

### **4. Screen: Checkout Flow (`/checkout`)**

**Purpose:** Gather fulfillment data, validate location, and construct the WhatsApp payload.

* **Step 1: Order Notes**
* Input: "Name on Cake" (Optional)
* Textarea: "Special Messages or Allergy Notes" (Optional)


* **Step 2: Fulfillment Method (Radio Buttons or Toggle)**
* **Option A: Store Pickup**
* UI updates to show the Shop's Address and a map link.


* **Option B: Delivery**
* UI reveals "Delivery Address" input field.
* *Action:* On `blur` or "Verify Location" click, trigger client-side distance calculation (using free geocoding API to get Lat/Lng, compared to shop's Lat/Lng via Haversine formula).
* *Success:* Show green checkmark "Location eligible for delivery."
* *Error:* Show red text "Sorry, we only deliver within 10km. Please select Store Pickup." Force toggle back to Pickup or disable "Place Order" button.




* **Step 3: Date & Time Picker**
* Date Input: Block out past dates.
* Time Input: Dropdown limited to shop hours (e.g., 10:00 AM - 8:00 PM).


* **Step 4: Policy Acknowledgement**
* Read-only alert box: *"Please Note: Order confirmation happens on WhatsApp. A 30-40% advance payment is required for large orders."*


* **Step 5: Final Submission**
* **Button Action:** "Place Order via WhatsApp" click triggers:
1. Validates all required fields.
2. Constructs the WhatsApp Message String (see below).
3. URI-encodes the string.
4. Redirects `window.location.href = "[https://wa.me/91XXXXXXXXXX?text=](https://wa.me/91XXXXXXXXXX?text=)" + encodedString`.





---

### **State Handling Definitions**

**Empty States:**

* **Cart Drawer:** If `cartState.length === 0`, display illustration of an empty box. Text: "Your cart is empty." Button: "Browse Standard Cakes" (Routes to `/`).

**Error States:**

* **Form Validation:** Red borders on missing required fields in Checkout (Date, Time, Address if Delivery). Helper text: "This field is required."
* **Out of Bounds Delivery:** As defined in Checkout Step 2. Disables submission until resolved.

**Success States:**

* **Add to Cart:** Green toast notification bottom-right.
* **Order Submission:**
* Since it's a redirect, briefly show a full-screen loading overlay: "Generating your order summary... Redirecting to WhatsApp."



---

### **System Data Architecture: The WhatsApp Payload**

The AI agent must format the final string to look clean on WhatsApp.
**Format Template:**

```text
🎂 *NEW ORDER REQUEST* 🎂

*Items:*
1x Chocolate Truffle (1kg) - ₹800
1x Custom Cake Request - To be quoted
1x Magic Candle - ₹50

*Custom Details:*
Name on Cake: Happy Birthday Sarah
Notes: Please ensure absolutely no nuts.

*Fulfillment:* 
Method: Delivery
Date: 25 Aug 2026
Time: 4:00 PM
Address: 123 Main St, Kolkata...

*Total (Excluding Custom):* ₹850

```

---

### **Flows Explicitly Excluded (As per V1 PRD)**

* **Login/Signup Flow:** Omitted. Users check out as guests to reduce friction.
* **Payment/Upgrade Flow:** Omitted. Checkout ends at the WhatsApp redirect. The business owner will generate a manual payment link (UPI/Bank Transfer) and send it directly in the WhatsApp chat to collect the 30-40% advance.