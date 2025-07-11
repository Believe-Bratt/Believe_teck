<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once('../config/config.php');
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ .'/../classes/Admin.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Get current admin's ID
$current_admin = $admin->getUserById($_SESSION['admin_id']);
if (!$current_admin) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_post':
                $title = trim($_POST['title']);
                $content = $_POST['content'];
                $author = trim($_POST['author']);
                
                // Handle image upload
                $image_url = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/blog/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $file_name = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            $image_url = 'uploads/blog/' . $file_name;
                        } else {
                            $error_message = "Failed to upload image. Please try again.";
                            break;
                        }
                    } else {
                        $error_message = "Invalid file type. Please upload JPG, JPEG, PNG, or GIF files only.";
                        break;
                    }
                }
                
                if (empty($title) || empty($content) || empty($author)) {
                    $error_message = "Title, content, and author are required fields.";
                } else {
                    try {
                        if ($admin->addBlogPost($title, $content, $image_url, $author)) {
                            $success_message = "Blog post added successfully!";
                            // Redirect to refresh the page and show the new post
                            header("Location: blog.php");
                            exit;
                        } else {
                            $error_message = "Error adding blog post. Please check the error logs for details.";
                        }
                    } catch (Exception $e) {
                        $error_message = "Error: " . $e->getMessage();
                        error_log("Blog post addition error: " . $e->getMessage());
                    }
                }
                break;

            case 'update_post':
                $id = $_POST['id'];
                $title = trim($_POST['title']);
                $content = $_POST['content'];
                $current_image = $_POST['current_image'];
                
                // Handle image upload
                $image_url = $current_image;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/blog/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        $file_name = uniqid() . '.' . $file_extension;
                        $target_path = $upload_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                            // Delete old image if exists
                            if ($current_image && file_exists('../' . $current_image)) {
                                unlink('../' . $current_image);
                            }
                            $image_url = 'uploads/blog/' . $file_name;
                        }
                    }
                }
                
                if (empty($title) || empty($content)) {
                    $error_message = "Title and content are required fields.";
                } else {
                    if ($admin->updateBlogPost($id, $title, $content, $image_url, $current_admin['id'])) {
                        $success_message = "Blog post updated successfully!";
                    } else {
                        $error_message = "Error updating blog post. Please try again.";
                    }
                }
                break;

            case 'delete_post':
                $id = $_POST['id'];
                $image_url = $_POST['image_url'];
                
                // Delete image file if exists
                if ($image_url && file_exists('../' . $image_url)) {
                    unlink('../' . $image_url);
                }
                
                if ($admin->deleteBlogPost($id)) {
                    $success_message = "Blog post deleted successfully!";
                } else {
                    $error_message = "Error deleting blog post. Please try again.";
                }
                break;
        }
    }
}

// Fetch blog posts
$blog_posts = $admin->getBlogPosts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blog - Believe Teckk</title>
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
            <h2 class="text-2xl font-bold">Manage Blog Posts</h2>
            <div class="flex items-center">
                <span class="text-gray-600 mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                <a href="profile.php" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-user-circle text-2xl"></i>
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Add New Post Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h3 class="text-xl font-bold mb-4">Add New Blog Post</h3>
            <form method="POST" action="" enctype="multipart/form-data" id="addPostForm">
                <input type="hidden" name="action" value="add_post">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                            Post Title
                        </label>
                        <input type="text" id="title" name="title" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="author">
                            Author Name
                        </label>
                        <input type="text" id="author" name="author" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                            Featured Image (Optional)
                        </label>
                        <input type="file" id="image" name="image" accept="image/*"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <p class="text-sm text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                        Content
                    </label>
                    <textarea id="content" name="content" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Add Post
                    </button>
                </div>
            </form>
        </div>

        <!-- Blog Posts List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($blog_posts)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No blog posts found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($blog_posts as $post): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <?php if ($post['featured_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                 class="h-10 w-10 rounded-full object-cover">
                                        <?php endif; ?>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($post['author_name']); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <a href="edit-blog.php?id=<?php echo $post['id']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-4">
                                        Edit
                                    </a>
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="action" value="delete_post">
                                        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                        <input type="hidden" name="image_url" value="<?php echo htmlspecialchars($post['featured_image']); ?>">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure you want to delete this post?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-3/4 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Edit Blog Post</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_post">
                <input type="hidden" name="id" id="edit_id">
                <input type="hidden" name="current_image" id="edit_current_image">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_title">
                            Post Title
                        </label>
                        <input type="text" id="edit_title" name="title" required
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_image">
                            Featured Image (Optional)
                        </label>
                        <input type="file" id="edit_image" name="image" accept="image/*"
                               class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <p class="text-sm text-gray-500 mt-1">Supported formats: JPG, JPEG, PNG, GIF</p>
                        <div id="current_image_preview" class="mt-2"></div>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_content">
                        Content
                    </label>
                    <textarea id="edit_content" name="content" required
                              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                </div>

                <div class="mt-6">
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Update Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editPost(post) {
            document.getElementById('edit_id').value = post.id;
            document.getElementById('edit_title').value = post.title;
            document.getElementById('edit_content').value = post.content;
            document.getElementById('edit_current_image').value = post.featured_image;
            
            const imagePreview = document.getElementById('current_image_preview');
            if (post.featured_image) {
                imagePreview.innerHTML = `<img src="../${post.featured_image}" alt="Current image" class="max-h-32 mt-2">`;
            } else {
                imagePreview.innerHTML = '';
            }
            
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeEditModal();
            }
        }

        // Add form submission handling
        document.getElementById('addPostForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const author = document.getElementById('author').value.trim();
            
            if (!title || !content || !author) {
                e.preventDefault();
                alert('Please fill in all required fields (Title, Content, and Author).');
                return false;
            }
        });
    </script>
</body>
</html> 