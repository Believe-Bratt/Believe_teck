<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../config/config.php';
require_once __DIR__ . '/..//classes/Database.php';
require_once __DIR__ .'/../classes/Admin.php';
require_once __DIR__ .'/../includes/csrf.php';
require_once __DIR__ .'/../includes/validation.php';
require_once __DIR__ .'/../includes/logging.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            // Debug: Log POST data
            error_log('POST data received: ' . print_r($_POST, true));
            
            // Validate CSRF token
        
            switch ($_POST['action']) {
                case 'add_service':
                    // Debug: Log service data
                    error_log('Adding service: ' . print_r($_POST, true));
                    
                    // Validate required fields
                    $title = validateRequired($_POST['title'], 'Service Title');
                    $description = validateRequired($_POST['description'], 'Service Description');
                    $icon = validateRequired($_POST['icon'], 'Icon Class');
                    
                    // Validate lengths
                    validateLength($title, 'Service Title', 3, 100);
                    validateLength($description, 'Service Description', 10);
                    validateLength($icon, 'Icon Class', 3, 50);
                    
                    // Add service
                    $result = $admin->addService($title, $description, $icon);
                    
                    // Debug: Log result
                    error_log('Add service result: ' . ($result ? 'success' : 'failed'));
                    
                    // Log activity
                    logAdminAction('Add Service', ['title' => $title]);
                    logFormSubmission('Add Service Form', $_POST);
                    
                    $success_message = "Service added successfully!";
                    break;

                case 'update_service':
                    // Debug: Log service data
                    error_log('Updating service: ' . print_r($_POST, true));
                    
                    // Validate required fields
                    $id = validateRequired($_POST['id'], 'Service ID');
                    $title = validateRequired($_POST['title'], 'Service Title');
                    $description = validateRequired($_POST['description'], 'Service Description');
                    $icon = validateRequired($_POST['icon'], 'Icon Class');
                    
                    // Validate lengths
                    validateLength($title, 'Service Title', 3, 100);
                    validateLength($description, 'Service Description', 10);
                    validateLength($icon, 'Icon Class', 3, 50);
                    
                    // Update service
                    $result = $admin->updateService($id, $title, $description, $icon);
                    
                    // Debug: Log result
                    error_log('Update service result: ' . ($result ? 'success' : 'failed'));
                    
                    // Log activity
                    logAdminAction('Update Service', ['id' => $id, 'title' => $title]);
                    logFormSubmission('Update Service Form', $_POST);
                    
                    $success_message = "Service updated successfully!";
                    break;

                case 'delete_service':
                    // Debug: Log service data
                    error_log('Deleting service: ' . print_r($_POST, true));
                    
                    // Validate required fields
                    $id = validateRequired($_POST['id'], 'Service ID');
                    
                    // Delete service
                    $result = $admin->deleteService($id);
                    
                    // Debug: Log result
                    error_log('Delete service result: ' . ($result ? 'success' : 'failed'));
                    
                    // Log activity
                    logAdminAction('Delete Service', ['id' => $id]);
                    logFormSubmission('Delete Service Form', $_POST);
                    
                    $success_message = "Service deleted successfully!";
                    break;
            }
        } catch (Exception $e) {
            // Debug: Log error
            error_log('Error in service action: ' . $e->getMessage());
            logError($e->getMessage(), ['action' => $_POST['action']]);
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch services
try {
    $services = $admin->getServices();
    // Debug: Log services count
    error_log('Services fetched: ' . count($services));
} catch (Exception $e) {
    error_log('Error fetching services: ' . $e->getMessage());
    $error_message = "Error fetching services: " . $e->getMessage();
    $services = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - Believe Teckk</title>
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
            <h2 class="text-2xl font-bold">Manage Services</h2>
            <div class="flex items-center">
                <span class="text-gray-600 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="profile.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-user-circle text-2xl"></i>
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Service Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Add New Service</h3>
            <form method="POST" action="" id="addServiceForm">
                <input type="hidden" name="action" value="add_service">
               
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                            Service Title
                        </label>
                        <input type="text" id="title" name="title" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="icon">
                            Icon Class (Font Awesome)
                        </label>
                        <input type="text" id="icon" name="icon" required
                               placeholder="e.g., fas fa-code"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Service Description
                    </label>
                    <textarea id="description" name="description" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Add Service
                    </button>
                </div>
            </form>
        </div>

        <!-- Services List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Icon</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($service['title']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    <?php echo htmlspecialchars($service['description']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    Edit
                                </button>
                                <button onclick="confirmDelete(<?php echo $service['id']; ?>)"
                                        class="text-red-600 hover:text-red-900">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Service Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Service</h2>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="" id="editServiceForm">
                <input type="hidden" name="action" value="update_service">
                <input type="hidden" name="id" id="edit_id">
               
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_title">
                            Service Title
                        </label>
                        <input type="text" id="edit_title" name="title" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_icon">
                            Icon Class (Font Awesome)
                        </label>
                        <input type="text" id="edit_icon" name="icon" required
                               placeholder="e.g., fas fa-code"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_description">
                        Service Description
                    </label>
                    <textarea id="edit_description" name="description" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()"
                            class="text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Service
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        

        function editService(service) {
            document.getElementById('edit_id').value = service.id;
            document.getElementById('edit_title').value = service.title;
            document.getElementById('edit_icon').value = service.icon;
            
            
            
            // Show modal
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('flex');
            document.getElementById('editModal').classList.add('hidden');
            
            // Reset form
            document.getElementById('editServiceForm').reset();
            document.getElementById('edit_id').value = '';
            
            
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Handle add service form submission
        document.getElementById('addServiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Submit the form
            this.submit();
        });

        // Handle edit service form submission
        document.getElementById('editServiceForm').addEventListener('submit', function(e) {
            e.preventDefault();
        
    
            // Submit the form
            this.submit();
        });

        // Handle delete service confirmation
        function confirmDelete(serviceId) {
            if (confirm('Are you sure you want to delete this service?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_service">
                    <input type="hidden" name="id" value="${serviceId}">
                    ${document.querySelector('input[name="csrf_token"]').outerHTML}
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 