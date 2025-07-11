<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../classes/Database.php';
require_once '../config/config.php';
require_once '../classes/Admin.php';
require_once '../classes/FileUploader.php';

$db = Database::getInstance();
$admin = new Admin($db);
$success_message = '';
$error_message = '';

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/founder';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_slug = $_POST['page_slug'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $mission = $_POST['mission'] ?? null;
    $vision = $_POST['vision'] ?? null;
    $values = $_POST['values'] ?? null;
    $founder_name = $_POST['founder_name'] ?? null;
    $founder_position = $_POST['founder_position'] ?? null;
    $founder_content = $_POST['founder_content'] ?? null;
    $meta_description = $_POST['meta_description'];
    $meta_keywords = $_POST['meta_keywords'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        // Handle founder image upload if present
        $founder_image = null;
        if (!empty($_FILES['founder_image']['name'])) {
            $fileUploader = new FileUploader();
            $upload_result = $fileUploader->uploadFile(
                $_FILES['founder_image'],
                $upload_dir,
                ['jpg', 'jpeg', 'png', 'webp'],
                5000000 // 5MB limit
            );
            
            if ($upload_result['success']) {
                $founder_image = 'uploads/founder/' . $upload_result['filename'];
                
                // Delete old image if exists
                $stmt = $db->prepare("SELECT founder_image FROM page_contents WHERE page_slug = ?");
                $stmt->execute([$page_slug]);
                $old_image = $stmt->fetch(PDO::FETCH_COLUMN);
                
                if ($old_image && file_exists("../{$old_image}")) {
                    unlink("../{$old_image}");
                }
            } else {
                $error_message = "Image upload failed: " . $upload_result['message'];
                throw new Exception($error_message);
            }
        }

        // Update page content
        $sql = "UPDATE page_contents SET 
                title = ?, 
                content = ?, 
                mission = ?,
                vision = ?,
                values = ?,
                founder_name = ?,
                founder_position = ?,
                founder_content = ?,
                meta_description = ?, 
                meta_keywords = ?, 
                is_active = ?";
        
        $params = [
            $title, 
            $content, 
            $mission,
            $vision,
            $values,
            $founder_name,
            $founder_position,
            $founder_content,
            $meta_description, 
            $meta_keywords, 
            $is_active
        ];

        // Add founder_image to update if uploaded
        if ($founder_image) {
            $sql .= ", founder_image = ?";
            $params[] = $founder_image;
        }

        $sql .= " WHERE page_slug = ?";
        $params[] = $page_slug;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $success_message = "Content updated successfully!";
    } catch (Exception $e) {
        $error_message = "Error updating content: " . $e->getMessage();
    }
}

// Get all pages
$pages = $db->query("SELECT * FROM page_contents ORDER BY page_slug")->fetchAll(PDO::FETCH_ASSOC);

// Get TinyMCE API key from settings
$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tinymce_api_key'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$tinymce_api_key = $result ? $result['setting_value'] : 'no-api-key';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Content Management - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-textarea {
            min-height: 300px;
            white-space: pre-wrap;
        }
        .section-textarea {
            min-height: 150px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include '../includes/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Page Content Management</h2>
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

        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="" id="pageContentForm" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="page_slug" class="block text-sm font-medium text-gray-700 mb-2">Select Page</label>
                        <select class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                id="page_slug" name="page_slug" required>
                            <?php foreach ($pages as $page): ?>
                                <option value="<?php echo htmlspecialchars($page['page_slug']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($page['page_slug'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                        <input type="text" id="title" name="title" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                    <textarea id="content" name="content" 
                              class="content-textarea w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-y"></textarea>
                    <p class="mt-1 text-sm text-gray-500">HTML tags are allowed for formatting.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="mission" class="block text-sm font-medium text-gray-700 mb-2">Mission</label>
                        <textarea id="mission" name="mission"
                                  class="section-textarea w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-y"></textarea>
                    </div>

                    <div>
                        <label for="vision" class="block text-sm font-medium text-gray-700 mb-2">Vision</label>
                        <textarea id="vision" name="vision"
                                  class="section-textarea w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-y"></textarea>
                    </div>

                    <div>
                        <label for="values" class="block text-sm font-medium text-gray-700 mb-2">Values</label>
                        <textarea id="values" name="values"
                                  class="section-textarea w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-y"></textarea>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Founder Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="founder_name" class="block text-sm font-medium text-gray-700 mb-2">Founder Name</label>
                            <input type="text" id="founder_name" name="founder_name"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="founder_position" class="block text-sm font-medium text-gray-700 mb-2">Founder Position</label>
                            <input type="text" id="founder_position" name="founder_position"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="founder_image" class="block text-sm font-medium text-gray-700 mb-2">Founder Image</label>
                        <input type="file" id="founder_image" name="founder_image" accept="image/*"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <div id="current_image_preview" class="mt-2 hidden">
                            <img src="" alt="Current Founder Image" class="max-h-32 rounded">
                            <p class="text-sm text-gray-500 mt-1">Current image</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="founder_content" class="block text-sm font-medium text-gray-700 mb-2">Founder Content</label>
                        <textarea id="founder_content" name="founder_content"
                                  class="section-textarea w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-y"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                        <textarea id="meta_description" name="meta_description" rows="2"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" checked
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Load page content when page is selected
        document.getElementById('page_slug').addEventListener('change', function() {
            const pageSlug = this.value;
            const form = document.getElementById('pageContentForm');
            
            // Show loading state
            form.querySelectorAll('input, textarea').forEach(input => {
                input.disabled = true;
            });
            
            // Fetch page content
            fetch(`ajax/get-page-content.php?page_slug=${encodeURIComponent(pageSlug)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Update form fields
                    document.getElementById('title').value = data.title || '';
                    document.getElementById('content').value = data.content || '';
                    document.getElementById('mission').value = data.mission || '';
                    document.getElementById('vision').value = data.vision || '';
                    document.getElementById('values').value = data.values || '';
                    document.getElementById('founder_name').value = data.founder_name || '';
                    document.getElementById('founder_position').value = data.founder_position || '';
                    document.getElementById('founder_content').value = data.founder_content || '';
                    document.getElementById('meta_description').value = data.meta_description || '';
                    document.getElementById('meta_keywords').value = data.meta_keywords || '';
                    document.getElementById('is_active').checked = data.is_active == 1;
                    
                    // Handle founder image preview
                    const imagePreview = document.getElementById('current_image_preview');
                    if (data.founder_image) {
                        const previewImg = imagePreview.querySelector('img');
                        previewImg.src = '../' + data.founder_image;
                        imagePreview.classList.remove('hidden');
                    } else {
                        imagePreview.classList.add('hidden');
                    }
                    
                    // Enable form fields
                    form.querySelectorAll('input, textarea').forEach(input => {
                        input.disabled = false;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading page content');
                    // Enable form fields
                    form.querySelectorAll('input, textarea').forEach(input => {
                        input.disabled = false;
                    });
                });
        });

        // Trigger change event on page load to load initial content
        document.getElementById('page_slug').dispatchEvent(new Event('change'));
    </script>
</body>
</html> 