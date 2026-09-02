# Product Requirements Document (PRD) - Version 2

### **App Name**
**Raj Confections**

## **Brand Identity & Location**
**Raj Confections** is a premier **Kolkata-based** boutique bakery and homemade confectionery specializing in eggless, gluten-free, and lactose-free customized bakes. The platform explicitly highlights its Kolkata heritage and local delivery scope (validating home delivery radius within 10 km from its central Kolkata origin coordinates).

## **One-Line App Idea**
A fullstack e-commerce platform for a Kolkata boutique bakery featuring a React customer web app served via a PHP server, Cloudflare R2 object storage for image assets, a MySQL database for order and catalog persistence, an Admin Back-Office with CMS and CRM tools, and automated WhatsApp notifications for seamless offline payment and order fulfillment.

---

## **Target Users & Device Focus**

1. **Customers (Mobile Phone Primary Focus):** Local Kolkata celebration planners and cake buyers accessing the site predominantly from mobile smartphones. The client website must prioritize mobile-first UX, touch-friendly navigation, quick tap actions, and instant mobile WhatsApp deep-linking.
2. **Shop Admin / Bakery Owner (Laptop + WhatsApp Automation Primary Focus):** The business owner managing operations via desktop/laptop computers. The admin panel must feature a laptop-optimized wide layout (data grids, side-by-side order detail drawers, bulk CMS management) paired with automated server-to-WhatsApp alerts to immediately notify the owner when new orders arrive.

---

## **Platform & Device Priorities**

* **Client Website (Customer-Facing):** **Mobile Phones (Mobile-First UI/UX)**
  * Touch-optimized catalog grids, mobile-friendly custom cake image upload, drawer-based cart, and one-tap WhatsApp link opening natively in the WhatsApp mobile app.
* **Admin Panel (CMS & CRM):** **Laptops / Desktops & WhatsApp Automation**
  * Wide-screen table layouts for viewing high-density order data, quick status filters, mouse & keyboard-friendly CMS forms, and automated background WhatsApp message triggers directly from the server.

---

## **Problem Solved**

### **V1 Limitations:**
* **No Data Persistence:** Orders existed only in transient client memory and WhatsApp URL strings. If a message failed to send, order details were lost.
* **Static Content:** Catalog updates, price changes, and new product offerings required code edits and re-deployments.
* **No Order Tracking:** The shop owner lacked a centralized system to view pending orders, filter completed tasks, or manage customer records.

### **V2 Solution:**
* **Fullstack Database Architecture:** All catalog items, custom uploads, customer records, and orders are stored securely in a MySQL database.
* **Cloudflare R2 Object Storage:** All customer reference photos and admin CMS product/gallery images are stored in Cloudflare R2 bucket via S3-compatible API for fast CDN delivery and zero egress fees.
* **Admin CMS (Content Management System):** Allows the shop owner to perform full CRUD operations on standard cakes, custom options, gallery images, and announcement banners.
* **Admin CRM (Customer Relationship Management):** Provides a centralized order dashboard to view customer contact info, delivery schedules, and order breakdown, with the ability to mark orders as "Done" to clear them from the pending queue.
* **Automated WhatsApp Workflow:** Triggers instant automated WhatsApp alerts to the admin upon new order submission, while explicitly guiding customers to WhatsApp for payment processing and order status updates.

---

## **Main Features**

### **1. Customer-Facing Website (React App served via PHP)**
* **Kolkata Brand Identity & Specialty Banner:** Prominent branding emphasizing Kolkata's premier homemade eggless bakery (*"Happiness Homemade | 100% Eggless | Gluten-Free | Lactose-Free Creams"*).
* **Dynamic Product Catalog:** Categorized display of standard bakes, seasonal specials, and custom flavor bases fetched directly from the backend database.
* **Custom Cake Builder:** Dedicated interface allowing customers to select flavor bases, specify weight/size, upload reference design images (uploaded directly to Cloudflare R2 bucket via backend API), and specify detailed instructions.
* **Smart Cart & Upsell Engine:** Cart drawer with real-time total calculation and optional add-on recommendations (candles, party poppers, birthday knives).
* **Fulfillment & Location Validation Checkout:**
  * Toggle between **Pickup** and **Home Delivery**.
  * Distance check using Haversine calculation against Kolkata origin coordinates (restricts delivery if > 10 km within Kolkata).
  * Date and Time picker for fulfillment schedule.
  * Inputs for Name on Cake, Special Instructions, and Customer Contact Details (Full Name, Phone Number, Kolkata Delivery Address).
* **Order Placement & WhatsApp Redirection Screen:**
  * Submits order to PHP backend REST API (`POST /api/orders.php`).
  * Displays confirmation notice: *"Order Placed! Contact us to know more..."*
  * Displays prominent WhatsApp CTA button linking directly to the admin's WhatsApp (`wa.me/<admin_phone>?text=<Order_ID_and_Details>`).
  * Explicit banner stating: *"Placed an order? Contact us to know more about payment and updates."*

