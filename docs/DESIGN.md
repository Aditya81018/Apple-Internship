Here is the comprehensive UI/UX Design Brief for the Raj Confections MVP. This document provides exact styling parameters, CSS values, and structural rules to ensure an AI coding agent can implement a pixel-perfect, cohesive interface without ambiguity.

### **1. Overall User Experience & Design Style**

* **Design Style:** **"Artisan DTC (Direct-to-Consumer) Minimalist."** The UI must feel appetizing, clean, and premium, while remaining highly functional. The design should get out of the way to let high-quality cake images serve as the primary visual anchors.
* **Core Vibe:** Warm, trustworthy, accessible, and fresh.
* **Key UX Principle:** **"Zero Guesswork."** Customers must immediately understand dietary specializations (eggless, gluten-free), see prices clearly, and understand that checkout happens via WhatsApp.

---

### **2. Color Palette (CSS-Ready Hex Codes)**

Use a warm, bakery-inspired palette balanced with high-contrast UI colors for accessibility.

* **Backgrounds & Surfaces:**
* Global Background: `#FAFAFA` (Soft Cream/Off-white - reduces eye strain compared to pure white).
* Surface/Card Background: `#FFFFFF` (Pure white for content cards to pop).
* Banner Background: `#FDF2F4` (Very light pastel pink for the top announcement bar).


* **Brand & Accents:**
* Primary Brand Color: `#E07A5F` (Warm Baked Terracotta / Soft Orange-Pink). Used for active states, badges, and primary buttons.
* Secondary Brand Color: `#F4A261` (Warm Peach). Used for subtle highlights or secondary buttons.


* **Text & Typography:**
* Text Primary (Headings/Body): `#2D2522` (Dark Chocolate Brown - softer than black, fits the bakery theme).
* Text Secondary (Subtitles/Meta): `#756B68` (Mocha Grey).


* **Functional Colors:**
* Checkout / Final CTA: `#25D366` (Official WhatsApp Green - crucial for user expectation).
* Error/Warning: `#E63946` (Soft Red).
* Success: `#2A9D8F` (Teal/Green).



---

### **3. Typography**

* **Font Pairings:** Use Google Fonts.
* **Headings (H1, H2, H3):** `Playfair Display` (Serif). Adds an elegant, artisan bakery feel.
* **Body & UI Text (Buttons, Inputs, Labels):** `Inter` or `Plus Jakarta Sans` (Sans-serif). Ensures maximum legibility on mobile devices.


* **Scale & Weights (Desktop / Mobile):**
* **H1:** 40px / 32px, Weight: 700 (Bold). Line-height: 1.2.
* **H2:** 32px / 24px, Weight: 600 (Semi-bold). Line-height: 1.3.
* **H3 (Card Titles):** 20px / 18px, Weight: 600.
* **Body Main:** 16px / 16px, Weight: 400 (Regular). Line-height: 1.5.
* **Small/Helper Text:** 14px / 12px, Weight: 400. Text Secondary color.
* **Button Text:** 16px, Weight: 600, Letter-spacing: 0.5px.



---

### **4. Component Style: Buttons and Cards**

**Buttons:**

* **Primary Button (Add to Cart / Generic Action):**
* Background: `#E07A5F`
* Text: `#FFFFFF`
* Border-radius: `8px` (friendly, modern curve).
* Padding: `12px 24px`.
* Hover State: `transform: translateY(-2px)`, Background: `#D16A50`, Box-shadow: `0 4px 12px rgba(224, 122, 95, 0.3)`.
* Transition: `all 0.2s ease-in-out`.


* **WhatsApp Checkout Button (The Ultimate CTA):**
* Background: `#25D366`
* Text: `#FFFFFF`
* Icon: Include a white SVG WhatsApp logo aligned left of the text.
* Border-radius: `8px`.
* Hover State: Background `#20B858`, shadow `0 6px 16px rgba(37, 211, 102, 0.4)`.


* **Secondary Button (Ghost/Outline):**
* Background: Transparent.
* Border: `2px solid #E07A5F`. Text: `#E07A5F`.



**Cards (Product Catalog):**

* **Structure:** Border-radius `12px`, Background `#FFFFFF`.
* **Border:** `1px solid #F0EBE9` (very subtle border to separate from the `#FAFAFA` background).
* **Shadow:** Default `0 2px 8px rgba(45, 37, 34, 0.04)`. Hover state `0 8px 24px rgba(45, 37, 34, 0.08)`.
* **Image Area:** Top half of the card. `border-radius: 12px 12px 0 0`. Aspect ratio `4:3` or `1:1`. `object-fit: cover`.
* **Content Padding:** `16px` inside the card below the image.

---

### **5. Layout Rules & Spacing System**

* **Grid System:** Base-8 scale (8px, 16px, 24px, 32px, 48px, 64px).
* **Max Width:** Desktop container constrained to `1200px` and centered.
* **Component Spacing:**
* Gap between grid items: `24px`.
* Section vertical padding: `64px` (Desktop), `40px` (Mobile).


* **Forms/Inputs:**
* Height: `48px` (highly tap-friendly).
* Border: `1px solid #D6D0CF`. Border-radius: `8px`.
* Focus State: `border-color: #E07A5F`, `box-shadow: 0 0 0 3px rgba(224, 122, 95, 0.2)`.



---

### **6. Mobile vs. Desktop Behavior**

| Feature | Mobile Behavior (< 768px) | Desktop Behavior (≥ 768px) |
| --- | --- | --- |
| **Navigation** | Hamburger menu. Logo center. Cart icon right. | Full horizontal links. Logo left. Cart right. |
| **Catalog Grid** | 1 Column (`1fr`). 16px gap. | 3 or 4 Columns (`repeat(auto-fill, minmax(280px, 1fr))`). 24px gap. |
| **Cart Drawer** | Slides up from bottom OR slides right, taking `100vw` (full width). | Slides in from right side, fixed width `400px`. |
| **Checkout CTA** | Sticky at the bottom of the screen (`position: fixed; bottom: 0`). | Normal flow below the checkout form. |

---

### **7. Dashboard Design Direction**

* **Note:** As per the MVP PRD, an Admin/Owner Dashboard is **NOT included**.
* **Owner Experience:** The "dashboard" for the owner is entirely managed via WhatsApp. The UI focus for order management relies strictly on generating clean, easily readable, line-broken text structures inside the final WhatsApp URL payload (using `%0A` for line breaks, `*` for bolding).

---

### **8. Inspiration References**

To guide the AI's CSS generation, reference the visual language of:

1. **Milk Bar (milkbarstore.com):** For their clean product cards, bold typography, and focus on high-quality product imagery over heavy UI elements.
2. **Magnolia Bakery:** For the use of soft pastel accent colors mixed with elegant typography.
3. **Modern Shopify Themes (e.g., "Dawn"):** For the spacious, responsive grid structures, slide-out cart drawer mechanics, and sticky mobile CTAs.