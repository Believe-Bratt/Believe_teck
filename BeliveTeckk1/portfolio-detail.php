<?php
require_once  __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once  __DIR__ . '/classes/Portfolio.php';

$db = Database::getInstance();
$portfolio = new Portfolio($db);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = $portfolio->getItemById($id);

if (!$item) {
    header('Location: portfolio.php');
    exit;
}

$page_title = $item['title'];
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
    <div class="container mx-auto px-4 py-16 md:py-24">
        <div class="max-w-4xl">
            <h1 class="text-4xl md:text-5xl font-bold mb-6"><?php echo htmlspecialchars($item['title']); ?></h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-8"><?php echo htmlspecialchars($item['description']); ?></p>
            <div class="flex gap-3">
                <span class="px-4 py-2 bg-blue-500 bg-opacity-50 rounded-full text-sm font-medium">
                    <?php echo htmlspecialchars($item['category_name']); ?>
                </span>
                <?php if ($item['is_featured']): ?>
                    <span class="px-4 py-2 bg-yellow-500 bg-opacity-50 rounded-full text-sm font-medium">Featured</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2">
            <!-- Project Image -->
            <div class="rounded-2xl overflow-hidden shadow-2xl mb-12">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                     class="w-full h-auto object-cover" 
                     alt="<?php echo htmlspecialchars($item['title']); ?>">
            </div>
            
            <!-- Project Content -->
            <div class="prose prose-lg max-w-none">
                <?php echo nl2br(htmlspecialchars($item['content'])); ?>
            </div>
            
            <!-- Technologies -->
            <?php if (!empty($item['technologies'])): ?>
                <div class="mt-12">
                    <h3 class="text-2xl font-bold mb-6">Technologies Used</h3>
                    <div class="flex flex-wrap gap-3">
                        <?php 
                        $technologies = explode(',', $item['technologies']);
                        foreach ($technologies as $tech): 
                        ?>
                            <span class="px-4 py-2 bg-gray-100 rounded-full text-gray-700 text-sm font-medium">
                                <?php echo htmlspecialchars(trim($tech)); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Project Details -->
        <div class="lg:col-span-1">
            <!-- Project Info Card -->
            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <h3 class="text-2xl font-bold mb-6">Project Details</h3>
                
                <?php if (!empty($item['client'])): ?>
                    <div class="mb-6">
                        <h4 class="text-gray-500 text-sm font-medium mb-2">Client</h4>
                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($item['client']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($item['project_date'])): ?>
                    <div class="mb-6">
                        <h4 class="text-gray-500 text-sm font-medium mb-2">Project Date</h4>
                        <p class="text-gray-900 font-medium"><?php echo date('F Y', strtotime($item['project_date'])); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($item['project_url'])): ?>
                    <div class="mb-6">
                        <h4 class="text-gray-500 text-sm font-medium mb-2">Project URL</h4>
                        <a href="<?php echo htmlspecialchars($item['project_url']); ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-800 font-medium break-all">
                            <?php echo htmlspecialchars($item['project_url']); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <a href="contact.php" 
                   class="block w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-center font-medium rounded-lg transition duration-150">
                    Start a Similar Project
                </a>
            </div>
            
            <!-- Testimonial Card -->
            <?php if (!empty($item['testimonial'])): ?>
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <h3 class="text-2xl font-bold mb-6">Client Testimonial</h3>
                    <div class="relative">
                        <svg class="absolute top-0 left-0 transform -translate-x-6 -translate-y-6 h-12 w-12 text-gray-200" fill="currentColor" viewBox="0 0 32 32">
                            <path d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z"/>
                        </svg>
                        <blockquote class="relative pl-8">
                            <p class="text-gray-800 text-lg mb-4"><?php echo htmlspecialchars($item['testimonial']); ?></p>
                            <?php if (!empty($item['testimonial_author'])): ?>
                                <footer class="text-gray-600 font-medium">
                                    — <?php echo htmlspecialchars($item['testimonial_author']); ?>
                                </footer>
                            <?php endif; ?>
                        </blockquote>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Related Projects -->
<div class="bg-gray-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Related Projects</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            $related_items = $portfolio->getItemsByCategory($item['category_id']);
            $count = 0;
            foreach ($related_items as $related):
                if ($related['id'] != $item['id'] && $count < 3):
            ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden transition duration-300 hover:shadow-2xl">
                    <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                         class="w-full h-48 object-cover" 
                         alt="<?php echo htmlspecialchars($related['title']); ?>">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-3"><?php echo htmlspecialchars($related['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($related['description']); ?></p>
                        <a href="portfolio-detail.php?id=<?php echo $related['id']; ?>" 
                           class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150">
                            View Details
                        </a>
                    </div>
                </div>
            <?php
                    $count++;
                endif;
            endforeach;
            ?>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">Ready to Start Your Project?</h2>
            <p class="text-xl text-blue-100 mb-8">Let's discuss how we can help bring your ideas to life.</p>
            <a href="contact.php" 
               class="inline-block px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition duration-150">
                Get in Touch
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 