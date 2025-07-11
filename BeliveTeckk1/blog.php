<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Blog.php';

$db = Database::getInstance();
$blog = new Blog($db);

// Get blog posts
$blog_posts = $blog->getAllPosts();

// Set page title for header
$page_title = "Blog - Believe Teckk";
$active_page = "blog";

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-blue-600 text-white py-16">
    <div class="container mx-auto px-4">
        <h1 class="text-4xl font-bold mb-4">Our Blog</h1>
        <p class="text-xl">Stay updated with the latest news, insights, and trends in technology</p>
    </div>
</div>

<!-- Blog Posts -->
<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($blog_posts as $post): ?>
            <article class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php if ($post['featured_image']): ?>
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title'] ?? ''); ?>"
                     class="w-full h-48 object-cover">
                <?php endif; ?>
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-2">
                        <a href="blog-post.php?id=<?php echo $post['id']; ?>" 
                           class="text-gray-900 hover:text-blue-600">
                            <?php echo htmlspecialchars($post['title'] ?? ''); ?>
                        </a>
                    </h2>
                    <div class="flex items-center text-gray-600 text-sm mb-4">
                        <i class="fas fa-user mr-2"></i>
                        <span><?php echo htmlspecialchars($post['author_name'] ?? ''); ?></span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar mr-2"></i>
                        <span><?php echo date('M d, Y', strtotime($post['created_at'] ?? '')); ?></span>
                    </div>
                    <p class="text-gray-600 mb-4">
                        <?php echo substr(strip_tags($post['excerpt'] ?? $post['content']), 0, 150) . '...'; ?>
                    </p>
                    <a href="blog-post.php?id=<?php echo $post['id']; ?>" 
                       class="inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Read More
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 