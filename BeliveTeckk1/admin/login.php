<?php
session_start();

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Content-Security-Policy: default-src 'self' https:; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:;");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");

// Check if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once('../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once('../classes/Admin.php');

$db = Database::getInstance();
$admin = new Admin($db);

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission. Please try again.";
    } else {
        try {
            // Get and sanitize inputs
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Please enter a valid email address.");
            }

            // Check for brute force attempts
            $ip = $_SERVER['REMOTE_ADDR'];
            $attempts = $admin->getLoginAttempts($ip, 300); // Last 5 minutes
            if ($attempts >= 5) {
                throw new Exception("Too many login attempts. Please try again later.");
            }

            // Attempt login
            $user = $admin->login($email, $password);
            if ($user && isset($user['id'])) {
                // Reset login attempts on success
                $admin->resetLoginAttempts($ip);

                // Set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['last_activity'] = time();

                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days
                    setcookie('remember_token', $token, [
                        'expires' => $expires,
                        'path' => '/',
                        'domain' => '',
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                    $admin->saveRememberToken($user['id'], $token, $expires);
                }

                // Log successful login
                error_log("Admin login successful: $email from IP: $ip");

                header('Location: dashboard.php');
                exit;
            } else {
                // Increment failed login attempts
                $admin->incrementLoginAttempts($ip);
                throw new Exception("Invalid email or password");
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            error_log("Admin login failed: " . $e->getMessage() . " from IP: " . $_SERVER['REMOTE_ADDR']);
        }
    }
}

// Set page title
$pageTitle = "Admin Login - Believe Teckk";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="Admin login page for Believe Teckk website management.">
    
    <!-- Preload critical resources -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" as="style">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style">
    
    <!-- Stylesheets -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Admin Login</h1>
                <p class="text-gray-600 mt-2">Believe Teckk Administration</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div>
                    <label for="email" class="block text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-envelope text-gray-400" aria-hidden="true"></i>
                        </span>
                        <input type="email" id="email" name="email" required
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="Enter your email"
                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                            title="Please enter a valid email address"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            autocomplete="email">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="fas fa-lock text-gray-400" aria-hidden="true"></i>
                        </span>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="Enter your password"
                            minlength="8"
                            title="Password must be at least 8 characters long"
                            autocomplete="current-password">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember"
                            class="h-4 w-4 text-red-500 focus:ring-red-500 border-gray-300 rounded"
                            <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                        <label for="remember" class="ml-2 block text-gray-700">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="text-red-500 hover:text-red-600">Forgot password?</a>
                </div>

                <button type="submit"
                    class="w-full bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition duration-300">
                    Sign In
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="../index.php" class="text-gray-600 hover:text-red-500">
                    <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i>Back to Website
                </a>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return;
            }

            // Email validation
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }

            // Password validation
            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long');
                return;
            }
        });

        // Prevent form resubmission on page refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>
</html> 