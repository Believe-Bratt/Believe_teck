<?php
require_once 'config/config.php';
require_once 'classes/Database.php';
require_once 'classes/Admin.php';

$db = Database::getInstance();
$admin = new Admin($db);

// Get page content from database
$stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = 'terms-of-service' AND is_active = 1");
$stmt->execute();
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// Set page title
$page_title = "Terms of Service - Believe Teckk";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-gray-900 to-blue-900 text-white py-24">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-5xl font-bold mb-6">Terms of Service</h1>
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
                            <h2 class="text-2xl font-bold text-blue-900 mb-4">Agreement to Terms</h2>
                            <p class="text-gray-700">By accessing our website, you agree to be bound by these terms of service and comply with all applicable laws and regulations.</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Use License</h2>
                            <p class="text-gray-700">Permission is granted to temporarily access the materials on Believe Teckk's website for personal, non-commercial use only.</p>
                        </div>

                        <div class="bg-blue-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-blue-900 mb-4">Disclaimer</h2>
                            <p class="text-gray-700">The materials on Believe Teckk's website are provided on an 'as is' basis. Believe Teckk makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
                        </div>

                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Limitations</h2>
                            <p class="text-gray-700">In no event shall Believe Teckk or its suppliers be liable for any damages arising out of the use or inability to use the materials on Believe Teckk's website.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>