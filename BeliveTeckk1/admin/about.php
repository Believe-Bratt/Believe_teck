<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once('../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once('../classes/Admin.php');
require_once('../includes/csrf.php');
require_once('../includes/validation.php');
require_once('../includes/logging.php');

$db = Database::getInstance();
$admin = new Admin($db);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_about') {
    try {
        if (!validateCSRFToken($_POST['csrf_token'])) {
            throw new Exception('Invalid CSRF token');
        }

        // Validate required fields
        $required_fields = ['title', 'content', 'mission', 'vision', 'values'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Process founder image if uploaded
        $founder_image_path = null;
        if (!empty($_FILES['founder_image']['name'])) {
            $upload_dir = '../uploads/founder/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['founder_image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception('Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF');
            }

            $new_filename = 'founder_' . time() . '.' . $file_extension;
            $target_file = $upload_dir . $new_filename;

            if (!move_uploaded_file($_FILES['founder_image']['tmp_name'], $target_file)) {
                throw new Exception('Failed to upload founder image');
            }

            $founder_image_path = 'uploads/founder/' . $new_filename;
        }

        // Prepare data for update
        $data = [
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'mission' => $_POST['mission'],
            'vision' => $_POST['vision'],
            'values' => $_POST['values'],
            'founder_name' => $_POST['founder_name'] ?? null,
            'founder_position' => $_POST['founder_position'] ?? null,
            'founder_content' => $_POST['founder_content'] ?? null
        ];

        // Only update founder image if a new one was uploaded
        if ($founder_image_path) {
            $data['founder_image'] = $founder_image_path;
        }

        // Update the about page content
        $admin->updateAboutPage($data);
        
        $_SESSION['success_message'] = 'About page updated successfully';
        header('Location: about.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        header('Location: about.php');
        exit;
    }
}

// Fetch current about page content
try {
    $about_content = $admin->getAboutPage();
    if (!$about_content) {
        throw new Exception("Failed to fetch about page content");
    }
} catch (Exception $e) {
    error_log('Error fetching about content: ' . $e->getMessage());
    $error_message = "Error fetching about page content: " . $e->getMessage();
    $about_content = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage About Page - Believe Teckk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php 
    // Initialize TinyMCE for each textarea with different heights
    /*initTinyMCE('#content', 400); // Main content - taller
    initTinyMCE('#mission', 250); // Mission - medium
    initTinyMCE('#vision', 250);  // Vision - medium
    initTinyMCE('#values', 300);  // Values - medium*/
    ?>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>  
    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Manage About Page</h1>

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

            <form method="POST" class="bg-white shadow-lg rounded-lg p-6" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="update_about">

                <!-- Page Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo htmlspecialchars($about_content['title'] ?? ''); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Main Content -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Main Content</label>
                    <textarea id="content" name="content" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($about_content['content'] ?? ''); ?></textarea>
                </div>

                <!-- Mission Statement -->
                <div class="mb-6">
                    <label for="mission" class="block text-sm font-medium text-gray-700 mb-2">Mission Statement</label>
                    <textarea id="mission" name="mission" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($about_content['mission'] ?? ''); ?></textarea>
                </div>

                <!-- Vision Statement -->
                <div class="mb-6">
                    <label for="vision" class="block text-sm font-medium text-gray-700 mb-2">Vision Statement</label>
                    <textarea id="vision" name="vision" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($about_content['vision'] ?? ''); ?></textarea>
                </div>

                <!-- Core Values -->
                <div class="mb-6">
                    <label for="values" class="block text-sm font-medium text-gray-700 mb-2">Core Values</label>
                    <textarea id="values" name="values" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($about_content['values'] ?? ''); ?></textarea>
                </div>

                <!-- Founder Section -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">About the Founder</h3>
                    
                    <!-- Founder Name -->
                    <div class="mb-6">
                        <label for="founder_name" class="block text-sm font-medium text-gray-700 mb-2">Founder Name</label>
                        <input type="text" id="founder_name" name="founder_name"
                               value="<?php echo htmlspecialchars($about_content['founder_name'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Founder Position -->
                    <div class="mb-6">
                        <label for="founder_position" class="block text-sm font-medium text-gray-700 mb-2">Founder Position</label>
                        <input type="text" id="founder_position" name="founder_position"
                               value="<?php echo htmlspecialchars($about_content['founder_position'] ?? ''); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Founder Image -->
                    <div class="mb-6">
                        <label for="founder_image" class="block text-sm font-medium text-gray-700 mb-2">Founder Image</label>
                        <?php if (!empty($about_content['founder_image'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo htmlspecialchars($about_content['founder_image']); ?>" 
                                     alt="Current founder image" 
                                     class="w-32 h-32 object-cover rounded-lg">
                            </div>
                        <?php endif; ?>
                        <input type="file" id="founder_image" name="founder_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Founder Content -->
                    <div class="mb-6">
                        <label for="founder_content" class="block text-sm font-medium text-gray-700 mb-2">About the Founder</label>
                        <textarea id="founder_content" name="founder_content"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($about_content['founder_content'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Update About Page
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Additional JavaScript for handling form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TinyMCE for each textarea with specific heights
        tinymce.init({
            selector: '#content',
            height: 400,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | table | code',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        tinymce.init({
            selector: '#mission',
            height: 200,
            plugins: 'lists link code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        tinymce.init({
            selector: '#vision',
            height: 200,
            plugins: 'lists link code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        tinymce.init({
            selector: '#values',
            height: 300,
            plugins: 'lists link code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link | code',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        tinymce.init({
            selector: '#founder_content',
            height: 300,
            plugins: 'lists link image table code',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | table | code',
            setup: function(editor) {
                editor.on('change', function() {
                    editor.save();
                });
            }
        });

        // Ensure all TinyMCE editors save their content before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            tinymce.triggerSave();
        });
    });
    </script>
</body>
</html> 