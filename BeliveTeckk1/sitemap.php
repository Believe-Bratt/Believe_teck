<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Admin.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Get services
$services = $admin->getServices();

// Get blog posts
$stmt = $db->prepare("SELECT * FROM blog_posts WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$blog_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set page title
$page_title = "Sitemap - Believe Teckk";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-gray-900 to-blue-900 text-white py-24">
    <div class="container mx-auto px-4">
        <h1 class="text-5xl font-bold mb-6 text-center">Sitemap</h1>
        <p class="text-gray-300 text-xl text-center">Navigate through our website with ease</p>
    </div>
</section>

<!-- Sitemap Content -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="grid md:grid-cols-2 gap-12">
            <!-- Main Pages -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Main Pages</h2>
                <ul class="space-y-4">
                    <li><a href="index.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-home mr-2"></i>Home</a></li>
                    <li><a href="about.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-info-circle mr-2"></i>About Us</a></li>
                    <li><a href="services.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-cogs mr-2"></i>Services</a></li>
                    <li><a href="portfolio.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-briefcase mr-2"></i>Portfolio</a></li>
                    <li><a href="blog.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-blog mr-2"></i>Blog</a></li>
                    <li><a href="careers.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-user-tie mr-2"></i>Careers</a></li>
                    <li><a href="contact.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-envelope mr-2"></i>Contact</a></li>
                </ul>
            </div>

            <!-- Services -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Our Services</h2>
                <ul class="space-y-4">
                    <?php foreach ($services as $service): ?>
                    <li>
                        <a href="services.php#<?php echo htmlspecialchars($service['id']); ?>" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo htmlspecialchars($service['title']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Recent Blog Posts -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Recent Blog Posts</h2>
                <ul class="space-y-4">
                    <?php foreach ($blog_posts as $post): ?>
                    <li>
                        <a href="blog-post.php?id=<?php echo htmlspecialchars($post['id']); ?>" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center">
                            <i class="fas fa-file-alt mr-2"></i>
                            <?php echo htmlspecialchars($post['title']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Legal Pages -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-4">Legal Pages</h2>
                <ul class="space-y-4">
                    <li><a href="privacy-policy.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-shield-alt mr-2"></i>Privacy Policy</a></li>
                    <li><a href="terms-of-service.php" class="text-blue-600 hover:text-blue-800 hover:underline flex items-center"><i class="fas fa-file-contract mr-2"></i>Terms of Service</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>