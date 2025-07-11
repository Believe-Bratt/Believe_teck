<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Admin.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get post details
$stmt = $db->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: blog.php');
    exit;
}

// Get current URL for sharing
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Get related posts based on tags
$related_posts = [];
if (!empty($post['tags'])) {
    $tags = explode(',', $post['tags']);
    $placeholders = str_repeat('?,', count($tags) - 1) . '?';
    $sql = "SELECT * FROM blog_posts WHERE id != ? AND tags REGEXP ? LIMIT 3";
    $tag_pattern = implode('|', array_map('trim', $tags));
    $stmt = $db->prepare($sql);
    $stmt->execute([$post_id, $tag_pattern]);
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// If no related posts found by tags, get latest posts
if (empty($related_posts)) {
    $stmt = $db->prepare("SELECT * FROM blog_posts WHERE id != ? ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$post_id]);
    $related_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Set page title
$page_title = htmlspecialchars($post['title']) . " - Believe Teckk Blog";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-gray-900 via-blue-900 to-gray-900 text-white py-24">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="relative">
            <a href="blog.php" class="absolute left-0 top-0 inline-flex items-center text-white hover:text-red-400 transition duration-300">
                <i class="fas fa-arrow-left mr-2"></i>
                <span>Back to Blog</span>
            </a>
        </div>
        <div class="text-center">
            <div class="inline-block px-4 py-1 bg-red-500/20 rounded-full text-red-400 mb-6">
                <span class="text-sm font-medium"><?php echo date('F d, Y', strtotime($post['created_at'])); ?></span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-clip-text text-transparent bg-gradient-to-r from-red-400 to-blue-400">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            <div class="flex items-center justify-center space-x-6 text-gray-300">
                <div class="flex items-center">
                    <i class="fas fa-user-circle text-red-400 mr-2"></i>
                    <span><?php echo htmlspecialchars($post['author_name'] ?? ''); ?></span>
                        <span class="mx-2">•</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-clock text-red-400 mr-2"></i>
                    <span class="font-medium"><?php echo date('g:i A', strtotime($post['created_at'])); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <?php if (!empty($post['featured_image'])): ?>
            <div class="w-full h-[20px] relative">
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                     class="w-full h-[20px] object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
            <?php endif; ?>
            
            <div class="p-8 md:p-12">
                <div class="prose prose-lg max-w-none">
                    <div class="leading-relaxed text-gray-800 prose-headings:text-gray-900 prose-a:text-red-600">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                </div>

                <!-- Tags Section -->
                <?php if (!empty($post['tags'])): ?>
                <div class="mt-12 pt-6 border-t border-gray-200">
                    <h4 class="text-sm uppercase tracking-wider text-gray-500 mb-4">Technologies Used</h4>
                    <div class="flex flex-wrap gap-3">
                        <?php 
                        $tags = explode(',', $post['tags']);
                        foreach ($tags as $tag): 
                        ?>
                        <a href="blog.php?tag=<?php echo urlencode(trim($tag)); ?>" 
                           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm hover:bg-red-50 hover:text-red-700 transition duration-300">
                            <i class="fas fa-tag mr-1 text-red-500"></i>
                            <?php echo trim(htmlspecialchars($tag)); ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Share Section -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-bold mb-6 text-gray-900">Share this insight</h3>
                    <div class="flex justify-center">
                        <button onclick="copyToClipboard('<?php echo $currentUrl; ?>', event)"
                                class="inline-flex items-center bg-gray-900 text-white px-6 py-3 rounded-md hover:bg-gray-800 transition duration-300">
                            <i class="fas fa-link mr-2"></i>
                            <span class="font-medium">Copy Link</span>
                        </button>
                    </div>
                </div>

                <!-- Author Box -->
                <div class="mt-12 bg-gray-50 rounded-lg p-8 border border-gray-200">
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div class="w-20 h-20 rounded-lg bg-gradient-to-r from-red-500 to-blue-500 flex items-center justify-center">
                                <i class="fas fa-user-tie text-3xl text-white"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-gray-900">Technical Expert</h3>
                            <p class="text-gray-600">Enterprise Solutions Architect specializing in scalable systems and cloud infrastructure.</p>
                            <div class="mt-4 flex space-x-4">
                                <a href="#" class="text-gray-600 hover:text-red-600 transition duration-300">
                                    <i class="fab fa-github text-lg"></i>
                                </a>
                                <a href="#" class="text-gray-600 hover:text-red-600 transition duration-300">
                                    <i class="fab fa-linkedin-in text-lg"></i>
                                </a>
                                <a href="#" class="text-gray-600 hover:text-red-600 transition duration-300">
                                    <i class="fas fa-globe text-lg"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Posts -->
        <div class="mt-16">
            <h2 class="text-2xl font-bold mb-8 text-gray-900">Related Solutions</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($related_posts as $related_post): ?>
                <a href="blog-post.php?id=<?php echo $related_post['id']; ?>" 
                   class="group bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition duration-300">
                    <?php if (!empty($related_post['image'])): ?>
                    <div class="h-48 overflow-hidden">
                        <img src="uploads/<?php echo htmlspecialchars($related_post['image']); ?>" 
                             alt="<?php echo htmlspecialchars($related_post['title']); ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                    </div>
                    <?php endif; ?>
                    <div class="p-6">
                        <p class="text-sm text-red-500 mb-2">
                            <?php echo date('F d, Y', strtotime($related_post['created_at'])); ?>
                        </p>
                        <h3 class="text-lg font-semibold mb-2 group-hover:text-red-500 transition duration-300">
                            <?php echo htmlspecialchars($related_post['title']); ?>
                        </h3>
                        <p class="text-gray-600 line-clamp-2">
                            <?php echo htmlspecialchars(substr($related_post['content'], 0, 150)) . '...'; ?>
                        </p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>