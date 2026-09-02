# Raj Confections - PHP Backend Server

A lightweight, zero-dependency RESTful PHP backend server for catalog management, custom cake reference uploads, order storage, and health diagnostics.

---

## 📂 Project Structure

```
server/
├── public/
│   └── index.php          # Main REST API controller & routing entry point
├── data/
│   ├── products.json      # Product catalog dataset
│   └── orders.json        # Persistent order log dataset
├── uploads/               # Custom cake reference images upload store
│   └── .gitkeep
├── router.php             # Router script for PHP built-in web server
└── README.md              # Server documentation and API specifications
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
* **Description**: Verifies backend server health and environment status.
* **Sample Response**:
  ```json
  {
    "status": "ok",
    "app": "Raj Confections API",
    "version": "1.0.0",
    "timestamp": "2026-09-02 08:48:00 IST",
    "server": "PHP/8.x"
  }
  ```

---

### 2. Get Products Catalog
* **Endpoint**: `GET /api/products`
* **Description**: Fetches standard catalog cakes and birthday add-on products.
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
    "order": { ... }
  }
  ```

---

### 4. Custom Cake Image Upload
* **Endpoint**: `POST /api/upload`
* **Supported Formats**: `multipart/form-data` (`image` field) or Base64 payload in JSON.
* **Sample Response (201 Created)**:
  ```json
  {
    "success": true,
    "message": "Image uploaded successfully",
    "filename": "cake_20260902_084800_a1b2c3.png",
    "url": "/uploads/cake_20260902_084800_a1b2c3.png"
  }
  ```

---

### 5. Serve Uploaded Image Assets
* **Endpoint**: `GET /uploads/{filename}`
* **Description**: Serves stored reference images with proper `Content-Type` headers (`image/png`, `image/jpeg`, etc.).

---

## 🛠️ Testing Endpoints with curl

```bash
# Health Check
curl -X GET http://localhost:8000/api/health

# Get Products
curl -X GET http://localhost:8000/api/products

# Create Test Order
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{"items":[{"id":"vanilla-cake","name":"Vanilla Cake","quantity":1}],"total_amount":400}'
```

---

## 🌐 Integration with React / Vite Frontend

In your frontend application (e.g., `website/src/config.ts`), configure the API base URL:

```typescript
export const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000';
```