---

### **2. Backend Server, Cloud Storage & Database (PHP + Cloudflare R2 + MySQL)**
* **Static Web Hosting:** Serves the compiled React production bundle (`dist/`) as the web root.
* **Cloudflare R2 Object Storage Integration:** API layer uses S3-compatible SDK/credentials to upload and manage assets in a Cloudflare R2 bucket (`POST /api/upload.php`), returning public CDN URLs (`https://assets.rajconfections.com/...` or R2 public URLs).
* **REST API Layer:** Exposes endpoints for client catalog fetching, Cloudflare R2 image uploads, order creation, and admin data management.
* **MySQL Database Schema:** Stores products, categories, gallery assets, customer records, orders, order items, CMS banners, and admin credentials.
* **Automated WhatsApp Notification Trigger:** Upon receiving a valid new order, the server executes a notification script/service to send an instant order summary message to the admin's WhatsApp number.

---

### **3. Admin Back-Office Portal (PHP Server Side)**
* **Secure Authentication:** Password-protected admin login session.
* **CMS (Content Management System):**
  * **Catalog Management:** Create, Read, Update, Delete (CRUD) catalog products (name, description, price per lb, category, availability status, product image).
  * **Gallery Management:** Upload and manage customer showcase images and testaments.
  * **Site Settings & Banner Control:** Update announcement banner text, USP highlights, shop origin coordinates, and delivery radius limits.
* **CRM (Customer Relationship Management):**
  * **Order Dashboard:** List view of all incoming customer orders displaying Order ID, Customer Name, Phone Number, Delivery/Pickup Type, Delivery Address, Fulfillment Date/Time, Items ordered, Custom Image reference link, Total Amount, and Order Timestamp.
  * **Pending vs. Completed Tabs:** Filter view to isolate active pending orders.
  * **Mark as "Done" Action:** Clicking "Mark Done" updates the order status in the database to `completed` and hides it from the pending orders list view.
  * **Direct WhatsApp Customer Chat:** One-click button on each order card to open a WhatsApp chat directly with the customer (`wa.me/<customer_phone>`).

---

## **User Roles**

| Role | Access Level | Core Responsibilities |
| :--- | :--- | :--- |
| **Guest Customer** | Public Website | Browses catalog, builds custom cakes, submits orders, views WhatsApp redirection guidance. |
| **Admin / Owner** | Authenticated Admin Portal | Manages catalog & gallery (CMS), monitors & processes pending orders (CRM), contacts customers via WhatsApp. |

---

## **User Stories & Acceptance Criteria**

### **US1: Dynamic Catalog Browsing**
* **As a** customer,  
* **I want to** view up-to-date products and prices from the bakery,  
* **So that** I can select items currently available for order.
* **Acceptance Criteria:**
  * Client fetches products from `GET /api/products.php`.
  * Items are visually grouped by Category (Standard Cakes, Specialty, Winter Specials, Small Bakes).
  * Product images load from backend server uploads directory (`/uploads/products/`).
  * Disabled/Out-of-stock items (as set in CMS) are marked un-selectable or hidden.

---

### **US2: Custom Cake Request with Image Upload**
* **As a** customer,  
* **I want to** upload a design image and submit custom cake specifications,  
* **So that** the baker understands my exact vision.
* **Acceptance Criteria:**
  * Form accepts image file formats (`.png`, `.jpg`, `.jpeg`, `.webp`, max 5MB).
  * Image is uploaded to backend `POST /api/upload.php` returning a server URL.
  * Custom order payload includes selected flavor, size, uploaded image URL, and text instructions.

---

### **US3: Order Placement & Database Persistence**
* **As a** customer,  
* **I want my** order details saved securely upon clicking "Place Order",  
* **So that** my order is officially registered with the bakery even if my phone disconnects.
* **Acceptance Criteria:**
  * Submitting checkout form triggers `POST /api/orders.php`.
  * Server validates required fields (Customer Name, Phone, Fulfillment Date, Address if delivery).
  * Order is saved to MySQL `orders` and `order_items` tables with `pending` status.
  * Response returns a unique `order_id` (e.g., `#RC-1042`).

---

### **US4: WhatsApp Redirection & Customer Post-Order Guidance**
* **As a** customer,  
* **I want clear** instructions on how to confirm payment and track my order,  
* **So that** I know what step to take next after submitting the form.
* **Acceptance Criteria:**
  * Upon successful API order creation, UI switches to Order Confirmation view.
  * UI explicitly states: *"Order Placed! Contact us to know more..."*
  * UI presents a prominent WhatsApp button: `wa.me/<admin_whatsapp>?text=Hi%20Raj%20Confections,%20I%20placed%20Order%20#RC-1042`.
  * Persistent disclaimer displayed: *"Placed an order? Contact us to know more about payment & updates."*

---

