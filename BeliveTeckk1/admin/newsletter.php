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
require_once __DIR__ .'/../classes/Newsletter.php';

$db = Database::getInstance();
$admin = new Admin($db);
$newsletter = new Newsletter($db);

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_subscriber':
                $email = $_POST['email'];
                $name = $_POST['name'];
                try {
                    if ($newsletter->subscribe($email, $name)) {
                        $success_message = "Subscriber added successfully!";
                    }
                } catch (PDOException $e) {
                    if ($e->getCode() == 23000) { // Duplicate entry
                        $error_message = "This email is already subscribed.";
                    } else {
                        $error_message = "Error adding subscriber.";
                    }
                }
                break;

            case 'update_status':
                $id = $_POST['subscriber_id'];
                $status = $_POST['status'];
                try {
                    if ($status === 'unsubscribed') {
                        $newsletter->unsubscribe($_POST['email']);
                        $success_message = "Subscriber status updated successfully!";
                    } else {
                        $newsletter->subscribe($_POST['email']);
                        $success_message = "Subscriber reactivated successfully!";
                    }
                } catch (Exception $e) {
                    $error_message = "Error updating subscriber status.";
                }
                break;

            case 'delete_subscriber':
                $id = $_POST['subscriber_id'];
                try {
                    if ($newsletter->delete($id)) {
                        $success_message = "Subscriber deleted successfully!";
                    } else {
                        $error_message = "Error deleting subscriber.";
                    }
                } catch (Exception $e) {
                    $error_message = "Error deleting subscriber.";
                }
                break;
        }
    }
}

// Get all subscribers
$subscribers = $newsletter->all();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Management - Believe Teckk</title>
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
            <h2 class="text-2xl font-bold">Newsletter Management</h2>
            <div class="flex items-center">
                <span class="text-gray-600 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="profile.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-user-circle text-2xl"></i>
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Add New Subscriber Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-lg font-semibold mb-4">Add New Subscriber</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_subscriber">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300">
                        Add Subscriber
                    </button>
                </div>
            </form>
        </div>

        <!-- Subscribers List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Newsletter Subscribers</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($subscriber['name'] ?? 'N/A'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($subscriber['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $subscriber['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo ucfirst($subscriber['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo date('M d, Y', strtotime($subscriber['subscribed_at'] ?? 'now')); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <form method="POST" class="inline-block mr-2">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                        <input type="hidden" name="email" value="<?php echo $subscriber['email']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $subscriber['status'] === 'active' ? 'unsubscribed' : 'active'; ?>">
                                        <button type="submit" class="text-blue-600 hover:text-blue-900">
                                            <?php echo $subscriber['status'] === 'active' ? 'Unsubscribe' : 'Reactivate'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this subscriber?');">
                                        <input type="hidden" name="action" value="delete_subscriber">
                                        <input type="hidden" name="subscriber_id" value="<?php echo $subscriber['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 