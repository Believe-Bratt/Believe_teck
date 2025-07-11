<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Admin.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Get page content from database
$stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = 'privacy-policy' AND is_active = 1");
$stmt->execute();
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// Set page title
$page_title = "Privacy Policy - Believe Teckk";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-gray-900 to-blue-900 text-white py-24">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Privacy Policy</h1>
        <p class="text-gray-300 text-xl">Last updated: <?php echo date('F d, Y'); ?></p>
    </div>
</section>

<!-- Content Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="prose prose-lg max-w-none">
                <?php if ($page && !empty($page['content'])): ?>
                    <?php echo nl2br(htmlspecialchars($page['content'])); ?>
                <?php else: ?>
                    <div class="space-y-8">
                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-blue-900 mb-4">Introduction</h2>
                            <p class="text-gray-700">At Believe Teckk, we take your privacy seriously. This privacy policy describes how we collect, use, and protect your personal information.</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Information We Collect</h2>
                            <p class="text-gray-700 mb-4">We collect information that you provide directly to us, including:</p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li>Name and contact information</li>
                                <li>Email address</li>
                                <li>Information you provide in forms</li>
                            </ul>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-blue-900 mb-4">How We Use Your Information</h2>
                            <p class="text-gray-700 mb-4">We use the information we collect to:</p>
                            <ul class="list-disc list-inside space-y-2 text-gray-700">
                                <li>Provide and maintain our services</li>
                                <li>Respond to your inquiries</li>
                                <li>Send you updates and marketing communications</li>
                                <li>Improve our website and services</li>
                            </ul>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Contact Us</h2>
                            <p class="text-gray-700">If you have any questions about our privacy policy, please contact us.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>