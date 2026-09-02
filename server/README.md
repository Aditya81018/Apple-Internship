# Raj Confections - PHP Backend Server

A lightweight RESTful PHP backend server connected to MariaDB / MySQL (`raj-confections-db`) for product catalog management, custom cake reference uploads, order storage, and health diagnostics.

---

## 📂 Project Structure

```
server/
├── config/
│   └── database.php       # PDO connection manager & environment loader
├── public/
│   └── index.php          # Main REST API controller & routing entry point
├── data/
│   ├── products.json      # Product catalog dataset (seed source & fallback)
│   └── orders.json        # Persistent order log dataset (fallback)
├── uploads/               # Custom cake reference images upload store
│   └── .gitkeep
├── schema.sql             # SQL schema creation script for MariaDB / MySQL
├── seed.php               # Seeding script to import products into MariaDB / MySQL
├── router.php             # Router script for PHP built-in web server
└── README.md              # Server documentation and API specifications
```

---

## 🗄️ Database Setup (`raj-confections-db`)

### Step 1: Create Database & Tables
Import `schema.sql` into MariaDB or MySQL:

```bash
mysql -u root -p < server/schema.sql
```

### Step 2: Configure Environment Variables (Optional)
By default, the server connects using:
- `DB_HOST`: `127.0.0.1`
- `DB_PORT`: `3306`
- `DB_NAME`: `raj-confections-db`
- `DB_USER`: `root`
- `DB_PASS`: `` (empty)

You can customize these by setting environment variables before starting the server:
```bash
export DB_HOST=127.0.0.1
export DB_NAME=raj-confections-db
export DB_USER=myuser
export DB_PASS=mypassword
```

### Step 3: Seed Catalog Data
Import initial product data from `data/products.json` into MariaDB/MySQL:

```bash
php server/seed.php
```

---

## 🚀 Quick Start (Running Locally)

To start the PHP backend server locally using PHP's built-in web server:

```bash
# Navigate to project root
cd /path/to/Apple-Internship

# Start the server on port 8000
php -S localhost:8000 server/router.php
```

The API will now be accessible at `http://localhost:8000`.

---

## 📡 API Endpoints Reference

### 1. Health Check
* **Endpoint**: `GET /api/health`
* **Sample Response**:
  ```json
  {
    "status": "ok",
    "app": "Raj Confections API",
    "version": "1.1.0",
    "database": "connected (MariaDB/MySQL)",
    "timestamp": "2026-09-02 09:12:00 IST",
    "server": "PHP/8.x"
  }
  ```

---

### 2. Get Products Catalog
* **Endpoint**: `GET /api/products`
* **Description**: Queries `products` table in MariaDB/MySQL (or JSON fallback).
* **Sample Response**:
  ```json
  [
    {
      "id": "vanilla-cake",
      "name": "Vanilla Cake",
      "price": 400,
      "image": "https://images.unsplash.com/...",
      "category": "cake",
      "sizes": ["1 lb", "2 lb"],
      "prices": {
        "1 lb": 400,
        "2 lb": 700
      }
    }
  ]
  ```

---

### 3. Create Order
* **Endpoint**: `POST /api/orders`
* **Headers**: `Content-Type: application/json`
* **Description**: Inserts order record into `orders` and line items into `order_items` in MariaDB/MySQL.
* **Sample Request Body**:
  ```json
  {
    "items": [
      {
        "id": "vanilla-cake",
        "name": "Vanilla Cake",
        "size": "1 lb",
        "quantity": 1,
        "price": 400
      }
    ],
    "total_amount": 400,
    "customer": {
      "name": "Rahul Sharma",
      "phone": "+919876543210"
    },
    "fulfillment": {
      "type": "delivery",
      "date": "2026-09-05",
      "time": "16:00",
      "address": "123 Park Street, City"
    },
    "customization": {
      "name_on_cake": "Happy Birthday Rahul",
      "special_notes": "Less sugar please"
    }
  }
  ```
* **Sample Response (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Order created successfully",
    "order_id": "RC-20260902-A1B2",
    "storage": "mariadb",
    "order": { ... }
  }
  ```

---

### 4. Custom Cake Image Upload
* **Endpoint**: `POST /api/upload`
* **Supported Formats**: `multipart/form-data` (`image` field) or Base64 payload in JSON.

---

### 5. Serve Uploaded Image Assets
* **Endpoint**: `GET /uploads/{filename}`
