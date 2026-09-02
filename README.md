# Raj Confections 🎂

A modern e-commerce catalog, custom cake builder, and order management platform built with a React + Vite frontend and a lightweight PHP backend server.

---

## 📁 Repository Structure

```
.
├── docs/               # Product Requirements Document (PRD) & specification files
├── server/             # PHP REST API server (catalog, orders, image uploads)
│   ├── public/         # API entry point (index.php)
│   ├── data/           # JSON databases (products.json, orders.json)
│   ├── uploads/        # Custom cake reference images upload store
│   └── router.php      # Router script for PHP built-in web server
├── website/            # React 19 + Vite + Tailwind CSS frontend web application
│   ├── src/            # Components, pages, stores, and assets
│   └── package.json    # Frontend dependencies and scripts
├── GEMINI.md           # Project assistant workspace rules
└── README.md           # Master project documentation
```

---

## ⚡ Prerequisites

Before running the project locally, ensure you have the following installed on your machine:

- **Node.js**: v18.0.0 or higher
- **Package Manager**: `pnpm` (recommended) or `npm`
- **PHP**: v7.4 or v8.x CLI (`php`)

---

## 🚀 How to Start the Server & Web Application

To run the application locally, you will need to start both the **PHP Backend Server** and the **Vite Frontend Development Server**.

### Step 1: Start the PHP Backend Server

1. Open your terminal and navigate to the project root directory:
   ```bash
   cd /home/aditya/Documents/Projects/Apple-Internship
   ```

2. Start the PHP built-in web server on **port 8000**:
   ```bash
   php -S localhost:8000 server/router.php
   ```

3. **Verify Server Status**:
   Open `http://localhost:8000/api/health` in your browser or run:
   ```bash
   curl http://localhost:8000/api/health
   ```
   You should see a `{"status": "ok"}` response.

---

### Step 2: Start the Frontend Website

1. Open a **new terminal window/tab** and navigate to the `website` folder:
   ```bash
   cd /home/aditya/Documents/Projects/Apple-Internship/website
   ```

2. Install the project dependencies (if not already installed):
   ```bash
   pnpm install
   # or
   npm install
   ```

3. Start the Vite development server:
   ```bash
   pnpm dev
   # or
   npm run dev
   ```

4. **Access the Website**:
   Open `http://localhost:5173` in your web browser.

---

## 📡 Backend API Reference (`http://localhost:8000`)

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/health` | Diagnostic check to verify PHP server uptime |
| `GET` | `/api/products` | Retrieves standard cake catalog & birthday add-ons |
| `POST` | `/api/orders` | Submits customer order, assigns ID (`RC-YYYYMMDD-XXXX`), logs to `data/orders.json` |
| `POST` | `/api/upload` | Uploads reference image for custom cake builder (multipart form or Base64) |
| `GET` | `/uploads/{filename}` | Serves uploaded custom cake reference image assets |

---

## 🛠️ Build & Production Deployment

### Frontend Production Build
To create a optimized production build of the frontend website:
```bash
cd website
pnpm build
```
The output static files will be placed in `website/dist/`.

### Backend Production Setup
For production deployment, point your web server (Nginx, Apache, or Caddy) document root to the `server/public/` folder, ensuring all traffic routes through `server/public/index.php`.
