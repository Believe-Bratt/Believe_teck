<?php
require_once  __DIR__ . '/config/config.php';
require_once  __DIR__ . '/classes/Newsletter.php';

$message = '';
$error = '';

if (isset($_GET['email'])) {
    $email = filter_var($_GET['email'], FILTER_VALIDATE_EMAIL);
    
    if ($email) {
        $newsletter = new Newsletter($db);
        if ($newsletter->unsubscribe($email)) {
            $message = 'You have been successfully unsubscribed from our newsletter.';
        } else {
            $error = 'An error occurred while unsubscribing. Please try again later.';
        }
    } else {
        $error = 'Invalid email address.';
    }
} else {
    $error = 'No email address provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Newsletter Unsubscribe</h1>
                <p class="text-gray-600">Manage your subscription preferences</p>
            </div>
            
            <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-6">
                <a href="<?php echo SITE_URL; ?>" class="text-red-600 hover:text-red-800">
                    Return to Homepage
                </a>
            </div>
        </div>
    </div>
</body>
</html> 