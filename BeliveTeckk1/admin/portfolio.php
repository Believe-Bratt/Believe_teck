<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ .'/../config/config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ .'/../classes/Portfolio.php';
require_once __DIR__ .'/../classes/Admin.php';

$db = Database::getInstance();
$portfolio = new Portfolio($db);
$admin = new Admin($db);

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'create':
                    if (empty($_POST['title']) || empty($_POST['description'])) {
                        throw new Exception("Title and description are required.");
                    }
                    
                    $data = [
                        'title' => trim($_POST['title']),
                        'description' => trim($_POST['description']),
                        'content' => trim($_POST['content']),
                        'category_id' => $_POST['category_id'],
                        'client' => isset($_POST['client']) ? trim($_POST['client']) : null,
                        'project_date' => !empty($_POST['project_date']) ? $_POST['project_date'] : null,
                        'project_url' => isset($_POST['project_url']) ? trim($_POST['project_url']) : null,
                        'technologies' => isset($_POST['technologies']) ? trim($_POST['technologies']) : null,
                        'testimonial' => isset($_POST['testimonial']) ? trim($_POST['testimonial']) : null,
                        'testimonial_author' => isset($_POST['testimonial_author']) ? trim($_POST['testimonial_author']) : null,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0,
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                    ];
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/portfolio/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_extension, $allowed_extensions)) {
                            $file_name = uniqid() . '.' . $file_extension;
                            $target_path = $upload_dir . $file_name;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                $data['image'] = 'uploads/portfolio/' . $file_name;
                            } else {
                                throw new Exception("Error uploading image.");
                            }
                        } else {
                            throw new Exception("Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.");
                        }
                    }
                    
                    if ($portfolio->createItem($data)) {
                        $success_message = "Portfolio item created successfully.";
                    } else {
                        throw new Exception("Error creating portfolio item.");
                    }
                    break;
                    
                case 'update':
                    if (empty($_POST['id']) || empty($_POST['title']) || empty($_POST['description'])) {
                        throw new Exception("ID, title and description are required.");
                    }
                    
                    $id = $_POST['id'];
                    $data = [
                        'title' => trim($_POST['title']),
                        'description' => trim($_POST['description']),
                        'content' => trim($_POST['content']),
                        'category_id' => $_POST['category_id'],
                        'client' => isset($_POST['client']) ? trim($_POST['client']) : null,
                        'project_date' => !empty($_POST['project_date']) ? $_POST['project_date'] : null,
                        'project_url' => isset($_POST['project_url']) ? trim($_POST['project_url']) : null,
                        'technologies' => isset($_POST['technologies']) ? trim($_POST['technologies']) : null,
                        'testimonial' => isset($_POST['testimonial']) ? trim($_POST['testimonial']) : null,
                        'testimonial_author' => isset($_POST['testimonial_author']) ? trim($_POST['testimonial_author']) : null,
                        'is_active' => isset($_POST['is_active']) ? 1 : 0,
                        'is_featured' => isset($_POST['is_featured']) ? 1 : 0
                    ];
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/portfolio/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                        
                        if (in_array($file_extension, $allowed_extensions)) {
                            $file_name = uniqid() . '.' . $file_extension;
                            $target_path = $upload_dir . $file_name;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                                $data['image'] = 'uploads/portfolio/' . $file_name;
                            } else {
                                throw new Exception("Error uploading image.");
                            }
                        } else {
                            throw new Exception("Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.");
                        }
                    }
                    
                    if ($portfolio->updateItem($id, $data)) {
                        $success_message = "Portfolio item updated successfully.";
                    } else {
                        throw new Exception("Error updating portfolio item.");
                    }
                    break;
                    
                case 'delete':
                    if (empty($_POST['id'])) {
                        throw new Exception("Portfolio item ID is required.");
                    }
                    
                    if ($portfolio->deleteItem($_POST['id'])) {
                        $success_message = "Portfolio item deleted successfully.";
                    } else {
                        throw new Exception("Error deleting portfolio item.");
                    }
                    break;
                    
                case 'toggle_active':
                    if (empty($_POST['id'])) {
                        throw new Exception("Portfolio item ID is required.");
                    }
                    
                    if ($portfolio->toggleActive($_POST['id'])) {
                        $success_message = "Portfolio item status updated successfully.";
                    } else {
                        throw new Exception("Error updating portfolio item status.");
                    }
                    break;
                    
                case 'toggle_featured':
                    if (empty($_POST['id'])) {
                        throw new Exception("Portfolio item ID is required.");
                    }
                    
                    if ($portfolio->toggleFeatured($_POST['id'])) {
                        $success_message = "Portfolio item featured status updated successfully.";
                    } else {
                        throw new Exception("Error updating portfolio item featured status.");
                    }
                    break;
            }
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }
    }
}

