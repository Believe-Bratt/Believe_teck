<?php
require_once 'config/config.php';
require_once 'includes/header.php';

try {
    // Get page content from database
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM page_contents WHERE page_slug = 'home' AND is_active = 1");
    $stmt->execute();
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$page) {
        throw new Exception("Home page content not found");
    }

    // Get active services with error handling
    $servicesStmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY id");
    if ($servicesStmt === false) {
        throw new Exception("Failed to fetch services");
    }
    $services = $servicesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get latest portfolio items with error handling
    $portfolioStmt = $db->query("SELECT * FROM portfolio_items WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3");
    if ($portfolioStmt === false) {
        throw new Exception("Failed to fetch portfolio items");
    }
    $portfolio = $portfolioStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Set default values for error case
    $page = ['title' => 'Welcome to Believe Teckk', 'content' => 'We are a leading technology company providing innovative solutions for businesses worldwide.'];
    $services = [];
    $portfolio = [];
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    // Set default values for error case
    $page = ['title' => 'Welcome to Believe Teckk', 'content' => 'We are a leading technology company providing innovative solutions for businesses worldwide.'];
    $services = [];
    $portfolio = [];
}
?>

<!-- Hero Section -->
<section class="relative bg-gray-900 text-white py-32">
    <div class="absolute inset-0 bg-gradient-to-r from-red-600/20 to-blue-600/20"></div>
    <div class="container mx-auto px-4 relative">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6"><?php echo htmlspecialchars($page['title'] ?? 'Welcome to Believe Teckk'); ?></h1>
            <p class="text-xl text-gray-300 mb-8"><?php echo nl2br(htmlspecialchars($page['content'] ?? 'We are a leading technology company providing innovative solutions for businesses worldwide.')); ?></p>
            <a href="#services" class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold px-8 py-3 rounded-lg transition duration-300">
                Our Services
            </a>
        </div>
    </div>
</section>

