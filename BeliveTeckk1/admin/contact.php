<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ .'/../classes/Admin.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_contact':
                $settings = [
                    'contact_title' => $_POST['contact_title'] ?? '',
                    'contact_subtitle' => $_POST['contact_subtitle'] ?? '',
                    'contact_description' => $_POST['contact_description'] ?? '',
                    'contact_email' => $_POST['contact_email'] ?? '',
                    'contact_phone' => $_POST['contact_phone'] ?? '',
                    'contact_address' => $_POST['contact_address'] ?? '',
                    'contact_map_embed' => $_POST['contact_map_embed'] ?? '',
                    'contact_form_title' => $_POST['contact_form_title'] ?? '',
                    'contact_form_description' => $_POST['contact_form_description'] ?? '',
                    'office_hours' => $_POST['office_hours'] ?? ''
                ];

                $sql = "UPDATE settings SET setting_value = ? WHERE setting_key = ?";
                $stmt = $db->prepare($sql);
                
                foreach ($settings as $key => $value) {
                    $stmt->execute([$value, $key]);
                }

                $success_message = "Contact page content updated successfully!";
                break;
        }
    }
}

// Fetch current settings
$settings = [];
$result = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'contact_%' OR setting_key = 'office_hours'")->fetchAll();
foreach ($result as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Contact Page - Believe Teckk</title>
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
            <h2 class="text-2xl font-bold">Manage Contact Page</h2>
            <div class="flex items-center">
                <span class="text-gray-600 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="profile.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-user-circle text-2xl"></i>
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="update_contact">
                
                <!-- Hero Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Hero Section</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Page Title</label>
                            <input type="text" name="contact_title" value="<?php echo htmlspecialchars($settings['contact_title'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subtitle</label>
                            <input type="text" name="contact_subtitle" value="<?php echo htmlspecialchars($settings['contact_subtitle'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="contact_description" name="contact_description" rows="4" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($settings['contact_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="contact_email" value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Office Address</label>
                            <textarea name="contact_address" rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($settings['contact_address'] ?? ''); ?></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Office Hours</label>
                            <textarea name="office_hours" rows="2" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($settings['office_hours'] ?? ''); ?></textarea>
                            <p class="mt-1 text-sm text-gray-500">Enter your office hours (e.g., "Monday - Friday: 9:00 AM - 6:00 PM")</p>
                        </div>
                    </div>
                </div>

                <!-- Map Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Google Maps</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Google Maps Embed Code</label>
                        <textarea name="contact_map_embed" rows="3" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($settings['contact_map_embed'] ?? ''); ?></textarea>
                        <p class="mt-1 text-sm text-gray-500">Paste your Google Maps embed code here</p>
                    </div>
                </div>

                <!-- Contact Form Section -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Form Settings</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Form Title</label>
                            <input type="text" name="contact_form_title" value="<?php echo htmlspecialchars($settings['contact_form_title'] ?? ''); ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Form Description</label>
                            <textarea id="contact_form_description" name="contact_form_description" rows="3" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($settings['contact_form_description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>