// Get all portfolio items and categories
$items = $portfolio->getAllItems();
$categories = $portfolio->getCategories();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Portfolio - Believe Teckk</title>
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
            <h2 class="text-2xl font-bold">Manage Portfolio</h2>
            <div class="flex items-center">
                <span class="text-gray-600 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="profile.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-user-circle text-2xl"></i>
                </a>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Add New Portfolio Item Button -->
        <div class="mb-4">
            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" 
                    onclick="document.getElementById('portfolioModal').classList.remove('hidden')">
                Add New Portfolio Item
            </button>
        </div>

        <!-- Portfolio Items Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <?php if (!empty($item['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                             class="w-full h-48 object-cover rounded-lg mb-4">
                    <?php endif; ?>
                    
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                    
                    <div class="flex justify-between items-center">
                        <div class="flex space-x-2">
                            <button onclick="editPortfolioItem(<?php echo htmlspecialchars(json_encode($item)); ?>)"
                                    class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this item?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        
                        <div class="flex space-x-2">
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="action" value="toggle_active">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="text-sm <?php echo $item['is_active'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                </button>
                            </form>
                            <form method="POST" action="" class="inline">
                                <input type="hidden" name="action" value="toggle_featured">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="text-sm <?php echo $item['is_featured'] ? 'text-yellow-600' : 'text-gray-600'; ?>">
                                    <?php echo $item['is_featured'] ? 'Featured' : 'Not Featured'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Portfolio Modal -->
    <div id="portfolioModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Portfolio Item</h2>
                <button onclick="closePortfolioModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form method="POST" action="" enctype="multipart/form-data" id="portfolioForm">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                            Title
                        </label>
                        <input type="text" id="title" name="title" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="category_id">
                            Category
                        </label>
                        <select id="category_id" name="category_id" required
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Description
                    </label>
                    <textarea id="description" name="description" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                        Content
                    </label>
                    <textarea id="content" name="content"
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="client">
                            Client
                        </label>
                        <input type="text" id="client" name="client"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label for="project_date" class="block text-sm font-medium text-gray-700 mb-2">Project Date</label>
                        <input type="date" id="project_date" name="project_date"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="project_url">
                            Project URL
                        </label>
                        <input type="url" id="project_url" name="project_url"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="technologies">
                            Technologies
                        </label>
                        <input type="text" id="technologies" name="technologies"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="testimonial">
                            Testimonial
                        </label>
                        <textarea id="testimonial" name="testimonial"
                                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="testimonial_author">
                            Testimonial Author
                        </label>
                        <input type="text" id="testimonial_author" name="testimonial_author"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                        Image
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-sm text-gray-500 mt-1">Only JPG, JPEG, PNG & GIF files are allowed</p>
                </div>

                <div class="mt-6 flex items-center space-x-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" class="form-checkbox h-4 w-4 text-blue-600">
                        <span class="ml-2 text-gray-700">Active</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" class="form-checkbox h-4 w-4 text-blue-600">
                        <span class="ml-2 text-gray-700">Featured</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="closePortfolioModal()"
                            class="text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Save Portfolio Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editPortfolioItem(item) {
            document.getElementById('edit_id').value = item.id;
            document.getElementById('title').value = item.title;
            document.getElementById('category_id').value = item.category_id;
            document.getElementById('description').value = item.description;
            document.getElementById('content').value = item.content;
            document.getElementById('client').value = item.client;
            document.getElementById('project_date').value = item.project_date;
            document.getElementById('project_url').value = item.project_url;
            document.getElementById('technologies').value = item.technologies;
            document.getElementById('testimonial').value = item.testimonial;
            document.getElementById('testimonial_author').value = item.testimonial_author;
            
           
           
            
            // Set checkboxes
            document.querySelector('input[name="is_active"]').checked = item.is_active == 1;
            document.querySelector('input[name="is_featured"]').checked = item.is_featured == 1;
            
            // Change form action
            document.getElementById('portfolioForm').querySelector('input[name="action"]').value = 'update';
            
            // Show modal
            document.getElementById('portfolioModal').classList.remove('hidden');
            document.getElementById('portfolioModal').classList.add('flex');
        }

        function closePortfolioModal() {
            document.getElementById('portfolioModal').classList.remove('flex');
            document.getElementById('portfolioModal').classList.add('hidden');
            
            // Reset form
            document.getElementById('portfolioForm').reset();
            document.getElementById('edit_id').value = '';
            document.getElementById('portfolioForm').querySelector('input[name="action"]').value = 'create';
            
            // Reset TinyMCE content
            if (tinymce.get('content')) {
                tinymce.get('content').setContent('');
            }
        }

        // Close modal when clicking outside
        document.getElementById('portfolioModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePortfolioModal();
            }
        });

        // Form submission handling
        document.getElementById('portfolioForm').addEventListener('submit', function(e) {
            // Update textarea with TinyMCE content before submitting
            if (tinymce.get('content')) {
                document.getElementById('content').value = tinymce.get('content').getContent();
            }
        });
    </script>
</body>
</html> 