# UI/UX Design Brief - Raj Confections (Playful & Bright V2)

This document defines the updated styling parameters and design system for a bright, large, and playful artisan storefront.

---

### **1. Design Style: "Bubbly Artisan Bakery"**

*   **Vibe:** Bright, friendly, Appetizing, and energetic. The UI should feel like a premium, sweet, and modern candy/bakery store.
*   **Key UX Principle:** Highly visible sizing, clear typography, and tactile, bouncy buttons.
*   **Borders & Radius:** Extra-rounded, puffy corners (`rounded-3xl` for cards, `rounded-2xl` for buttons/inputs) to convey a friendly and soft aesthetic.

---

### **2. Playful Color Palette (CSS-Ready Hex Codes)**

*   **Backgrounds & Surfaces:**
    *   Global Background: `#FFFDF9` (Bright Warm Vanilla Cream - feels sunny and clean).
    *   Surface/Card Background: `#FFFFFF` (Pure white cards to stand out).
    *   Banner Background: `#FFE8EC` (Sweet Cotton Candy Pink for announcement bars).
*   **Brand & Accents:**
    *   Primary Brand Color: `#FF6B8B` (Vibrant Strawberry Pink). Used for active states, key highlight elements, and primary buttons.
    *   Secondary Brand Color: `#FFBE53` (Sweet Honey Peach). Used for warm highlights, size badges, and ratings.
    *   Accent Background: `#FFF0F2` (Soft Pastel Rose).
*   **Text & Typography:**
    *   Text Primary (Headings/Body): `#3D2721` (Rich Cocoa Brown - warmer and softer than harsh black, matching the bakery theme).
    *   Text Secondary: `#8A736D` (Sweet Mocha Grey).
*   **Functional Colors:**
    *   Checkout / Final CTA: `#25D366` (Official WhatsApp Green).
    *   Success: `#2DCA96` (Mint Green).
    *   Error/Warning: `#FF4B5C` (Bright Coral Red).

---

### **3. Typography & Spacing (Bigger & Bolder)**

*   **Font Pairings:**
    *   **Headings (H1, H2, H3):** `Playfair Display` (Serif, Styled extra-bold for a playful, classical confectionery look).
    *   **Body & UI Text (Buttons, Inputs, Labels):** `Plus Jakarta Sans` (Sans-serif, highly legible with slightly rounded, friendly letterforms).
*   **Scale & Weights (Desktop / Mobile):**
    *   **H1 (Hero Title):** 52px / 40px, Weight: 800 (Extra Bold). Line-height: 1.15.
    *   **H2 (Section Headings):** 38px / 28px, Weight: 700 (Bold).
    *   **H3 (Card Titles):** 22px / 20px, Weight: 700.
    *   **Body Main:** 18px / 16px, Weight: 500 (Medium). Line-height: 1.6.
    *   **Button Text:** 16px, Weight: 700, Letter-spacing: 0.5px.

---

### **4. Component Style: Buttons and Cards**

#### **Tactile Buttons:**
*   **Primary Button (Add to Cart / Generic Action):**
    *   Background: `#FF6B8B` (Strawberry Pink)
    *   Text: `#FFFFFF`
    *   Border-radius: `16px` (`rounded-2xl`).
    *   Padding: `14px 28px`.
    *   **Playful Hover State:** `transform: translateY(-4px) scale(1.03) rotate(-1deg)`, Box-shadow: `0 10px 20px rgba(255, 107, 139, 0.3)`.
    *   **Active/Press State:** `transform: translateY(1px) scale(0.97)`.
    *   Transition: `all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275)` (bouncy ease-out).

*   **WhatsApp Checkout Button:**
    *   Background: `#25D366`
    *   Text: `#FFFFFF`
    *   Border-radius: `16px`.
    *   Hover State: `transform: translateY(-4px) scale(1.03)`, Background `#20B858`, shadow `0 12px 24px rgba(37, 211, 102, 0.4)`.

#### **Confectionery Cards:**
*   **Structure:** Border-radius `24px` (`rounded-3xl`), Background `#FFFFFF`.
*   **Border:** `2px solid #FCEEEB` (soft cream-pink border).
*   **Shadow:** Default `0 4px 12px rgba(61, 39, 33, 0.03)`.
*   **Playful Hover State:** `transform: translateY(-8px) scale(1.02) rotate(0.5deg)`, Box-shadow: `0 20px 32px rgba(61, 39, 33, 0.08)`, Border-color: `#FFD3DC`.
*   **Image Area:** Top half. Aspect ratio `4:3`. Rounded top `22px 22px 0 0` with overflow clip.

---

### **5. Spacing System**
*   Base-8 scale expanded for spacious breathing room (16px, 24px, 32px, 48px, 64px, 80px).
*   Desktop content max width: `1200px` centered.
*   Grid layout gaps: `32px` on desktop for an airy, premium catalog look.