<!-- Services Section -->
<section id="services" class="py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Services</h2>
            <p class="text-xl text-gray-600">Comprehensive technology solutions for your business needs</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($services as $service): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="text-red-600 text-4xl mb-4">
                    <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($service['title']); ?></h3>
                <p class="text-gray-600"><?php echo htmlspecialchars($service['description']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Why Choose Us</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">At BelieveTeck, we are more than just a tech company—we are your trusted partner in innovation and digital transformation. Our commitment to excellence, cutting-edge technology, and client satisfaction sets us apart.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Expert Team</h3>
                <p class="text-gray-600">Our team of highly skilled professionals brings years of experience in software development, UI/UX design, digital marketing, and IT consulting. We stay ahead of industry trends to deliver top-tier solutions tailored to your business needs.</p>
            </div>
            
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Innovation</h3>
                <p class="text-gray-600">We don't just build solutions; we craft experiences. Our innovative approach ensures that every project we handle incorporates the latest technologies, trends, and creative strategies for maximum impact.</p>
            </div>
            
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Fast Delivery</h3>
                <p class="text-gray-600">Time is money, and we respect both. Our agile development process ensures that your projects are completed on time without compromising quality, helping you stay ahead in a competitive market.</p>
            </div>
            
            <div class="text-center">
                <div class="text-red-600 mb-4">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                <p class="text-gray-600">Your success is our priority. We offer round-the-clock support to ensure that your business runs smoothly with minimal downtime. Whether it's troubleshooting, updates, or consultation, we're always here to assist you.</p>
            </div>
        </div>
    </div>
</section>

<!-- Latest Projects Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Latest Projects</h2>
            <p class="text-xl text-gray-600">Check out some of our recent work</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($portfolio as $item): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php if ($item['image']): ?>
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-full h-48 object-cover">
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($item['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($item['description']); ?></p>
                    <a href="portfolio.php?slug=<?php echo htmlspecialchars($item['slug']); ?>" class="text-red-600 hover:text-red-700 font-semibold">
                        View Project →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Trusted Clients Section -->
<section class="py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-gray-900 mb-4">Our Trusted Clients</h2>
            <p class="text-xl text-gray-600">Companies that trust our expertise and services</p>
        </div>
        
        <!-- Client Logo Slider -->
        <div class="client-slider relative overflow-hidden">
            <div class="client-track flex transition-transform duration-500 ease-in-out">
                <?php
                // Get clients from database
                try {
                    $clientsStmt = $db->query("SELECT * FROM clients WHERE status = 'approved' ORDER BY created_at DESC");
                    if ($clientsStmt !== false) {
                        $clients = $clientsStmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($clients as $client): ?>
                            <div class="client-slide flex-shrink-0 w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/6 px-4">
                                <div class="flex items-center justify-center p-4 h-30 grayscale hover:grayscale-0 transition duration-300">
                                    <?php if (!empty($client['logo'])): ?>
                                        <img src="<?php echo htmlspecialchars($client['logo']); ?>" alt="<?php echo htmlspecialchars($client['name']); ?>" class="max-h-38 max-w-full">
                                    <?php else: ?>
                                        <span class="text-xl font-bold text-gray-700"><?php echo htmlspecialchars($client['name']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach;
                    }
                } catch (Exception $e) {
                    error_log("Error fetching clients: " . $e->getMessage());
                }
                ?>
            </div>
            
            <!-- Navigation Buttons -->
            <button class="client-prev absolute top-1/2 left-1 sm:left-0 transform -translate-y-1/2 bg-red-600 text-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shadow-lg z-10 focus:outline-none hover:bg-red-700 transition">
                <i class="fas fa-chevron-left text-sm sm:text-base"></i>
            </button>
            <button class="client-next absolute top-1/2 right-1 sm:right-0 transform -translate-y-1/2 bg-red-600 text-white rounded-full w-8 h-8 sm:w-10 sm:h-10 flex items-center justify-center shadow-lg z-10 focus:outline-none hover:bg-red-700 transition">
                <i class="fas fa-chevron-right text-sm sm:text-base"></i>
            </button>
            
            <!-- Dots Navigation -->
            <div class="client-dots flex justify-center mt-6 space-x-2"></div>
        </div>
    </div>
</section>

<!-- Add this JavaScript before the closing body tag or in your footer -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Client Logo Slider
    initSlider('.client-slider', '.client-track', '.client-slide', '.client-prev', '.client-next', '.client-dots');
    
    // Generic slider initialization function
    function initSlider(sliderSelector, trackSelector, slideSelector, prevSelector, nextSelector, dotsSelector) {
        const slider = document.querySelector(sliderSelector);
        if (!slider) return;
        
        const track = slider.querySelector(trackSelector);
        const slides = slider.querySelectorAll(slideSelector);
        const nextButton = slider.querySelector(nextSelector);
        const prevButton = slider.querySelector(prevSelector);
        const dotsContainer = slider.querySelector(dotsSelector);
        
        if (!track || slides.length === 0) return;
        
        let slideWidth = slides[0].getBoundingClientRect().width;
        let slideIndex = 0;
        let slidesPerView = 1;
        let touchStartX = 0;
        let touchEndX = 0;
        let autoSlideInterval;
        
        // Determine slides per view based on screen size
        function updateSlidesPerView() {
            if (window.innerWidth >= 1024) {
                slidesPerView = 6; // Large screens
            } else if (window.innerWidth >= 768) {
                slidesPerView = 4; // Medium screens
            } else if (window.innerWidth >= 640) {
                slidesPerView = 3; // Small screens
            } else {
                slidesPerView = 2; // Extra small screens
            }
            
            // Update slide width
            slideWidth = track.clientWidth / slidesPerView;
            slides.forEach(slide => {
                slide.style.width = `${slideWidth}px`;
            });
            
            // Update track position
            goToSlide(slideIndex);
            
            // Create dots
            createDots();
        }
        
        // Create navigation dots
        function createDots() {
            dotsContainer.innerHTML = '';
            const numDots = Math.ceil(slides.length / slidesPerView);
            
            for (let i = 0; i < numDots; i++) {
                const dot = document.createElement('button');
                dot.classList.add('w-3', 'h-3', 'rounded-full', 'bg-gray-300', 'hover:bg-red-600', 'transition');
                if (Math.floor(slideIndex / slidesPerView) === i) {
                    dot.classList.add('bg-red-600');
                }
                
                dot.addEventListener('click', () => {
                    goToSlide(i * slidesPerView);
                });
                
                dotsContainer.appendChild(dot);
            }
        }
        
        // Go to specific slide
        function goToSlide(index) {
            // Ensure index is within bounds
            if (index < 0) {
                slideIndex = 0;
            } else if (index > slides.length - slidesPerView) {
                slideIndex = slides.length - slidesPerView;
            } else {
                slideIndex = index;
            }
            
            // Move track
            track.style.transform = `translateX(-${slideIndex * slideWidth}px)`;
            
            // Update dots
            updateDots();
        }
        
        // Update active dot
        function updateDots() {
            const dots = dotsContainer.querySelectorAll('button');
            const activeDotIndex = Math.floor(slideIndex / slidesPerView);
            
            dots.forEach((dot, index) => {
                if (index === activeDotIndex) {
                    dot.classList.add('bg-red-600');
                    dot.classList.remove('bg-gray-300');
                } else {
                    dot.classList.remove('bg-red-600');
                    dot.classList.add('bg-gray-300');
                }
            });
        }
        
        // Next slide
        function nextSlide() {
            goToSlide(slideIndex + slidesPerView);
        }
        
        // Previous slide
        function prevSlide() {
            goToSlide(slideIndex - slidesPerView);
        }
        
        // Auto slide
        function startAutoSlide() {
            autoSlideInterval = setInterval(() => {
                if (slideIndex >= slides.length - slidesPerView) {
                    goToSlide(0); // Loop back to first slide
                } else {
                    nextSlide();
                }
            }, 4000); // Change slide every 4 seconds
        }
        
        function stopAutoSlide() {
            clearInterval(autoSlideInterval);
        }
        
        // Touch events for mobile swipe
        track.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoSlide();
        });
        
        track.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            startAutoSlide();
        });
        
        function handleSwipe() {
            const swipeThreshold = 50; // Minimum distance to register as swipe
            if (touchEndX < touchStartX - swipeThreshold) {
                // Swipe left - go to next slide
                nextSlide();
            } else if (touchEndX > touchStartX + swipeThreshold) {
                // Swipe right - go to previous slide
                prevSlide();
            }
        }
        
        // Event listeners
        nextButton.addEventListener('click', () => {
            nextSlide();
            stopAutoSlide();
            startAutoSlide();
        });
        
        prevButton.addEventListener('click', () => {
            prevSlide();
            stopAutoSlide();
            startAutoSlide();
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            updateSlidesPerView();
        });
        
        // Initialize slider
        updateSlidesPerView();
        startAutoSlide();
        
        // Pause auto-slide on hover
        track.addEventListener('mouseenter', stopAutoSlide);
        track.addEventListener('mouseleave', startAutoSlide);
    }
});
</script>

<!-- Newsletter Section -->
<?php include 'components/newsletter-form.php'; ?>

<!-- Join Our Team Section -->
<section class="py-20 bg-gray-900 text-white">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-6">Join Our Team</h2>
            <p class="text-xl text-gray-300 mb-8">We're always looking for talented individuals to join our team. Check out our current openings.</p>
            <a href="careers.php" class="inline-block bg-white text-gray-900 hover:bg-gray-100 font-semibold px-8 py-3 rounded-lg transition duration-300">
                View Careers
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>