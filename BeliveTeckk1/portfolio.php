<?php
require_once  __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once  __DIR__ . '/classes/Portfolio.php';

$db = Database::getInstance();
$portfolio = new Portfolio($db);
$categories = $portfolio->getCategories();
$items = $portfolio->getAllItems();

$page_title = "Our Portfolio";
include 'includes/header.php';
?>

<!-- Portfolio Hero Section -->
<section class="relative bg-gradient-to-br from-blue-900 to-blue-600 overflow-hidden py-32 lg:py-40">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.1)_0%,transparent_50%),radial-gradient(circle_at_80%_80%,rgba(255,255,255,0.1)_0%,transparent_50%)] opacity-50"></div>
    
    <!-- Diagonal Cut -->
    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-br from-transparent via-transparent to-white"></div>

    <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-6 leading-tight">
                Our Creative Portfolio
                <div class="w-24 h-1 bg-white/80 mx-auto mt-4 rounded"></div>
            </h1>
            
            <p class="text-xl text-white/90 mb-12 leading-relaxed">
                Explore our collection of innovative projects that demonstrate our expertise in creating exceptional digital experiences. Each project reflects our commitment to excellence and innovation.
            </p>

            <!-- Stats Section -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl border border-white/20 p-8 mb-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-white mb-2">100+</div>
                        <div class="text-white/80 uppercase tracking-wider text-sm">Projects Completed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-white mb-2">50+</div>
                        <div class="text-white/80 uppercase tracking-wider text-sm">Happy Clients</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-bold text-white mb-2">15+</div>
                        <div class="text-white/80 uppercase tracking-wider text-sm">Years Experience</div>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#portfolio-grid" class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 rounded-xl font-semibold hover:bg-blue-50 transition-all duration-300 shadow-lg hover:shadow-xl">
                    View Our Work
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
                <a href="contact.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white rounded-xl font-semibold hover:bg-white hover:text-blue-600 transition-all duration-300">
                    Start a Project
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Filter Section -->
<div class="bg-white shadow-lg rounded-xl border border-gray-100 -mt-16 mb-16 relative z-20">
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap justify-center gap-2">
            <button class="px-6 py-3 rounded-lg font-medium text-gray-700 hover:bg-blue-600 hover:text-white transition-all duration-300 active" data-filter="all">
                All Projects
            </button>
            <?php foreach ($categories as $category): ?>
                <button class="px-6 py-3 rounded-lg font-medium text-gray-700 hover:bg-blue-600 hover:text-white transition-all duration-300" data-filter="<?php echo htmlspecialchars($category['id']); ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Portfolio Grid Section -->
<section class="bg-gray-50 py-20">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($items)): ?>
                <div class="col-span-full text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Projects Available</h3>
                    <p class="text-gray-500">We're working on adding new projects to our portfolio. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="group portfolio-item" data-category="<?php echo htmlspecialchars($item['category_id']); ?>">
                        <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                            <div class="relative h-64 overflow-hidden">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? '');?>" 
                                     alt="<?php echo htmlspecialchars($item['title']?? '');?>" 
                                     class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500">
                                <div class="absolute top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg">
                                    <?php echo htmlspecialchars($item['category_name']?? '');?>" 
                                </div>
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="text-gray-600 mb-4 line-clamp-2"><?php echo htmlspecialchars($item['description']); ?></p>
                                <a href="portfolio-detail.php?id=<?php echo $item['id']; ?>" 
                                   class="inline-flex items-center text-blue-600 font-medium hover:text-blue-700 transition-colors duration-300">
                                    View Project
                                    <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Portfolio CTA Section -->
<section class="bg-white py-20 border-t border-gray-100">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold text-gray-900 mb-6">Ready to Start Your Project?</h2>
        <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
            Let's create something amazing together. Our team is ready to bring your vision to life.
        </p>
        <a href="contact.php" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl">
            Get Started
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
            </svg>
        </a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.portfolio-filters button');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filterValue = this.getAttribute('data-filter');
            
            portfolioItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    item.style.animation = 'fadeInUp 0.6s ease forwards';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 