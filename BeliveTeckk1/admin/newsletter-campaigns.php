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
require_once __DIR__ .'/../classes/NewsletterEmail.php';
require_once __DIR__ .'/../classes/FileUploader.php';

$db = Database::getInstance();
$admin = new Admin($db);
$newsletter = new Newsletter($db);
$newsletterEmail = new NewsletterEmail($db);

// Create upload directory if it doesn't exist
$upload_dir = '../uploads/newsletter';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Get TinyMCE API key from settings
$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'tinymce_api_key'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$tinymce_api_key = $result ? $result['setting_value'] : 'no-api-key';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_template':
                $title = $_POST['name'] ?? '';
                $subject = $_POST['subject'] ?? '';
                $content = $_POST['content'] ?? '';
                $image_url = '';
                
                // Handle image upload if present
                if (!empty($_FILES['image']['name'])) {
                    $fileUploader = new FileUploader();
                    $upload_result = $fileUploader->uploadImage(
                        $_FILES['image'],
                        $upload_dir,
                       
                    );
                    
                    if ($upload_result['success']) {
                        $image_url = 'uploads/newsletter/' . $upload_result['filename'];
                    } else {
                        $error_message = "Image upload failed: " . $upload_result['message'];
                        break;
                    }
                }
                
                if ($newsletter->createTemplate($title, $subject, $content, $image_url)) {
                    $success_message = "Template created successfully!";
                } else {
                    $error_message = "Failed to create template.";
                }
                break;

            case 'update_template':
                $id = $_POST['id'] ?? 0;
                $title = $_POST['name'] ?? '';
                $subject = $_POST['subject'] ?? '';
                $content = $_POST['content'] ?? '';
                $current_image = $_POST['current_image'] ?? '';
                $image_url = $current_image;
                
                // Handle image upload if present
                if (!empty($_FILES['image']['name'])) {
                    $fileUploader = new FileUploader();
                    $upload_result = $fileUploader->uploadImage(
                        $_FILES['image'],
                        $upload_dir,
                      // 5MB limit
                    );
                    
                    if ($upload_result['success']) {
                        // Delete old image if exists
                        if (!empty($current_image) && file_exists("../{$current_image}")) {
                            unlink("../{$current_image}");
                        }
                        $image_url = 'uploads/newsletter/' . $upload_result['filename'];
                    } else {
                        $error_message = "Image upload failed: " . $upload_result['message'];
                        break;
                    }
                }
                
                if ($newsletter->updateTemplate($id, $title, $subject, $content, $image_url)) {
                    $success_message = "Template updated successfully!";
                } else {
                    $error_message = "Failed to update template.";
                }
                break;

            case 'delete_template':
                $id = $_POST['id'] ?? 0;
                
                if ($newsletter->deleteTemplate($id)) {
                    $success_message = "Template deleted successfully!";
                } else {
                    $error_message = "Failed to delete template.";
                }
                break;

            case 'send_campaign':
                $template_id = $_POST['template_id'] ?? 0;
                $test_email = $_POST['test_email'] ?? '';

            
                
                
                if ($test_email) {
                    // Send test email
                    if ($newsletterEmail->$sendTestEmail = $admin->sendTestEmail($test_email, $template_id)) {
                        $success_message = "Test email sent successfully!";
                    } else {
                        $error_message = "Failed to send test email.";
                    }
                } else {
                    // Send to all subscribers
                    if ($newsletterEmail->$sendCampaign = $admin->sendCampaign ($template_id)) {
                        $success_message = "Campaign sent successfully!";
                    } else {
                        $error_message = "Failed to send campaign.";
                    }
                }
                break;
        }
    }
}

// Fetch templates
$templates = $newsletter->getTemplates();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Campaigns - Believe Teckk</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/<?php echo htmlspecialchars($tinymce_api_key); ?>/tinymce/5/tinymce.min.js"></script>
    <script>
      /*tinymce.init({
            selector: '#content',
            height: 300,
            plugins: 'link image code table lists',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
        });*/
    </script>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>
    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Newsletter Campaigns</h2>
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

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Create Template Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Create New Template</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create_template">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                            Template Title
                        </label>
                        <input type="text" id="title" name="name" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="subject">
                            Email Subject
                        </label>
                        <input type="text" id="subject" name="subject" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                        Featured Image (Optional)
                    </label>
                    <input type="file" id="image" name="image" accept="image/*"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-sm text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB</p>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                        Email Content
                    </label>
                    <textarea id="content" name="content" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="10"></textarea>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Create Template
                    </button>
                </div>
            </form>
        </div>

        <!-- Templates List -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold mb-4">Email Templates</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title Name </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($template['name']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php echo htmlspecialchars($template['subject']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button onclick="editTemplate(<?php echo htmlspecialchars(json_encode($template)); ?>)"
                                            class="text-blue-600 hover:text-blue-900 mr-4">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="sendCampaign(<?php echo $template['id']; ?>)"
                                            class="text-green-600 hover:text-green-900 mr-4">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="action" value="delete_template">
                                        <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this template?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Template Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-4xl w-full mx-4">
            <h2 class="text-2xl font-bold mb-6">Edit Template</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_template">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="current_image" id="edit_current_image">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_title">
                            Template Title
                        </label>
                        <input type="text" id="edit_title" name="name" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_subject">
                            Email Subject
                        </label>
                        <input type="text" id="edit_subject" name="subject" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_image">
                        Featured Image (Optional)
                    </label>
                    <div id="current_image_preview" class="mb-2 hidden">
                        <img src="" alt="Current Image" class="max-h-32 mb-2">
                        <p class="text-sm text-gray-500">Current image</p>
                    </div>
                    <input type="file" id="edit_image" name="image" accept="image/*"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <p class="text-sm text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF, WEBP. Max size: 5MB</p>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_content">
                        Email Content
                    </label>
                    <textarea id="edit_content" name="content" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="10"></textarea>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="closeEditModal()"
                            class="text-gray-600 hover:text-gray-800 mr-4">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Send Campaign Modal -->
    <div id="sendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <h2 class="text-2xl font-bold mb-6">Send Campaign</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="send_campaign">
                <input type="hidden" name="template_id" id="send_template_id">
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="test_email">
                        Test Email (Optional)
                    </label>
                    <input type="email" id="test_email" name="test_email"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           placeholder="Leave empty to send to all subscribers">
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="closeSendModal()"
                            class="text-gray-600 hover:text-gray-800 mr-4">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Send Campaign
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editTemplate(template) {
            document.getElementById('edit_id').value = template.id;
            document.getElementById('edit_title').value = template.name;
            document.getElementById('edit_subject').value = template.subject;
            document.getElementById('edit_content').value = template.content;
            document.getElementById('edit_current_image').value = template.image_url || '';
            
            // Handle image preview
            const imagePreview = document.getElementById('current_image_preview');
            if (template.image_url) {
                const previewImg = imagePreview.querySelector('img');
                previewImg.src = '../' + template.image_url;
                imagePreview.classList.remove('hidden');
            } else {
                imagePreview.classList.add('hidden');
            }
            
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('flex');
            document.getElementById('editModal').classList.add('hidden');
        }

        function sendCampaign(templateId) {
            document.getElementById('send_template_id').value = templateId;
            document.getElementById('sendModal').classList.remove('hidden');
            document.getElementById('sendModal').classList.add('flex');
        }

        function closeSendModal() {
            document.getElementById('sendModal').classList.remove('flex');
            document.getElementById('sendModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        document.getElementById('sendModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSendModal();
            }
        });
    </script>
</body>
</html> 