# Raj Confections 🎂

A modern e-commerce catalog, custom cake builder, and order management platform built with a React + Vite frontend and a lightweight PHP backend server connected to MariaDB / MySQL (`raj-confections-db`).

---

## 📁 Repository Structure

```
.
├── docs/               # Product Requirements Document (PRD) & specification files
├── server/             # PHP REST API server connected to MariaDB/MySQL
│   ├── config/         # Database connection manager & environment loader
│   ├── public/         # API entry point (index.php)
│   ├── data/           # JSON databases (products.json, orders.json)
│   ├── uploads/        # Custom cake reference images upload store
│   ├── schema.sql      # MariaDB / MySQL database schema setup script
│   ├── seed.php        # Database seeding CLI script
│   └── router.php      # Router script for PHP built-in web server
├── website/            # React 19 + Vite + Tailwind CSS frontend web application
│   ├── src/            # Components, pages, stores, and assets
│   └── package.json    # Frontend dependencies and scripts
├── GEMINI.md           # Project assistant workspace rules
└── README.md           # Master project documentation
```

---

## ⚡ Prerequisites

Before running the project locally, ensure you have:

- **Node.js**: v18.0.0 or higher
- **Package Manager**: `pnpm` (recommended) or `npm`
- **PHP**: v7.4 or v8.x CLI (`php`)
- **Database**: MariaDB or MySQL server

---

## 🗄️ Database Setup (`raj-confections-db`)

1. **Create Database & Tables**:
   ```bash
   mysql -u root -p < server/schema.sql
   ```

2. **Configure Environment Variables (Optional)**:
   By default, the server connects to `127.0.0.1:3306` with database name `raj-confections-db` and username `root`. You can override these defaults:
   ```bash
   export DB_HOST=127.0.0.1
   export DB_NAME=raj-confections-db
   export DB_USER=root
   export DB_PASS=your_password
   ```

3. **Seed Catalog Data**:
   Populate the `products` table from `data/products.json`:
   ```bash
   php server/seed.php
   ```

---

## 🚀 How to Start the Server & Web Application

### Step 1: Start the PHP Backend Server

1. Navigate to the project root directory:
   ```bash
   cd /home/aditya/Documents/Projects/Apple-Internship
   ```

2. Start the PHP built-in web server on **port 8000**:
   ```bash
   php -S localhost:8000 server/router.php
   ```

3. **Verify Server Uptime & DB Connection**:
   Open `http://localhost:8000/api/health` or run:
   ```bash
   curl http://localhost:8000/api/health
   ```
   *Response when connected*:
   `{"status":"ok","app":"Raj Confections API","database":"connected (MariaDB/MySQL)"}`

---

### Step 2: Start the Frontend Website

1. Open a **new terminal window** and navigate to `website`:
   ```bash
   cd /home/aditya/Documents/Projects/Apple-Internship/website
   ```

2. Install dependencies & start dev server:
   ```bash
   pnpm install
   pnpm dev
   ```

3. Open `http://localhost:5173` in your browser.

---

## 📡 Backend API Reference (`http://localhost:8000`)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/health` | Diagnostic check verifying server status and database connectivity |
| `GET` | `/api/products` | Retrieves products catalog from MariaDB/MySQL `products` table |
| `POST` | `/api/orders` | Inserts order into `orders` and `order_items` tables in MariaDB/MySQL |
| `POST` | `/api/upload` | Uploads reference image for custom cake builder |
| `GET` | `/uploads/{filename}` | Serves uploaded custom cake reference image assets |
