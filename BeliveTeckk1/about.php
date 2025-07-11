<?php
require_once 'config/config.php';
require_once 'includes/header.php';

// Get page content from database
$db = getDBConnection();
$stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = 'about' AND is_active = 1");
$stmt->execute();
$page = $stmt->fetch(PDO::FETCH_ASSOC);

// Get team members
$team = $db->query("SELECT * FROM team_members WHERE is_active = 1 ORDER BY order_index ASC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Debug output
error_log("About page team members count: " . count($team));
error_log("About page team members data: " . print_r($team, true));
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-32">
    <div class="absolute inset-0 bg-black/20"></div>
    <div class="container mx-auto px-4 relative">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo htmlspecialchars($page['title'] ?? 'About Us'); ?></h1>
            <p class="text-xl text-blue-100 mb-8"><?php echo nl2br(htmlspecialchars($page['content'] ?? '')); ?></p>
        </div>
    </div>
</section>

<!-- Mission, Vision & Values Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Mission -->
            <div class="bg-gray-50 rounded-2xl p-8 shadow-lg transform hover:-translate-y-1 transition duration-300">
                <div class="text-blue-600 mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Mission</h2>
                <div class="prose prose-lg text-gray-600">
                    <?php echo nl2br(htmlspecialchars($page['mission'] ?? '')); ?>
                </div>
            </div>

            <!-- Vision -->
            <div class="bg-gray-50 rounded-2xl p-8 shadow-lg transform hover:-translate-y-1 transition duration-300">
                <div class="text-blue-600 mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Vision</h2>
                <div class="prose prose-lg text-gray-600">
                    <?php echo nl2br(htmlspecialchars($page['vision'] ?? '')); ?>
                </div>
            </div>

            <!-- Values -->
            <div class="bg-gray-50 rounded-2xl p-8 shadow-lg transform hover:-translate-y-1 transition duration-300">
                <div class="text-blue-600 mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Our Values</h2>
                <div class="prose prose-lg text-gray-600">
                    <?php echo nl2br(htmlspecialchars($page['values'] ?? '')); ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About the Founder Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="relative max-w-md mx-auto lg:mx-0">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-indigo-700 transform -rotate-6 rounded-2xl"></div>
                    <img src="<?php echo htmlspecialchars($page['founder_image'] ?? 'assets/images/founder.jpg'); ?>" 
                         alt="Founder" 
                         class="relative rounded-2xl shadow-xl w-full h-[300px] object-cover object-center">
                </div>
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">About the Founder</h2>
                    <div class="prose prose-lg text-gray-600">
                        <?php echo nl2br(htmlspecialchars($page['founder_content'] ?? '')); ?>
                    </div>
                    <div class="mt-8">
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($page['founder_name'] ?? ''); ?></h3>
                        <p class="text-blue-600 font-medium"><?php echo htmlspecialchars($page['founder_position'] ?? 'Founder & CEO'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Our Team</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Meet the talented individuals who make our vision a reality.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($team as $member): ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden transform hover:-translate-y-1 transition duration-300">
                <div class="aspect-w-1 aspect-h-1">
                    <img src="<?php echo htmlspecialchars($member['image'] ?? 'assets/images/default-avatar.png'); ?>" 
                         alt="<?php echo htmlspecialchars($member['name']); ?>" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p class="text-blue-600 font-medium mb-4"><?php echo htmlspecialchars($member['position']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($member['bio']); ?></p>
                    <div class="flex space-x-4 mt-4">
                        <?php if (!empty($member['linkedin_url'])): ?>
                        <a href="<?php echo htmlspecialchars($member['linkedin_url']); ?>" 
                           target="_blank" 
                           class="text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fab fa-linkedin fa-lg"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($member['twitter_url'])): ?>
                        <a href="<?php echo htmlspecialchars($member['twitter_url']); ?>" 
                           target="_blank" 
                           class="text-gray-600 hover:text-blue-400 transition duration-300">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Work With Us?</h2>
            <p class="text-xl text-blue-100 mb-8">Let's discuss how we can help bring your ideas to life.</p>
            <a href="contact.php" class="inline-block px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition duration-300">
                Get in Touch
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?> 