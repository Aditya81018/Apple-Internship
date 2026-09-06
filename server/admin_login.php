<?php
require_once __DIR__ . '/config/auth.php';

$error = '';
$message = '';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logoutAdminSession();
    $message = "You have been logged out successfully.";
}

// Redirect if already logged in
if (isAdminLoggedIn() && !isset($_GET['action'])) {
    $redirect = $_GET['redirect'] ?? '/admin_products.php';
    header("Location: " . $redirect);
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? $_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($id) || empty($password)) {
        $error = "Please enter both Server ID and Password.";
    } elseif (verifyAdminCredentials($id, $password)) {
        loginAdminSession($id);
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '/admin_products.php';
        header("Location: " . $redirect);
        exit;
    } else {
        $error = "Invalid Server ID or Password. Please try again.";
    }
}

$redirectUri = htmlspecialchars($_GET['redirect'] ?? '/admin_products.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Raj Confections</title>
    <!-- Google Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius: 14px;
            --shadow-lg: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .lucide {
            vertical-align: middle;
            stroke-width: 2.2px;
        }

        .login-card {
            background: var(--card-bg);
            width: 100%;
            max-width: 400px;
            padding: 40px 32px;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-lg);
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: var(--primary);
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            border: 1px solid #bfdbfe;
        }

        .login-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .login-header p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            font-size: 14px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .footer-note {
            margin-top: 28px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="brand-icon">
            <i data-lucide="cake-slice" style="width:28px; height:28px;"></i>
        </div>
        <h1>Raj Confections</h1>
        <p>Fixed Server Credentials Authentication</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i data-lucide="alert-circle" style="width:18px; height:18px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success">
            <i data-lucide="check-circle-2" style="width:18px; height:18px;"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="admin_login.php?redirect=<?= $redirectUri ?>">
        <input type="hidden" name="redirect" value="<?= $redirectUri ?>">
        
        <div class="form-group">
            <label for="id">Server ID / Username</label>
            <input type="text" id="id" name="id" placeholder="Enter Server ID" required autofocus value="<?= htmlspecialchars($_POST['id'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter Server Password" required>
        </div>

        <button type="submit" class="btn-submit">
            <i data-lucide="log-in" style="width:18px; height:18px;"></i>
            <span>Sign In to Dashboard</span>
        </button>
    </form>

    <div class="footer-note">
        <i data-lucide="shield-check" style="width:14px; height:14px;"></i>
        <span>Protected Server Panel &bull; Fixed Auth Mode</span>
    </div>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
