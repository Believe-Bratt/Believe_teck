<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once('../config/config.php');
require_once('../classes/Database.php');
require_once('../classes/Admin.php');
require_once('../classes/FileUploader.php');

$db = Database::getInstance();
$admin = new Admin($db);
$fileUploader = new FileUploader();

// Define upload directory
$upload_dir = '../uploads/team/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                case 'update':
                    $name = trim($_POST['name']);
                    $position = trim($_POST['position']);
                    $bio = trim($_POST['bio']);
                    $linkedin_url = trim($_POST['linkedin_url'] ?? '');
                    $twitter_url = trim($_POST['twitter_url'] ?? '');
                    $order_index = intval($_POST['order_index'] ?? 0);
                    $image = $_POST['current_image'] ?? '';
                    $id = $_POST['id'] ?? null;

                    // Validate input
                    if (empty($name) || empty($position) || empty($bio)) {
                        throw new Exception("Name, position, and bio are required.");
                    }

                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                        
                        if (!in_array($file_extension, $allowed_extensions)) {
                            throw new Exception("Invalid file type. Only JPG, JPEG, PNG & WEBP files are allowed.");
                        }

                        // Validate file size (max 5MB)
                        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                            throw new Exception("File size should be less than 5MB.");
                        }

                        // Generate unique filename
                        $file_name = uniqid() . '_' . time() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;

                        // Delete old image if exists
                        if (!empty($image) && file_exists('../' . $image)) {
                            unlink('../' . $image);
                        }

                        // Move uploaded file
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image = 'uploads/team/' . $file_name;
                        } else {
                            throw new Exception("Failed to upload image.");
                        }
                    }

                    if ($_POST['action'] === 'add') {
                        $admin->addTeamMember($name, $position, $bio, $image, $linkedin_url, $twitter_url, $order_index);
                        $_SESSION['success'] = "Team member added successfully!";
                    } else {
                        $admin->updateTeamMember($id, $name, $position, $bio, $image, $linkedin_url, $twitter_url, $order_index);
                        $_SESSION['success'] = "Team member updated successfully!";
                    }
                    break;

                case 'delete':
                    $id = $_POST['id'];
                    $member = $admin->getTeamMember($id);
                    
                    if ($member) {
                        // Delete image file if exists
                        if (!empty($member['image']) && file_exists('../' . $member['image'])) {
                            unlink('../' . $member['image']);
                        }
                        
                        $admin->deleteTeamMember($id);
                        $_SESSION['success'] = "Team member deleted successfully!";
                    } else {
                        throw new Exception("Team member not found.");
                    }
                    break;

                case 'toggle_status':
                    $id = $_POST['id'];
                    if ($admin->toggleTeamMemberStatus($id)) {
                        $_SESSION['success'] = "Team member status updated successfully!";
                    } else {
                        throw new Exception("Failed to update team member status.");
                    }
                    break;

                case 'update_order':
                    $id = $_POST['id'];
                    $order_index = intval($_POST['order_index']);
                    if ($admin->updateTeamMemberOrder($id, $order_index)) {
                        $_SESSION['success'] = "Team member order updated successfully!";
                    } else {
                        throw new Exception("Failed to update team member order.");
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    header('Location: team.php');
    exit;
}

// Get all team members
$team_members = $admin->getTeamMembers();

// Debug output
error_log("Team members count: " . count($team_members));
error_log("Team members data: " . print_r($team_members, true));

// Check if the team_members table exists
$table_check = $db->query("SHOW TABLES LIKE 'team_members'")->fetch();
error_log("Team members table exists: " . ($table_check ? 'Yes' : 'No'));

// If table doesn't exist, create it
if (!$table_check) {
    $db->query("CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        position VARCHAR(255) NOT NULL,
        bio TEXT NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        linkedin_url VARCHAR(255),
        twitter_url VARCHAR(255),
        order_index INT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    error_log("Team members table created");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Team - Admin Panel</title>
    <link rel="icon" type="image/png" href="../assets/images/b-logo.png">
    <link rel="apple-touch-icon" href="../assets/images/b-logo.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .team-member-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .image-preview-container {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <?php include('../includes/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Manage Team</h1>
            <button onclick="document.getElementById('addMemberModal').classList.remove('hidden')" 
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                <i class="fas fa-plus mr-2"></i>Add New Team Member
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Team Members List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Social Links</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($team_members as $member): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" value="<?php echo htmlspecialchars($member['order_index']); ?>" 
                                       onchange="updateOrder(<?php echo $member['id']; ?>, this.value)"
                                       class="w-16 rounded border-gray-300">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <img src="../<?php echo htmlspecialchars($member['image'] ?? 'assets/images/default-avatar.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($member['name']); ?>"
                                     class="team-member-image">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($member['position']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($member['bio']); ?></td>
                            <td class="px-6 py-4">
                                <?php if (!empty($member['linkedin_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($member['linkedin_url']); ?>" target="_blank" class="text-blue-600 hover:text-blue-900 mr-2">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($member['twitter_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($member['twitter_url']); ?>" target="_blank" class="text-blue-400 hover:text-blue-600">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="toggleStatus(<?php echo $member['id']; ?>)" 
                                        class="<?php echo $member['is_active'] ? 'text-green-600' : 'text-red-600'; ?> hover:<?php echo $member['is_active'] ? 'text-green-900' : 'text-red-900'; ?>">
                                    <i class="fas fa-<?php echo $member['is_active'] ? 'check-circle' : 'times-circle'; ?>"></i>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editMember(<?php echo htmlspecialchars(json_encode($member)); ?>)" 
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteMember(<?php echo $member['id']; ?>)" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium" id="modalTitle">Add New Team Member</h3>
                <button onclick="document.getElementById('addMemberModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="memberForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="memberId">
                <input type="hidden" name="current_image" id="currentImage">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Position</label>
                        <input type="text" name="position" required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea name="bio" required rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Twitter URL</label>
                        <input type="url" name="twitter_url" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Display Order</label>
                        <input type="number" name="order_index" min="0" value="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Profile Image</label>
                        <input type="file" name="image" id="imageInput" accept="image/*" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <div id="imagePreview" class="image-preview-container">
                            <img id="previewImage" class="preview-image" src="" alt="Preview">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('addMemberModal').classList.add('hidden')" 
                            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });

        function editMember(member) {
            document.getElementById('modalTitle').textContent = 'Edit Team Member';
            document.getElementById('formAction').value = 'update';
            document.getElementById('memberId').value = member.id;
            document.getElementById('currentImage').value = member.image;
            
            // Set form values
            const form = document.getElementById('memberForm');
            form.elements['name'].value = member.name;
            form.elements['position'].value = member.position;
            form.elements['bio'].value = member.bio;
            form.elements['linkedin_url'].value = member.linkedin_url || '';
            form.elements['twitter_url'].value = member.twitter_url || '';
            form.elements['order_index'].value = member.order_index || 0;
            
            // Show current image
            const preview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            if (member.image) {
                previewImage.src = '../' + member.image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
            
            document.getElementById('addMemberModal').classList.remove('hidden');
        }

        function deleteMember(id) {
            if (confirm('Are you sure you want to delete this team member?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function toggleStatus(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function updateOrder(id, order) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_order">
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="order_index" value="${order}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html> 