### **US5: Automated Admin Notification**
* **As the** shop admin,  
* **I want to** receive an immediate WhatsApp notification whenever a new order is placed,  
* **So that** I am instantly alerted without monitoring the admin dashboard 24/7.
* **Acceptance Criteria:**
  * Upon successful insertion of an order into MySQL, PHP backend triggers a notification dispatch.
  * Message received on Admin WhatsApp contains: Order ID, Customer Name, Phone, Fulfillment Type, Date/Time, Total Amount, and a direct link to the Admin CRM.

---

### **US6: Admin CMS - Catalog Management**
* **As the** shop admin,  
* **I want to** add, edit, toggle, or delete catalog items via an admin UI,  
* **So that** I can update prices and seasonal menu items instantly.
* **Acceptance Criteria:**
  * Admin logs into `/admin/login.php`.
  * Admin accesses CMS -> Catalog.
  * Form permits uploading new product images, setting name, description, price (1 lb / 2 lb), category, and availability.
  * Changes immediately reflect on the public React website upon refresh.

---

### **US7: Admin CRM - Order Processing & Status Toggle**
* **As the** shop admin,  
* **I want to** view all pending customer orders and mark completed ones as "Done",  
* **So that** my active workspace stays clean and organized.
* **Acceptance Criteria:**
  * Admin accesses CRM -> Orders dashboard.
  * Pending orders table lists all orders with status `pending`.
  * Admin can click "View Details" to see full item breakdown, custom image reference, customer notes, and address.
  * Clicking "Mark Done" updates DB status to `completed` and removes the item from the active Pending Orders view (retrievable under "All Orders" filter).
  * Includes a "Chat on WhatsApp" button next to customer phone number that opens `wa.me/<customer_phone>`.

---

## **Success Metrics**

1. **Zero Order Loss:** 100% of submitted orders correctly stored in MySQL database before WhatsApp redirect.
2. **Catalog Update Speed:** Time required for admin to add/modify a product reduced from code deployment to < 2 minutes via CMS.
3. **Order Response Efficiency:** Reduction in admin order processing time due to automated WhatsApp notification and one-click CRM customer chat links.

---

## **V2 Technical Scope & Architecture**

```
+-----------------------------------------------------------------------+
|                       CLIENT SIDE (Mobile-First)                      |
|  React (Vite) App served from PHP public root                         |
|  - Catalog View  - Custom Cake Builder  - Smart Cart & Checkout       |
+-----------------------------------++----------------------------------+
                                    || REST API Requests
                                    \/
+-----------------------------------------------------------------------+
|                             PHP SERVER                                |
|  - Router / Static Asset Server (dist/)                               |
|  - API Layer (/api/products.php, /api/orders.php, /api/upload.php)    |
|  - Admin Back-Office Portal (/admin/cms.php, /admin/crm.php)           |
|  - Automated WhatsApp Notification Service                            |
+---------+-------------------------++------------------------+----------+
          | S3-Compatible Uploads   || PDO DB Queries        | Trigger Msg
          \/                        \/                       \/
+-------------------+   +-----------------------+   +-------------------+
|  CLOUDFLARE R2    |   |     MYSQL DATABASE    |   |  ADMIN WHATSAPP   |
|  Object Storage   |   |  - products           |   |   RECEIVER        |
|  (Media / CDN)    |   |  - orders / items     |   | (Instant Alert)   |
+-------------------+   +-----------------------+   +-------------------+
```

### **Database Schema Specification (MySQL)**
* `categories`: `id`, `name`, `slug`, `display_order`, `created_at`
* `products`: `id`, `category_id`, `name`, `description`, `price_1lb`, `price_2lb`, `image_url`, `is_available`, `created_at`
* `gallery`: `id`, `title`, `image_url`, `created_at`
* `orders`: `id`, `order_number` (e.g. `#RC-1001`), `customer_name`, `customer_phone`, `fulfillment_type` (`pickup`/`delivery`), `delivery_address`, `fulfillment_date`, `fulfillment_time`, `name_on_cake`, `special_instructions`, `total_amount`, `status` (`pending`/`completed`/`cancelled`), `created_at`
* `order_items`: `id`, `order_id`, `product_name`, `size`, `quantity`, `price`, `custom_image_url`, `item_notes`
* `cms_settings`: `setting_key`, `setting_value` (e.g., banner text, shop lat/lng, whatsapp_number)
* `admin_users`: `id`, `username`, `password_hash`, `created_at`

---

## **Features Not Included in Version 2 (Out of Scope)**

* **Embedded Online Payment Gateways:** No automated Stripe, Razorpay, or credit card processing on the website (all payments handled offline via WhatsApp interaction with admin).
* **Customer Account System:** No customer registration, login, or password management required (checkout remains frictionless guest checkout).
* **Live Customer Order Tracking Portal:** Customers do not have a self-serve order tracking webpage; order updates are obtained by contacting the admin on WhatsApp.
* **Automated Customer SMS Gateway:** SMS OTPs or SMS status updates are not included; all customer communications rely on WhatsApp.
* **Multi-Store / Multi-Vendor Multi-Tenancy:** Single bakery, single admin operational scope.
