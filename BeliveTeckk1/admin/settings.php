<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../includes/csrf.php';   
require_once __DIR__ .'/../includes/validation.php';
require_once __DIR__ .'/../includes/logging.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        validateCSRFToken($_POST['csrf_token'] ?? '');
        
        $settings = [
            'site_name' => validateRequired($_POST['site_name'], 'Site Name'),
            'site_description' => validateRequired($_POST['site_description'], 'Site Description'),
            'contact_email' => validateEmail($_POST['contact_email']),
            'contact_phone' => validateRequired($_POST['contact_phone'], 'Contact Phone'),
            'contact_address' => validateRequired($_POST['contact_address'], 'Contact Address'),
            'working_hours' => validateRequired($_POST['working_hours'], 'Working Hours'),
            'social_facebook' => validateURL($_POST['social_facebook'], 'Facebook URL'),
            'social_twitter' => validateURL($_POST['social_twitter'], 'Twitter URL'),
            'social_linkedin' => validateURL($_POST['social_linkedin'], 'LinkedIn URL'),
            'google_analytics_id' => $_POST['google_analytics_id'] ?? '',
            'recaptcha_site_key' => $_POST['recaptcha_site_key'] ?? '',
            'recaptcha_secret_key' => $_POST['recaptcha_secret_key'] ?? '',
            'tinymce_api_key' => $_POST['tinymce_api_key'] ?? ''
        ];

        // Update settings
        $admin->updateSettings($settings);
        
        // Log activity
        logAdminAction('Update Settings', ['settings_updated' => array_keys($settings)]);
        
        $success_message = "Settings updated successfully!";
    } catch (Exception $e) {
        logError($e->getMessage(), ['action' => 'update_settings']);
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch current settings
$settings = $admin->getSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Believe Teckk Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Settings</h2>
            <div class="flex items-center">
                <span class="text-gray-600 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="profile.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-user-circle text-2xl"></i>
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" class="space-y-6">
                <?php echo getCSRFTokenField(); ?>
                
                <!-- General Settings -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">General Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site Name</label>
                            <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site Description</label>
                            <textarea name="site_description" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Contact Address</label>
                            <textarea name="contact_address" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Working Hours</label>
                            <input type="text" name="working_hours" value="<?php echo htmlspecialchars($settings['working_hours'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                </div>

                <!-- Social Media -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Social Media</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Facebook URL</label>
                            <input type="url" name="social_facebook" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Twitter URL</label>
                            <input type="url" name="social_twitter" value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">LinkedIn URL</label>
                            <input type="url" name="social_linkedin" value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                    </div>
                </div>

                <!-- Integration Settings -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Integration Settings</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Google Analytics ID</label>
                            <input type="text" name="google_analytics_id" value="<?php echo htmlspecialchars($settings['google_analytics_id'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">reCAPTCHA Site Key</label>
                            <input type="text" name="recaptcha_site_key" value="<?php echo htmlspecialchars($settings['recaptcha_site_key'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">reCAPTCHA Secret Key</label>
                            <input type="password" name="recaptcha_secret_key" value="<?php echo htmlspecialchars($settings['recaptcha_secret_key'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">TinyMCE API Key</label>
                            <input type="text" name="tinymce_api_key" value="<?php echo htmlspecialchars($settings['tinymce_api_key'] ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 