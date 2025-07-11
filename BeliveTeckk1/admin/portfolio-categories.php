<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
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
            // Validate CSRF token
            validateCSRFToken($_POST['csrf_token'] ?? '');
            
            switch ($_POST['action']) {
                case 'add_category':
                    // Validate required fields
                    $name = validateRequired($_POST['name'], 'Category Name');
                    $description = validateRequired($_POST['description'], 'Description');
                    
                    // Validate lengths
                    validateLength($name, 'Category Name', 3, 100);
                    validateLength($description, 'Description', 10);
                    
                    // Add category
                    $result = $admin->addPortfolioCategory($name, $description);
                    
                    if ($result) {
                        // Log activity
                        logAdminAction('Add Portfolio Category', ['name' => $name]);
                        logFormSubmission('Add Portfolio Category Form', $_POST);
                        
                        $success_message = "Portfolio category added successfully!";
                    } else {
                        throw new Exception("Failed to add portfolio category");
                    }
                    break;

                case 'update_category':
                    // Validate required fields
                    $id = validateRequired($_POST['id'], 'Category ID');
                    $name = validateRequired($_POST['name'], 'Category Name');
                    $description = validateRequired($_POST['description'], 'Description');
                    
                    // Validate lengths
                    validateLength($name, 'Category Name', 3, 100);
                    validateLength($description, 'Description', 10);
                    
                    // Update category
                    $result = $admin->updatePortfolioCategory($id, $name, $description);
                    
                    if ($result) {
                        // Log activity
                        logAdminAction('Update Portfolio Category', ['id' => $id, 'name' => $name]);
                        logFormSubmission('Update Portfolio Category Form', $_POST);
                        
                        $success_message = "Portfolio category updated successfully!";
                    } else {
                        throw new Exception("Failed to update portfolio category");
                    }
                    break;

                case 'delete_category':
                    // Validate required fields
                    $id = validateRequired($_POST['id'], 'Category ID');
                    
                    // Delete category
                    $result = $admin->deletePortfolioCategory($id);
                    
                    if ($result) {
                        // Log activity
                        logAdminAction('Delete Portfolio Category', ['id' => $id]);
                        logFormSubmission('Delete Portfolio Category Form', $_POST);
                        
                        $success_message = "Portfolio category deleted successfully!";
                    } else {
                        throw new Exception("Failed to delete portfolio category");
                    }
                    break;
            }
        } catch (Exception $e) {
            error_log('Error in portfolio category action: ' . $e->getMessage());
            logError($e->getMessage(), ['action' => $_POST['action']]);
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Fetch portfolio categories
try {
    $categories = $admin->getPortfolioCategories();
    if (!$categories) {
        throw new Exception("Failed to fetch portfolio categories");
    }
} catch (Exception $e) {
    error_log('Error fetching portfolio categories: ' . $e->getMessage());
    $error_message = "Error fetching portfolio categories: " . $e->getMessage();
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Categories - Believe Teckk Admin</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
    <style>
        .sidebar {
            transition: all 0.3s ease;
        }
        .sidebar a {
            transition: all 0.2s ease;
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #4F46E5;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #4338CA;
        }
        .btn-danger {
            background-color: #EF4444;
            transition: all 0.2s ease;
        }
        .btn-danger:hover {
            background-color: #DC2626;
        }
        .modal {
            transition: all 0.3s ease;
        }
        .modal.show {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Portfolio Categories</h1>
                <button onclick="openAddModal()" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>Add New Category
                </button>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <!-- Categories Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $category): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden card">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($category['name']); ?></h3>
                                <div class="flex space-x-2">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($category)); ?>)" 
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="text-gray-600 mb-4">
                                <?php echo $category['description']; ?>
                            </div>
                            <div class="flex justify-between items-center text-sm text-gray-500">
                                <span>Created: <?php echo date('M d, Y', strtotime($category['created_at'])); ?></span>
                                <span>Updated: <?php echo date('M d, Y', strtotime($category['updated_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div id="categoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium" id="modalTitle">Add New Category</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="categoryForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_category">
                <input type="hidden" name="id" id="edit_id">

                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" required></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Category';
        document.getElementById('categoryForm').reset();
        document.getElementById('edit_id').value = '';
        document.getElementById('categoryForm').action.value = 'add_category';
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function openEditModal(category) {
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('edit_id').value = category.id;
        document.getElementById('name').value = category.name;
        document.getElementById('description').value = category.description;
        document.getElementById('categoryForm').action.value = 'update_category';
        document.getElementById('categoryModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('categoryModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('categoryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html> 